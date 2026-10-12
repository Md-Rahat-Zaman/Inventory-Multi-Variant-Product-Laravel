<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = [
         'name',
            'sku',
            'barcode',
            'category_id',
            'brand_id',
            'unit_id',
            'purchase_price',
            'sale_price',
            'tax_rate',
            'opening_stock',
            'stock',
            'minimum_stock',
            'status',
            'note',
            'description' 
        //database a ar bahire kono kisu insert a nibe na

    ];

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class,'unit_id');
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    
}
