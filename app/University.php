<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class University extends Model
{
    use SoftDeletes;

    public $table = 'universities';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function colleges()
    {
        return $this->hasMany(College::class, 'university_id', 'id');
    }

    public function studentProfiles()
    {
        return $this->hasMany(StudentProfile::class, 'university_id', 'id');
    }
}
