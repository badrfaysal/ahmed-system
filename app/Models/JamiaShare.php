<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JamiaShare extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payout_month' => 'date',
        'is_collected' => 'boolean',
        'is_company' => 'boolean',
        'is_transferred' => 'boolean',
        'is_claim_recovered' => 'boolean',
        'payout_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'company_claim_amount' => 'decimal:2',
        'recovered_amount' => 'decimal:2',
    ];

    public function jamia()
    {
        return $this->belongsTo(Jamia::class, 'jamia_id');
    }

    public function installments()
    {
        return $this->hasMany(JamiaInstallment::class, 'share_id');
    }

    public function collectedAccount()
    {
        return $this->belongsTo(Account::class, 'collected_account_id');
    }

    public function recoveredAccount()
    {
        return $this->belongsTo(Account::class, 'recovered_account_id');
    }
}
