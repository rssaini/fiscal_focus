<?php

namespace App\Http\Controllers;

use App\Models\Mines;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MinesController extends Controller
{
    /**
     * Display a listing of the mines.
     */
    public function index(Request $request)
    {
        $query = Mines::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $mines = $query->orderBy('ml_number')->paginate(15);

        return view('mines.index', compact('mines'));
    }

    /**
     * Show the form for creating a new mines.
     */
    public function create()
    {
        return view('mines.create');
    }

    /**
     * Store a newly created mines in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'ml_number' => 'required|string|max:255|unique:mines',
            'mines_name' => 'nullable|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                Mines::create($validatedData);
            });

            return redirect()->route('mines.index')
                           ->with('success', 'Mines created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating mines: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error creating mines. Please try again.');
        }
    }

    /**
     * Display the specified mines.
     */
    public function show(Mines $mine)
    {
        return view('mines.show', compact('mine'));
    }

    /**
     * Show the form for editing the specified mines.
     */
    public function edit(Mines $mine)
    {
        return view('mines.edit', compact('mine'));
    }

    /**
     * Update the specified mines in storage.
     */
    public function update(Request $request, Mines $mine)
    {
        $validatedData = $request->validate([
            'ml_number' => 'required|string|max:255|unique:mines,ml_number,' . $mine->id,
            'mines_name' => 'nullable|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($mine, $validatedData) {
                $mine->update($validatedData);
            });

            return redirect()->route('mines.index')
                           ->with('success', 'Mines updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating mines: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error updating mines. Please try again.');
        }
    }

    /**
     * Remove the specified mines from storage.
     */
    public function destroy(Mines $mine)
    {
        try {
            DB::transaction(function () use ($mine) {
                $mine->delete();
            });

            return redirect()->route('mines.index')
                           ->with('success', 'Mines deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting mines: ' . $e->getMessage());
            return redirect()->route('mines.index')
                           ->with('error', 'Error deleting mines. Please try again.');
        }
    }

    /**
     * Get mines statistics for dashboard.
     */
    public function getStatistics()
    {
        return [
            'total' => Mines::count(),
            'active' => Mines::where('status', 'active')->count(),
            'inactive' => Mines::where('status', 'inactive')->count(),
        ];
    }
}
