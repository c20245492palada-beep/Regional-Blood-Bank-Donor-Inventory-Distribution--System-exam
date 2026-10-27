<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionLog extends Model
{
    protected $fillable = [
        'blood_bag_id',
        'hospital_request_id',
        'units_issued',
        'processing_fee',
        'status',
        'issued_at',
    ];

    protected $casts = [
        'units_issued' => 'integer',
        'processing_fee' => 'decimal:2',
        'issued_at' => 'datetime',
    ];

    public function bloodBag(): BelongsTo
    {
        return $this->belongsTo(BloodBag::class);
    }

    public function hospitalRequest(): BelongsTo
    {
        return $this->belongsTo(HospitalRequest::class);
    }
}
