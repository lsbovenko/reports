<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TasksHistory extends Model
{
    protected $table = 'tasks_history';
    public $timestamps = FALSE;

    protected $fillable = [
        'user_id',
        'task',
        'max_date',
    ];
}
