<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <style>
        body { color: #26362c; font-family: notosansjp; font-size: 10pt; }
        .brand { color: #237f4b; font-size: 12pt; font-weight: bold; }
        .title { font-size: 25pt; font-weight: bold; text-align: right; }
        .muted { color: #68786e; font-size: 8pt; }
        .amount { color: #237f4b; font-size: 23pt; font-weight: bold; text-align: right; }
        .right { text-align: right; }
        .center { text-align: center; }
        .section-title { font-size: 12pt; font-weight: bold; }
        .header-cell { background-color: #f1f6f2; color: #68786e; font-weight: bold; }
        .info { background-color: #f1f6f2; }
        .total { border-top: 1px solid #dce5dc; font-size: 11pt; font-weight: bold; }
        .issuer-note { color: #68786e; font-size: 7pt; }
        .footer { color: #68786e; font-size: 8pt; text-align: center; }
    </style>
</head>
<body>
<table cellpadding="4" width="100%">
    <tr>
        <td width="48%" class="brand">● {{ $issuer['name'] }}</td>
        <td width="52%" class="right">
            <div class="title">領収書</div>
            <div class="muted">領収書番号：{{ $receiptNumber }}</div>
            <div class="muted">発行日：{{ $issuedAt->timezone('Asia/Tokyo')->format('Y年m月d日') }}</div>
        </td>
    </tr>
</table>
<br>
<table cellpadding="7" border="0" width="100%">
    <tr>
        <td width="56%">
            <div><strong>{{ $order->deliveryAddress?->recipient_name ?? 'お客様' }} 様</strong></div>
            <div class="muted">但し：商品購入代金として</div>
            <div class="muted">注文番号 {{ $order->order_number }}</div>
        </td>
        <td width="44%" class="right">
            <div class="muted"><strong>領収金額（税込・送料込み）</strong></div>
            <div class="amount">{{ number_format($order->total_yen) }}円</div>
            <div class="muted">上記金額を正に領収いたしました。</div>
        </td>
    </tr>
</table>
<br>
<div class="section-title">ご注文内容</div>
<table cellpadding="5" cellspacing="0" border="0" width="100%">
    <thead>
    <tr class="header-cell" style="border-bottom: 1px solid #dce5dc;">
        <th width="52%">商品</th>
        <th width="10%" class="center">数量</th>
        <th width="19%" class="right">単価（税込）</th>
        <th width="19%" class="right">金額（税込）</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($producerOrder->items as $item)
        <tr style="border-bottom: 1px solid #dce5dc;">
            <td width="52%">{{ $item->product_name_snapshot }}</td>
            <td width="10%" class="center">{{ $item->quantity }}</td>
            <td width="19%" class="right">{{ number_format($item->unit_price_yen) }}円</td>
            <td width="19%" class="right">{{ number_format($item->line_total_yen) }}円</td>
        </tr>
    @endforeach
    </tbody>
</table>
<table cellpadding="4" width="100%">
    <tr>
        <td width="50%"></td>
        <td width="32%">商品小計（税込・送料込み）</td>
        <td width="18%" class="right">{{ number_format($order->subtotal_yen) }}円</td>
    </tr>
    @if ($order->discount_yen > 0)
        <tr>
            <td width="50%"></td><td width="32%">商品割引</td><td width="18%" class="right">-{{ number_format($order->discount_yen) }}円</td>
        </tr>
    @endif
    @if ($order->shipping_yen > 0)
        <tr>
            <td width="50%"></td><td width="32%">送料</td><td width="18%" class="right">{{ number_format($order->shipping_yen) }}円</td>
        </tr>
    @endif
    <tr class="total">
        <td width="50%"></td><td width="32%">領収金額（税込・送料込み）</td><td width="18%" class="right">{{ number_format($order->total_yen) }}円</td>
    </tr>
</table>
<br>
<table cellpadding="8" width="100%" class="info">
    <tr>
        <td width="50%"><span class="muted">支払方法</span><br>ローカルテスト決済</td>
        <td width="50%"><span class="muted">決済日時</span><br>{{ \Illuminate\Support\Carbon::parse($paidAt)->timezone('Asia/Tokyo')->format('Y/m/d H:i') }}</td>
    </tr>
</table>
<br>
<table cellpadding="8" width="100%" class="info">
    <tr>
        <td>
            <span class="muted">発行者</span><br>
            <strong>{{ $issuer['name'] }}</strong><br>
            〒{{ $issuer['postal_code'] }} {{ $issuer['address'] }}<br>
            <span class="issuer-note">発行者住所は開発用の仮データです。</span>
        </td>
    </tr>
</table>
<br><br>
<div class="footer">本領収書は注文 {{ $order->order_number }} に対して発行されたものです。</div>
<div class="footer">お問い合わせは注文詳細からご連絡ください。</div>
</body>
</html>
