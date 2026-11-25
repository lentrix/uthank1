<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    public function debtor(): BelongsTo
    {
        return $this->belongsTo(Debtor::class);
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function getBalanceAttribute() { //balance
        return $this->paymentSchedules->sum('balance');
    }
}
