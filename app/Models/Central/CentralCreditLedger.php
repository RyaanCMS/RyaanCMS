<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class CentralCreditLedger extends Model
{
    protected $table = 'central_credit_ledger';

    protected $fillable = [
        'license_key', 'domain', 'type', 'amount',
        'balance_after', 'description', 'reference', 'recorded_at',
    ];

    protected $casts = [
        'amount'       => 'integer',
        'balance_after' => 'integer',
        'recorded_at'  => 'datetime',
    ];

    public function isDebit(): bool
    {
        return $this->amount < 0;
    }

    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

    public function license()
    {
        return $this->belongsTo(CentralLicense::class, 'license_key', 'license_key');
    }
}
