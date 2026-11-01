<?php

namespace App;

use App\Scopes\StreamScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stream extends Model
{
    use SoftDeletes;

    public $table = 'streams';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'college_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StreamScope);
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'stream_id', 'id');
    }

    public function studentProfiles()
    {
        return $this->hasMany(StudentProfile::class, 'stream_id', 'id');
    }

    public function getFullNameAttribute()
    {
        return $this->college ? "{$this->name} ({$this->college->full_name})" : $this->name;
    }
}
