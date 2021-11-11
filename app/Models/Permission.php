<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';

    /**
     * Establish one to many relationship with system modules
     */
    public function systemModule()
    {
    	return $this->belongsTo(SystemModule::class,'system_module_id');
    }

    /**
     * Establish many to many relationship with roles
     */
    public function roles()
    {
    	return $this->belongsToMany(Role::class,'role_permission','permission_id','role_id');
    }
}
