<?php

namespace App\Http\Controllers;

use App\Models\Consignee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsigneeController extends Controller
{
    /**
     * Display a listing of the consignees.
     */
    public function index(Request $request)
    {
        $query = Consignee::query();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('state')) {
            $query->where('state', 'like', '%' . $request->state . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('consignee_name', 'like', "%{$search}%")
                  ->orWhere('gstin', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }

        $consignees = $query->orderBy('consignee_name')->paginate(15);

        // Get unique cities and states for filters
        $cities = Consignee::distinct()->pluck('city')->sort()->values();
        $states = Consignee::distinct()->pluck('state')->sort()->values();

        return view('consignees.index', compact('consignees', 'cities', 'states'));
    }

    /**
     * Show the form for creating a new consignee.
     */
    public function create()
    {
        return view('consignees.create');
    }

    /**
     * Store a newly created consignee in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'consignee_name' => 'required|string|max:255',
            'gstin' => 'nullable|string|max:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'address' => 'required|string|max:500',
            'address2' => 'nullable|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:10',
            'status' => 'required|in:active,inactive'
        ], [
            'gstin.regex' => 'Please enter a valid GSTIN format (e.g., 22AAAAA0000A1Z5)',
        ]);

        try {
            DB::beginTransaction();

            $consignee = Consignee::create($request->all());

            DB::commit();

            return redirect()->route('consignees.index')
                           ->with('success', 'Consignee created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error creating consignee: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified consignee.
     */
    public function show(Consignee $consignee)
    {
        // Load relationships
        $consignee->load(['sales']);

        // Get recent sales
        $recentSales = $consignee->sales()
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        return view('consignees.show', compact('consignee', 'recentSales'));
    }

    /**
     * Show the form for editing the specified consignee.
     */
    public function edit(Consignee $consignee)
    {
        return view('consignees.edit', compact('consignee'));
    }

    /**
     * Update the specified consignee in storage.
     */
    public function update(Request $request, Consignee $consignee)
    {
        $request->validate([
            'consignee_name' => 'required|string|max:255',
            'gstin' => 'nullable|string|max:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'address' => 'required|string|max:500',
            'address2' => 'nullable|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:10',
            'status' => 'required|in:active,inactive'
        ], [
            'gstin.regex' => 'Please enter a valid GSTIN format (e.g., 22AAAAA0000A1Z5)',
        ]);

        try {
            DB::beginTransaction();

            $consignee->update($request->all());

            DB::commit();

            return redirect()->route('consignees.index')
                           ->with('success', 'Consignee updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error updating consignee: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified consignee from storage.
     */
    public function destroy(Consignee $consignee)
    {
        try {
            // Check if consignee has any sales
            if ($consignee->sales()->count() > 0) {
                return redirect()->route('consignees.index')
                               ->with('error', 'Cannot delete consignee. There are sales records associated with this consignee.');
            }

            $consignee->delete();

            return redirect()->route('consignees.index')
                           ->with('success', 'Consignee deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->route('consignees.index')
                           ->with('error', 'Error deleting consignee: ' . $e->getMessage());
        }
    }

    /**
     * Export consignees to CSV.
     */
    public function export(Request $request)
    {
        $query = Consignee::query();

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('state')) {
            $query->where('state', 'like', '%' . $request->state . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('consignee_name', 'like', "%{$search}%")
                  ->orWhere('gstin', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }

        $consignees = $query->orderBy('consignee_name')->get();

        $filename = 'consignees_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($consignees) {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, [
                'ID',
                'Consignee Name',
                'GSTIN',
                'Address',
                'Address 2',
                'City',
                'State',
                'ZIP',
                'Status',
                'Created At'
            ]);

            // CSV Data
            foreach ($consignees as $consignee) {
                fputcsv($file, [
                    $consignee->id,
                    $consignee->consignee_name,
                    $consignee->gstin ?? '',
                    $consignee->address,
                    $consignee->address2 ?? '',
                    $consignee->city,
                    $consignee->state,
                    $consignee->zip,
                    ucfirst($consignee->status),
                    $consignee->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get consignees for AJAX requests.
     */
    public function search(Request $request)
    {
        $query = Consignee::active();

        if ($request->filled('term')) {
            $term = $request->term;
            $query->where(function($q) use ($term) {
                $q->where('consignee_name', 'like', "%{$term}%")
                  ->orWhere('city', 'like', "%{$term}%");
            });
        }

        $consignees = $query->limit(20)->get(['id', 'consignee_name', 'city']);

        return response()->json($consignees);
    }
}
