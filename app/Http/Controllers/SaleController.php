<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Party;
use App\Models\Product;
use App\Http\Requests\SaleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'refParty', 'product']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('vehicle_no', 'like', "%{$search}%")
                  ->orWhere('rec_no', 'like', "%{$search}%")
                  ->orWhere('consignee_name', 'like', "%{$search}%");
            });
        }

        $sales = $query->orderBy('date', 'desc')->paginate(15);

        $customers = Customer::active()->pluck('name', 'id');

        return view('sales.index', compact('sales', 'customers'));
    }

    public function create()
    {
        $customers = Customer::active()->get();
        $parties = Party::all();
        $products = Product::active()->get();

        return view('sales.create', compact('customers', 'parties', 'products'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validate([
                'vehicle_no' => 'required|string|max:20',
                'tare_wt' => 'required|numeric|min:0',
                'product_id' => 'required|exists:products,id',
                'product_rate' => 'nullable|numeric|min:0',
                'date' => 'nullable|date',
            ]);

            /*
            // Generate invoice number if not provided
            if (empty($data['invoice_number'])) {
                $data['invoice_number'] = Sale::generateInvoiceNumber();
            }
            */

            // Set date if not provided
            if (empty($data['date'])) {
                $data['date'] = now();
            }

            $sale = Sale::create($data);


            DB::commit();
            return redirect()->route('sales.show', $sale)
                           ->with('success', 'Sale created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error creating sale: ' . $e->getMessage());
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'refParty', 'product', 'payments']);

        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        // Only allow editing of draft or confirmed sales
        if (in_array($sale->status, ['paid', 'cancelled'])) {
            return redirect()->route('sales.show', $sale)
                           ->with('error', 'Cannot edit ' . $sale->status . ' sales.');
        }

        $customers = Customer::active()->get();
        $parties = Party::all();
        $products = Product::active()->get();

        return view('sales.edit', compact('sale', 'customers', 'parties', 'products'));
    }

    public function update(SaleRequest $request, Sale $sale)
    {
        // Only allow updating of draft or confirmed sales
        if (in_array($sale->status, ['paid', 'cancelled'])) {
            return redirect()->route('sales.show', $sale)
                           ->with('error', 'Cannot update ' . $sale->status . ' sales.');
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();
            $sale->update($data);

            DB::commit();

            return redirect()->route('sales.show', $sale)
                           ->with('success', 'Sale updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error updating sale: ' . $e->getMessage());
        }
    }

    public function destroy(Sale $sale)
    {
        // Only allow deletion of draft sales
        if ($sale->status !== 'draft') {
            return redirect()->route('sales.index')
                           ->with('error', 'Only draft sales can be deleted.');
        }

        try {
            $sale->delete();
            return redirect()->route('sales.index')
                           ->with('success', 'Sale deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('sales.index')
                           ->with('error', 'Error deleting sale: ' . $e->getMessage());
        }
    }

    public function confirm(Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return redirect()->back()
                           ->with('error', 'Only draft sales can be confirmed.');
        }

        $sale->update(['status' => 'confirmed']);

        return redirect()->back()
                       ->with('success', 'Sale confirmed successfully.');
    }

    public function cancel(Sale $sale)
    {
        if (in_array($sale->status, ['paid', 'cancelled'])) {
            return redirect()->back()
                           ->with('error', 'Cannot cancel ' . $sale->status . ' sales.');
        }

        $sale->update(['status' => 'cancelled']);

        return redirect()->back()
                       ->with('success', 'Sale cancelled successfully.');
    }

    // Quick create for minimal required fields
    public function quickStore(Request $request)
    {
        $request->validate([
            'tare_wt' => 'required|numeric|min:0',
            'gross_wt' => 'required|numeric|min:0|gt:tare_wt',
            'product_id' => 'required|exists:products,id',
            'vehicle_no' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::create([
                'invoice_number' => Sale::generateInvoiceNumber(),
                'date' => now(),
                'tare_wt' => $request->tare_wt,
                'gross_wt' => $request->gross_wt,
                'product_id' => $request->product_id,
                'vehicle_no' => $request->vehicle_no,
                'rec_no' => 'TEMP-' . time(), // Temporary receipt number
                'consignee_name' => 'To be filled',
                'consignee_address' => 'To be filled',
                'status' => 'draft'
            ]);

            DB::commit();

            return redirect()->route('sales.edit', $sale)
                           ->with('success', 'Sale created. Please complete the remaining details.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error creating sale: ' . $e->getMessage());
        }
    }
}
