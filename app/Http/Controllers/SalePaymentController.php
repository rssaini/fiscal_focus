<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalePayment;
use App\Http\Requests\SalePaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalePaymentController extends Controller
{
    public function index(Sale $sale)
    {
        $payments = $sale->payments()->orderBy('payment_date', 'desc')->get();
        $remainingAmount = $sale->getRemainingAmount();

        return view('sales.payments.index', compact('sale', 'payments', 'remainingAmount'));
    }

    public function create(Sale $sale)
    {
        if ($sale->isFullyPaid()) {
            return redirect()->route('sales.payments.index', $sale)
                           ->with('error', 'Sale is already fully paid.');
        }

        $remainingAmount = $sale->getRemainingAmount();
        $paymentMethods = SalePayment::getPaymentMethodOptions();

        return view('sales.payments.create', compact('sale', 'remainingAmount', 'paymentMethods'));
    }

    public function store(SalePaymentRequest $request, Sale $sale)
    {
        $remainingAmount = $sale->getRemainingAmount();

        if ($request->amount > $remainingAmount) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Payment amount cannot exceed remaining amount of ₹' . number_format($remainingAmount, 2));
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['sale_id'] = $sale->id;
            $data['payment_reference'] = SalePayment::generatePaymentReference();

            SalePayment::create($data);

            DB::commit();

            return redirect()->route('sales.payments.index', $sale)
                           ->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    public function show(Sale $sale, SalePayment $payment)
    {
        return view('sales.payments.show', compact('sale', 'payment'));
    }

    public function edit(Sale $sale, SalePayment $payment)
    {
        if ($payment->status === 'cancelled') {
            return redirect()->back()
                           ->with('error', 'Cannot edit cancelled payments.');
        }

        $paymentMethods = SalePayment::getPaymentMethodOptions();

        return view('sales.payments.edit', compact('sale', 'payment', 'paymentMethods'));
    }

    public function update(SalePaymentRequest $request, Sale $sale, SalePayment $payment)
    {
        if ($payment->status === 'cancelled') {
            return redirect()->back()
                           ->with('error', 'Cannot update cancelled payments.');
        }

        // Calculate remaining amount excluding current payment
        $remainingAmount = $sale->getRemainingAmount() + $payment->amount;

        if ($request->amount > $remainingAmount) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Payment amount cannot exceed remaining amount of ₹' . number_format($remainingAmount, 2));
        }

        try {
            DB::beginTransaction();

            $payment->update($request->validated());

            DB::commit();

            return redirect()->route('sales.payments.index', $sale)
                           ->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error updating payment: ' . $e->getMessage());
        }
    }

    public function destroy(Sale $sale, SalePayment $payment)
    {
        try {
            $payment->delete();

            return redirect()->route('sales.payments.index', $sale)
                           ->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
    }

    // Add multiple payments at once
    public function storeMultiple(Request $request, Sale $sale)
    {
        $request->validate([
            'payments' => 'required|array|min:1',
            'payments.*.payment_method' => 'required|in:cash,upi,rtgs,neft,cheque,card,discount,adjustment',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.payment_date' => 'required|date',
            'payments.*.transaction_id' => 'nullable|string|max:100',
            'payments.*.cheque_number' => 'nullable|string|max:50',
            'payments.*.cheque_date' => 'nullable|date',
            'payments.*.bank_name' => 'nullable|string|max:100',
            'payments.*.notes' => 'nullable|string',
        ]);

        $totalAmount = array_sum(array_column($request->payments, 'amount'));
        $remainingAmount = $sale->getRemainingAmount();

        if ($totalAmount > $remainingAmount) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Total payment amount cannot exceed remaining amount of ₹' . number_format($remainingAmount, 2));
        }

        try {
            DB::beginTransaction();

            foreach ($request->payments as $paymentData) {
                $paymentData['sale_id'] = $sale->id;
                $paymentData['payment_reference'] = SalePayment::generatePaymentReference();
                $paymentData['status'] = 'cleared';

                SalePayment::create($paymentData);
            }

            DB::commit();

            return redirect()->route('sales.payments.index', $sale)
                           ->with('success', count($request->payments) . ' payments recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error recording payments: ' . $e->getMessage());
        }
    }
}
