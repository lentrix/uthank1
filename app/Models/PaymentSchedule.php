<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentSchedule extends Model
{
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function paidPaymentSchedules(): HasMany
    {
        return $this->hasMany(PaidPaymentSchedule::class);
    }
}
