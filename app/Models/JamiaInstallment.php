<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JamiaInstallment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'month_date' => 'date',
        'is_paid' => 'boolean',
        'is_waived' => 'boolean',
        'amount' => 'decimal:2',
    ];

    public function jamia()
    {
        return $this->belongsTo(Jamia::class, 'jamia_id');
    }

    public function share()
    {
        return $this->belongsTo(JamiaShare::class, 'share_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'paid_account_id');
    }
}
