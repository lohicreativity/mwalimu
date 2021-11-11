<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    /**
     * Establish many to many relationship with permissions
     */
    public function permissions()
    {
    	return $this->belongsToMany(Permission::class,'role_permission','role_id','permission_id');
    }
}
