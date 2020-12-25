<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    use HasFactory;

    protected $fillable = [
        'geofence_id',
        'device_id',
        'in_point_id',
        'current_point_id',
        'out_point_id',
        'status',
    ];
}
