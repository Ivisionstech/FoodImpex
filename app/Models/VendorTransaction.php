<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorTransaction extends Model
{
    protected $guarded = [];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
    public function vendorTransactionImages()
    {
        return $this->hasMany(VendorTransactionImage::class);
    }
    public function bankTransaction()
    {
        return $this->hasOne(BankTransaction::class);
    }
    public function cashTransaction()
    {
        return $this->hasOne(CashTransaction::class);
    }
}
