<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bill extends Model
{
    protected $fillable = [
        'uuid',
        'date',
        'payment_terms',
        'total_amount',
        'status',          // Added status field (pending/completed etc)
        'purchase_id',     // Added purchase_id to link with the Purchases table
        'vendor_id',
        'status',
        'extra_amount_1',
        'extra_amount_2',
        'extra_amount_3',
    ];

    /**
     * Relationship: Bill belongs to a Vendor
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Relationship: Bill belongs to a Purchase record
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Relationship: Bill has many products/items
     */
    public function billProducts(): HasMany
    {
        return $this->hasMany(BillProduct::class);
    }

    /**
     * Relationship: Bill has one transaction entry in the vendor ledger
     */
    public function vendorTransaction(): HasOne
    {
        return $this->hasOne(VendorTransaction::class);
    }

    /**
     * Relationship: Bill has many dynamic extra charges
     */
    public function extraCharges(): HasMany
    {
        return $this->hasMany(BillExtraCharge::class);
    }

    /**
     * Relationship: Bill has many dynamic additional charges (to be added)
     */
    public function additionalCharges(): HasMany
    {
        return $this->hasMany(BillAdditionalCharge::class);
    }
}
