<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RolePermission extends Pivot
{
    protected $table = 'role_permission';
    protected $fillable = ['role_id', 'permission_id', 'allowed'];
    public $timestamps = true;
}
