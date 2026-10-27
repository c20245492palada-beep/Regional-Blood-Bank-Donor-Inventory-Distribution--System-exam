<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospital extends Model
{
    protected $fillable = ['hospital_name'];

    public function hospitalRequests(): HasMany
    {
        return $this->hasMany(HospitalRequest::class);
    }
}
