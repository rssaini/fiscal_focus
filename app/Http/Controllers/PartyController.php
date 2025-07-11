<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\PartyContact;
use App\Models\PartyHasEntity;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartyController extends Controller
{
    /**
     * Display a listing of the parties.
     */
    public function index(Request $request)
    {
        $query = Party::with(['contacts', 'primaryContact', 'entityRelationships.entity']);

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by entity type
        if ($request->filled('entity_type')) {
            $query->withEntityType($request->entity_type);
        }

        // Filter by credit limit range
        if ($request->filled('credit_limit_min')) {
            $query->where('credit_limit', '>=', $request->credit_limit_min);
        }
        if ($request->filled('credit_limit_max')) {
            $query->where('credit_limit', '<=', $request->credit_limit_max);
        }

        $parties = $query->orderBy('name')->paginate(20);

        // Get filter options
        $entityTypes = Party::getAvailableEntityTypes();

        return view('parties.index', compact('parties', 'entityTypes'));
    }

    /**
     * Show the form for creating a new party.
     */
    public function create()
    {
        $availableEntityTypes = Party::getAvailableEntityTypes();
        $availableEntities = $this->getAvailableEntitiesForLinking();

        return view('parties.create', compact('availableEntityTypes', 'availableEntities'));
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

            // Contact validation
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.designation' => 'nullable|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.mobile' => 'nullable|string|max:20',
            'contacts.*.is_primary' => 'boolean',
            'contacts.*.notes' => 'nullable|string',

            // Entity linking validation
            'linked_entities' => 'nullable|array',
            'linked_entities.*' => 'string', // Format: "ModelClass:ID"
        ]);

        try {
            DB::beginTransaction();

            // Create party
            $party = Party::create([
                'name' => $request->name,
                'credit_limit' => $request->credit_limit,
                'credit_days' => $request->credit_days,
            ]);

            // Create contacts
            if ($request->has('contacts')) {
                $this->createPartyContacts($party, $request->contacts);
            }

            // Link entities
            if ($request->has('linked_entities')) {
                $this->linkEntitiesToParty($party, $request->linked_entities);
            }

            DB::commit();

            return redirect()->route('parties.index')
                ->with('success', 'Party created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error creating party: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified party.
     */
    public function show(Party $party)
    {
        $party->load(['contacts', 'entityRelationships.entity']);

        // Get entity counts by type
        $entityCounts = $party->getEntityCountByType();

        // Get recent activity or transactions if available
        $recentActivity = $this->getRecentActivityForParty($party);

        return view('parties.show', compact('party', 'entityCounts', 'recentActivity'));
    }

    /**
     * Show the form for editing the specified party.
     */
    public function edit(Party $party)
    {
        $party->load(['contacts', 'entityRelationships.entity']);
        $availableEntityTypes = Party::getAvailableEntityTypes();
        $availableEntities = $this->getAvailableEntitiesForLinking();
        $linkedEntityIds = $this->getLinkedEntityIds($party);

        return view('parties.edit', compact('party', 'availableEntityTypes', 'availableEntities', 'linkedEntityIds'));
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

            // Contact validation
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.designation' => 'nullable|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.mobile' => 'nullable|string|max:20',
            'contacts.*.is_primary' => 'boolean',
            'contacts.*.notes' => 'nullable|string',

            // Entity linking validation
            'linked_entities' => 'nullable|array',
            'linked_entities.*' => 'string', // Format: "ModelClass:ID"
        ]);

        try {
            DB::beginTransaction();

            // Update party
            $party->update([
                'name' => $request->name,
                'credit_limit' => $request->credit_limit,
                'credit_days' => $request->credit_days,
            ]);

            // Update contacts
            $party->contacts()->delete();
            if ($request->has('contacts')) {
                $this->createPartyContacts($party, $request->contacts);
            }

            // Update entity relationships
            $party->entityRelationships()->delete();
            if ($request->has('linked_entities')) {
                $this->linkEntitiesToParty($party, $request->linked_entities);
            }

            DB::commit();

            return redirect()->route('parties.show', $party)
                ->with('success', 'Party updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error updating party: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified party from storage.
     */
    public function destroy(Party $party)
    {
        try {
            $party->delete();
            return redirect()->route('parties.index')
                ->with('success', 'Party deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error deleting party: ' . $e->getMessage()]);
        }
    }

    /**
     * Link an entity to a party via AJAX.
     */
    public function linkEntity(Request $request, Party $party)
    {
        $request->validate([
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
        ]);

        try {
            $entityClass = $request->entity_type;
            $entity = $entityClass::findOrFail($request->entity_id);

            $relationship = $party->linkEntity($entity);

            return response()->json([
                'success' => true,
                'message' => 'Entity linked successfully.',
                'relationship' => $relationship->load('entity')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error linking entity: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Unlink an entity from a party via AJAX.
     */
    public function unlinkEntity(Request $request, Party $party)
    {
        $request->validate([
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
        ]);

        try {
            $entityClass = $request->entity_type;
            $entity = $entityClass::findOrFail($request->entity_id);

            $success = $party->unlinkEntity($entity);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Entity unlinked successfully.' : 'Entity was not linked.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unlinking entity: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get entities for AJAX requests.
     */
    public function getEntitiesByType(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string',
        ]);

        try {
            $entityClass = $request->entity_type;
            $entities = $entityClass::select('id', 'name')->get();

            return response()->json([
                'success' => true,
                'entities' => $entities
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching entities: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Helper method to create party contacts.
     */
    private function createPartyContacts(Party $party, array $contactsData)
    {
        $hasPrimary = collect($contactsData)->contains('is_primary', true);

        foreach ($contactsData as $index => $contactData) {
            if (!empty($contactData['name'])) {
                // Set first contact as primary if no primary is selected
                $isPrimary = $contactData['is_primary'] ?? false;
                if (!$hasPrimary && $index === 0) {
                    $isPrimary = true;
                }

                PartyContact::create([
                    'party_id' => $party->id,
                    'name' => $contactData['name'],
                    'designation' => $contactData['designation'] ?? null,
                    'email' => $contactData['email'] ?? null,
                    'phone' => $contactData['phone'] ?? null,
                    'mobile' => $contactData['mobile'] ?? null,
                    'is_primary' => $isPrimary,
                    'notes' => $contactData['notes'] ?? null,
                ]);
            }
        }
    }

    /**
     * Helper method to link entities to party.
     */
    private function linkEntitiesToParty(Party $party, array $linkedEntities)
    {
        foreach ($linkedEntities as $entityString) {
            if (strpos($entityString, ':') !== false) {
                [$entityClass, $entityId] = explode(':', $entityString);

                if (class_exists($entityClass)) {
                    $entity = $entityClass::find($entityId);
                    if ($entity) {
                        $party->linkEntity($entity);
                    }
                }
            }
        }
    }

    /**
     * Get available entities for linking.
     */
    private function getAvailableEntitiesForLinking(): array
    {
        $entities = [];

        // Get customers
        if (class_exists('App\Models\Customer')) {
            $entities['App\Models\Customer'] = Customer::select('id', 'name')->get();
        }

        // Get suppliers
        if (class_exists('App\Models\Supplier')) {
            $entities['App\Models\Supplier'] = Supplier::select('id', 'name')->get();
        }

        // Get employees
        if (class_exists('App\Models\Employee')) {
            $entities['App\Models\Employee'] = Employee::select('id', 'name')->get();
        }

        return $entities;
    }

    /**
     * Get linked entity IDs for editing.
     */
    private function getLinkedEntityIds(Party $party): array
    {
        return $party->entityRelationships->map(function ($relationship) {
            return $relationship->model_type . ':' . $relationship->model_id;
        })->toArray();
    }

    /**
     * Get recent activity for a party (placeholder for future implementation).
     */
    private function getRecentActivityForParty(Party $party): array
    {
        // This could be extended to show recent transactions, vouchers, etc.
        // For now, return empty array
        return [];
    }
}
