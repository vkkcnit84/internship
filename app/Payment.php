<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $table = 'payments';

    protected $dates = [
        'created_at',
        'updated_at',
        'paid_at',
    ];

    protected $fillable = [
        'internship_registration_id',
        'gateway',
        'gateway_order_id',
        'gateway_payment_id',
        'amount_paise',
        'currency',
        'status',
        'paid_at',
        'gateway_response',
    ];

    const STATUS_CREATED = 'created';
    const STATUS_PAID    = 'paid';
    const STATUS_FAILED  = 'failed';

    public function registration()
    {
        return $this->belongsTo(InternshipRegistration::class, 'internship_registration_id');
    }
}
