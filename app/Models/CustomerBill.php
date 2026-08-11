<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CustomerBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'bill_date' => 'datetime',
        'grand_total' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'profit' => 'decimal:2',
        'paid_amount' => 'decimal:2',
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
     * Get the customer that owns the bill.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the products for this bill.
     */
    public function billProducts()
    {
        return $this->hasMany(CustomerBillProduct::class, 'customer_bill_id');
    }

    /**
     * Alias for billProducts() for compatibility.
     */
    public function items()
    {
        return $this->hasMany(CustomerBillProduct::class, 'customer_bill_id');
    }

    /**
     * Get the extra charges for this bill.
     */
    public function extraCharges()
    {
        return $this->hasMany(CustomerBillExtraCharge::class, 'customer_bill_id');
    }

    /**
     * Get the transactions for this bill.
     */
    public function transactions()
    {
        return $this->hasMany(CustomerTransaction::class, 'customer_bill_id');
    }

    /**
     * Get the total amount with proper formatting.
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount ?? $this->grand_total ?? 0, 2);
    }

    /**
     * Get the grand total with proper formatting.
     */
    public function getFormattedGrandTotalAttribute()
    {
        return number_format($this->grand_total ?? $this->total_amount ?? 0, 2);
    }

    /**
     * Check if bill is approved.
     */
    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Check if bill is pending.
     */
    public function isPending()
    {
        return $this->approval_status === 'pending';
    }

    /**
     * Calculate subtotal from products.
     */
    public function getSubtotalAttribute()
    {
        return $this->billProducts()->sum('total');
    }

    /**
     * Calculate extra charges total.
     */
    public function getExtraChargesTotalAttribute()
    {
        return $this->extraCharges()->sum('amount');
    }

    /**
     * Calculate grand total (subtotal - extra charges).
     */
    public function getCalculatedGrandTotalAttribute()
    {
        return max(0, $this->subtotal - $this->extra_charges_total);
    }
}