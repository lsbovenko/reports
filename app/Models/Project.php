<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $name
 * @property Report[] $reports
 */
class Project extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'last_used'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reports()
    {
        return $this->hasMany('App\Models\Report');
    }

    public static function allRelatedToUser(User $user)
    {
        $query = static::query();
        $query
            ->select('projects.*')
            ->join('reports', 'reports.project_id', '=', 'projects.id')
            ->where('reports.user_id', $user->id)
            ->groupBy('projects.id');

        return $query;
    }
}
