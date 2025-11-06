<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KioskEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'age',
        'is_pwd',
        'pwd_id',
        'applicant_type',
        'service_type',
        'number_of_copies',
        'pin_land',
        'pin_building',
        'pin_machinery',
        'purpose',
        'address',
        'govt_id_type',
        'govt_id_number',
        'issued_at',
        'issued_on',
        'status',
        'queue_id',
        'priority_type'
    ];

    protected $casts = [
        'is_pwd' => 'boolean',
        'number_of_copies' => 'integer',
        'age' => 'integer',
        'issued_on' => 'date'
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class, 'queue_id');
    }

    public function getServiceTypeDisplayAttribute(): string
    {
        $types = [
            'tax_declaration' => 'Certified True Copy of Tax Declaration',
            'no_improvement' => 'Certification of No Improvement',
            'property_holdings' => 'Certification of Property Holdings',
            'non_property_holdings' => 'Certification of Non-property Holdings'
        ];

        return $types[$this->service_type] ?? $this->service_type;
    }
}