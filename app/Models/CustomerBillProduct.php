<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerBillProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'customer_bill_id',
        'product_id',
        'description',
        'quantity',
        'packing',
        'total_weight',
        'bardana_weight',
        'net_weight',
        'price',
        'rate_per_40kg',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'packing' => 'decimal:2',
        'total_weight' => 'decimal:2',
        'bardana_weight' => 'decimal:2',
        'net_weight' => 'decimal:2',
        'price' => 'decimal:2',
        'rate_per_40kg' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function bill()
    {
        return $this->belongsTo(CustomerBill::class, 'customer_bill_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
