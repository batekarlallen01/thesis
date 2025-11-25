<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard with real-time statistics
     */
    public function index()
    {
        $stats = $this->calculateStats();
        return view('Admin.dashboard', compact('stats'));
    }

    /**
     * Get dashboard statistics via AJAX for real-time updates
     */
    public function getDashboardStats()
    {
        $stats = $this->calculateStats();
        return response()->json($stats);
    }

    /**
     * Calculate all dashboard statistics based on actual database structure
     */
    private function calculateStats()
    {
        try {
            $today = Carbon::today();

            // PENDING REVIEW (Step 1)
            // Pre-registrations waiting for admin review/approval
            // Status: 'pending' or 'submitted' (not yet approved/disapproved)
            $pendingReview = DB::table('pre_registrations')
                ->whereIn('status', ['pending', 'submitted'])
                ->count();

            // APPROVED & WAITING (Step 2)
            // Pre-registrations that are approved but haven't entered the queue yet
            // Status: 'approved', has_entered_queue: 0
            $approvedWaiting = DB::table('pre_registrations')
                ->where('status', 'approved')
                ->where('has_entered_queue', 0)
                ->count();

            // CANCELLED TODAY
            // Count entries from 'queues' table with status 'cancelled' updated today
            $cancelled = DB::table('queues')
                ->where('status', 'cancelled')
                ->whereDate('updated_at', $today)
                ->count();

            // COMPLETED TODAY
            // Count entries from 'queues' table with status 'completed' completed today
            $completed = DB::table('queues')
                ->where('status', 'completed')
                ->whereDate('completed_at', $today)
                ->count();

            // QUEUE ACTIVITY STATS
            // Currently waiting in queue
            $waiting = DB::table('queues')
                ->where('status', 'waiting')
                ->count();

            // Currently being served
            $serving = DB::table('queues')
                ->where('status', 'serving')
                ->count();

            // Total entries today (all statuses)
            $totalToday = DB::table('queues')
                ->whereDate('queue_entered_at', $today)
                ->count();

            // Requeued (needs manual recall)
            $requeued = DB::table('queues')
                ->where('status', 'requeued')
                ->count();

            // PRIORITY BREAKDOWN (Waiting only)
            $pwdWaiting = DB::table('queues')
                ->where('status', 'waiting')
                ->where('priority_type', 'PWD')
                ->count();

            $seniorWaiting = DB::table('queues')
                ->where('status', 'waiting')
                ->where('priority_type', 'Senior')
                ->count();

            $regularWaiting = DB::table('queues')
                ->where('status', 'waiting')
                ->where(function($q) {
                    $q->where('priority_type', 'Regular')
                      ->orWhereNull('priority_type');
                })
                ->count();

            // ENTRY TYPE BREAKDOWN (Waiting only)
            $kioskWaiting = DB::table('queues')
                ->where('status', 'waiting')
                ->where('entry_type', 'direct')
                ->count();

            $preregWaiting = DB::table('queues')
                ->where('status', 'waiting')
                ->where('entry_type', 'pre_registration')
                ->count();

            return [
                // Pre-registration stats
                'pending_review' => $pendingReview,
                'approved_waiting' => $approvedWaiting,
                'cancelled' => $cancelled,
                'completed' => $completed,
                
                // Queue activity stats
                'waiting' => $waiting,
                'serving' => $serving,
                'total_today' => $totalToday,
                'requeued' => $requeued,
                
                // Priority breakdown
                'pwd_waiting' => $pwdWaiting,
                'senior_waiting' => $seniorWaiting,
                'regular_waiting' => $regularWaiting,
                
                // Entry type breakdown
                'kiosk_waiting' => $kioskWaiting,
                'prereg_waiting' => $preregWaiting,
            ];

        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            
            return [
                'pending_review' => 0,
                'approved_waiting' => 0,
                'cancelled' => 0,
                'completed' => 0,
            ];
        }
    }

    /**
     * Get detailed statistics with breakdowns
     * Optional: Use this if you want more detailed stats
     */
    public function getDetailedStats()
    {
        try {
            $today = Carbon::today();
            $startOfDay = $today->copy()->startOfDay();
            $endOfDay = $today->copy()->endOfDay();

            $stats = [
                // Pre-registration breakdown
                'pre_registrations' => [
                    'pending_review' => DB::table('pre_registrations')
                        ->whereIn('status', ['pending', 'submitted'])
                        ->count(),
                    
                    'approved_waiting' => DB::table('pre_registrations')
                        ->where('status', 'approved')
                        ->where('has_entered_queue', 0)
                        ->count(),
                    
                    'disapproved' => DB::table('pre_registrations')
                        ->where('status', 'disapproved')
                        ->count(),
                    
                    'in_queue' => DB::table('pre_registrations')
                        ->where('has_entered_queue', 1)
                        ->count(),
                ],

                // Queue statistics for today
                'queue_today' => [
                    'total_entered' => DB::table('queues')
                        ->whereBetween('queue_entered_at', [$startOfDay, $endOfDay])
                        ->count(),
                    
                    'waiting' => DB::table('queues')
                        ->where('status', 'waiting')
                        ->count(),
                    
                    'serving' => DB::table('queues')
                        ->where('status', 'serving')
                        ->count(),
                    
                    'completed' => DB::table('queues')
                        ->where('status', 'completed')
                        ->whereDate('completed_at', $today)
                        ->count(),
                    
                    'cancelled' => DB::table('queues')
                        ->where('status', 'cancelled')
                        ->whereDate('updated_at', $today)
                        ->count(),
                    
                    'requeued' => DB::table('queues')
                        ->where('status', 'requeued')
                        ->count(),
                ],

                // Priority breakdown
                'priority' => [
                    'regular' => DB::table('queues')
                        ->where('priority_type', 'Regular')
                        ->where('status', 'waiting')
                        ->count(),
                    
                    'pwd' => DB::table('queues')
                        ->where('priority_type', 'PWD')
                        ->where('status', 'waiting')
                        ->count(),
                    
                    'senior' => DB::table('queues')
                        ->where('priority_type', 'Senior')
                        ->where('status', 'waiting')
                        ->count(),
                ],

                // Entry type breakdown
                'entry_types' => [
                    'direct_kiosk' => DB::table('queues')
                        ->where('entry_type', 'direct')
                        ->where('status', 'waiting')
                        ->count(),
                    
                    'pre_registration' => DB::table('queues')
                        ->where('entry_type', 'pre_registration')
                        ->where('status', 'waiting')
                        ->count(),
                ],
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            \Log::error('Detailed stats error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get statistics for a date range
     * Useful for reports
     */
    public function getStatsForDateRange(Request $request)
    {
        try {
            $startDate = $request->input('start_date', Carbon::today()->startOfDay());
            $endDate = $request->input('end_date', Carbon::today()->endOfDay());

            $stats = [
                'pre_registrations_created' => DB::table('pre_registrations')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),

                'pre_registrations_approved' => DB::table('pre_registrations')
                    ->where('status', 'approved')
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->count(),

                'pre_registrations_disapproved' => DB::table('pre_registrations')
                    ->where('status', 'disapproved')
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->count(),

                'queue_entries' => DB::table('queues')
                    ->whereBetween('queue_entered_at', [$startDate, $endDate])
                    ->count(),

                'completed' => DB::table('queues')
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate, $endDate])
                    ->count(),

                'cancelled' => DB::table('queues')
                    ->where('status', 'cancelled')
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            \Log::error('Date range stats error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}