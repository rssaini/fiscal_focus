<?php
namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = Voucher::with(['entries.ledger', 'creator']);

        // Filter by voucher type
        if ($request->filled('voucher_type')) {
            $query->where('voucher_type', $request->voucher_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('voucher_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('voucher_date', '<=', $request->to_date);
        }

        // Search by voucher number or narration
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_number', 'like', "%{$search}%")
                  ->orWhere('narration', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $vouchers = $query->orderBy('voucher_date', 'desc')
                         ->orderBy('voucher_number', 'desc')
                         ->paginate(20);

        return view('vouchers.index', compact('vouchers'));
    }

    public function create(Request $request)
    {
        $voucherType = $request->get('type', 'journal');

        $ledgers = Ledger::with('chartOfAccount')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Group ledgers by account type for better organization
        $groupedLedgers = $ledgers->groupBy(function ($ledger) {
            return $ledger->chartOfAccount ? $ledger->chartOfAccount->account_type : 'other';
        });

        return view('vouchers.create', compact('voucherType', 'ledgers', 'groupedLedgers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'voucher_type' => 'required|in:journal,payment,receipt,contra',
            'voucher_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'narration' => 'required|string|max:500',
            'remarks' => 'nullable|string|max:1000',
            'entries' => 'required|array|min:2',
            'entries.*.ledger_id' => 'required|exists:ledgers,id',
            'entries.*.particular' => 'required|string|max:255',
            'entries.*.debit' => 'nullable|numeric|min:0',
            'entries.*.credit' => 'nullable|numeric|min:0',
            'entries.*.narration' => 'nullable|string|max:255',
        ]);

        // Validate entries
        $totalDebit = 0;
        $totalCredit = 0;
        $validEntries = [];

        foreach ($request->entries as $index => $entry) {
            $debit = floatval($entry['debit'] ?? 0);
            $credit = floatval($entry['credit'] ?? 0);

            // Each entry must have either debit or credit (not both, not neither)
            if (($debit > 0 && $credit > 0) || ($debit == 0 && $credit == 0)) {
                return back()->withErrors(["entries.{$index}" => 'Each entry must have either debit or credit amount, not both or neither.'])
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

        try {
            DB::beginTransaction();

            // Create voucher
            $voucher = new Voucher([
                'voucher_type' => $request->voucher_type,
                'voucher_date' => $request->voucher_date,
                'reference_number' => $request->reference_number,
                'narration' => $request->narration,
                'total_amount' => $totalDebit, // or $totalCredit, they should be equal
                'remarks' => $request->remarks,
                'created_by' => auth()->id(),
                'status' => 'draft'
            ]);

            $voucher->voucher_number = $voucher->generateVoucherNumber();
            $voucher->save();

            // Create voucher entries
            foreach ($validEntries as $index => $entry) {
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'ledger_id' => $entry['ledger_id'],
                    'particular' => $entry['particular'],
                    'debit' => floatval($entry['debit'] ?? 0),
                    'credit' => floatval($entry['credit'] ?? 0),
                    'narration' => $entry['narration'],
                    'sort_order' => $index + 1
                ]);
            }

            DB::commit();

            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Voucher created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred while creating the voucher: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Voucher $voucher)
    {
        $voucher->load(['entries.ledger.chartOfAccount', 'creator', 'approver']);
        return view('vouchers.show', compact('voucher'));
    }

    public function edit(Voucher $voucher)
    {
        if (!$voucher->canBeEdited()) {
            return redirect()->route('vouchers.show', $voucher)
                ->with('error', 'This voucher cannot be edited.');
        }

        $voucher->load('entries.ledger');

        $ledgers = Ledger::with('chartOfAccount')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $groupedLedgers = $ledgers->groupBy(function ($ledger) {
            return $ledger->chartOfAccount ? $ledger->chartOfAccount->account_type : 'other';
        });

        return view('vouchers.edit', compact('voucher', 'ledgers', 'groupedLedgers'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        if (!$voucher->canBeEdited()) {
            return redirect()->route('vouchers.show', $voucher)
                ->with('error', 'This voucher cannot be edited.');
        }

        $request->validate([
            'voucher_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'narration' => 'required|string|max:500',
            'remarks' => 'nullable|string|max:1000',
            'entries' => 'required|array|min:2',
            'entries.*.ledger_id' => 'required|exists:ledgers,id',
            'entries.*.particular' => 'required|string|max:255',
            'entries.*.debit' => 'nullable|numeric|min:0',
            'entries.*.credit' => 'nullable|numeric|min:0',
            'entries.*.narration' => 'nullable|string|max:255',
        ]);

        // Validate entries (same logic as store)
        $totalDebit = 0;
        $totalCredit = 0;
        $validEntries = [];

        foreach ($request->entries as $index => $entry) {
            $debit = floatval($entry['debit'] ?? 0);
            $credit = floatval($entry['credit'] ?? 0);

            if (($debit > 0 && $credit > 0) || ($debit == 0 && $credit == 0)) {
                return back()->withErrors(["entries.{$index}" => 'Each entry must have either debit or credit amount, not both or neither.'])
                    ->withInput();
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
            $validEntries[] = $entry;
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['entries' => 'Total debit must equal total credit.'])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update voucher
            $voucher->update([
                'voucher_date' => $request->voucher_date,
                'reference_number' => $request->reference_number,
                'narration' => $request->narration,
                'total_amount' => $totalDebit,
                'remarks' => $request->remarks
            ]);

            // Delete existing entries
            $voucher->entries()->delete();

            // Create new entries
            foreach ($validEntries as $index => $entry) {
                VoucherEntry::create([
                    'voucher_id' => $voucher->id,
                    'ledger_id' => $entry['ledger_id'],
                    'particular' => $entry['particular'],
                    'debit' => floatval($entry['debit'] ?? 0),
                    'credit' => floatval($entry['credit'] ?? 0),
                    'narration' => $entry['narration'],
                    'sort_order' => $index + 1
                ]);
            }

            DB::commit();

            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Voucher updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred while updating the voucher: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function post(Voucher $voucher)
    {
        try {
            $voucher->post();
            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Voucher posted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('vouchers.show', $voucher)
                ->with('error', 'Error posting voucher: ' . $e->getMessage());
        }
    }

    public function cancel(Voucher $voucher)
    {
        if (!$voucher->canBeCancelled()) {
            return redirect()->route('vouchers.show', $voucher)
                ->with('error', 'This voucher cannot be cancelled.');
        }

        try {
            DB::beginTransaction();

            // If voucher is posted, delete related transactions
            if ($voucher->status === 'posted') {
                Transaction::where('uuid', $voucher->voucher_number)->delete();

                // Recalculate running balances for affected ledgers
                foreach ($voucher->entries as $entry) {
                    $this->recalculateLedgerBalances($entry->ledger);
                }
            }

            $voucher->update(['status' => 'cancelled']);

            DB::commit();

            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Voucher cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('vouchers.show', $voucher)
                ->with('error', 'Error cancelling voucher: ' . $e->getMessage());
        }
    }

    private function recalculateLedgerBalances($ledger)
    {
        $runningBalance = $ledger->opening_balance;
        $runningType = $ledger->balance_type;

        $transactions = $ledger->transactions()
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        foreach ($transactions as $transaction) {
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

    public function duplicate(Voucher $voucher)
    {
        $voucher->load('entries.ledger');

        $ledgers = Ledger::with('chartOfAccount')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $groupedLedgers = $ledgers->groupBy(function ($ledger) {
            return $ledger->chartOfAccount ? $ledger->chartOfAccount->account_type : 'other';
        });

        return view('vouchers.duplicate', compact('voucher', 'ledgers', 'groupedLedgers'));
    }

    public function print(Voucher $voucher)
    {
        $voucher->load(['entries.ledger.chartOfAccount', 'creator', 'approver']);
        return view('vouchers.print', compact('voucher'));
    }
}
