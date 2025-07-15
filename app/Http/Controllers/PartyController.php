<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\PartyContact;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PartyController extends Controller
{
    /**
     * Display a listing of the parties.
     */
    public function index()
    {
        $parties = Party::with(['contacts', 'ledgers'])
            ->orderBy('name')
            ->get();

        return view('parties.index', compact('parties'));
    }

    /**
     * Show the form for creating a new party.
     */
    public function create()
    {
        return view('parties.create');
    }

    /**
     * Store a newly created party in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'credit_limit' => 'required|numeric|min:0',
            'credit_days' => 'required|integer|min:0|max:365',
        ]);

        $party = Party::create($request->all());

        return redirect()->route('parties.index')
            ->with('success', 'Party created successfully.');
    }

    /**
     * Display the specified party.
     */
    public function show(Party $party)
    {
        $party->load(['contacts', 'ledgers.chartOfAccount']);

        // Get ledgers summary
        $ledgersSummary = $party->ledgers_summary;

        // Get recent transactions from all linked ledgers
        $recentTransactions = collect();
        foreach ($party->ledgers as $ledger) {
            $transactions = $ledger->transactions()
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($transaction) use ($ledger) {
                    $transaction->ledger_name = $ledger->name;
                    return $transaction;
                });
            $recentTransactions = $recentTransactions->merge($transactions);
        }

        // Sort by date and limit to 10 most recent
        $recentTransactions = $recentTransactions
            ->sortByDesc(function ($transaction) {
                return $transaction->transaction_date->timestamp;
            })
            ->take(10);

        return view('parties.show', compact('party', 'ledgersSummary', 'recentTransactions'));
    }

    /**
     * Show the form for editing the specified party.
     */
    public function edit(Party $party)
    {
        return view('parties.edit', compact('party'));
    }

    /**
     * Update the specified party in storage.
     */
    public function update(Request $request, Party $party)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'credit_limit' => 'required|numeric|min:0',
            'credit_days' => 'required|integer|min:0|max:365',
        ]);

        $party->update($request->all());

        return redirect()->route('parties.show', $party)
            ->with('success', 'Party updated successfully.');
    }

    /**
     * Remove the specified party from storage.
     */
    public function destroy(Party $party)
    {
        $partyName = $party->name;
        $party->delete();

        return redirect()->route('parties.index')
            ->with('success', "Party '{$partyName}' has been deleted successfully.");
    }

    /**
     * Get available ledgers for linking to a party.
     */
    public function getAvailableLedgers(Party $party): JsonResponse
    {
        $linkedLedgerIds = $party->ledgers()->pluck('ledger_id')->toArray();

        $availableLedgers = Ledger::active()
            ->whereNotIn('id', $linkedLedgerIds)
            ->with('chartOfAccount')
            ->orderBy('name')
            ->get()
            ->map(function ($ledger) {
                return [
                    'id' => $ledger->id,
                    'name' => $ledger->name,
                    'folio' => $ledger->folio,
                    'chart_of_account' => $ledger->chartOfAccount ? $ledger->chartOfAccount->account_name : 'N/A'
                ];
            });

        return response()->json($availableLedgers);
    }

    /**
     * Link a ledger to a party.
     */
    public function linkLedger(Request $request, Party $party): JsonResponse
    {
        $request->validate([
            'ledger_id' => 'required|exists:ledgers,id'
        ]);

        $ledger = Ledger::findOrFail($request->ledger_id);

        if ($party->hasLedger($ledger)) {
            return response()->json([
                'success' => false,
                'message' => 'This ledger is already linked to the party.'
            ], 422);
        }

        $party->linkLedger($ledger);

        // Log activity
        activity()
            ->performedOn($party)
            ->withProperties(['ledger_name' => $ledger->name, 'ledger_id' => $ledger->id])
            ->log('Linked ledger to party');

        return response()->json([
            'success' => true,
            'message' => "Ledger '{$ledger->name}' has been successfully linked to party '{$party->name}'.",
            'ledger' => [
                'id' => $ledger->id,
                'name' => $ledger->name,
                'folio' => $ledger->folio,
                'balance' => $ledger->getCurrentBalance()
            ]
        ]);
    }

    /**
     * Unlink a ledger from a party.
     */
    public function unlinkLedger(Request $request, Party $party): JsonResponse
    {
        $request->validate([
            'ledger_id' => 'required|exists:ledgers,id'
        ]);

        $ledger = Ledger::findOrFail($request->ledger_id);

        if (!$party->hasLedger($ledger)) {
            return response()->json([
                'success' => false,
                'message' => 'This ledger is not linked to the party.'
            ], 422);
        }

        $party->unlinkLedger($ledger);

        // Log activity
        activity()
            ->performedOn($party)
            ->withProperties(['ledger_name' => $ledger->name, 'ledger_id' => $ledger->id])
            ->log('Unlinked ledger from party');

        return response()->json([
            'success' => true,
            'message' => "Ledger '{$ledger->name}' has been successfully unlinked from party '{$party->name}'."
        ]);
    }

    /**
     * Get party's ledger summary.
     */
    public function getLedgerSummary(Party $party): JsonResponse
    {
        $summary = $party->ledgers_summary;

        $ledgersDetail = $party->ledgers->map(function ($ledger) {
            $balance = $ledger->getCurrentBalance();
            return [
                'id' => $ledger->id,
                'name' => $ledger->name,
                'folio' => $ledger->folio,
                'balance' => $balance['balance'],
                'balance_type' => $balance['type'],
                'chart_of_account' => $ledger->chartOfAccount ? $ledger->chartOfAccount->account_name : 'N/A'
            ];
        });

        return response()->json([
            'summary' => $summary,
            'ledgers' => $ledgersDetail
        ]);
    }
}
