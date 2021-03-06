<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $name
 * @property int $is_active
 * @property int $is_fixed_price
 * @property int $parent_id
 * @property int $rate
 * @property Project $parent
 * @property Report[] $reports
 * @property Project[] $children
 */
class Project extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'last_used', 'rate', 'is_active', 'parent_id', 'is_fixed_price'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reports()
    {
        return $this->hasMany('App\Models\Report');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany('App\Models\Project', 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo('App\Models\Project', 'parent_id', 'id');
    }

    public static function allActiveRelatedToUser(User $user)
    {
        $query = static::query();
        $query
            ->select('projects.*')
            ->join('reports', 'reports.project_id', '=', 'projects.id')
            ->where('reports.user_id', $user->id)
            ->where('projects.is_active', 1)
            ->groupBy('projects.id');

        return $query;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        if ($this->parent_id) {
            return $this->parent->name . ' - ' . $this->name;
        }
        return $this->name;
    }
}
