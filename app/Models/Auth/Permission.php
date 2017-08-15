<?php

namespace App\Models\Auth;

use Zizaco\Entrust\EntrustPermission;

/**
 * App\Models\Auth\Permission
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Auth\Role[] $roles
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Auth\Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Auth\Permission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Auth\Permission whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Auth\Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Auth\Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Auth\Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Permission extends EntrustPermission
{

}
