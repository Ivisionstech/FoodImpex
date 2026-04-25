<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Daybook extends Model
{
    protected $guarded = [];
    
    protected $table = 'daybooks';
    
    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function vendorTransaction()
    {
        return $this->belongsTo(VendorTransaction::class, 'vendor_transaction_id');
    }
    
    public function customerTransaction()
    {
        return $this->belongsTo(CustomerTransaction::class, 'customer_transaction_id');
    }
    
    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }
}