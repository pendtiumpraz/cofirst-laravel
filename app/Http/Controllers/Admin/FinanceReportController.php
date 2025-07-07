<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;

class FinanceReportController extends Controller
{
    /**
     * Display finance dashboard for admin
     */
    public function dashboard(Request $request)
    {
        // Today's revenue (paid transactions today)
        $todaysRevenue = FinancialTransaction::where('status', 'paid')
            ->whereDate('created_at', today())
            ->sum('amount');
            
        // Monthly revenue (paid transactions this month)
        $monthlyRevenue = FinancialTransaction::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
            
        // Total revenue (all time paid transactions)
        $totalRevenue = FinancialTransaction::where('status', 'paid')->sum('amount');
        
        // Pending payments amount
        $pendingAmount = FinancialTransaction::where('status', 'pending')->sum('amount');
        
        // Pending payments count
        $pendingPayments = FinancialTransaction::where('status', 'pending')->count();
        
        // New students today (students who made their first transaction today)
        $newStudentsToday = FinancialTransaction::whereDate('created_at', today())
            ->distinct('student_id')
            ->count('student_id');
            
        // Recent transactions with relationships
        $recentTransactions = FinancialTransaction::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Chart data based on filter
        $filter = $request->get('filter', '7days');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $chartData = $this->getChartData($filter, $month, $year);
        
        // Top courses by revenue
        $topCourses = FinancialTransaction::where('status', 'paid')
            ->whereNotNull('course_id')
            ->select('course_id')
            ->selectRaw('SUM(amount) as total_revenue')
            ->selectRaw('COUNT(*) as transaction_count')
            ->groupBy('course_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->with('course')
            ->get();
            
        return view('admin.finance.dashboard', compact(
            'todaysRevenue', 
            'monthlyRevenue', 
            'totalRevenue', 
            'pendingAmount', 
            'pendingPayments',
            'newStudentsToday',
            'recentTransactions',
            'chartData',
            'filter',
            'month',
            'year',
            'topCourses'
        ));
    }

    /**
     * Get chart data for different date ranges
     */
    private function getChartData($filter = '7days', $month = null, $year = null)
    {
        $labels = [];
        $data = [];
        
        switch ($filter) {
            case '7days':
                // Last 7 days
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->format('M j');
                    
                    $dailyRevenue = FinancialTransaction::where('status', 'paid')
                        ->whereDate('created_at', $date->toDateString())
                        ->sum('amount');
                        
                    $data[] = $dailyRevenue;
                }
                break;
                
            case '1month':
                // Last 30 days
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->format('M j');
                    
                    $dailyRevenue = FinancialTransaction::where('status', 'paid')
                        ->whereDate('created_at', $date->toDateString())
                        ->sum('amount');
                        
                    $data[] = $dailyRevenue;
                }
                break;
                
            case 'custom':
                // Custom month and year
                $startDate = \Carbon\Carbon::create($year, $month, 1);
                $endDate = $startDate->copy()->endOfMonth();
                $daysInMonth = $endDate->day;
                
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = \Carbon\Carbon::create($year, $month, $day);
                    $labels[] = $date->format('M j');
                    
                    $dailyRevenue = FinancialTransaction::where('status', 'paid')
                        ->whereDate('created_at', $date->toDateString())
                        ->sum('amount');
                        
                    $data[] = $dailyRevenue;
                }
                break;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'filter' => $filter,
            'month' => $month,
            'year' => $year,
        ];
    }

    /**
     * Display detailed financial reports
     */
    public function reports(Request $request)
    {
        $filter = $request->get('filter', 'month');
        $startDate = null;
        $endDate = null;
        
        switch ($filter) {
            case 'today':
                $startDate = today();
                $endDate = today();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date')) : now()->startOfMonth();
                $endDate = $request->get('end_date') ? \Carbon\Carbon::parse($request->get('end_date')) : now()->endOfMonth();
                break;
        }
        
        // Get transactions for the period
        $transactions = FinancialTransaction::with(['student', 'course'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Calculate statistics
        $stats = [
            'total_revenue' => $transactions->where('status', 'paid')->where('type', 'payment')->sum('amount'),
            'total_refunds' => $transactions->where('type', 'refund')->sum('amount'),
            'net_revenue' => $transactions->where('status', 'paid')->where('type', 'payment')->sum('amount') - $transactions->where('type', 'refund')->sum('amount'),
            'total_transactions' => $transactions->count(),
            'paid_transactions' => $transactions->where('status', 'paid')->count(),
            'pending_transactions' => $transactions->where('status', 'pending')->count(),
            'cancelled_transactions' => $transactions->where('status', 'cancelled')->count(),
        ];
        
        // Group by course
        $courseRevenue = $transactions->where('status', 'paid')
            ->where('type', 'payment')
            ->whereNotNull('course_id')
            ->groupBy('course_id')
            ->map(function ($group) {
                return [
                    'course' => $group->first()->course,
                    'total_revenue' => $group->sum('amount'),
                    'transaction_count' => $group->count(),
                ];
            })
            ->sortByDesc('total_revenue')
            ->values();
            
        return view('admin.finance.reports', compact(
            'transactions',
            'stats',
            'courseRevenue',
            'filter',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export financial report
     */
    public function export(Request $request)
    {
        $filter = $request->get('filter', 'month');
        $format = $request->get('format', 'csv');
        
        // Get the same data as reports method
        $startDate = null;
        $endDate = null;
        
        switch ($filter) {
            case 'today':
                $startDate = today();
                $endDate = today();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date')) : now()->startOfMonth();
                $endDate = $request->get('end_date') ? \Carbon\Carbon::parse($request->get('end_date')) : now()->endOfMonth();
                break;
        }
        
        $transactions = FinancialTransaction::with(['student', 'course'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->get();
            
        if ($format === 'csv') {
            $filename = 'financial_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($transactions) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, ['Date', 'Student', 'Email', 'Course', 'Type', 'Amount', 'Status', 'Description']);
                
                foreach ($transactions as $transaction) {
                    fputcsv($file, [
                        $transaction->created_at->format('Y-m-d H:i:s'),
                        $transaction->student->name,
                        $transaction->student->email,
                        $transaction->course ? $transaction->course->name : 'N/A',
                        $transaction->type,
                        $transaction->amount,
                        $transaction->status,
                        $transaction->description,
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
        
        // For other formats, return JSON for now
        return response()->json([
            'message' => 'Export format not yet implemented',
            'format' => $format
        ]);
    }
}