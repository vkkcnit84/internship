<?php

namespace App;

use App\Scopes\SubjectScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes;

    public $table = 'subjects';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'stream_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new SubjectScope);
    }

    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }

    public function studentProfiles()
    {
        return $this->hasMany(StudentProfile::class, 'subject_id', 'id');
    }

    public function getFullNameAttribute()
    {
        return $this->stream ? "{$this->name} ({$this->stream->full_name})" : $this->name;
    }
}
