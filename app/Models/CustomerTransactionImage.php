<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerTransactionImage extends Model
{
    protected $guarded = [];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function customerTransaction()
    {
        return $this->belongsTo(CustomerTransaction::class);
    }
}
