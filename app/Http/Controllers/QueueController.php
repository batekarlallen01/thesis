<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QueueController extends Controller
{
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
    public function getQueueData()
    {
        try {
            // Get currently serving
            $nowServing = Queue::getCurrentlyServing();
            
            // Get priority queue (PWD and Senior citizens)
            $priorityQueue = Queue::priority()
                ->select('id', 'queue_number', 'full_name as name', 'service_type', 'priority_type')
                ->get();
            
            // Get regular queue
            $regularQueue = Queue::regular()
                ->select('id', 'queue_number', 'full_name as name', 'service_type', 'priority_type')
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
                    'entry_type' => ucfirst($nowServing->entry_type),
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
            }

            return response()->json([
                'success' => true,
                'now_serving' => $nowServingData,
                'priority' => $priorityQueue,
                'regular' => $regularQueue,
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
     * Mark the next person in queue as serving
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

            // Get next from priority queue first, then regular queue
            $nextInQueue = Queue::priority()->first() ?? Queue::regular()->first();

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
                'message' => 'Client requeued successfully'
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