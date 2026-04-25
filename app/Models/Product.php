<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * * Added: net_weight, price_40kg
     * Updated: stock (ensuring it handles decimal weights)
     */
    protected $fillable = [
        'uuid',
        'name',
        'vendor_id',
        'net_weight',    // Added for weight-based tracking
        'price_40kg',    // Added for your specific pricing logic
        'purchase_price',
        'sale_price',
        'stock',         // This will now represent Total Net Weight in Stock
        'description',
        'image',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'net_weight' => 'decimal:2',
        'price_40kg' => 'decimal:2',
        'stock' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    /**
     * Boot function to generate UUID automatically on creation.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relationship with the Vendor.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Relationship with Bill Products.
     */
    public function billProducts()
    {
        return $this->hasMany(BillProduct::class);
    }
}