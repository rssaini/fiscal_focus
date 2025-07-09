<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\CustomerDocument;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['primaryContact', 'ledger']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer type
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Filter by state
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $customers = $query->orderBy('name')->paginate(20);

        // Get filter options
        $cities = Customer::distinct()->pluck('city')->filter()->sort();
        $states = Customer::distinct()->pluck('state')->filter()->sort();

        return view('customers.index', compact('customers', 'cities', 'states'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'customer_type' => 'required|in:individual,business',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'gstin' => 'nullable|string|max:15',
            'pan' => 'nullable|string|max:10',
            'billing_address' => 'required|string',
            'shipping_address' => 'nullable|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_balance_type' => 'required|in:debit,credit',
            'opening_date' => 'required|date',
            'notes' => 'nullable|string',

            // Contact details
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.designation' => 'nullable|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.mobile' => 'nullable|string|max:20',
            'contacts.*.is_primary' => 'boolean',
            'contacts.*.notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Create customer
            $customer = new Customer($request->only([
                'name', 'company_name', 'customer_type', 'email', 'phone', 'mobile',
                'website', 'gstin', 'pan', 'billing_address', 'shipping_address',
                'city', 'state', 'country', 'pincode', 'credit_limit', 'credit_days',
                'opening_balance', 'opening_balance_type', 'opening_date', 'notes'
            ]));

            $customer->customer_code = $customer->generateCustomerCode();
            $customer->save();

            // Create ledger for customer
            $customer->createLedger();

            // Create contacts
            if ($request->has('contacts')) {
                foreach ($request->contacts as $contactData) {
                    if (!empty($contactData['name'])) {
                        CustomerContact::create([
                            'customer_id' => $customer->id,
                            'name' => $contactData['name'],
                            'designation' => $contactData['designation'] ?? null,
                            'email' => $contactData['email'] ?? null,
                            'phone' => $contactData['phone'] ?? null,
                            'mobile' => $contactData['mobile'] ?? null,
                            'is_primary' => $contactData['is_primary'] ?? false,
                            'notes' => $contactData['notes'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error creating customer: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'contacts',
            'documents',
            'ledger.transactions' => function ($query) {
                $query->orderBy('transaction_date', 'desc')->limit(10);
            }
        ]);

        // Get summary data
        $summary = [
            'current_balance' => $customer->getCurrentBalance(),
            'outstanding_amount' => $customer->getOutstandingAmount(),
            'advance_amount' => $customer->getAdvanceAmount(),
            'overdue_amount' => $customer->getOverdueAmount(),
            'credit_utilization' => $customer->getCreditUtilizationPercentage(),
            'is_over_limit' => $customer->isOverCreditLimit(),
        ];

        return view('customers.show', compact('customer', 'summary'));
    }

    public function edit(Customer $customer)
    {
        $customer->load('contacts');
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'customer_type' => 'required|in:individual,business',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'gstin' => 'nullable|string|max:15',
            'pan' => 'nullable|string|max:10',
            'billing_address' => 'required|string',
            'shipping_address' => 'nullable|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'status' => 'required|in:active,inactive,blocked',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $customer->update($request->only([
                'name', 'company_name', 'customer_type', 'email', 'phone', 'mobile',
                'website', 'gstin', 'pan', 'billing_address', 'shipping_address',
                'city', 'state', 'country', 'pincode', 'status', 'credit_limit',
                'credit_days', 'notes'
            ]));

            // Update ledger name if changed
            if ($customer->ledger) {
                $customer->ledger->update([
                    'name' => $customer->display_name,
                    'is_active' => $customer->status === 'active'
                ]);
            }

            DB::commit();

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error updating customer: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            DB::beginTransaction();

            // Check if customer has transactions
            if ($customer->ledger && $customer->ledger->transactions()->count() > 0) {
                return redirect()->route('customers.index')
                    ->with('error', 'Cannot delete customer with existing transactions.');
            }

            // Delete associated records
            $customer->contacts()->delete();
            $customer->documents()->delete();

            // Delete ledger if exists
            if ($customer->ledger) {
                $customer->ledger->delete();
            }

            $customer->delete();

            DB::commit();

            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('customers.index')
                ->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }

    public function ledger(Customer $customer, Request $request)
    {
        if (!$customer->ledger) {
            return redirect()->route('customers.show', $customer)
                ->with('error', 'No ledger found for this customer.');
        }

        return redirect()->route('ledgers.show', $customer->ledger)
            ->with('from_date', $request->get('from_date'))
            ->with('to_date', $request->get('to_date'));
    }

    public function statement(Customer $customer, Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->endOfMonth()->format('Y-m-d'));

        if (!$customer->ledger) {
            return redirect()->route('customers.show', $customer)
                ->with('error', 'No ledger found for this customer.');
        }

        $openingBalance = $customer->ledger->getOpeningBalanceForDate($fromDate);

        $transactions = $customer->ledger->transactions()
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        return view('customers.statement', compact('customer', 'transactions', 'openingBalance', 'fromDate', 'toDate'));
    }

    public function uploadDocument(Request $request, Customer $customer)
    {
        $request->validate([
            'document_type' => 'required|string|max:255',
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // 10MB max
            'expiry_date' => 'nullable|date',
            'description' => 'nullable|string'
        ]);

        try {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('customer_documents/' . $customer->id, $fileName, 'public');

            CustomerDocument::create([
                'customer_id' => $customer->id,
                'document_type' => $request->document_type,
                'document_name' => $request->document_name,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'expiry_date' => $request->expiry_date,
                'description' => $request->description
            ]);

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Document uploaded successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error uploading document: ' . $e->getMessage()]);
        }
    }

    public function deleteDocument(Customer $customer, CustomerDocument $document)
    {
        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->delete();

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error deleting document: ' . $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        $query = Customer::with(['primaryContact', 'ledger']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $customers = $query->orderBy('name')->get();

        $filename = "customers_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'Customer Code', 'Name', 'Company Name', 'Type', 'Email', 'Mobile',
                'City', 'State', 'Status', 'Credit Limit', 'Current Balance', 'Outstanding'
            ]);

            foreach ($customers as $customer) {
                $balance = $customer->getCurrentBalance();
                fputcsv($file, [
                    $customer->customer_code,
                    $customer->name,
                    $customer->company_name,
                    ucfirst($customer->customer_type),
                    $customer->email,
                    $customer->mobile,
                    $customer->city,
                    $customer->state,
                    ucfirst($customer->status),
                    number_format($customer->credit_limit, 2),
                    number_format($balance['balance'], 2) . ' ' . ucfirst($balance['type']),
                    number_format($customer->getOutstandingAmount(), 2)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
