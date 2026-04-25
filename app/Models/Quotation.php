<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $guarded = [];
    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }
}
