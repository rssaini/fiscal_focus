<?php

namespace App\Http\Controllers;

use App\Models\EntityManagement;
use App\Models\ChartOfAccount;
use App\Models\Ledger;
use Illuminate\Http\Request;

class EntityManagementController extends Controller
{
    /**
     * Display a listing of the entity management records.
     */
    public function index(Request $request)
    {
        $query = EntityManagement::with(['chartOfAccount', 'ledger']);

        // Filter by head name
        if ($request->filled('head_name')) {
            $query->where('head_name', $request->head_name);
        }

        // Filter by voucher type
        if ($request->filled('voucher_type')) {
            $query->where('voucher_type', $request->voucher_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $entityManagements = $query->orderBy('head_name')->orderBy('voucher_type')->paginate(10);

        // Get filter options
        $headNames = EntityManagement::getHeadNames();
        $voucherTypes = EntityManagement::getVoucherTypes();

        return view('entity-management.index', compact('entityManagements', 'headNames', 'voucherTypes'));
    }

    /**
     * Show the form for creating a new entity management record.
     */
    public function create()
    {
        $headNames = EntityManagement::getHeadNames();
        $voucherTypes = EntityManagement::getVoucherTypes();
        $chartOfAccounts = ChartOfAccount::where('allow_posting', true)
            ->orderBy('account_code')
            ->get();
        $ledgers = Ledger::orderBy('name')->get();

        return view('entity-management.create', compact('headNames', 'voucherTypes', 'chartOfAccounts', 'ledgers'));
    }

    /**
     * Store a newly created entity management record in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'head_name' => 'required|string|max:255|unique:entity_management,head_name',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'voucher_type' => 'required|string|max:255',
            'ledger_id' => 'required|exists:ledgers,id',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        EntityManagement::create([
            'head_name' => $request->head_name,
            'chart_of_account_id' => $request->chart_of_account_id,
            'voucher_type' => $request->voucher_type,
            'ledger_id' => $request->ledger_id,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('entity-management.index')
            ->with('success', 'Entity management record created successfully.');
    }

    /**
     * Display the specified entity management record.
     */
    public function show(EntityManagement $entityManagement)
    {
        $entityManagement->load(['chartOfAccount', 'ledger']);
        return view('entity-management.show', compact('entityManagement'));
    }

    /**
     * Show the form for editing the specified entity management record.
     */
    public function edit(EntityManagement $entityManagement)
    {
        $headNames = EntityManagement::getHeadNames();
        $voucherTypes = EntityManagement::getVoucherTypes();
        $chartOfAccounts = ChartOfAccount::where('allow_posting', true)
            ->orderBy('account_code')
            ->get();
        $ledgers = Ledger::orderBy('name')->get();

        return view('entity-management.edit', compact(
            'entityManagement',
            'headNames',
            'voucherTypes',
            'chartOfAccounts',
            'ledgers'
        ));
    }

    /**
     * Update the specified entity management record in storage.
     */
    public function update(Request $request, EntityManagement $entityManagement)
    {
        $request->validate([
            'head_name' => 'required|string|max:255|unique:entity_management,head_name,' . $entityManagement->id,
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'voucher_type' => 'required|string|max:255',
            'ledger_id' => 'required|exists:ledgers,id',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $entityManagement->update([
            'head_name' => $request->head_name,
            'chart_of_account_id' => $request->chart_of_account_id,
            'voucher_type' => $request->voucher_type,
            'ledger_id' => $request->ledger_id,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('entity-management.index')
            ->with('success', 'Entity management record updated successfully.');
    }

    /**
     * Remove the specified entity management record from storage.
     */
    public function destroy(EntityManagement $entityManagement)
    {
        $entityManagement->delete();

        return redirect()->route('entity-management.index')
            ->with('success', 'Entity management record deleted successfully.');
    }

    /**
     * API endpoint to get accounting head for entity creation.
     */
    public function getEntityCreationHead(Request $request)
    {
        $headName = $request->get('head_name');
        $entityManagement = EntityManagement::getEntityCreationHead($headName);

        if (!$entityManagement) {
            return response()->json(['error' => 'No entity management record found for this entity type'], 404);
        }

        return response()->json([
            'chart_of_account_id' => $entityManagement->chart_of_account_id,
            'chart_of_account' => $entityManagement->chartOfAccount,
            'ledger_id' => $entityManagement->ledger_id,
            'ledger' => $entityManagement->ledger,
        ]);
    }

    /**
     * API endpoint to get voucher ledger information.
     */
    public function getVoucherLedger(Request $request)
    {
        $headName = $request->get('head_name');
        $voucherType = $request->get('voucher_type');

        $entityManagement = EntityManagement::getVoucherLedger($headName, $voucherType);

        if (!$entityManagement) {
            return response()->json(['error' => 'No ledger mapping found for this combination'], 404);
        }

        return response()->json([
            'chart_of_account_id' => $entityManagement->chart_of_account_id,
            'chart_of_account' => $entityManagement->chartOfAccount,
            'ledger_id' => $entityManagement->ledger_id,
            'ledger' => $entityManagement->ledger,
        ]);
    }

    /**
     * Bulk create default entity management records.
     */
    public function createDefaults()
    {
        // Note: You'll need to adjust these IDs based on your actual chart of accounts and ledgers
        $defaults = [
            [
                'head_name' => 'customers',
                'chart_of_account_id' => 1, // Adjust based on your Accounts Receivable account
                'voucher_type' => 'sale',
                'ledger_id' => 1, // Adjust based on your Sale Ledger
                'description' => 'Default mapping for customer sales transactions',
            ],
            [
                'head_name' => 'employees',
                'chart_of_account_id' => 2, // Adjust based on your Salary Payable account
                'voucher_type' => 'expense',
                'ledger_id' => 2, // Adjust based on your Salary Expense Ledger
                'description' => 'Default mapping for employee salary expenses',
            ],
            [
                'head_name' => 'vendors',
                'chart_of_account_id' => 3, // Adjust based on your Accounts Payable account
                'voucher_type' => 'purchase',
                'ledger_id' => 3, // Adjust based on your Purchase Ledger
                'description' => 'Default mapping for vendor purchase transactions',
            ],
            [
                'head_name' => 'suppliers',
                'chart_of_account_id' => 3, // Adjust based on your Accounts Payable account
                'voucher_type' => 'purchase',
                'ledger_id' => 3, // Adjust based on your Purchase Ledger
                'description' => 'Default mapping for supplier purchase transactions',
            ],
        ];

        foreach ($defaults as $default) {
            EntityManagement::updateOrCreate(
                ['head_name' => $default['head_name']],
                $default
            );
        }

        return redirect()->route('entity-management.index')
            ->with('success', 'Default entity management records created successfully.');
    }
}
