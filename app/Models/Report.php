<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property int $is_tracked
 * @property int $is_meeting
 * * @property int $is_overtime
 * @property string $created_at
 * @property string $updated_at
 * @property string $date
 * @property string $task
 * @property integer $worked_minutes
 * @property string $description
 * @property Project $project
 * @property Auth\User $user
 */
class Report extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'task',
        'date',
        'worked_minutes',
        'is_meeting',
        'description',
        'is_tracked',
        'is_overtime',
    ];

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
        return $this->belongsTo('App\Models\Auth\User');
    }

    /**
     * @param User $user
     * @param bool $groupByProject
     * @return $this
     */
    public static function findLatestTracked(User $user, bool $groupByProject = true)
    {
        $query = static::where('is_tracked', 1)
            ->where('user_id', $user->id)
            ->where('created_at', function(Builder $query) use($user){
                // we don't use limit because there can be more 1 items
                $query->selectRaw('MAX(created_at)')
                    ->from('reports')
                    ->where('is_tracked', 1)
                    ->where('user_id', $user->id);
            });

        if ($groupByProject) {
            $query->groupBy('project_id');
        }

        return $query;
    }
}
