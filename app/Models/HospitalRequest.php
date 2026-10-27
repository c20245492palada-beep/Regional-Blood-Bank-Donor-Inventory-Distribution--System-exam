<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalRequest extends Model
{
    protected $fillable = [
        'hospital_id',
        'request_date',
        'blood_type',
        'rh_factor',
        'units_requested',
    ];

    protected $casts = [
        'request_date' => 'date',
        'units_requested' => 'integer',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function distributionLogs(): HasMany
    {
        return $this->hasMany(DistributionLog::class);
    }
}
