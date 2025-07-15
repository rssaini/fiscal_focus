<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    /**
     * Display a listing of the vehicles.
     */
    public function index(Request $request)
    {
        $query = Vehicle::with('primaryContact');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->orderBy('vehicle_number')->paginate(15);

        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create()
    {
        return view('vehicles.create');
    }

    /**
     * Store a newly created vehicle in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_number' => 'required|string|max:255|unique:vehicles',
            'tare_weight' => 'nullable|numeric|min:0',
            'load_capacity' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'contacts' => 'required|array|min:1',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.designation' => 'nullable|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.mobile' => 'nullable|string|max:20',
            'contacts.*.is_primary' => 'nullable|boolean',
            'contacts.*.notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Create vehicle
                $vehicle = Vehicle::create($request->only([
                    'vehicle_number', 'tare_weight', 'load_capacity', 'status', 'notes'
                ]));

                // Create contacts
                $hasPrimary = false;
                foreach ($request->contacts as $index => $contactData) {
                    $contactData['vehicle_id'] = $vehicle->id;

                    // Ensure only one primary contact
                    if (!$hasPrimary && isset($contactData['is_primary']) && $contactData['is_primary']) {
                        $hasPrimary = true;
                    } elseif ($hasPrimary) {
                        $contactData['is_primary'] = false;
                    }

                    // Set first contact as primary if none selected
                    if ($index === 0 && !$hasPrimary) {
                        $contactData['is_primary'] = true;
                        $hasPrimary = true;
                    }

                    VehicleContact::create($contactData);
                }
            });

            return redirect()->route('vehicles.index')
                           ->with('success', 'Vehicle created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating vehicle: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error creating vehicle. Please try again.');
        }
    }

    /**
     * Display the specified vehicle.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load('contacts');
        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified vehicle.
     */
    public function edit(Vehicle $vehicle)
    {
        $vehicle->load('contacts');
        return view('vehicles.edit', compact('vehicle'));
    }

    /**
     * Update the specified vehicle in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'vehicle_number' => 'required|string|max:255|unique:vehicles,vehicle_number,' . $vehicle->id,
            'tare_weight' => 'nullable|numeric|min:0',
            'load_capacity' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'contacts' => 'required|array|min:1',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.designation' => 'nullable|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.mobile' => 'nullable|string|max:20',
            'contacts.*.is_primary' => 'nullable|boolean',
            'contacts.*.notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $vehicle) {
                // Update vehicle
                $vehicle->update($request->only([
                    'vehicle_number', 'tare_weight', 'load_capacity', 'status', 'notes'
                ]));

                // Delete existing contacts
                $vehicle->contacts()->delete();

                // Create new contacts
                $hasPrimary = false;
                foreach ($request->contacts as $index => $contactData) {
                    $contactData['vehicle_id'] = $vehicle->id;

                    // Ensure only one primary contact
                    if (!$hasPrimary && isset($contactData['is_primary']) && $contactData['is_primary']) {
                        $hasPrimary = true;
                    } elseif ($hasPrimary) {
                        $contactData['is_primary'] = false;
                    }

                    // Set first contact as primary if none selected
                    if ($index === 0 && !$hasPrimary) {
                        $contactData['is_primary'] = true;
                        $hasPrimary = true;
                    }

                    VehicleContact::create($contactData);
                }
            });

            return redirect()->route('vehicles.index')
                           ->with('success', 'Vehicle updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating vehicle: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error updating vehicle. Please try again.');
        }
    }

    /**
     * Remove the specified vehicle from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        try {
            DB::transaction(function () use ($vehicle) {
                $vehicle->delete();
            });

            return redirect()->route('vehicles.index')
                           ->with('success', 'Vehicle deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting vehicle: ' . $e->getMessage());
            return redirect()->route('vehicles.index')
                           ->with('error', 'Error deleting vehicle. Please try again.');
        }
    }

    /**
     * Get vehicle statistics for dashboard.
     */
    public function getStatistics()
    {
        return [
            'total' => Vehicle::count(),
            'active' => Vehicle::where('status', 'active')->count(),
            'inactive' => Vehicle::where('status', 'inactive')->count(),
            'total_load_capacity' => Vehicle::where('status', 'active')->sum('load_capacity'),
            'total_tare_weight' => Vehicle::where('status', 'active')->sum('tare_weight'),
        ];
    }
}
