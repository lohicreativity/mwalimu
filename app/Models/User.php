<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

use App\Domain\Registration\Models\Student;
use App\Domain\HumanResources\Models\Staff;
use App\Domain\Application\Models\Applicant;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'email', 'password', 'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function isEmployee(): bool
    {
        return $this->staff()->exists();
    }

    public function isActive(): bool
    {
        return $this->status == 'ACTIVE';
    }

    public function isNotActive(): bool
    {
        return !$this->isActive();
    }

    public function lockAccount()
    {
        $this->update(['status' => 'INACTIVE']);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function authorizeRoles($roles)
    {
      if ($this->hasAnyRole($roles)) {
        return true;
      }
      abort(401, 'This action is unauthorized.');
    }

    public function hasAnyRole($roles)
    {
          if (is_array($roles)) {
            foreach ($roles as $role) {
              if ($this->hasRole($role)) {
                return true;
              }
            }
          } else {
            if ($this->hasRole($roles)) {
              return true;
            }
          }
          return false;
    }

    public function hasRole($role)
    {
          if ($this->roles()->where('name', $role)->first()) {
            return true;
          }
          return false;
    }

    public function hasPermissionTo($permission)
    {
          return $this->hasPermissionThroughRole($permission);
    }

    public function hasPermissionThroughRole($permission)
    {
          foreach ($permission->roles as $role){
            if($this->roles->contains($role)) {
              return true;
            }
          }
          return false;
    }

    /**
     * Establish one to one relationship with students
     */
    public function student()
    {
        return $this->hasOne(Student::class,'user_id');
    }

    /**
     * Establish one to one relationship with staffs
     */
    public function staff()
    {
        return $this->hasOne(Staff::class,'user_id');
    }

    /**
     * Establish one to one relationship with staffs
     */
    public function applicants()
    {
        return $this->hasMany(Applicant::class,'user_id');
    }
}
