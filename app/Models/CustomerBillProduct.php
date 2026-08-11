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
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the bill that owns this product.
     */
    public function bill()
    {
        return $this->belongsTo(CustomerBill::class, 'customer_bill_id');
    }

    /**
     * Get the product details.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the net weight (total_weight - bardana_weight).
     */
    public function getNetWeightAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }
        
        // Auto-calculate if not set
        $totalWeight = $this->total_weight ?? 0;
        $bardanaWeight = $this->bardana_weight ?? 0;
        return max(0, $totalWeight - $bardanaWeight);
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total ?? 0, 2);
    }

    /**
     * Get formatted rate per 40kg.
     */
    public function getFormattedRateAttribute()
    {
        return number_format($this->rate_per_40kg ?? 0, 2);
    }

    /**
     * Calculate total from net weight and rate.
     */
    public function calculateTotal()
    {
        $netWeight = $this->net_weight ?? 0;
        $ratePer40kg = $this->rate_per_40kg ?? 0;
        
        if ($netWeight > 0 && $ratePer40kg > 0) {
            return ($netWeight * $ratePer40kg) / 40;
        }
        
        return $this->total ?? 0;
    }

    /**
     * Scope to filter by bill.
     */
    public function scopeByBill($query, $billId)
    {
        return $query->where('customer_bill_id', $billId);
    }

    /**
     * Scope to filter by product.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}