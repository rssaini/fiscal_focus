<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class SalesApiController extends Controller
{
    public function getCustomerInfo($customerId)
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        return response()->json([
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->billing_address,
            'credit_limit' => $customer->credit_limit,
            'outstanding_amount' => $customer->getOutstandingAmount() ?? 0
        ]);
    }

    public function getProductInfo($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json([
            'name' => $product->name,
            'code' => $product->code,
            'default_rate' => $product->default_rate
        ]);
    }

    public function calculateSaleAmounts(Request $request)
    {
        $request->validate([
            'tare_wt' => 'required|numeric|min:0',
            'gross_wt' => 'required|numeric|min:0',
            'product_rate' => 'nullable|numeric|min:0',
            'invoice_rate' => 'nullable|numeric|min:0',
            'tp_wt' => 'nullable|numeric|min:0',
        ]);

        $tare_wt = $request->tare_wt;
        $gross_wt = $request->gross_wt;
        $product_rate = $request->product_rate ?: 0;
        $invoice_rate = $request->invoice_rate ?: 0;
        $tp_wt = $request->tp_wt ?: 0;

        $net_wt = $gross_wt - $tare_wt;
        $wt_ton = $net_wt / 1000;
        $amount = $wt_ton * $product_rate;

        // Calculate GST
        $gst_base = $invoice_rate * $tp_wt;
        $cgst = $gst_base * 0.025; // 2.5%
        $sgst = $gst_base * 0.025; // 2.5%
        $total_gst = $cgst + $sgst;
        $total_amount = $amount + $total_gst;

        return response()->json([
            'net_wt' => round($net_wt, 2),
            'wt_ton' => round($wt_ton, 3),
            'amount' => round($amount, 2),
            'cgst' => round($cgst, 2),
            'sgst' => round($sgst, 2),
            'total_gst' => round($total_gst, 2),
            'total_amount' => round($total_amount, 2)
        ]);
    }

    public function getSalesSummary(Request $request)
    {
        $query = Sale::query();

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $summary = [
            'total_sales' => $query->sum('total_amount'),
            'total_paid' => $query->whereHas('payments', function($q) {
                $q->where('status', 'cleared');
            })->withSum(['payments' => function($q) {
                $q->where('status', 'cleared');
            }], 'amount')->get()->sum('payments_sum_amount'),
            'sales_count' => $query->count(),
            'pending_amount' => 0
        ];

        $summary['pending_amount'] = $summary['total_sales'] - $summary['total_paid'];

        return response()->json($summary);
    }
}
