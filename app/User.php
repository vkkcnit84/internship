<?php

namespace App;

use Carbon\Carbon;
use Hash;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use SoftDeletes, Notifiable, HasApiTokens;

    public $table = 'users';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at',
        'email_verified_at',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'institution_id',
        'college_id',
        'email_verified_at',
    ];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'user_id', 'id');
    }

    public function getEmailVerifiedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setEmailVerifiedAtAttribute($value)
    {
        $this->attributes['email_verified_at'] = $value ? Carbon::createFromFormat(config('panel.date_format') . ' ' . config('panel.time_format'), $value)->format('Y-m-d H:i:s') : null;
    }

    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Ids of the roles this user may assign. Admin (role 1) can always assign every role.
     */
    public function assignableRoleIds(): array
    {
        if ($this->isAdmin()) {
            return Role::pluck('id')->all();
        }

        return $this->roles()->with('assignableRoles')->get()
            ->flatMap(function ($role) {
                return $role->assignableRoles->pluck('id');
            })
            ->unique()->values()->all();
    }

    /**
     * A user can be managed (listed, edited, deleted) only if every role they hold is one the actor may assign.
     */
    public function canManage(User $target): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return empty(array_diff($target->roles->pluck('id')->all(), $this->assignableRoleIds()));
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class, 'user_id', 'id');
    }

    public function internshipRegistrations()
    {
        return $this->hasMany(InternshipRegistration::class, 'user_id', 'id');
    }

    public function isAdmin()
    {
        return $this->roles->contains(1);
    }

    public function isInstitution()
    {
        return $this->roles->contains(2);
    }

    public function isStudent()
    {
        return $this->roles->contains(3);
    }

    public function isStudentOnly()
    {
        return $this->isStudent() && $this->roles->count() === 1;
    }

    public function isCollegeAdmin()
    {
        return $this->roles->contains(4);
    }
}
