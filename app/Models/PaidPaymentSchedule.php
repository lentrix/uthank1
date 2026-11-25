<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaidPaymentSchedule extends Model
{

    public function payment() {
        return $this->belongsTo(Payment::class);
    }

    public function paymentSchedule() {
        return $this->belongsTo(PaymentSchedule::class);
    }
}
