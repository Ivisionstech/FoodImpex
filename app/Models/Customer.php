<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'person_name',
        'phone',
        'address',
        'email',
        'balance',
        'notes',
        'active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function bills()
    {
        return $this->hasMany(CustomerBill::class);
    }

    public function customerTransactions()
    {
        return $this->hasMany(CustomerTransaction::class);
    }
}
