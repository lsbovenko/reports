<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $date
 * @property integer $worked_minutes
 * @property string $description
 * @property Project $project
 * @property User $user
 */
class Report extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'project_id', 'date', 'worked_minutes', 'description', 'is_tracked'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('App\Models\Project');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
