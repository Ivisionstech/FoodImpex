<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    // Add this array to allow data saving
    protected $fillable = [
        'reference_no',
        'purchase_date',
        'description'
    ];
}
