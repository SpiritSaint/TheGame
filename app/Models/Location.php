<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory, SpatialTrait;

    protected $fillable = [
        'point'
    ];

    protected $spatialFields = [
        'point'
    ];
}
