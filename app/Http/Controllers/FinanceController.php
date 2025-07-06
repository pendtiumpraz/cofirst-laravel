<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialTransaction;
use App\Models\User;

class FinanceController extends Controller
{
    /**
     * Display finance dashboard.
     */
    public function dashboard()
    {
        $totalRevenue = FinancialTransaction::where('status', 'paid')->sum('amount');
        $pendingAmount = FinancialTransaction::where('status', 'pending')->sum('amount');
        $monthlyRevenue = FinancialTransaction::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
        $recentTransactions = FinancialTransaction::with(['student'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return view('finance.dashboard', compact('totalRevenue', 'pendingAmount', 'monthlyRevenue', 'recentTransactions'));
    }

    /**
     * Display a listing of transactions.
     */
    public function index()
    {
        $transactions = FinancialTransaction::with(['student'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('finance.transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create()
    {
        $students = User::role('student')->get();
        return view('finance.transactions.create', compact('students'));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'type' => 'required|in:payment,refund',
            'status' => 'required|in:pending,paid,cancelled',
        ]);

        FinancialTransaction::create($request->all());

        return redirect()->route('finance.transactions.index')->with('success', 'Transaction created successfully.');
    }

    /**
     * Show the form for editing the transaction.
     */
    public function edit(FinancialTransaction $transaction)
    {
        $students = User::role('student')->get();
        return view('finance.transactions.edit', compact('transaction', 'students'));
    }

    /**
     * Update the transaction.
     */
    public function update(Request $request, FinancialTransaction $transaction)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'type' => 'required|in:payment,refund',
            'status' => 'required|in:pending,paid,cancelled',
        ]);

        $transaction->update($request->all());

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
        $transaction->update(['status' => 'paid']);
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
