<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Mines;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the purchases.
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['mines', 'vehicle']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('datetime', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('datetime', '<=', $request->end_date);
        }

        // Filter by mines
        if ($request->has('mines_id') && $request->mines_id) {
            $query->where('mines_id', $request->mines_id);
        }

        // Filter by vehicle
        if ($request->has('vehicle_id') && $request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by use_at
        if ($request->has('use_at') && $request->use_at) {
            $query->where('use_at', $request->use_at);
        }

        $purchases = $query->orderBy('datetime', 'desc')->paginate(15);

        // Get filter options
        $mines = Mines::orderBy('ml_number')->get();
        $vehicles = Vehicle::orderBy('vehicle_number')->get();

        return view('purchases.index', compact('purchases', 'mines', 'vehicles'));
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create()
    {
        $mines = Mines::where('status', 'active')->orderBy('ml_number')->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('vehicle_number')->get();

        return view('purchases.create', compact('mines', 'vehicles'));
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'datetime' => 'required|date',
            'mines_id' => 'required|exists:mines,id',
            'rec_no' => 'required|string|max:255',
            'token_no' => 'required|string|max:255',
            'vehicle_id' => 'required|exists:vehicles,id',
            'gross_wt' => 'required|integer|min:0',
            'tare_wt' => 'required|integer|min:0',
            'driver' => 'required|string|max:255',
            'commission' => 'nullable|numeric|min:0',
            'use_at' => 'required|in:stock,manufacturing',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                Purchase::create($validatedData);
            });

            return redirect()->route('purchases.index')
                           ->with('success', 'Purchase record created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating purchase: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error creating purchase record. Please try again.');
        }
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['mines', 'vehicle']);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified purchase.
     */
    public function edit(Purchase $purchase)
    {
        $mines = Mines::where('status', 'active')->orderBy('ml_number')->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('vehicle_number')->get();

        return view('purchases.edit', compact('purchase', 'mines', 'vehicles'));
    }

    /**
     * Update the specified purchase in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $validatedData = $request->validate([
            'datetime' => 'required|date',
            'mines_id' => 'required|exists:mines,id',
            'rec_no' => 'required|string|max:255',
            'token_no' => 'required|string|max:255',
            'vehicle_id' => 'required|exists:vehicles,id',
            'gross_wt' => 'required|integer|min:0',
            'tare_wt' => 'required|integer|min:0',
            'driver' => 'required|string|max:255',
            'commission' => 'nullable|numeric|min:0',
            'use_at' => 'required|in:stock,manufacturing',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($purchase, $validatedData) {
                $purchase->update($validatedData);
            });

            return redirect()->route('purchases.index')
                           ->with('success', 'Purchase record updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating purchase: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error updating purchase record. Please try again.');
        }
    }

    /**
     * Remove the specified purchase from storage.
     */
    public function destroy(Purchase $purchase)
    {
        try {
            DB::transaction(function () use ($purchase) {
                $purchase->delete();
            });

            return redirect()->route('purchases.index')
                           ->with('success', 'Purchase record deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting purchase: ' . $e->getMessage());
            return redirect()->route('purchases.index')
                           ->with('error', 'Error deleting purchase record. Please try again.');
        }
    }

    /**
     * Get vehicle drivers for AJAX request.
     */
    public function getVehicleDrivers(Request $request)
    {
        $vehicleId = $request->vehicle;
        $vehicle = Vehicle::with('contacts')->find($vehicleId);

        if (!$vehicle) {
            return response()->json(['drivers' => []]);
        }

        $drivers = [];

        // Get drivers from vehicle contacts
        foreach ($vehicle->contacts as $contact) {
            if ($contact->designation && (
                stripos($contact->designation, 'driver') !== false ||
                stripos($contact->designation, 'operator') !== false
            )) {
                $drivers[] = $contact->name;
            } else {
                // If no specific driver designation, add all contacts
                $drivers[] = $contact->name;
            }
        }

        // Remove duplicates
        $drivers = array_unique($drivers);

        return response()->json(['drivers' => array_values($drivers)]);
    }

    /**
     * Get purchase statistics for dashboard.
     */
    public function getStatistics()
    {
        return [
            'total_purchases' => Purchase::count(),
            'today_purchases' => Purchase::whereDate('datetime', today())->count(),
            'total_weight_tons' => Purchase::sum('wt_ton'),
            'total_commission' => Purchase::sum('commission'),
            'stock_purchases' => Purchase::where('use_at', 'stock')->count(),
            'manufacturing_purchases' => Purchase::where('use_at', 'manufacturing')->count(),
        ];
    }
}
