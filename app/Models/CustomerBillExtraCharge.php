<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerBillExtraCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'customer_bill_id',
        'name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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
}
