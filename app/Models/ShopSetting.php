<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $fillable = [
        'shop_name',
        'tagline',
        'email',
        'phone',
        'address',
        'website',
        'tax_number',
        'currency_code',
        'logo_path',
    ];
}
