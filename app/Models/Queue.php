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
        'govt_id_type',
        'govt_id_number',
        'issued_at',
        'issued_on',
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
        'birthdate' => 'date'
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
     * Scope for active queue items (waiting or serving)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'serving']);
    }

    /**
     * Scope for waiting items only
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope for priority queue
     */
    public function scopePriority($query)
    {
        return $query->whereIn('priority_type', ['PWD', 'Senior'])
            ->waiting()
            ->orderBy('queue_entered_at', 'asc');
    }

    /**
     * Scope for regular queue
     */
    public function scopeRegular($query)
    {
        return $query->where('priority_type', 'Regular')
            ->waiting()
            ->orderBy('queue_entered_at', 'asc');
    }

    /**
     * Get currently serving client
     */
    public static function getCurrentlyServing()
    {
        return self::where('status', 'serving')->first();
    }

    /**
     * Mark this queue item as serving
     */
    public function markAsServing(): void
    {
        $this->update([
            'status' => 'serving',
            'served_at' => now()
        ]);
    }

    /**
     * Mark this queue item as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    /**
     * Mark this queue item as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled'
        ]);
    }

    /**
     * Requeue this item
     */
    public function requeue(): void
    {
        $this->update([
            'status' => 'waiting',
            'served_at' => null,
            'queue_entered_at' => now()
        ]);
    }

    /**
     * Get pre-registration if exists
     */
    public function preRegistration()
    {
        return $this->belongsTo(PreRegistration::class, 'pre_registration_id');
    }
}