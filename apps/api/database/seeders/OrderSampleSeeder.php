<?php

namespace Database\Seeders;

use App\Enums\ProductPublicationState;
use App\Enums\UserRole;
use App\Models\AuditEvent;
use App\Models\Order;
use App\Models\OrderDeliveryAddress;
use App\Models\OrderItem;
use App\Models\ProducerOrder;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OrderSampleSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Sample orders may only be seeded in local or testing environments.');
        }

        // FR-P-008 / SEC-002: use the same Producer selection as ProductSampleSeeder.
        $producer = User::query()->with('producerProfile')
            ->where('role', UserRole::Producer)
            ->whereHas('producerProfile', fn ($query) => $query->whereNotNull('selling_eligible_at'))
            ->orderByRaw('CASE WHEN email = ? THEN 1 ELSE 0 END', ['producer@example.test'])
            ->latest('updated_at')->firstOrFail();
        $variants = ProductVariant::query()->with('product.images')
            ->whereHas('product', fn ($query) => $query->where('producer_id', $producer->id)
                ->where('publication_state', ProductPublicationState::Published))
            ->where('is_default', true)->where('stock_quantity', '>', 0)
            ->orderBy('id')->get();
        if ($variants->isEmpty()) {
            throw new RuntimeException('Seed published, in-stock products for this Producer before sample orders.');
        }

        DB::transaction(function () use ($producer, $variants): void {
            $buyer = User::query()->firstOrCreate(
                ['email' => 'order-sample-buyer@example.test'],
                User::factory()->buyer()->make(['email' => 'order-sample-buyer@example.test'])->getAttributes(),
            );
            $samples = [
                ['received', 5], ['received', 60], ['processing', 1440], ['shipped', 4320],
            ];
            foreach ($samples as $index => [$fulfillment, $minutesAgo]) {
                // Preserve the four fixture records even when their references change.
                $seeded = AuditEvent::query()->where('action', 'sample_order.seeded')
                    ->whereIn('target_id', Order::query()->whereHas('producerOrders', fn ($query) => $query->where('producer_id', $producer->id))->select('id'))
                    ->count();
                if ($index < $seeded) {
                    continue;
                }
                $variant = $variants[$index % $variants->count()];
                $product = $variant->product;
                // One unit per order avoids the unresolved multi-item rule (TBD-009).
                $discount = (int) round($variant->price_yen * $variant->discount_bps / 10000);
                $shipping = (int) round($product->delivery_fee_honshu_yen * (10000 - $variant->discount_bps) / 10000);
                $net = $variant->price_yen - $discount;
                $placedAt = now()->subMinutes($minutesAgo);
                $order = Order::query()->create([
                    'buyer_id' => $buyer->id,
                    'order_state' => $minutesAgo < 30 ? '注文確定' : '完了',
                    'payment_state' => 'succeeded', 'refund_state' => 'none',
                    'subtotal_yen' => $variant->price_yen, 'discount_yen' => $discount,
                    'shipping_yen' => $shipping, 'total_yen' => $net + $shipping,
                    'placed_at' => $placedAt, 'cancellation_deadline_at' => $placedAt->copy()->addMinutes(30),
                ]);
                $record = ProducerOrder::query()->create([
                    'order_id' => $order->id, 'producer_id' => $producer->id,
                    'sub_order_number' => $order->order_number, 'shop_name_snapshot' => $producer->producerProfile->farm_name,
                    'fulfillment_state' => $fulfillment, 'subtotal_yen' => $net,
                    'producer_discount_yen' => $discount, 'company_commission_bps' => 1000,
                    'company_commission_yen' => (int) round($net * .1), 'total_yen' => $order->total_yen,
                ]);
                $image = $product->images->first();
                OrderItem::query()->create([
                    'producer_order_id' => $record->id, 'producer_id' => $producer->id,
                    'product_id' => $product->id, 'variant_id' => $variant->id,
                    'product_name_snapshot' => $product->name, 'variant_label_snapshot' => $variant->option_label,
                    'unit_price_yen' => $variant->price_yen, 'discount_bps' => $variant->discount_bps,
                    'discount_yen' => $discount, 'quantity' => 1, 'line_total_yen' => $net,
                    'image_url_snapshot' => $image ? Storage::disk($image->disk)->url($image->object_path) : null,
                ]);
                OrderDeliveryAddress::query()->create([
                    'order_id' => $order->id, 'recipient_name' => '注文サンプル購入者',
                    'phone' => '090-0000-0000', 'postal_code' => '000-0000',
                    'prefecture' => '東京都', 'city' => 'サンプル市', 'address_line1' => 'サンプル住所1-1',
                ]);
                AuditEvent::query()->create([
                    'target_type' => 'order', 'target_id' => $order->id,
                    'action' => 'sample_order.seeded', 'result' => 'success',
                    'reason' => 'Local sample fixture; no provider payment or email was sent.',
                    'occurred_at' => now(),
                ]);
            }
        });
        $this->command?->info('Sample orders are available for Producer '.$producer->id.'.');
    }
}
