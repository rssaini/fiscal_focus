<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Consignee;
use App\Models\Party;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['consignee', 'refParty', 'items.product']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('consignee_id')) {
            $query->where('consignee_id', $request->consignee_id);
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
        $consignees = Consignee::active()->pluck('consignee_name', 'id');

        return view('sales.index', compact('sales', 'consignees'));
    }

    public function create()
    {
        $consignees = Consignee::active()->get();
        $parties = Party::all();
        $products = Product::active()->get();

        return view('sales.create', compact('consignees', 'parties', 'products'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'vehicle_no' => 'required|string|max:20',
            'tare_wt' => 'required|integer|min:0',
            'product_id' => 'required|exists:products,id',
            'product_rate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create the sale record
            $sale = Sale::create([
                'date' => $request->date,
                'vehicle_no' => $request->vehicle_no,
                'tare_wt' => $request->tare_wt,
                'status' => 'pending',
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
            ]);

            // Create the first sale item
            $saleItem = SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $request->product_id,
                'tare_wt' => $request->tare_wt, // First item uses sale's tare weight
                'rate' => $request->product_rate,
                'sort_order' => 1,
            ]);

            DB::commit();

            return redirect()->route('sales.show', $sale)
                           ->with('success', 'Sale created successfully. Please weigh the loaded vehicle to complete the first product.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error creating sale: ' . $e->getMessage());
        }
    }

    public function show(Sale $sale)
    {
        if ($sale->status === 'draft') {
            return redirect()->route('sales.edit', $sale->id);
        }
        $sale->load(['consignee', 'refParty', 'items.product', 'payments']);

        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        if (in_array($sale->status, ['paid', 'cancelled'])) {
            return redirect()->route('sales.show', $sale)
                           ->with('error', 'Cannot edit ' . $sale->status . ' sales.');
        }
        if ($sale->status !== 'draft') {
            return redirect()->route('sales.show', $sale)
                           ->with('error', 'Only draft sales can be updated.');
        }

        $consignees = Consignee::active()->get();
        $parties = Party::all();
        $products = Product::active()->get();
        $sale->load(['items.product']);

        return view('sales.edit', compact('sale', 'consignees', 'parties', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        if (in_array($sale->status, ['paid', 'cancelled'])) {
            return redirect()->route('sales.show', $sale)
                           ->with('error', 'Cannot update ' . $sale->status . ' sales.');
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'consignee_id' => 'nullable|exists:consignees,id',
            'ref_party_id' => 'nullable|exists:parties,id',
            'vehicle_no' => 'required|string|max:20',
            'tp_no' => 'nullable|string|max:50',
            'tp_wt' => 'nullable|numeric|min:0',
            'rec_no' => 'nullable|string|max:50',
            'royalty_book_no' => 'nullable|string|max:50',
            'royalty_receipt_no' => 'nullable|string|max:50',
            'consignee_name' => 'nullable|string|max:255',
            'consignee_address' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            $sale->update($request->all());

            return redirect()->route('sales.show', $sale)
                           ->with('success', 'Sale updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error updating sale: ' . $e->getMessage());
        }
    }

    public function destroy(Sale $sale)
    {
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

    // Add gross weight to a sale item (weighing process)
    public function weighItem(Request $request, Sale $sale, SaleItem $saleItem)
    {
        $validator = Validator::make($request->all(), [
            'gross_wt' => 'required|integer|min:' . ($saleItem->tare_wt + 1),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->with('error', 'Gross weight must be greater than tare weight (' . $saleItem->tare_wt . ' kg)');
        }

        try {
            $saleItem->update([
                'gross_wt' => $request->gross_wt
            ]);

            return redirect()->back()
                           ->with('success', 'Item weighed successfully. Net weight: ' . $saleItem->formatted_weight);

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error weighing item: ' . $e->getMessage());
        }
    }

    // Add a new product to the sale
    public function addProduct(Request $request, Sale $sale)
    {
        if (!$sale->canAddProducts()) {
            return redirect()->back()
                           ->with('error', 'Cannot add products to ' . $sale->status . ' sales.');
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator);
        }

        try {
            $nextSortOrder = SaleItem::getNextSortOrder($sale->id);
            $tareWeight = SaleItem::calculateTareWeight($sale->id, $nextSortOrder);

            $saleItem = SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $request->product_id,
                'tare_wt' => $tareWeight,
                'rate' => $request->rate,
                'sort_order' => $nextSortOrder,
            ]);

            return redirect()->back()
                           ->with('success', 'Product added successfully. Tare weight: ' . number_format($tareWeight) . ' kg. Please weigh the loaded vehicle.');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error adding product: ' . $e->getMessage());
        }
    }

    // Remove a product from the sale
    public function removeProduct(Sale $sale, SaleItem $saleItem)
    {
        if (!$sale->canAddProducts()) {
            return redirect()->back()
                           ->with('error', 'Cannot remove products from ' . $sale->status . ' sales.');
        }

        try {
            // Check if this item affects subsequent items
            $subsequentItems = SaleItem::where('sale_id', $sale->id)
                                     ->where('sort_order', '>', $saleItem->sort_order)
                                     ->exists();

            if ($subsequentItems) {
                return redirect()->back()
                               ->with('error', 'Cannot remove this product as it affects subsequent items. Remove later products first.');
            }

            $saleItem->delete();

            return redirect()->back()
                           ->with('success', 'Product removed successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error removing product: ' . $e->getMessage());
        }
    }

    public function confirm(Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return redirect()->back()
                           ->with('error', 'Only draft sales can be confirmed.');
        }

        // Check if all items are complete
        $incompleteItems = $sale->items()->where(function($q) {
            $q->whereNull('gross_wt')->orWhere('gross_wt', 0);
        })->count();

        if ($incompleteItems > 0) {
            return redirect()->back()
                           ->with('error', 'Cannot confirm sale. ' . $incompleteItems . ' items are not weighed yet.');
        }

        try {
            $sale->update(['status' => 'confirmed']);

            return redirect()->back()
                           ->with('success', 'Sale confirmed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error confirming sale: ' . $e->getMessage());
        }
    }

    public function draft(Sale $sale)
    {
        if ($sale->status !== 'pending') {
            return redirect()->back()
                           ->with('error', 'Only pending sales can be drafted.');
        }

        // Check if all items are complete
        $incompleteItems = $sale->items()->where(function($q) {
            $q->whereNull('gross_wt')->orWhere('gross_wt', 0);
        })->count();

        if ($incompleteItems > 0) {
            return redirect()->back()
                           ->with('error', 'Cannot draft sale. ' . $incompleteItems . ' items are not weighed yet.');
        }

        try {
            $sale->update(['status' => 'draft']);
            return redirect()->route('sales.edit', $sale->id)->with('success', 'Sale drafted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error drafting sale: ' . $e->getMessage());
        }
    }

    public function cancel(Sale $sale)
    {
        if (in_array($sale->status, ['paid', 'cancelled'])) {
            return redirect()->back()
                           ->with('error', 'Cannot cancel ' . $sale->status . ' sales.');
        }

        try {
            $sale->update(['status' => 'cancelled']);

            return redirect()->back()
                           ->with('success', 'Sale cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error cancelling sale: ' . $e->getMessage());
        }
    }

    // API endpoint to get product details and default rate
    public function getProductDetails(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'default_rate' => $product->default_rate,
        ]);
    }

    // API endpoint to get next tare weight for adding product
    public function getNextTareWeight(Sale $sale)
    {
        return response()->json([
            'next_tare_weight' => $sale->getNextTareWeight(),
            'can_add_products' => $sale->canAddProducts(),
        ]);
    }
}
