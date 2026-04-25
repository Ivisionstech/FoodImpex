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
        'total_amount' => 'decimal:2', // This is your database column
        'paid_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function billProducts()
    {
        return $this->hasMany(CustomerBillProduct::class);
    }

    public function items() // Alias for billProducts
    {
        return $this->hasMany(CustomerBillProduct::class);
    }

    public function extraCharges()
    {
        return $this->hasMany(CustomerBillExtraCharge::class);
    }

    public function transactions()
    {
        return $this->hasMany(CustomerTransaction::class);
    }
}