<?php
namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index()
    {
        $journalEntries = Transaction::select('uuid', 'transaction_date', 'particular')
            ->groupBy('uuid', 'transaction_date', 'particular')
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        return view('journal-entries.index', compact('journalEntries'));
    }

    public function create()
    {
        $ledgers = Ledger::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('journal-entries.create', compact('ledgers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'particular' => 'required|string|max:255',
            'entries' => 'required|array|min:2',
            'entries.*.ledger_id' => 'required|exists:ledgers,id',
            'entries.*.debit' => 'nullable|numeric|min:0',
            'entries.*.credit' => 'nullable|numeric|min:0',
            'entries.*.notes' => 'nullable|string|max:500',
        ]);

        // Custom validation for debit/credit logic
        $totalDebit = 0;
        $totalCredit = 0;
        $validEntries = [];

        foreach ($request->entries as $entry) {
            $debit = floatval($entry['debit'] ?? 0);
            $credit = floatval($entry['credit'] ?? 0);

            // Each entry must have either debit or credit (not both, not neither)
            if (($debit > 0 && $credit > 0) || ($debit == 0 && $credit == 0)) {
                return back()->withErrors(['entries' => 'Each entry must have either debit or credit amount, not both or neither.'])
                    ->withInput();
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
            $validEntries[] = $entry;
        }

        // Total debit must equal total credit
        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['entries' => 'Total debit must equal total credit.'])
                ->withInput();
        }

        // Check for duplicate ledgers
        $ledgerIds = collect($validEntries)->pluck('ledger_id');
        if ($ledgerIds->count() !== $ledgerIds->unique()->count()) {
            return back()->withErrors(['entries' => 'Each ledger can only be selected once per journal entry.'])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $uuid = Str::uuid();
            $transactionDate = Carbon::parse($request->transaction_date);

            foreach ($validEntries as $entry) {
                $ledger = Ledger::find($entry['ledger_id']);
                $debit = floatval($entry['debit'] ?? 0);
                $credit = floatval($entry['credit'] ?? 0);

                // Calculate new running balance
                $runningBalance = $this->calculateRunningBalance($ledger, $transactionDate, $debit, $credit);

                Transaction::create([
                    'ledger_id' => $ledger->id,
                    'uuid' => $uuid,
                    'transaction_date' => $transactionDate,
                    'particular' => $request->particular,
                    'debit' => $debit,
                    'credit' => $credit,
                    'running_balance' => $runningBalance['balance'],
                    'running_balance_type' => $runningBalance['type'],
                    'notes' => $entry['notes'] ?? null,
                ]);

                // Update running balances for all subsequent transactions
                $this->updateSubsequentBalances($ledger, $transactionDate);
            }

            DB::commit();

            return redirect()->route('journal-entries.index')
                ->with('success', 'Journal entry created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred while creating the journal entry.'])
                ->withInput();
        }
    }

    public function show($uuid)
    {
        $transactions = Transaction::where('uuid', $uuid)
            ->with('ledger')
            ->orderBy('id')
            ->get();

        if ($transactions->isEmpty()) {
            abort(404);
        }

        $journalEntry = [
            'uuid' => $uuid,
            'transaction_date' => $transactions->first()->transaction_date,
            'particular' => $transactions->first()->particular,
            'transactions' => $transactions
        ];

        return view('journal-entries.show', compact('journalEntry'));
    }

    private function calculateRunningBalance($ledger, $transactionDate, $debit, $credit)
    {
        // Get the last transaction before this date
        $lastTransaction = $ledger->transactions()
            ->where('transaction_date', '<', $transactionDate)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            $currentBalance = $lastTransaction->running_balance;
            $currentType = $lastTransaction->running_balance_type;
        } else {
            $currentBalance = $ledger->opening_balance;
            $currentType = $ledger->balance_type;
        }

        // Calculate new balance
        if ($currentType == 'debit') {
            $newBalance = $currentBalance + $debit - $credit;
        } else {
            $newBalance = $currentBalance - $debit + $credit;
        }

        $newType = $newBalance >= 0 ? $currentType : ($currentType == 'debit' ? 'credit' : 'debit');
        $newBalance = abs($newBalance);

        return [
            'balance' => $newBalance,
            'type' => $newType
        ];
    }

    private function updateSubsequentBalances($ledger, $transactionDate)
    {
        $subsequentTransactions = $ledger->transactions()
            ->where('transaction_date', '>=', $transactionDate)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $runningBalance = $ledger->opening_balance;
        $runningType = $ledger->balance_type;

        foreach ($subsequentTransactions as $transaction) {
            if ($runningType == 'debit') {
                $runningBalance = $runningBalance + $transaction->debit - $transaction->credit;
            } else {
                $runningBalance = $runningBalance - $transaction->debit + $transaction->credit;
            }

            if ($runningBalance < 0) {
                $runningType = $runningType == 'debit' ? 'credit' : 'debit';
                $runningBalance = abs($runningBalance);
            }

            $transaction->update([
                'running_balance' => $runningBalance,
                'running_balance_type' => $runningType
            ]);
        }
    }
}
