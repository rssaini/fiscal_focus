<?php

// Create this file as app/Services/PurchaseImportService.php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Mines;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class PurchaseImportService
{
    protected $errors = [];
    protected $warnings = [];
    protected $statistics = [
        'total_rows' => 0,
        'successful' => 0,
        'failed' => 0,
        'skipped' => 0,
        'updated' => 0
    ];

    /**
     * Process bulk import from CSV file.
     */
    public function importFromCsv($filePath, $options = [])
    {
        $skipDuplicates = $options['skip_duplicates'] ?? false;
        $updateExisting = $options['update_existing'] ?? false;
        $chunkSize = $options['chunk_size'] ?? 100;

        try {
            $csvData = $this->readCsvFile($filePath);

            if (empty($csvData['data'])) {
                throw new Exception('No data found in CSV file');
            }

            $this->statistics['total_rows'] = count($csvData['data']);

            // Validate CSV structure
            $this->validateCsvStructure($csvData['headers']);

            // Process data in chunks for better memory management
            $chunks = array_chunk($csvData['data'], $chunkSize);

            DB::beginTransaction();

            foreach ($chunks as $chunkIndex => $chunk) {
                $this->processChunk($chunk, $csvData['headers'], $chunkIndex * $chunkSize, [
                    'skip_duplicates' => $skipDuplicates,
                    'update_existing' => $updateExisting
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'statistics' => $this->statistics,
                'errors' => $this->errors,
                'warnings' => $this->warnings
            ];

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Purchase import failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'statistics' => $this->statistics,
                'errors' => $this->errors
            ];
        }
    }

    /**
     * Read and parse CSV file.
     */
    protected function readCsvFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception('CSV file not found');
        }

        $csvData = array_map('str_getcsv', file($filePath));

        if (empty($csvData)) {
            throw new Exception('CSV file is empty');
        }

        $headers = array_shift($csvData);

        // Clean headers
        $headers = array_map('trim', $headers);

        return [
            'headers' => $headers,
            'data' => $csvData
        ];
    }

    /**
     * Validate CSV structure and headers.
     */
    protected function validateCsvStructure($headers)
    {
        $requiredHeaders = [
            'datetime', 'mines_id', 'rec_no', 'token_no', 'vehicle_id',
            'gross_wt', 'tare_wt', 'driver', 'use_at'
        ];

        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (!empty($missingHeaders)) {
            throw new Exception('Missing required columns: ' . implode(', ', $missingHeaders));
        }

        $extraHeaders = array_diff($headers, [
            'datetime', 'mines_id', 'rec_no', 'token_no', 'vehicle_id',
            'gross_wt', 'tare_wt', 'driver', 'commission', 'use_at', 'notes'
        ]);

        if (!empty($extraHeaders)) {
            $this->warnings[] = 'Unknown columns found (will be ignored): ' . implode(', ', $extraHeaders);
        }
    }

    /**
     * Process a chunk of CSV data.
     */
    protected function processChunk($chunk, $headers, $startIndex, $options)
    {
        foreach ($chunk as $rowIndex => $row) {
            $actualRowNumber = $startIndex + $rowIndex + 2; // +2 for header and 1-based indexing

            try {
                if (empty(array_filter($row))) {
                    $this->statistics['skipped']++;
                    continue;
                }

                $data = $this->mapRowToData($row, $headers);
                $validatedData = $this->validateRowData($data, $actualRowNumber);

                if ($validatedData === false) {
                    $this->statistics['failed']++;
                    continue;
                }

                $result = $this->saveOrUpdatePurchase($validatedData, $options);

                switch ($result) {
                    case 'created':
                        $this->statistics['successful']++;
                        break;
                    case 'updated':
                        $this->statistics['updated']++;
                        break;
                    case 'skipped':
                        $this->statistics['skipped']++;
                        break;
                    case 'failed':
                        $this->statistics['failed']++;
                        break;
                }

            } catch (Exception $e) {
                $this->errors[] = "Row {$actualRowNumber}: " . $e->getMessage();
                $this->statistics['failed']++;
            }
        }
    }

    /**
     * Map CSV row to associative array.
     */
    protected function mapRowToData($row, $headers)
    {
        $data = [];

        foreach ($headers as $index => $header) {
            $data[$header] = $row[$index] ?? '';
        }

        return $data;
    }

    /**
     * Validate and clean row data.
     */
    protected function validateRowData($data, $rowNumber)
    {
        try {
            $rules = [
                'datetime' => 'required|date',
                'mines_id' => 'required|integer|exists:mines,id',
                'rec_no' => 'required|string|max:255',
                'token_no' => 'required|string|max:255',
                'vehicle_id' => 'required|integer|exists:vehicles,id',
                'gross_wt' => 'required|integer|min:1',
                'tare_wt' => 'required|integer|min:0',
                'driver' => 'required|string|max:255',
                'commission' => 'nullable|numeric|min:0',
                'use_at' => 'required|in:stock,manufacturing',
                'notes' => 'nullable|string'
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $this->errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                return false;
            }

            // Additional business logic validation
            if ((int)$data['gross_wt'] <= (int)$data['tare_wt']) {
                $this->errors[] = "Row {$rowNumber}: Gross weight must be greater than tare weight";
                return false;
            }

            // Clean and format data
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

            return $cleanData;

        } catch (Exception $e) {
            $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Save or update purchase record.
     */
    protected function saveOrUpdatePurchase($data, $options)
    {
        $existingPurchase = Purchase::where('rec_no', $data['rec_no'])
            ->where('token_no', $data['token_no'])
            ->first();

        if ($existingPurchase) {
            if ($options['skip_duplicates']) {
                return 'skipped';
            } elseif ($options['update_existing']) {
                $existingPurchase->update($data);
                return 'updated';
            } else {
                throw new Exception("Duplicate record found with rec_no: {$data['rec_no']} and token_no: {$data['token_no']}");
            }
        }

        Purchase::create($data);
        return 'created';
    }

    /**
     * Parse datetime from various formats.
     */
    protected function parseDateTime($dateString)
    {
        $dateString = trim($dateString);

        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd-m-Y',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/Y',
            'm/d/Y H:i:s',
            'm/d/Y H:i',
            'm/d/Y'
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        throw new Exception("Invalid datetime format: {$dateString}");
    }

    /**
     * Get import statistics.
     */
    public function getStatistics()
    {
        return $this->statistics;
    }

    /**
     * Get import errors.
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get import warnings.
     */
    public function getWarnings()
    {
        return $this->warnings;
    }
}
