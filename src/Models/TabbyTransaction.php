<?php

namespace Aghfatehi\Tabby\Models;

use Illuminate\Database\Eloquent\Model;

class TabbyTransaction extends Model
{
    protected $fillable = [
        'tabby_payment_id',
        'tabby_session_id',
        'amount',
        'currency',
        'status',
        'payment_type',
        'request_payload',
        'response_payload',
        'error_message',
        'billable_id',
        'billable_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_payload' => 'json',
        'response_payload' => 'json',
    ];

    public function billable()
    {
        return $this->morphTo();
    }
}
