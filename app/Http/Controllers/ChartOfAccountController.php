<?php
namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $accounts = ChartOfAccount::with('parent', 'children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('chart-of-accounts.index', compact('accounts'));
    }

    public function create()
    {
        $parentAccounts = ChartOfAccount::where('allow_posting', false)
            ->orderBy('account_code')
            ->get();

        return view('chart-of-accounts.create', compact('parentAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'account_subtype' => 'required|string',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'allow_posting' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $level = 1;
        if ($request->parent_id) {
            $parent = ChartOfAccount::find($request->parent_id);
            $level = $parent->level + 1;
        }

        ChartOfAccount::create([
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'account_type' => $request->account_type,
            'account_subtype' => $request->account_subtype,
            'normal_balance' => $request->normal_balance,
            'parent_id' => $request->parent_id,
            'level' => $level,
            'allow_posting' => $request->boolean('allow_posting', true),
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function show(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->load('parent', 'children');
        return view('chart-of-accounts.show', compact('chartOfAccount'));
    }

    public function getAccountsByType($type)
    {
        $accounts = ChartOfAccount::where('account_type', $type)
            ->where('allow_posting', true)
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        return response()->json($accounts);
    }
    public function edit(ChartOfAccount $chartOfAccount)
{
    $parentAccounts = ChartOfAccount::where('allow_posting', false)
        ->where('id', '!=', $chartOfAccount->id)
        ->orderBy('account_code')
        ->get();

    return view('chart-of-accounts.edit', compact('chartOfAccount', 'parentAccounts'));
}

public function update(Request $request, ChartOfAccount $chartOfAccount)
{
    $request->validate([
        'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code,' . $chartOfAccount->id,
        'account_name' => 'required|string|max:255',
        'account_type' => 'required|in:asset,liability,equity,revenue,expense',
        'account_subtype' => 'required|string',
        'normal_balance' => 'required|in:debit,credit',
        'parent_id' => 'nullable|exists:chart_of_accounts,id',
        'allow_posting' => 'boolean',
        'is_active' => 'boolean',
        'description' => 'nullable|string',
    ]);

    $level = 1;
    if ($request->parent_id) {
        $parent = ChartOfAccount::find($request->parent_id);
        $level = $parent->level + 1;
    }

    $chartOfAccount->update([
        'account_code' => $request->account_code,
        'account_name' => $request->account_name,
        'account_type' => $request->account_type,
        'account_subtype' => $request->account_subtype,
        'normal_balance' => $request->normal_balance,
        'parent_id' => $request->parent_id,
        'level' => $level,
        'allow_posting' => $request->boolean('allow_posting', true),
        'is_active' => $request->boolean('is_active', true),
        'description' => $request->description,
        'sort_order' => $request->sort_order ?? 0,
    ]);

    return redirect()->route('chart-of-accounts.show', $chartOfAccount)
        ->with('success', 'Account updated successfully.');
    }
}
