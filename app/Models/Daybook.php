<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Daybook extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_date',
        'amount',
        'status',
        'type',
        'credit_type',
        'credit_id',
        'debit_type',
        'debit_id',
        'approval_status',
        'description',
        'reference',
        'customer_transaction_id',
        'vendor_transaction_id',
        'expense_id',
    ];
}