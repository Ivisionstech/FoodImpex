<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'customer_id',
        'transaction_date',
        'amount',
        'type',
        'description',
        'current_balance',
        'customer_bill_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'current_balance' => 'decimal:2',
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

    public function bill()
    {
        return $this->belongsTo(CustomerBill::class, 'customer_bill_id');
    }
    public function transactionImages()
    {
        return $this->hasMany(CustomerTransactionImage::class);
    }
}
