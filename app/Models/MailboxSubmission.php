<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailboxSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type',
        'applicant_type',
        'number_of_copies',
        'full_name',
        'age',
        'is_pwd',
        'pwd_id',
        'pin_land',
        'pin_building',
        'pin_machinery',
        'purpose',
        'govt_id_type',
        'govt_id_number',
        'issued_at',
        'issued_on',
        'address',
        'email',
        'pin_code',
        'qr_token',
        'qr_expires_at',
        'qr_image_path',
        'has_entered_queue',
        'pre_registration_id',
        'status',
        'submitted_at',
        'approved_at',
        'disapproved_at',
        'collected_at'
    ];

    protected $casts = [
        'issued_on' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'disapproved_at' => 'datetime',
        'collected_at' => 'datetime',
        'qr_expires_at' => 'datetime',
        'number_of_copies' => 'integer',
        'age' => 'integer',
        'is_pwd' => 'boolean',
        'has_entered_queue' => 'boolean'
    ];

    /**
     * Relationship with PreRegistration
     */
    public function preRegistration()
    {
        return $this->belongsTo(PreRegistration::class);
    }

    /**
     * Generate a unique 6-digit PIN code
     */
    public static function generateUniquePinCode(): string
    {
        do {
            $pinCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('pin_code', $pinCode)->exists());

        return $pinCode;
    }

    /**
     * Get the fee amount based on service and applicant type
     */
    public function getFeeAmount(): float
    {
        $fees = [
            'tax_declaration' => ['owner' => 50.00, 'representative' => 100.00],
            'no_improvement' => ['owner' => 50.00, 'representative' => 100.00],
            'property_holdings' => ['owner' => 50.00, 'representative' => 100.00],
            'non_property_holdings' => ['owner' => 70.00, 'representative' => 120.00],
        ];

        return $fees[$this->service_type][$this->applicant_type] ?? 0.00;
    }

    /**
     * Get formatted service type name
     */
    public function getServiceTypeNameAttribute(): string
    {
        $names = [
            'tax_declaration' => 'Certified True Copy of Tax Declaration (TD)',
            'no_improvement' => 'Certification of No Improvement',
            'property_holdings' => 'Certification of Property Holdings',
            'non_property_holdings' => 'Certification of Non-property Holdings',
        ];

        return $names[$this->service_type] ?? $this->service_type;
    }
}