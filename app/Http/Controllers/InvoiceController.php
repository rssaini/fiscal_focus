<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function weighmentSlip(Sale $sale){
        return view('prints.weight-receipt',compact('sale'));
    }

    public function generatePDF(Sale $sale)
    {
        // Load all necessary relationships
        $sale->load(['customer', 'refParty', 'items.product', 'payments']);

        // Prepare invoice data
        $invoiceData = $this->prepareInvoiceData($sale);

        // Generate PDF
        $pdf = Pdf::loadView('invoices.pdf', $invoiceData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('invoice-' . $sale->invoice_number . '.pdf');
    }

    public function downloadPDF(Sale $sale)
    {
        // Load all necessary relationships
        $sale->load(['customer', 'refParty', 'items.product', 'payments']);

        // Prepare invoice data
        $invoiceData = $this->prepareInvoiceData($sale);

        // Generate PDF
        $pdf = Pdf::loadView('invoices.pdf', $invoiceData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('invoice-' . $sale->invoice_number . '.pdf');
    }

    public function printPDF(Sale $sale)
    {
        // Load all necessary relationships
        $sale->load(['customer', 'refParty', 'items.product', 'payments']);

        // Prepare invoice data
        $invoiceData = $this->prepareInvoiceData($sale);

        // Generate PDF for printing (with print-optimized CSS)
        $pdf = Pdf::loadView('invoices.pdf', $invoiceData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('invoice-' . $sale->invoice_number . '.pdf', ['Attachment' => false]);
    }

    private function prepareInvoiceData(Sale $sale)
    {
        // Company details
        $company = [
            'name' => 'JAI BAJRANG BALI STONE CRUSHER',
            'address' => 'Ward No 08, Ramkuwarpura, Khetri, Jhunjhunu, Rajasthan - 333503',
            'upi_id' => '9518106240.eazypay@icici',
            'gstn' => '08AAQFJ6966L1ZD',
            'state' => 'Rajasthan',
            'bank_details' => [
                'account_holder' => 'Jai Bajarang Bali Stone Crusher',
                'bank_name' => 'AU BANK',
                'account_number' => '2121219531645909',
                'ifsc_code' => 'AUBL0002195'
            ]
        ];

        // Calculate tax amounts
        $taxableAmount = $sale->subtotal - $sale->discount_amount;
        $cgstAmount = 0;
        $sgstAmount = 0;
        $igstAmount = 0;

        // Check if inter-state or intra-state
        $customerState = $sale->customer ? $sale->customer->state : 'Haryana';
        $isInterState = strtolower($customerState) !== strtolower($company['state']);

        if ($isInterState) {
            // IGST @ 5%
            $igstAmount = $taxableAmount * 0.05;
        } else {
            // CGST @ 2.5% + SGST @ 2.5%
            $cgstAmount = $taxableAmount * 0.025;
            $sgstAmount = $taxableAmount * 0.025;
        }

        $totalGst = $cgstAmount + $sgstAmount + $igstAmount;
        $totalAmount = $taxableAmount + $totalGst;

        // Convert amount to words
        $amountInWords = $this->numberToWords($totalAmount);

        // Return prepared data
        return [
            'company' => $company,
            'sale' => $sale,
            'tax_amount' => $taxableAmount,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'igst_amount' => $igstAmount,
            'total_gst' => $totalGst,
            'total_amount' => $totalAmount,
            'amount_in_words' => $amountInWords,
            'is_inter_state' => $isInterState,
            'customer_state' => $customerState
        ];
    }

    private function numberToWords($number)
    {
        $amount = number_format($number, 2, '.', '');
        $amount_after_decimal = round($amount - ($num = floor($amount)), 2) * 100;
        $amt_hundred = null;
        $count_length = strlen($num);
        $x = 0;
        $string = array();
        $change_words = array(
            0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        );
        $here_digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($x < $count_length) {
            $get_divider = ($x == 2) ? 10 : 100;
            $amount = floor($num % $get_divider);
            $num = floor($num / $get_divider);
            $x += $get_divider == 10 ? 1 : 2;
            if ($amount) {
                $add_plural = (($counter = count($string)) && $amount > 9) ? 's' : null;
                $amt_hundred = ($counter == 1 && $string[0]) ? ' and ' : null;
                $string[] = ($amount < 21) ? $change_words[$amount] . ' ' . $here_digits[$counter] . $add_plural . '
				' . $amt_hundred : $change_words[floor($amount / 10) * 10] . ' ' . $change_words[$amount % 10] . '
				' . $here_digits[$counter] . $add_plural . ' ' . $amt_hundred;
            } else $string[] = null;
        }
        $implode_to_Rupees = implode('', array_reverse($string));
        $get_paise = ($amount_after_decimal > 0) ? "and " . ($change_words[$amount_after_decimal / 10] . "
		" . $change_words[$amount_after_decimal % 10]) . ' Paise' : '';
        return ($implode_to_Rupees ? $implode_to_Rupees . 'Rupees ' : '') . $get_paise;
    }
}
