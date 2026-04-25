<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $guarded = [];
    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class);
    }
}
