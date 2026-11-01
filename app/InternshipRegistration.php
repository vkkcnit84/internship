<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\InternshipRegistrationScope;

class InternshipRegistration extends Model
{
    public $table = 'internship_registrations';

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new InternshipRegistrationScope());
    }

    protected $dates = [
        'created_at',
        'updated_at',
        'paid_at',
    ];

    protected $fillable = [
        'user_id',
        'internship_id',
        'student_profile_id',
        'price',
        'tax_percent',
        'tax_amount',
        'total_amount',
        'status',
        'paid_at',
        'certificate_number',
        'payment_method',
        'approval_status',
    ];

    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_PAID            = 'paid';
    const STATUS_FAILED          = 'failed';

    const STATUS_SELECT = [
        self::STATUS_PENDING_PAYMENT => 'Pending',
        self::STATUS_PAID            => 'Paid',
        self::STATUS_FAILED          => 'Failed',
    ];

    const PAYMENT_METHOD_ONLINE  = 'online';
    const PAYMENT_METHOD_OFFLINE = 'offline';

    const PAYMENT_METHODS = [
        self::PAYMENT_METHOD_ONLINE  => 'Online',
        self::PAYMENT_METHOD_OFFLINE => 'Offline',
    ];

    const APPROVAL_PENDING   = 'pending';
    const APPROVAL_APPROVED  = 'approved';
    const APPROVAL_REJECTED  = 'rejected';

    const APPROVAL_STATUSES = [
        self::APPROVAL_PENDING   => 'Pending',
        self::APPROVAL_APPROVED  => 'Approved',
        self::APPROVAL_REJECTED  => 'Rejected',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id');
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'internship_registration_id', 'id');
    }

    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    public function getAmountPaise()
    {
        return (int) round($this->total_amount * 100);
    }
}
