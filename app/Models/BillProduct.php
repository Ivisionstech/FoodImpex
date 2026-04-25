<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillProduct extends Model
{
    protected $fillable = [
        'uuid',
        'bill_id',
        'product_id',
        'quantity',
        'packing',
        'total_weight',
        'bardana_weight',
        'net_weight',
        'price',
        'total_price',
        'type',
        'description'
    ];
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
