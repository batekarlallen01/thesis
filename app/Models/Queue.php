<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_number',
        'full_name',
        'email',
        'contact',
        'birthdate',
        'age',
        'service_type',
        'is_pwd',
        'pwd_id',
        'senior_id',
        'priority_type',
        'entry_type',
        'status',
        'queue_entered_at',
        'served_at',
        'completed_at',
        'pre_registration_id',
        'form_data',
        'number_of_copies',
        'purpose',
        'address',
        'applicant_type',
        // Owner's Government ID
        'govt_id_type',
        'govt_id_number',
        'issued_at',
        'issued_on',
        // Representative's Government ID (new fields)
        'rep_govt_id_type',
        'rep_govt_id_number',
        'rep_issued_at',
        'rep_issued_on',
        // Property Index Numbers
        'pin_land',
        'pin_building',
        'pin_machinery',
    ];

    protected $casts = [
        'is_pwd' => 'boolean',
        'queue_entered_at' => 'datetime',
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
        'form_data' => 'array',
        'birthdate' => 'date',
        'issued_on' => 'date',
        'rep_issued_on' => 'date',
    ];

    /**
     * Generate the next queue number for today
     */
    public static function generateQueueNumber(): string
    {
        $today = Carbon::today();
        
        // Get the last queue number for today
        $lastQueue = self::whereDate('queue_entered_at', $today)
            ->orderBy('queue_number', 'desc')
            ->first();
        
        if (!$lastQueue) {
            return '01'; // First queue of the day
        }
        
        // Extract number and increment
        $lastNumber = intval($lastQueue->queue_number);
        $nextNumber = $lastNumber + 1;
        
        return str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate priority type based on age and PWD status
     */
    public static function calculatePriorityType($age, $isPwd): string
    {
        if ($isPwd) {
            return 'PWD';
        }
        
        if ($age && $age >= 60) {
            return 'Senior';
        }
        
        return 'Regular';
    }

    /**
     * Scope to get waiting queue items (excluding requeued)
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting')
                     ->orderBy('queue_entered_at', 'asc');
    }

    /**
     * Scope to get priority queue (PWD and Senior Citizens, excluding requeued)
     */
    public function scopePriority($query)
    {
        return $query->where('status', 'waiting')
                     ->whereIn('priority_type', ['PWD', 'Senior'])
                     ->orderBy('queue_entered_at', 'asc');
    }

    /**
     * Scope to get regular queue (excluding priority and requeued)
     */
    public function scopeRegular($query)
    {
        return $query->where('status', 'waiting')
                     ->where(function($q) {
                         $q->whereNull('priority_type')
                           ->orWhere('priority_type', 'Regular');
                     })
                     ->orderBy('queue_entered_at', 'asc');
    }

    /**
     * Get currently serving queue item
     */
    public static function getCurrentlyServing()
    {
        return self::where('status', 'serving')->first();
    }

    /**
     * Mark this queue item as serving
     */
    public function markAsServing()
    {
        $this->update([
            'status' => 'serving',
            'served_at' => now(),
        ]);
    }

    /**
     * Mark this queue item as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark this queue item as cancelled
     */
    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled',
            'updated_at' => now(),
        ]);
    }

    /**
     * Requeue this item (sets it aside for manual recall)
     * NOTE: Requeued clients are NOT automatically called by "Call Next"
     * They must be manually recalled using "Recall Now" button
     */
    public function requeue()
    {
        $this->update([
            'status' => 'requeued',
            'served_at' => null, // Clear served_at since they're going back to queue
            // 'updated_at' is automatically updated by Laravel (used for requeue ordering)
        ]);
    }

    /**
     * Get pre-registration if exists
     */
    public function preRegistration()
    {
        return $this->belongsTo(PreRegistration::class, 'pre_registration_id');
    }

    /**
     * Accessor: Get formatted owner ID type
     */
    public function getFormattedOwnerIdTypeAttribute(): ?string
    {
        return $this->govt_id_type ? ucwords(str_replace('_', ' ', $this->govt_id_type)) : null;
    }

    /**
     * Accessor: Get formatted representative ID type
     */
    public function getFormattedRepIdTypeAttribute(): ?string
    {
        return $this->rep_govt_id_type ? ucwords(str_replace('_', ' ', $this->rep_govt_id_type)) : null;
    }

    /**
     * Check if applicant is a representative
     */
    public function isRepresentative(): bool
    {
        return $this->applicant_type === 'representative';
    }

    /**
     * Check if has complete representative information
     */
    public function hasRepresentativeInfo(): bool
    {
        return $this->isRepresentative() 
            && !empty($this->rep_govt_id_type) 
            && !empty($this->rep_govt_id_number);
    }
}