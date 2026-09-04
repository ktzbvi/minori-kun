<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends DomainModel
{
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }
}
