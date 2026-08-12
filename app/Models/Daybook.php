<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}