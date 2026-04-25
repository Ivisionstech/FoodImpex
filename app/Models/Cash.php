<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cash extends Model
{
    protected $guarded = [];

    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class)
            ->orderBy('created_at', 'asc');  // ← Yeh line add karo
    }
    public function getBalance()
    {
        $currentBalance = 0;
        foreach ($this->cashTransactions as $transaction) {
            if ($transaction->transaction_type == 'credit') {
                $currentBalance += $transaction->amount;
            } else {
                $currentBalance -= $transaction->amount;
            }
        }

        return number_format($currentBalance, 0, '.', ',');
    }
}
