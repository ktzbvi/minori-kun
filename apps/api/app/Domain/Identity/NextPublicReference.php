<?php

namespace App\Domain\Identity;

use Illuminate\Support\Facades\DB;

class NextPublicReference
{
    public function next(string $kind): string
    {
        return DB::transaction(function () use ($kind): string {
            $sequence = DB::table('public_reference_sequences')->where('kind', $kind)->lockForUpdate()->first();
            if ($sequence === null) {
                throw new \RuntimeException('Public reference sequence is not configured.');
            }
            DB::table('public_reference_sequences')->where('kind', $kind)->update(['next_value' => $sequence->next_value + 1]);

            return ($kind === 'product' ? 'P-' : 'O-').str_pad((string) $sequence->next_value, 6, '0', STR_PAD_LEFT);
        });
    }
}
