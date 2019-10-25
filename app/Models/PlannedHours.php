<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlannedHours extends Model
{
    protected $fillable = ['year', 'month', 'planned_hours'];
}
