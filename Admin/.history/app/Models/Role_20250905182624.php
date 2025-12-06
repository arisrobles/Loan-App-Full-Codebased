<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'slug'];

    public function users()
    {
        // If your users table has role_id; otherwise use belongsToMany
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
                    ->withPivot('allowed')
                    ->withTimestamps();
    }

    public function allows(string $permissionKey): bool
    {
        $perm = $this->permissions()->where('key', $permissionKey)->first();
        return (bool) optional($perm)->pivot->allowed;
    }
}
