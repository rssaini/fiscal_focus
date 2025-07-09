<?php
namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\Transaction;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class LedgerController extends Controller
{
    public function index()
    {
        $ledgers = Ledger::with('chartOfAccount')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('ledgers.index', compact('ledgers'));
    }

    public function create()
    {
        $chartOfAccounts = ChartOfAccount::where('allow_posting', true)
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        return view('ledgers.create', compact('chartOfAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ledgers,name',
            'folio' => 'required|string|max:255',
            'opening_date' => 'required|date',
            'opening_balance' => 'required|numeric|min:0',
            'balance_type' => 'required|in:credit,debit',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        // Validate that the selected chart of account allows posting
        $chartOfAccount = ChartOfAccount::find($request->chart_of_account_id);
        if (!$chartOfAccount->allow_posting) {
            return back()->withErrors(['chart_of_account_id' => 'Selected account does not allow direct posting.'])
                ->withInput();
        }

        Ledger::create($request->all());

        return redirect()->route('ledgers.index')
            ->with('success', 'Ledger created successfully.');
    }

    public function show(Ledger $ledger, Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->endOfMonth()->format('Y-m-d'));

        $fromDate = Carbon::parse($fromDate);
        $toDate = Carbon::parse($toDate);

        // Get opening balance for the period
        $openingBalance = $ledger->getOpeningBalanceForDate($fromDate);

        // Get transactions for the period
        $transactions = $ledger->transactions()
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        return view('ledgers.show', compact('ledger', 'transactions', 'openingBalance', 'fromDate', 'toDate'));
    }

    public function export(Ledger $ledger, Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->endOfMonth()->format('Y-m-d'));

        $fromDate = Carbon::parse($fromDate);
        $toDate = Carbon::parse($toDate);

        $openingBalance = $ledger->getOpeningBalanceForDate($fromDate);

        $transactions = $ledger->transactions()
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $filename = "ledger_{$ledger->name}_{$fromDate->format('Y-m-d')}_to_{$toDate->format('Y-m-d')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($ledger, $transactions, $openingBalance, $fromDate, $toDate) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['Ledger Statement']);
            fputcsv($file, ['Ledger Name:', $ledger->name]);
            fputcsv($file, ['Period:', $fromDate->format('d/m/Y') . ' to ' . $toDate->format('d/m/Y')]);
            fputcsv($file, []); // Empty row

            // Opening Balance
            fputcsv($file, ['Opening Balance', '', '', number_format($openingBalance['balance'], 2), $openingBalance['type']]);
            fputcsv($file, []); // Empty row

            // Column headers
            fputcsv($file, ['Date', 'Particular', 'Debit', 'Credit', 'Balance', 'Balance Type']);

            // Transactions
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_date->format('d/m/Y'),
                    $transaction->particular,
                    $transaction->debit > 0 ? number_format($transaction->debit, 2) : '',
                    $transaction->credit > 0 ? number_format($transaction->credit, 2) : '',
                    number_format($transaction->running_balance, 2),
                    $transaction->running_balance_type
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function exportPdf(Ledger $ledger, Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->endOfMonth()->format('Y-m-d'));

        $fromDate = Carbon::parse($fromDate);
        $toDate = Carbon::parse($toDate);

        $openingBalance = $ledger->getOpeningBalanceForDate($fromDate);

        $transactions = $ledger->transactions()
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        // Calculate totals
        $totalDebit = $transactions->sum('debit');
        $totalCredit = $transactions->sum('credit');

        // Get closing balance
        $lastTransaction = $transactions->last();
        $closingBalance = $lastTransaction ? [
            'balance' => $lastTransaction->running_balance,
            'type' => $lastTransaction->running_balance_type
        ] : $openingBalance;

        $data = [
            'ledger' => $ledger,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'openingBalance' => $openingBalance,
            'transactions' => $transactions,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'closingBalance' => $closingBalance,
            'generatedAt' => now()
        ];

        $pdf = Pdf::loadView('ledgers.pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = "ledger_{$ledger->name}_{$fromDate->format('Y-m-d')}_to_{$toDate->format('Y-m-d')}.pdf";
        $filename = str_replace(' ', '_', $filename);

        return $pdf->download($filename);
    }
    public function getAccountDetails($accountId)
    {
        $account = ChartOfAccount::find($accountId);
        if ($account) {
            return response()->json([
                'normal_balance' => $account->normal_balance,
                'account_type' => $account->account_type,
                'account_subtype' => $account->account_subtype,
                'allow_posting' => $account->allow_posting
            ]);
        }
        return response()->json(['error' => 'Account not found'], 404);
    }
}
