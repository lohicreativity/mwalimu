<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Permission;

class SystemModule extends Model
{
    use HasFactory;

    protected $table = 'system_modules';

    /**
     * Establish one to many relationship with permissions
     */
    public function permissions()
    {
    	return $this->hasMany(Permission::class,'system_module_id');
    }
}
