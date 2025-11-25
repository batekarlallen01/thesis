<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        // Basic info
        'service_type',
        'applicant_type',
        'number_of_copies',
        'full_name',
        'age',
        'is_pwd',
        'pwd_id',
        'purpose',
        'address',
        'email',
        
        // Document uploads
        'owner_id_image',
        'spa_image',
        'rep_id_image',
        'tax_decl_form',
        'title',
        'tax_payment',
        'latest_tax_decl',
        'deed_of_sale',
        'transfer_tax_receipt',
        'car_from_bir',
        
        // Status and Review
        'status',
        'disapproval_reasons',
        'disapproval_other_reason',
        'reviewed_by',
        'reviewed_at',
        'approved_at',
        'disapproved_at',
        
        // QR and Queue
        'qr_token',
        'qr_expires_at',
        'pin_code',
        'qr_image_path',
        'has_entered_queue',
        'pin_numbers',
    ];

    protected $casts = [
        'is_pwd' => 'boolean',
        'has_entered_queue' => 'boolean',
        'qr_expires_at' => 'datetime',
        'pin_numbers' => 'array',
        'disapproval_reasons' => 'array',
        'approved_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'disapproved_at' => 'datetime',
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