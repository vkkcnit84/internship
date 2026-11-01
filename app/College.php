<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class College extends Model
{
    use SoftDeletes;

    public $table = 'colleges';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'logo',
        'university_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'university_id');
    }

    public function streams()
    {
        return $this->hasMany(Stream::class, 'college_id', 'id');
    }

    public function studentProfiles()
    {
        return $this->hasMany(StudentProfile::class, 'college_id', 'id');
    }

    public function collegeAdmins()
    {
        return $this->hasMany(User::class, 'college_id', 'id');
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function getFullNameAttribute()
    {
        return $this->university ? "{$this->name} ({$this->university->name})" : $this->name;
    }
}
