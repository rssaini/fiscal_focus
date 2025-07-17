<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Mines;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PurchaseImportService;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the purchases.
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['mines', 'vehicle']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('datetime', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('datetime', '<=', $request->end_date);
        }

        // Filter by mines
        if ($request->has('mines_id') && $request->mines_id) {
            $query->where('mines_id', $request->mines_id);
        }

        // Filter by vehicle
        if ($request->has('vehicle_id') && $request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by use_at
        if ($request->has('use_at') && $request->use_at) {
            $query->where('use_at', $request->use_at);
        }

        $purchases = $query->orderBy('datetime', 'desc')->paginate(15);

        // Get filter options
        $mines = Mines::orderBy('ml_number')->get();
        $vehicles = Vehicle::orderBy('vehicle_number')->get();

        return view('purchases.index', compact('purchases', 'mines', 'vehicles'));
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create()
    {
        $mines = Mines::where('status', 'active')->orderBy('ml_number')->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('vehicle_number')->get();

        return view('purchases.create', compact('mines', 'vehicles'));
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'datetime' => 'required|date',
            'mines_id' => 'required|exists:mines,id',
            'rec_no' => 'required|string|max:255',
            'token_no' => 'required|string|max:255',
            'vehicle_id' => 'required|exists:vehicles,id',
            'gross_wt' => 'required|integer|min:0',
            'tare_wt' => 'required|integer|min:0',
            'driver' => 'required|string|max:255',
            'commission' => 'nullable|numeric|min:0',
            'use_at' => 'required|in:stock,manufacturing',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                Purchase::create($validatedData);
            });

            return redirect()->route('purchases.index')
                           ->with('success', 'Purchase record created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating purchase: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error creating purchase record. Please try again.');
        }
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['mines', 'vehicle']);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified purchase.
     */
    public function edit(Purchase $purchase)
    {
        $mines = Mines::where('status', 'active')->orderBy('ml_number')->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('vehicle_number')->get();

        return view('purchases.edit', compact('purchase', 'mines', 'vehicles'));
    }

    /**
     * Update the specified purchase in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $validatedData = $request->validate([
            'datetime' => 'required|date',
            'mines_id' => 'required|exists:mines,id',
            'rec_no' => 'required|string|max:255',
            'token_no' => 'required|string|max:255',
            'vehicle_id' => 'required|exists:vehicles,id',
            'gross_wt' => 'required|integer|min:0',
            'tare_wt' => 'required|integer|min:0',
            'driver' => 'required|string|max:255',
            'commission' => 'nullable|numeric|min:0',
            'use_at' => 'required|in:stock,manufacturing',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($purchase, $validatedData) {
                $purchase->update($validatedData);
            });

            return redirect()->route('purchases.index')
                           ->with('success', 'Purchase record updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating purchase: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error updating purchase record. Please try again.');
        }
    }

    /**
     * Remove the specified purchase from storage.
     */
    public function destroy(Purchase $purchase)
    {
        try {
            DB::transaction(function () use ($purchase) {
                $purchase->delete();
            });

            return redirect()->route('purchases.index')
                           ->with('success', 'Purchase record deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting purchase: ' . $e->getMessage());
            return redirect()->route('purchases.index')
                           ->with('error', 'Error deleting purchase record. Please try again.');
        }
    }

    /**
     * Get vehicle drivers for AJAX request.
     */
    public function getVehicleDrivers(Request $request)
    {
        $vehicleId = $request->vehicle;
        $vehicle = Vehicle::with('contacts')->find($vehicleId);

        if (!$vehicle) {
            return response()->json(['drivers' => []]);
        }

        $drivers = [];

        // Get drivers from vehicle contacts
        foreach ($vehicle->contacts as $contact) {
            if ($contact->designation && (
                stripos($contact->designation, 'driver') !== false ||
                stripos($contact->designation, 'operator') !== false
            )) {
                $drivers[] = $contact->name;
            } else {
                // If no specific driver designation, add all contacts
                $drivers[] = $contact->name;
            }
        }

        // Remove duplicates
        $drivers = array_unique($drivers);

        return response()->json(['drivers' => array_values($drivers)]);
    }

    /**
     * Get purchase statistics for dashboard.
     */
    public function getStatistics()
    {
        return [
            'total_purchases' => Purchase::count(),
            'today_purchases' => Purchase::whereDate('datetime', today())->count(),
            'total_weight_tons' => Purchase::sum('wt_ton'),
            'total_commission' => Purchase::sum('commission'),
            'stock_purchases' => Purchase::where('use_at', 'stock')->count(),
            'manufacturing_purchases' => Purchase::where('use_at', 'manufacturing')->count(),
        ];
    }

    /**
     * Show the form for bulk import.
     */
    public function bulkImport()
    {
        $mines = Mines::where('status', 'active')->orderBy('ml_number')->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('vehicle_number')->get();

        return view('purchases.bulk-import', compact('mines', 'vehicles'));
    }

    /**
     * Download CSV template for bulk import.
     */
    public function downloadTemplate()
    {
        $filename = 'purchase_import_template.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, [
                'datetime',
                'mines_id',
                'rec_no',
                'token_no',
                'vehicle_id',
                'gross_wt',
                'tare_wt',
                'driver',
                'commission',
                'use_at',
                'notes'
            ]);

            // Sample data rows
            fputcsv($file, [
                '2025-07-16 10:30:00',
                '1',
                'REC001',
                'TOK001',
                '1',
                '5000',
                '2000',
                'John Doe',
                '500.00',
                'stock',
                'Sample purchase record'
            ]);

            fputcsv($file, [
                '2025-07-16 11:00:00',
                '2',
                'REC002',
                'TOK002',
                '2',
                '6000',
                '2200',
                'Jane Smith',
                '600.00',
                'manufacturing',
                'Another sample record'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Process the bulk import CSV file.
     */
    public function processBulkImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean'
        ]);

        try {
            $file = $request->file('csv_file');
            $importService = new PurchaseImportService();

            $options = [
                'skip_duplicates' => $request->boolean('skip_duplicates'),
                'update_existing' => $request->boolean('update_existing'),
                'chunk_size' => 100 // Process 100 records at a time
            ];

            $result = $importService->importFromCsv($file->getPathname(), $options);

            if ($result['success']) {
                $stats = $result['statistics'];
                $message = "Import completed! ";
                $message .= "Successful: {$stats['successful']}, ";
                $message .= "Updated: {$stats['updated']}, ";
                $message .= "Failed: {$stats['failed']}, ";
                $message .= "Skipped: {$stats['skipped']}";

                if (!empty($result['warnings'])) {
                    session()->flash('warnings', $result['warnings']);
                }

                if ($stats['failed'] > 0) {
                    return redirect()->back()
                        ->with('warning', $message)
                        ->with('import_errors', $result['errors']);
                } else {
                    return redirect()->route('purchases.index')
                        ->with('success', $message);
                }
            } else {
                return redirect()->back()
                    ->with('error', 'Import failed: ' . $result['message'])
                    ->with('import_errors', $result['errors'] ?? []);
            }

        } catch (\Exception $e) {
            Log::error('Bulk import error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
    /**
     * Validate CSV file and return validation report.
     */
    public function validateImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        try {
            $file = $request->file('csv_file');
            $importService = new PurchaseImportService();

            // Run validation without actually importing
            $result = $this->validateCsvFile($file->getPathname());

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validate CSV file content without importing.
     */
    private function validateCsvFile($filePath)
    {
        $csvData = array_map('str_getcsv', file($filePath));
        $headers = array_shift($csvData);

        $errors = [];
        $warnings = [];
        $validRows = 0;
        $invalidRows = 0;

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2;

            if (empty(array_filter($row))) {
                continue;
            }

            $data = array_combine($headers, $row);

            // Basic validation
            if (empty($data['datetime'])) {
                $errors[] = "Row {$rowNumber}: datetime is required";
                $invalidRows++;
                continue;
            }

            if (!is_numeric($data['mines_id']) || !Mines::find($data['mines_id'])) {
                $errors[] = "Row {$rowNumber}: Invalid mines_id";
                $invalidRows++;
                continue;
            }

            if (!is_numeric($data['vehicle_id']) || !Vehicle::find($data['vehicle_id'])) {
                $errors[] = "Row {$rowNumber}: Invalid vehicle_id";
                $invalidRows++;
                continue;
            }

            if ((int)$data['gross_wt'] <= (int)$data['tare_wt']) {
                $errors[] = "Row {$rowNumber}: Gross weight must be greater than tare weight";
                $invalidRows++;
                continue;
            }

            if (!in_array(strtolower(trim($data['use_at'])), ['stock', 'manufacturing'])) {
                $errors[] = "Row {$rowNumber}: use_at must be 'stock' or 'manufacturing'";
                $invalidRows++;
                continue;
            }

            $validRows++;
        }

        return [
            'total_rows' => count($csvData),
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
            'errors' => array_slice($errors, 0, 20), // Limit to first 20 errors
            'warnings' => $warnings,
            'can_import' => $validRows > 0
        ];
    }

    /**
     * Validate and clean import data for a single row.
     */
    private function validateAndCleanImportData($data, $rowNumber)
    {
        try {
            // Required field validation
            $requiredFields = ['datetime', 'mines_id', 'rec_no', 'token_no', 'vehicle_id', 'gross_wt', 'tare_wt', 'driver', 'use_at'];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new \Exception("Required field '{$field}' is missing or empty");
                }
            }

            // Data type validation and conversion
            $cleanData = [
                'datetime' => $this->parseDateTime($data['datetime']),
                'mines_id' => (int) $data['mines_id'],
                'rec_no' => trim($data['rec_no']),
                'token_no' => trim($data['token_no']),
                'vehicle_id' => (int) $data['vehicle_id'],
                'gross_wt' => (int) $data['gross_wt'],
                'tare_wt' => (int) $data['tare_wt'],
                'driver' => trim($data['driver']),
                'commission' => !empty($data['commission']) ? (float) $data['commission'] : null,
                'use_at' => trim(strtolower($data['use_at'])),
                'notes' => trim($data['notes'] ?? '')
            ];

            // Business logic validation
            if ($cleanData['gross_wt'] <= $cleanData['tare_wt']) {
                throw new \Exception("Gross weight must be greater than tare weight");
            }

            if (!in_array($cleanData['use_at'], ['stock', 'manufacturing'])) {
                throw new \Exception("use_at must be either 'stock' or 'manufacturing'");
            }

            // Validate foreign key references
            if (!Mines::where('id', $cleanData['mines_id'])->exists()) {
                throw new \Exception("Invalid mines_id: {$cleanData['mines_id']}");
            }

            if (!Vehicle::where('id', $cleanData['vehicle_id'])->exists()) {
                throw new \Exception("Invalid vehicle_id: {$cleanData['vehicle_id']}");
            }

            // Additional validations
            if (strlen($cleanData['rec_no']) > 255) {
                throw new \Exception("rec_no is too long (max 255 characters)");
            }

            if (strlen($cleanData['token_no']) > 255) {
                throw new \Exception("token_no is too long (max 255 characters)");
            }

            if (strlen($cleanData['driver']) > 255) {
                throw new \Exception("driver name is too long (max 255 characters)");
            }

            if ($cleanData['commission'] !== null && $cleanData['commission'] < 0) {
                throw new \Exception("commission cannot be negative");
            }

            return $cleanData;

        } catch (\Exception $e) {
            $this->logImportError($rowNumber, $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Parse datetime from various formats.
     */
    private function parseDateTime($dateString)
    {
        $dateString = trim($dateString);

        // Common datetime formats
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd-m-Y',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/Y'
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        throw new \Exception("Invalid datetime format: {$dateString}");
    }

    /**
     * Log import errors for debugging.
     */
    private function logImportError($rowNumber, $error, $data)
    {
        Log::warning("CSV Import Error - Row {$rowNumber}: {$error}", [
            'row_data' => $data
        ]);
    }

    /**
     * Get import validation rules for preview.
     */
    public function getImportValidationRules()
    {
        return [
            'datetime' => 'Date and time (Y-m-d H:i:s format)',
            'mines_id' => 'Valid mines ID (integer)',
            'rec_no' => 'Record number (max 255 chars)',
            'token_no' => 'Token number (max 255 chars)',
            'vehicle_id' => 'Valid vehicle ID (integer)',
            'gross_wt' => 'Gross weight in kg (integer)',
            'tare_wt' => 'Tare weight in kg (integer)',
            'driver' => 'Driver name (max 255 chars)',
            'commission' => 'Commission amount (optional, decimal)',
            'use_at' => 'Usage type (stock or manufacturing)',
            'notes' => 'Additional notes (optional)'
        ];
    }

    /**
     * Export purchases to CSV.
     */
    public function export(Request $request)
    {
        $query = Purchase::with(['mines', 'vehicle']);

        // Apply same filters as index page
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('datetime', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('datetime', '<=', $request->end_date);
        }

        if ($request->has('mines_id') && $request->mines_id) {
            $query->where('mines_id', $request->mines_id);
        }

        if ($request->has('vehicle_id') && $request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->has('use_at') && $request->use_at) {
            $query->where('use_at', $request->use_at);
        }

        $purchases = $query->orderBy('datetime', 'desc')->get();

        $filename = "purchases_export_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($purchases) {
            $file = fopen('php://output', 'w');

            // CSV Header - matches import template
            fputcsv($file, [
                'datetime',
                'mines_id',
                'mines_name',
                'mines_ml_number',
                'rec_no',
                'token_no',
                'vehicle_id',
                'vehicle_number',
                'gross_wt',
                'tare_wt',
                'net_wt',
                'wt_ton',
                'driver',
                'commission',
                'use_at',
                'notes',
                'created_at'
            ]);

            foreach ($purchases as $purchase) {
                fputcsv($file, [
                    $purchase->datetime->format('Y-m-d H:i:s'),
                    $purchase->mines_id,
                    $purchase->mines->name ?? '',
                    $purchase->mines->ml_number ?? '',
                    $purchase->rec_no,
                    $purchase->token_no,
                    $purchase->vehicle_id,
                    $purchase->vehicle->vehicle_number ?? '',
                    $purchase->gross_wt,
                    $purchase->tare_wt,
                    $purchase->net_wt,
                    $purchase->wt_ton,
                    $purchase->driver,
                    $purchase->commission,
                    $purchase->use_at,
                    $purchase->notes,
                    $purchase->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk delete purchases.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'purchase_ids' => 'required|array',
            'purchase_ids.*' => 'exists:purchases,id'
        ]);

        try {
            DB::transaction(function () use ($request) {
                Purchase::whereIn('id', $request->purchase_ids)->delete();
            });

            $count = count($request->purchase_ids);
            return redirect()->route('purchases.index')
                ->with('success', "Successfully deleted {$count} purchase record(s).");

        } catch (\Exception $e) {
            Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error occurred during bulk delete.');
        }
    }

    /**
     * Get purchase statistics for dashboard or reports.
     */
    public function getImportStatistics()
    {
        return [
            'total_purchases' => Purchase::count(),
            'today_purchases' => Purchase::whereDate('datetime', today())->count(),
            'this_week_purchases' => Purchase::whereBetween('datetime', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month_purchases' => Purchase::whereMonth('datetime', now()->month)
                ->whereYear('datetime', now()->year)
                ->count(),
            'total_weight_tons' => Purchase::sum('wt_ton'),
            'total_commission' => Purchase::sum('commission'),
            'stock_purchases' => Purchase::where('use_at', 'stock')->count(),
            'manufacturing_purchases' => Purchase::where('use_at', 'manufacturing')->count(),
            'avg_commission' => Purchase::whereNotNull('commission')->avg('commission'),
            'most_active_mine' => Purchase::select('mines_id')
                ->selectRaw('COUNT(*) as purchase_count')
                ->with('mines')
                ->groupBy('mines_id')
                ->orderByDesc('purchase_count')
                ->first(),
            'most_active_vehicle' => Purchase::select('vehicle_id')
                ->selectRaw('COUNT(*) as purchase_count')
                ->with('vehicle')
                ->groupBy('vehicle_id')
                ->orderByDesc('purchase_count')
                ->first(),
        ];
    }
}
