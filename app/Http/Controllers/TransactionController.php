<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Ledger;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('ledger');

        if ($request->has('ledger_id')) {
            $query->where('ledger_id', $request->ledger_id);
        }

        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50);

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ledger_id' => 'required|exists:ledgers,id',
            'date' => 'required|date',
            'particulars' => 'required|string|max:255',
            'debit' => 'required|numeric|min:0',
            'credit' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        // Ensure either debit or credit is provided, but not both
        if ($validated['debit'] > 0 && $validated['credit'] > 0) {
            return response()->json([
                'message' => 'Transaction cannot have both debit and credit amounts'
            ], 422);
        }

        if ($validated['debit'] == 0 && $validated['credit'] == 0) {
            return response()->json([
                'message' => 'Transaction must have either debit or credit amount'
            ], 422);
        }

        $transaction = Transaction::create($validated);

        // Recalculate running balance for subsequent transactions
        $this->recalculateSubsequentBalances($transaction);

        return response()->json([
            'message' => 'Transaction created successfully',
            'transaction' => $transaction->load('ledger')
        ], 201);
    }

    public function show(Transaction $transaction)
    {
        return response()->json($transaction->load('ledger'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'date' => 'sometimes|date',
            'particulars' => 'sometimes|string|max:255',
            'debit' => 'sometimes|numeric|min:0',
            'credit' => 'sometimes|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $transaction->update($validated);

        // Recalculate running balance for this and subsequent transactions
        $this->recalculateSubsequentBalances($transaction);

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction->load('ledger')
        ]);
    }

    public function destroy(Transaction $transaction)
    {
        $ledgerId = $transaction->ledger_id;
        $transaction->delete();

        // Recalculate running balance for subsequent transactions
        $this->recalculateAllBalances($ledgerId);

        return response()->json([
            'message' => 'Transaction deleted successfully'
        ]);
    }

    private function recalculateSubsequentBalances(Transaction $transaction)
    {
        $subsequentTransactions = Transaction::where('ledger_id', $transaction->ledger_id)
            ->where(function ($query) use ($transaction) {
                $query->where('date', '>', $transaction->date)
                    ->orWhere(function ($q) use ($transaction) {
                        $q->where('date', $transaction->date)
                          ->where('id', '>', $transaction->id);
                    });
            })
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        foreach ($subsequentTransactions as $t) {
            $t->calculateRunningBalance();
            $t->save();
        }
    }

    private function recalculateAllBalances($ledgerId)
    {
        $transactions = Transaction::where('ledger_id', $ledgerId)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        foreach ($transactions as $transaction) {
            $transaction->calculateRunningBalance();
            $transaction->save();
        }
    }
}
