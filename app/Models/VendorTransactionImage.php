<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorTransactionImage extends Model
{
    protected $guarded = [];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function vendorTransaction()
    {
        return $this->belongsTo(VendorTransaction::class);
    }
}
