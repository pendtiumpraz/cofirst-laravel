<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Models\Course;

class FinanceController extends Controller
{
    /**
     * Display finance dashboard.
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
            ->limit(5)
            ->get();
            
        // Chart data based on filter
        $filter = $request->get('filter', '7days');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $chartData = $this->getChartData($filter, $month, $year);
            
        return view('finance.dashboard', compact(
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
            'year'
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
     * Get chart data via AJAX
     */
    public function getChartDataAjax(Request $request)
    {
        $filter = $request->get('filter', '7days');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $chartData = $this->getChartData($filter, $month, $year);
        
        return response()->json($chartData);
    }

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20); // Default 20 per page
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20; // Validate per_page value
        
        $transactions = FinancialTransaction::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
        // Append current query parameters to pagination links
        $transactions->appends($request->query());
        
        // Calculate statistics for summary cards
        $stats = [
            'total_paid' => FinancialTransaction::where('status', 'paid')->sum('amount'),
            'total_pending' => FinancialTransaction::where('status', 'pending')->sum('amount'),
            'total_transactions' => FinancialTransaction::count(),
            'this_month_count' => FinancialTransaction::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
            
        return view('finance.transactions.index', compact('transactions', 'stats'));
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create()
    {
        $students = User::role('student')->get();
        $courses = Course::all();
        return view('finance.transactions.create', compact('students', 'courses'));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'type' => 'required|in:payment,refund',
            'status' => 'required|in:pending,paid,cancelled',
            'paid_date' => 'nullable|date',
        ]);

        $data = $request->all();
        
        // If status is not 'paid', remove paid_date
        if ($data['status'] !== 'paid') {
            $data['paid_date'] = null;
        } elseif ($data['status'] === 'paid' && !$data['paid_date']) {
            // If status is 'paid' but no paid_date provided, set to now
            $data['paid_date'] = now();
        }

        FinancialTransaction::create($data);

        return redirect()->route('finance.transactions.index')->with('success', 'Transaction created successfully.');
    }

    /**
     * Show the form for editing the transaction.
     */
    public function edit(FinancialTransaction $transaction)
    {
        $students = User::role('student')->get();
        $courses = Course::all();
        return view('finance.transactions.edit', compact('transaction', 'students', 'courses'));
    }

    /**
     * Update the transaction.
     */
    public function update(Request $request, FinancialTransaction $transaction)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'type' => 'required|in:payment,refund',
            'status' => 'required|in:pending,paid,cancelled',
            'paid_date' => 'nullable|date',
        ]);

        $data = $request->all();
        
        // If status is not 'paid', remove paid_date
        if ($data['status'] !== 'paid') {
            $data['paid_date'] = null;
        } elseif ($data['status'] === 'paid' && !$data['paid_date']) {
            // If status is 'paid' but no paid_date provided, set to now
            $data['paid_date'] = now();
        }

        $transaction->update($data);

        return redirect()->route('finance.transactions.index')->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the transaction.
     */
    public function destroy(FinancialTransaction $transaction)
    {
        $transaction->delete();
        return redirect()->route('finance.transactions.index')->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Mark transaction as paid.
     */
    public function markPaid(FinancialTransaction $transaction)
    {
        $transaction->update(['status' => 'paid', 'paid_date' => now()]);
        return redirect()->route('finance.transactions.index')->with('success', 'Transaction marked as paid.');
    }

    /**
     * Display financial reports.
     */
    public function reports()
    {
        $totalRevenue = FinancialTransaction::where('status', 'paid')->sum('amount');
        $pendingAmount = FinancialTransaction::where('status', 'pending')->sum('amount');
        $monthlyRevenue = FinancialTransaction::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
            
        return view('finance.reports.index', compact('totalRevenue', 'pendingAmount', 'monthlyRevenue'));
    }

    /**
     * Generate daily report.
     */
    public function dailyReport()
    {
        $transactions = FinancialTransaction::whereDate('created_at', today())
            ->with(['student'])
            ->get();
            
        return view('finance.reports.daily', compact('transactions'));
    }

    /**
     * Export financial report.
     */
    public function exportReport()
    {
        // Implementation for export functionality
        return response()->json(['message' => 'Export functionality coming soon']);
    }
}
