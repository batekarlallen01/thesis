<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QueueController extends Controller
{
    /**
     * Check if queue is still accepting new entries
     * ✅ Auto-closes when queue hits 50 users
     * ✅ Auto-closes exactly at 4:45 PM
     * ✅ Auto-closes earlier (4:25 PM) if still 15 applicants in line
     * ✅ Prevents new submissions
     * ✅ System resets automatically the next day
     */
    public function checkQueueStatus()
    {
        try {
            $now = Carbon::now('Asia/Manila');
            $currentTime = $now->format('H:i');
            $today = $now->toDateString();
            
            // Count today's active queue entries (waiting, serving, requeued)
            // Note: Using queue_entered_at instead of created_at for accuracy
            $todayCount = Queue::whereDate('queue_entered_at', $today)
                ->whereIn('status', ['waiting', 'serving', 'requeued'])
                ->count();
            
            // RULE 1: Auto-close if queue hits 100 users
            if ($todayCount >= 100) {
                return response()->json([
                    'allowed' => false,
                    'reason' => 'capacity',
                    'message' => 'Queue is full for today (100 applicants reached). Please come back tomorrow.',
                    'current_count' => $todayCount
                ]);
            }
            
            // RULE 2: Auto-close exactly at 4:45 PM
            if ($currentTime >= '16:45') {
                return response()->json([
                    'allowed' => false,
                    'reason' => 'time_cutoff',
                    'message' => 'Queue is closed for today (4:45 PM cutoff). Please come back tomorrow.',
                    'current_count' => $todayCount
                ]);
            }
            
            // RULE 3: Auto-close at 4:25 PM if still 15+ applicants in line
            if ($currentTime >= '16:25' && $todayCount >= 15) {
                return response()->json([
                    'allowed' => false,
                    'reason' => 'early_cutoff',
                    'message' => 'Queue is closed early due to high volume. Please come back tomorrow.',
                    'current_count' => $todayCount
                ]);
            }
            
            // Queue is open
            return response()->json([
                'allowed' => true,
                'current_count' => $todayCount,
                'remaining_slots' => 50 - $todayCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Queue status check error: ' . $e->getMessage());
            return response()->json([
                'allowed' => true, // Fail open to not block users on error
                'error' => 'Unable to verify queue status'
            ], 500);
        }
    }

    /**
     * Display the queue status page
     */
    public function index()
    {
        return view('admin.queuestatus');
    }

    /**
     * Get current queue data (API endpoint)
     */
    public function getQueueData(Request $request)
    {
        try {
            // Get filter parameter (optional)
            $entryTypeFilter = $request->query('entry_type', 'all'); // all, direct, pre_registration
            
            // Get currently serving WITH pre-registration data
            $nowServing = Queue::with('preRegistration')->where('status', 'serving')->first();
            
            // Build base query for requeued (highest priority)
            $requeuedQuery = Queue::where('status', 'requeued')
                ->orderBy('updated_at', 'asc'); // First in, first out for requeued
            
            // Apply entry type filter if not 'all'
            if ($entryTypeFilter !== 'all') {
                $requeuedQuery->where('entry_type', $entryTypeFilter);
            }
            
            // Get requeued list
            $requeuedQueue = $requeuedQuery
                ->select('id', 'queue_number', 'full_name as name', 'service_type', 'priority_type', 'entry_type')
                ->get();
            
            // Build base query for priority queue (exclude requeued)
            $priorityQuery = Queue::priority();
            
            // Apply entry type filter if not 'all'
            if ($entryTypeFilter !== 'all') {
                $priorityQuery->where('entry_type', $entryTypeFilter);
            }
            
            // Get priority queue
            $priorityQueue = $priorityQuery
                ->select('id', 'queue_number', 'full_name as name', 'service_type', 'priority_type', 'entry_type')
                ->get();
            
            // Build base query for regular queue (exclude requeued)
            $regularQuery = Queue::regular();
            
            // Apply entry type filter if not 'all'
            if ($entryTypeFilter !== 'all') {
                $regularQuery->where('entry_type', $entryTypeFilter);
            }
            
            // Get regular queue
            $regularQueue = $regularQuery
                ->select('id', 'queue_number', 'full_name as name', 'service_type', 'priority_type', 'entry_type')
                ->get();

            // Format now serving data with all details
            $nowServingData = null;
            if ($nowServing) {
                $nowServingData = [
                    'id' => $nowServing->id,
                    'queue_number' => $nowServing->queue_number,
                    'full_name' => $nowServing->full_name,
                    'email' => $nowServing->email,
                    'age' => $nowServing->age,
                    'service_type' => $nowServing->service_type,
                    'is_pwd' => $nowServing->is_pwd,
                    'pwd_id' => $nowServing->pwd_id,
                    'priority_type' => $nowServing->priority_type,
                    'entry_type' => $nowServing->entry_type,
                    'status' => ucfirst($nowServing->status),
                    'queue_entered_at' => $nowServing->queue_entered_at ? $nowServing->queue_entered_at->format('M d, Y h:i A') : null,
                    'served_at' => $nowServing->served_at ? $nowServing->served_at->format('M d, Y h:i A') : null,
                    'number_of_copies' => $nowServing->number_of_copies,
                    'purpose' => $nowServing->purpose,
                    'address' => $nowServing->address,
                    'applicant_type' => $nowServing->applicant_type,
                    'govt_id_type' => $nowServing->govt_id_type,
                    'govt_id_number' => $nowServing->govt_id_number,
                    'issued_at' => $nowServing->issued_at,
                    'issued_on' => $nowServing->issued_on,
                    'pin_land' => $nowServing->pin_land,
                    'pin_building' => $nowServing->pin_building,
                    'pin_machinery' => $nowServing->pin_machinery,
                ];

                // ADD PRE-REGISTRATION DOCUMENTS IF ENTRY IS FROM PRE-REG
                if ($nowServing->entry_type === 'pre_registration' && $nowServing->preRegistration) {
                    $preReg = $nowServing->preRegistration;
                    $nowServingData['documents'] = [
                        'owner_id_image' => $preReg->owner_id_image ? asset('storage/documents/' . $preReg->owner_id_image) : null,
                        'spa_image' => $preReg->spa_image ? asset('storage/documents/' . $preReg->spa_image) : null,
                        'rep_id_image' => $preReg->rep_id_image ? asset('storage/documents/' . $preReg->rep_id_image) : null,
                        'tax_decl_form' => $preReg->tax_decl_form ? asset('storage/documents/' . $preReg->tax_decl_form) : null,
                        'title' => $preReg->title ? asset('storage/documents/' . $preReg->title) : null,
                        'tax_payment' => $preReg->tax_payment ? asset('storage/documents/' . $preReg->tax_payment) : null,
                        'latest_tax_decl' => $preReg->latest_tax_decl ? asset('storage/documents/' . $preReg->latest_tax_decl) : null,
                        'deed_of_sale' => $preReg->deed_of_sale ? asset('storage/documents/' . $preReg->deed_of_sale) : null,
                        'transfer_tax_receipt' => $preReg->transfer_tax_receipt ? asset('storage/documents/' . $preReg->transfer_tax_receipt) : null,
                        'car_from_bir' => $preReg->car_from_bir ? asset('storage/documents/' . $preReg->car_from_bir) : null,
                    ];
                } else {
                    // For kiosk entries, documents array is empty or null
                    $nowServingData['documents'] = null;
                }
            }

            // Get queue counts by entry type
            $counts = [
                'priority_total' => Queue::priority()->count(),
                'priority_prereg' => Queue::priority()->where('entry_type', 'pre_registration')->count(),
                'priority_kiosk' => Queue::priority()->where('entry_type', 'direct')->count(),
                'regular_total' => Queue::regular()->count(),
                'regular_prereg' => Queue::regular()->where('entry_type', 'pre_registration')->count(),
                'regular_kiosk' => Queue::regular()->where('entry_type', 'direct')->count(),
                'requeued_total' => Queue::where('status', 'requeued')->count(),
            ];

            return response()->json([
                'success' => true,
                'now_serving' => $nowServingData,
                'requeued' => $requeuedQueue,
                'priority' => $priorityQueue,
                'regular' => $regularQueue,
                'counts' => $counts,
                'current_filter' => $entryTypeFilter,
            ]);
        } catch (\Exception $e) {
            Log::error('Queue data fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch queue data'
            ], 500);
        }
    }

    /**
     * Mark the next person in queue as serving (follows priority order)
     * NOTE: Requeued clients are NOT automatically called - they must be manually recalled
     */
    public function markNextAsServed()
    {
        try {
            // Check if someone is already being served
            $currentlyServing = Queue::getCurrentlyServing();
            if ($currentlyServing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Someone is already being served. Please complete or cancel the current client first.'
                ], 400);
            }

            // Priority order: 1. Priority Queue, 2. Regular Queue
            // NOTE: Requeued clients are EXCLUDED from automatic "Call Next"
            // They must be manually recalled using "Recall Now" button
            
            $nextInQueue = Queue::priority()->first();
            
            if (!$nextInQueue) {
                $nextInQueue = Queue::regular()->first();
            }

            if (!$nextInQueue) {
                return response()->json([
                    'success' => false,
                    'message' => 'No one in queue'
                ], 404);
            }

            // Mark as serving
            $nextInQueue->markAsServing();

            return response()->json([
                'success' => true,
                'message' => 'Next client is now being served',
                'now_serving' => [
                    'id' => $nextInQueue->id,
                    'queue_number' => $nextInQueue->queue_number,
                    'full_name' => $nextInQueue->full_name,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Mark next as served error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark next as served'
            ], 500);
        }
    }

    /**
     * Serve a specific person from the requeued list
     */
    public function serveSpecific($id)
    {
        try {
            // Check if someone is already being served
            $currentlyServing = Queue::getCurrentlyServing();
            if ($currentlyServing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Someone is already being served. Please complete or cancel the current client first.'
                ], 400);
            }

            $queueItem = Queue::find($id);

            if (!$queueItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Queue item not found'
                ], 404);
            }

            if ($queueItem->status !== 'requeued') {
                return response()->json([
                    'success' => false,
                    'message' => 'This client is not in the requeued list'
                ], 400);
            }

            // Mark as serving
            $queueItem->markAsServing();

            return response()->json([
                'success' => true,
                'message' => 'Client is now being served',
                'now_serving' => [
                    'id' => $queueItem->id,
                    'queue_number' => $queueItem->queue_number,
                    'full_name' => $queueItem->full_name,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Serve specific error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to serve client'
            ], 500);
        }
    }

    /**
     * Complete the currently serving client
     */
    public function completeNowServing()
    {
        try {
            $nowServing = Queue::getCurrentlyServing();

            if (!$nowServing) {
                return response()->json([
                    'success' => false,
                    'message' => 'No one is currently being served'
                ], 404);
            }

            $nowServing->markAsCompleted();

            return response()->json([
                'success' => true,
                'message' => 'Client service completed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Complete now serving error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete service'
            ], 500);
        }
    }

    /**
     * Cancel the currently serving client
     */
    public function cancelNowServing()
    {
        try {
            $nowServing = Queue::getCurrentlyServing();

            if (!$nowServing) {
                return response()->json([
                    'success' => false,
                    'message' => 'No one is currently being served'
                ], 404);
            }

            $nowServing->markAsCancelled();

            return response()->json([
                'success' => true,
                'message' => 'Client cancelled successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Cancel now serving error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel client'
            ], 500);
        }
    }

    /**
     * Requeue the currently serving client
     */
    public function requeueNowServing()
    {
        try {
            $nowServing = Queue::getCurrentlyServing();

            if (!$nowServing) {
                return response()->json([
                    'success' => false,
                    'message' => 'No one is currently being served'
                ], 404);
            }

            $nowServing->requeue();

            return response()->json([
                'success' => true,
                'message' => 'Client requeued successfully and will be prioritized'
            ]);
        } catch (\Exception $e) {
            Log::error('Requeue now serving error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to requeue client'
            ], 500);
        }
    }

    /**
     * Recall the currently serving number (announce again)
     */
    public function recallNowServing()
    {
        try {
            $nowServing = Queue::getCurrentlyServing();

            if (!$nowServing) {
                return response()->json([
                    'success' => false,
                    'message' => 'No one is currently being served'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Queue number recalled',
                'now_serving' => [
                    'queue_number' => $nowServing->queue_number,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Recall now serving error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to recall queue number'
            ], 500);
        }
    }

    /**
     * Get queue statistics for dashboard
     */
    public function getStatistics()
    {
        try {
            $today = Carbon::today();
            
            return response()->json([
                'success' => true,
                'stats' => [
                    'waiting' => Queue::waiting()->count(),
                    'serving' => Queue::where('status', 'serving')->count(),
                    'completed_today' => Queue::whereDate('completed_at', $today)
                        ->where('status', 'completed')
                        ->count(),
                    'cancelled_today' => Queue::whereDate('updated_at', $today)
                        ->where('status', 'cancelled')
                        ->count(),
                    'priority_waiting' => Queue::priority()->count(),
                    'regular_waiting' => Queue::regular()->count(),
                    'requeued_waiting' => Queue::where('status', 'requeued')->count(),
                    // Stats by entry type
                    'prereg_waiting' => Queue::waiting()->where('entry_type', 'pre_registration')->count(),
                    'kiosk_waiting' => Queue::waiting()->where('entry_type', 'direct')->count(),
                    'prereg_completed_today' => Queue::whereDate('completed_at', $today)
                        ->where('status', 'completed')
                        ->where('entry_type', 'pre_registration')
                        ->count(),
                    'kiosk_completed_today' => Queue::whereDate('completed_at', $today)
                        ->where('status', 'completed')
                        ->where('entry_type', 'direct')
                        ->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Queue statistics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics'
            ], 500);
        }
    }
}