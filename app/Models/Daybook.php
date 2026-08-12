<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Daybook extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'transaction_date',
        'expense_date',
        'amount',
        'in_hand',
        'status',
        'type',
        'approval_status',
        'description',
        'reference',
        'customer_transaction_id',
        'vendor_transaction_id',
        'expense_id',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'expense_date' => 'datetime',
        'amount' => 'decimal:2',
        'in_hand' => 'decimal:2',
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

    public function customerTransaction()
    {
        return $this->belongsTo(CustomerTransaction::class, 'customer_transaction_id');
    }

    public function vendorTransaction()
    {
        return $this->belongsTo(VendorTransaction::class, 'vendor_transaction_id');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}