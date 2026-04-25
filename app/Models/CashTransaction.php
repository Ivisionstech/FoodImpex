<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $guarded = [];

    public function cash()
    {
        return $this->belongsTo(Cash::class);
    }
}
