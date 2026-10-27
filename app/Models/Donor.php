<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donor extends Model
{
    protected $fillable = [
        'donor_national_id',
        'donor_name',
        'blood_type',
        'rh_factor',
    ];

    public function bloodBags(): HasMany
    {
        return $this->hasMany(BloodBag::class);
    }
}
