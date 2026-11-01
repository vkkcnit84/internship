<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentProfile extends Model
{
    use SoftDeletes;

    public $table = 'student_profiles';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'user_id',
        'mobile',
        'whatsapp',
        'guardian_name',
        'gender',
        'university_id',
        'college_id',
        'stream_id',
        'subject_id',
        'class_semester',
        'year_of_study',
        'registration_number',
        'roll_number',
        'academic_session',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    const GENDER_SELECT = [
        'male'   => 'Male',
        'female' => 'Female',
        'other'  => 'Other',
    ];

    const YEAR_OF_STUDY_SELECT = [
        '1st Year' => '1st Year',
        '2nd Year' => '2nd Year',
        '3rd Year' => '3rd Year',
        '4th Year' => '4th Year',
        '5th Year' => '5th Year',
    ];

    public static function classSemesterOptions()
    {
        $options = [];

        foreach (range(1, 10) as $semester) {
            $options["Semester $semester"] = "Semester $semester";
        }

        return $options;
    }

    /**
     * Sessions such as "2025-26", from two years back to next year.
     */
    public static function academicSessionOptions()
    {
        $options = [];
        $year    = (int) date('Y');

        foreach (range($year - 2, $year + 1) as $start) {
            $session           = $start . '-' . substr($start + 1, -2);
            $options[$session] = $session;
        }

        return $options;
    }

    /**
     * Digits for wa.me (country code 91 + 10-digit Indian number), or null if unusable.
     * Uses the WhatsApp number, falling back to the mobile number.
     */
    public function whatsappPhone(): ?string
    {
        foreach ([$this->whatsapp, $this->mobile] as $number) {
            $digits = preg_replace('/\D+/', '', (string) $number);
            $digits = preg_replace('/^(91|0)(?=\d{10}$)/', '', $digits);

            if (preg_match('/^[6-9]\d{9}$/', $digits)) {
                return '91' . $digits;
            }
        }

        return null;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'university_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
