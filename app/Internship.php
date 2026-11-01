<?php

namespace App;

use App\Scopes\InternshipScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Internship extends Model
{
    use SoftDeletes;

    public $table = 'internships';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new InternshipScope);
    }

    protected $fillable = [
        'name',
        'duration_weeks',
        'price',
        'tax_percent',
        'description',
        'is_active',
        'college_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function registrations()
    {
        return $this->hasMany(InternshipRegistration::class, 'internship_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTaxAmount()
    {
        return round($this->price * $this->tax_percent / 100, 2);
    }

    public function getTotalAmount()
    {
        return round($this->price + $this->getTaxAmount(), 2);
    }
}
