<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreRegistration extends Model
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
        'purpose',
        'govt_id_type',
        'govt_id_number',
        'issued_at',
        'issued_on',
        'address',
        'email',
        'pin_numbers',
        'qr_token',
        'qr_expires_at',
        'pin_code',
        'has_entered_queue',
        'qr_image_path',
    ];

    protected $casts = [
        'is_pwd' => 'boolean',
        'has_entered_queue' => 'boolean',
        'qr_expires_at' => 'datetime',
        'issued_on' => 'date',
        'pin_numbers' => 'array',
    ];

    /**
     * Check if QR code is still valid
     */
    public function isQrValid(): bool
    {
        return !$this->has_entered_queue && 
               $this->qr_expires_at && 
               now()->lte($this->qr_expires_at);
    }

    /**
     * Get the full URL to the QR code image
     */
    public function getQrImageUrlAttribute(): ?string
    {
        if (!$this->qr_image_path) {
            return null;
        }
        
        return asset('storage/qrcodes/' . $this->qr_image_path);
    }

    /**
     * Mark as entered into queue
     */
    public function markAsEntered(): void
    {
        $this->update(['has_entered_queue' => true]);
    }

    /**
     * Get priority status (attribute accessor)
     */
    public function getPriorityStatusAttribute(): string
    {
        if ($this->is_pwd) {
            return 'PWD';
        }
        
        if ($this->age && $this->age >= 60) {
            return 'Senior';
        }
        
        return 'Regular';
    }

    /**
     * Get priority type - Used by QREntryController
     * This method calculates priority based on age and PWD status
     */
    public function getPriorityType(): string
    {
        if ($this->is_pwd) {
            return 'PWD';
        }
        
        if ($this->age && $this->age >= 60) {
            return 'Senior';
        }
        
        return 'Regular';
    }

    /**
     * Get PIN numbers as individual properties
     */
    public function getPinLandAttribute(): ?string
    {
        return $this->pin_numbers['land'] ?? null;
    }

    public function getPinBuildingAttribute(): ?string
    {
        return $this->pin_numbers['building'] ?? null;
    }

    public function getPinMachineryAttribute(): ?string
    {
        return $this->pin_numbers['machinery'] ?? null;
    }

    /**
     * Relationship to Queue (if converted)
     */
    public function queue()
    {
        return $this->hasOne(Queue::class, 'pre_registration_id');
    }
}