<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\PartyContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartyController extends Controller
{
    /**
     * Display a listing of the parties.
     */
    public function index()
    {
        $parties = Party::with(['contacts', 'primaryContact'])->paginate(10);
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
            'credit_days' => 'required|integer|min:0',
            'contacts' => 'required|array|min:1',
            'contacts.*.contact_type' => 'required|string|in:phone,email,fax,address',
            'contacts.*.contact_value' => 'required|string|max:255',
            'contacts.*.designation' => 'nullable|string|max:255',
            'contacts.*.is_primary' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            $party = Party::create([
                'name' => $request->name,
                'credit_limit' => $request->credit_limit,
                'credit_days' => $request->credit_days,
            ]);

            // Ensure at least one primary contact
            $hasPrimary = collect($request->contacts)->contains('is_primary', true);
            if (!$hasPrimary) {
                $request->merge([
                    'contacts' => collect($request->contacts)->map(function ($contact, $index) {
                        if ($index === 0) {
                            $contact['is_primary'] = true;
                        }
                        return $contact;
                    })->toArray()
                ]);
            }

            foreach ($request->contacts as $contact) {
                PartyContact::create([
                    'party_id' => $party->id,
                    'contact_type' => $contact['contact_type'],
                    'contact_value' => $contact['contact_value'],
                    'designation' => $contact['designation'] ?? null,
                    'is_primary' => $contact['is_primary'] ?? false,
                ]);
            }
        });

        return redirect()->route('parties.index')->with('success', 'Party created successfully.');
    }

    /**
     * Display the specified party.
     */
    public function show(Party $party)
    {
        $party->load('contacts');
        return view('parties.show', compact('party'));
    }

    /**
     * Show the form for editing the specified party.
     */
    public function edit(Party $party)
    {
        $party->load('contacts');
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
            'credit_days' => 'required|integer|min:0',
            'contacts' => 'required|array|min:1',
            'contacts.*.contact_type' => 'required|string|in:phone,email,fax,address',
            'contacts.*.contact_value' => 'required|string|max:255',
            'contacts.*.designation' => 'nullable|string|max:255',
            'contacts.*.is_primary' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $party) {
            $party->update([
                'name' => $request->name,
                'credit_limit' => $request->credit_limit,
                'credit_days' => $request->credit_days,
            ]);

            // Delete existing contacts
            $party->contacts()->delete();

            // Ensure at least one primary contact
            $hasPrimary = collect($request->contacts)->contains('is_primary', true);
            if (!$hasPrimary) {
                $request->merge([
                    'contacts' => collect($request->contacts)->map(function ($contact, $index) {
                        if ($index === 0) {
                            $contact['is_primary'] = true;
                        }
                        return $contact;
                    })->toArray()
                ]);
            }

            foreach ($request->contacts as $contact) {
                PartyContact::create([
                    'party_id' => $party->id,
                    'contact_type' => $contact['contact_type'],
                    'contact_value' => $contact['contact_value'],
                    'designation' => $contact['designation'] ?? null,
                    'is_primary' => $contact['is_primary'] ?? false,
                ]);
            }
        });

        return redirect()->route('parties.index')->with('success', 'Party updated successfully.');
    }

    /**
     * Remove the specified party from storage.
     */
    public function destroy(Party $party)
    {
        $party->delete();
        return redirect()->route('parties.index')->with('success', 'Party deleted successfully.');
    }
}
