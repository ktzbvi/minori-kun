<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerSetting extends Model
{
    protected $primaryKey = 'singleton_id';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }
}
