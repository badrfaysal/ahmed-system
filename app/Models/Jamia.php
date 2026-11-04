<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jamia extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'total_amount' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
    ];

    public function shares()
    {
        return $this->hasMany(JamiaShare::class, 'jamia_id');
    }

    public function installments()
    {
        return $this->hasMany(JamiaInstallment::class, 'jamia_id');
    }
}
