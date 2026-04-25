<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'company_name',
        'person_name',
        'email',
        'phone',
        'profile',
        'address',
        'balance',
        'active',
    ];

    protected $appends = ['profile_url'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vendor) {
            $vendor->uuid = (string) Str::uuid();
        });

        // Delete the old profile picture when a new one is uploaded
        static::updating(function ($vendor) {
            if ($vendor->isDirty('profile') && $vendor->getOriginal('profile')) {
                Storage::disk('public')->delete($vendor->getOriginal('profile'));
            }
        });

        // Delete the profile picture when the vendor is deleted
        static::deleting(function ($vendor) {
            if ($vendor->profile) {
                Storage::disk('public')->delete($vendor->profile);
            }
        });
    }

    public function getProfileUrlAttribute(): string
    {
        return $this->profile
            ? Storage::disk('public')->url($this->profile)
            : url('/images/default-profile.png');
    }

    public function vendorTransactions()
    {
        return $this->hasMany(VendorTransaction::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
