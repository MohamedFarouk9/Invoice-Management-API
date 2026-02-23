<?php

namespace App\Models;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'unit_name',
        'customer_name',
        'rent_amount',
        'start_date',
        'end_date',
        'status',
    ];

 protected function casts(): array
    {
        return [
            'status' => ContractStatus::class,
            'rent_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function invoices() {
        return $this->hasMany(Invoice::class);  
    }
}
