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

            // PRE-REGISTERED COUNT
            // Count pre_registrations that haven't entered the queue yet
            $preRegistered = DB::table('pre_registrations')
                ->where('has_entered_queue', 0)
                ->count();

            // Alternative: Count all pre_registrations
            // $preRegistered = DB::table('pre_registrations')->count();

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

            // Alternative: Use updated_at instead of completed_at
            // $completed = DB::table('queues')
            //     ->where('status', 'completed')
            //     ->whereDate('updated_at', $today)
            //     ->count();

            // MAILBOX MESSAGES
            // Count pending mailbox submissions
            $mailboxMessages = DB::table('mailbox_submissions')
                ->where('status', 'pending')
                ->count();

            // Alternative: Count all unprocessed (pending or submitted)
            // $mailboxMessages = DB::table('mailbox_submissions')
            //     ->whereIn('status', ['pending', 'submitted'])
            //     ->count();

            return [
                'pre_registered' => $preRegistered,
                'cancelled' => $cancelled,
                'completed' => $completed,
                'mailbox_messages' => $mailboxMessages,
            ];

        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            
            return [
                'pre_registered' => 0,
                'cancelled' => 0,
                'completed' => 0,
                'mailbox_messages' => 0,
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
                // Pre-registered users who haven't entered queue
                'pre_registered' => DB::table('pre_registrations')
                    ->where('has_entered_queue', 0)
                    ->count(),

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
                    
                    'no_show' => DB::table('queues')
                        ->where('status', 'no_show')
                        ->whereDate('updated_at', $today)
                        ->count(),
                ],

                // Mailbox statistics
                'mailbox' => [
                    'pending' => DB::table('mailbox_submissions')
                        ->where('status', 'pending')
                        ->count(),
                    
                    'submitted' => DB::table('mailbox_submissions')
                        ->where('status', 'submitted')
                        ->count(),
                    
                    'processing' => DB::table('mailbox_submissions')
                        ->where('status', 'processing')
                        ->count(),
                    
                    'completed' => DB::table('mailbox_submissions')
                        ->where('status', 'completed')
                        ->count(),
                    
                    'total_today' => DB::table('mailbox_submissions')
                        ->whereDate('created_at', $today)
                        ->count(),
                ],

                // Kiosk entries
                'kiosk' => [
                    'pending' => DB::table('kiosk_entries')
                        ->where('status', 'pending')
                        ->count(),
                    
                    'in_queue' => DB::table('kiosk_entries')
                        ->where('status', 'in_queue')
                        ->count(),
                    
                    'completed' => DB::table('kiosk_entries')
                        ->where('status', 'completed')
                        ->whereDate('updated_at', $today)
                        ->count(),
                    
                    'total_today' => DB::table('kiosk_entries')
                        ->whereDate('created_at', $today)
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

                'mailbox_submissions' => DB::table('mailbox_submissions')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),

                'kiosk_entries' => DB::table('kiosk_entries')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            \Log::error('Date range stats error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}