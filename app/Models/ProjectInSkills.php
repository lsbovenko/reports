<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

/**
 * all projects which we send to skills.ikantam
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property Project $project
 * @property Auth\User $user
 */
class ProjectInSkills extends Model
{
    protected $table = 'projects_in_skills';

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
     * @param Project $project
     * @return $this
     */
    public static function findOneByUserByProject(User $user, Project $project)
    {
        return static::where('project_id', '=', $project->id)
            ->where('user_id', '=', $user->id)
            ->first();
    }
}
