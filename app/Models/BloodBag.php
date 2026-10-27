<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BloodBag extends Model
{
    protected $fillable = [
        'donor_id',
        'blood_bag_code',
        'donation_date',
    ];

    protected $casts = [
        'donation_date' => 'date',
    ];

    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }

    public function distributionLog(): HasOne
    {
        return $this->hasOne(DistributionLog::class);
    }
}
