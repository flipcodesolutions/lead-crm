<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\LeadNote;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\LeadStatus;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class LeadImportService
{
    /**
     * Parse any supported file (Excel .xlsx, .xls, .csv, .txt)
     */
    public function parseFile(string $filePath, int $previewRows = 3): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Import file not found.");
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'xlsx') {
            return $this->parseXlsxFile($filePath, $previewRows);
        }

        if ($ext === 'xls') {
            if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                return $this->parseWithPhpSpreadsheet($filePath, $previewRows);
            }
        }

        return $this->parseCsvFile($filePath, $previewRows);
    }

    /**
     * Native, ultra-fast zero-dependency XLSX parser using ZipArchive + SimpleXML.
     */
    public function parseXlsxFile(string $filePath, int $previewRows = 3): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            // Fallback to PhpSpreadsheet if installed
            if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                return $this->parseWithPhpSpreadsheet($filePath, $previewRows);
            }
            throw new \Exception("Could not open Excel .xlsx file.");
        }

        // 1. Load shared strings
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml) {
            $xml = simplexml_load_string($sharedStringsXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $val) {
                    if (isset($val->t)) {
                        $sharedStrings[] = (string)$val->t;
                    } elseif (isset($val->r)) {
                        $text = '';
                        foreach ($val->r as $r) {
                            $text .= (string)$r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Load worksheet xml
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_starts_with($name, 'xl/worksheets/sheet') && str_ends_with($name, '.xml')) {
                    $sheetXml = $zip->getFromName($name);
                    break;
                }
            }
        }
        $zip->close();

        if (!$sheetXml) {
            throw new \Exception("No worksheet found inside Excel workbook.");
        }

        $xml = simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData->row)) {
            throw new \Exception("Excel sheet contains no rows.");
        }

        $allRows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowCells = [];
            $colIdx = 0;
            foreach ($row->c as $c) {
                $cellRef = (string)$c['r'];
                $colLetter = preg_replace('/[0-9]/', '', $cellRef);
                $targetCol = 0;
                for ($l = 0; $l < strlen($colLetter); $l++) {
                    $targetCol = $targetCol * 26 + (ord($colLetter[$l]) - 64);
                }
                $targetCol -= 1;

                while ($colIdx < $targetCol) {
                    $rowCells[] = '';
                    $colIdx++;
                }

                $type = (string)$c['t'];
                $val = isset($c->v) ? (string)$c->v : '';

                if ($type === 's') {
                    $idx = (int)$val;
                    $rowCells[] = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $rowCells[] = (string)$c->is->t;
                } else {
                    $rowCells[] = $val;
                }
                $colIdx++;
            }

            if (!empty(array_filter($rowCells, fn($v) => trim((string)$v) !== ''))) {
                $allRows[] = $rowCells;
            }
        }

        if (empty($allRows)) {
            throw new \Exception("The Excel sheet is empty.");
        }

        $rawHeaders = array_shift($allRows);
        $headers = array_map(function ($h) {
            $cleaned = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string)$h));
            return $cleaned !== '' ? $cleaned : 'Column_' . uniqid();
        }, $rawHeaders);

        $preview = [];
        foreach (array_slice($allRows, 0, $previewRows) as $row) {
            $assoc = [];
            foreach ($headers as $idx => $header) {
                $assoc[$header] = isset($row[$idx]) ? trim((string)$row[$idx]) : '';
            }
            $preview[] = $assoc;
        }

        return [
            'headers' => $headers,
            'preview_rows' => $preview,
            'total_rows_count' => count($allRows),
            'delimiter' => 'excel',
            'raw_rows' => $allRows,
        ];
    }

    /**
     * Fallback parser using PhpSpreadsheet for older .xls files.
     */
    protected function parseWithPhpSpreadsheet(string $filePath, int $previewRows = 3): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rowsData = $sheet->toArray(null, true, true, false);

        if (empty($rowsData)) {
            throw new \Exception("Excel file is empty.");
        }

        $headerRow = array_shift($rowsData);
        $headers = array_map(function ($h) {
            $cleaned = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string)$h));
            return $cleaned !== '' ? $cleaned : 'Column_' . uniqid();
        }, $headerRow);

        $preview = [];
        $cleanRows = [];

        foreach ($rowsData as $row) {
            if (empty(array_filter($row, fn($val) => trim((string)$val) !== ''))) {
                continue;
            }
            $cleanRows[] = $row;
            if (count($preview) < $previewRows) {
                $assoc = [];
                foreach ($headers as $idx => $header) {
                    $assoc[$header] = isset($row[$idx]) ? trim((string)$row[$idx]) : '';
                }
                $preview[] = $assoc;
            }
        }

        return [
            'headers' => $headers,
            'preview_rows' => $preview,
            'total_rows_count' => count($cleanRows),
            'delimiter' => 'excel',
            'raw_rows' => $cleanRows,
        ];
    }

    /**
     * Parse CSV file with auto-detect delimiter and UTF-8 encoding.
     */
    public function parseCsvFile(string $filePath, int $previewRows = 3): array
    {
        $content = file_get_contents($filePath);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Auto-detect delimiter
        $delimiters = [',', ';', "\t", '|'];
        $firstLine = strtok($content, "\r\n");
        $detectedDelimiter = ',';
        $maxCount = 0;
        foreach ($delimiters as $delim) {
            $count = substr_count($firstLine, $delim);
            if ($count > $maxCount) {
                $maxCount = $count;
                $detectedDelimiter = $delim;
            }
        }

        $handle = fopen($filePath, 'r');
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = [];
        $rows = [];
        $rowCount = 0;

        while (($row = fgetcsv($handle, 10000, $detectedDelimiter)) !== false) {
            if (empty(array_filter($row, fn($val) => trim((string)$val) !== ''))) {
                continue;
            }

            if (empty($headers)) {
                $headers = array_map(function ($h) {
                    $cleaned = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string)$h));
                    return $cleaned !== '' ? $cleaned : 'Column_' . uniqid();
                }, $row);
            } else {
                $rowCount++;
                if (count($rows) < $previewRows) {
                    $assoc = [];
                    foreach ($headers as $idx => $header) {
                        $assoc[$header] = isset($row[$idx]) ? trim((string)$row[$idx]) : '';
                    }
                    $rows[] = $assoc;
                }
            }
        }
        fclose($handle);

        return [
            'headers' => $headers,
            'preview_rows' => $rows,
            'total_rows_count' => $rowCount,
            'delimiter' => $detectedDelimiter,
        ];
    }

    /**
     * Suggest column mappings based on header name heuristics.
     */
    public function suggestMapping(array $headers): array
    {
        $suggestions = [];

        foreach ($headers as $header) {
            $normalized = strtolower(trim((string)$header));
            $normalized = preg_replace('/[^a-z0-9_]/', '_', $normalized);
            $normalized = trim($normalized, '_');

            if (in_array($normalized, ['full_name', 'name', 'customer_name', 'client_name', 'lead_name', 'contact_name', 'fname'])) {
                $suggestions[$header] = 'name';
            } elseif (in_array($normalized, ['phone', 'mobile', 'phone_number', 'mobile_number', 'contact_no', 'contact_number', 'cell', 'tel'])) {
                $suggestions[$header] = 'phone';
            } elseif (in_array($normalized, ['email', 'email_address', 'e_mail', 'mail'])) {
                $suggestions[$header] = 'email';
            } elseif (in_array($normalized, ['company', 'company_name', 'organization', 'business_name', 'firm_name'])) {
                $suggestions[$header] = 'company_name';
            } elseif (in_array($normalized, ['alt_phone', 'alternate_phone', 'secondary_phone', 'other_phone'])) {
                $suggestions[$header] = 'alternate_phone';
            } elseif (in_array($normalized, ['city', 'town', 'district', 'location'])) {
                $suggestions[$header] = 'city';
            } elseif (in_array($normalized, ['state', 'province', 'region'])) {
                $suggestions[$header] = 'state';
            } elseif (in_array($normalized, ['address', 'street', 'full_address', 'area_address'])) {
                $suggestions[$header] = 'address';
            } elseif (in_array($normalized, ['expected_value', 'deal_value', 'value', 'amount', 'budget', 'price', 'revenue'])) {
                $suggestions[$header] = 'expected_value';
            } elseif (in_array($normalized, ['platform', 'lead_source', 'source', 'channel', 'medium'])) {
                $suggestions[$header] = 'source';
            } elseif (in_array($normalized, ['description', 'notes', 'comments', 'remark', 'remarks', 'requirement'])) {
                $suggestions[$header] = 'description';
            } else {
                $suggestions[$header] = 'append_to_notes';
            }
        }

        return $suggestions;
    }

    /**
     * Clean phone numbers, resolving Excel scientific notations (e.g. 9.18787E+11 -> 918787...)
     */
    public function cleanPhoneNumber(mixed $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $str = trim((string)$phone);

        // Check if scientific notation (e.g., 9.18787E+11 or 9.18787e+11)
        if (stripos($str, 'e+') !== false || stripos($str, 'e-') !== false || (is_numeric($str) && (float)$str > 1000000000)) {
            $formatted = sprintf('%.0f', (float)$str);
            if ($formatted !== '0') {
                $str = $formatted;
            }
        }

        $hasPlus = str_starts_with($str, '+');
        $digits = preg_replace('/\D/', '', $str);

        if (empty($digits)) {
            return $str;
        }

        // If 10 digits (Standard Indian Mobile) -> +91 XXXXX XXXXX
        if (strlen($digits) === 10 && in_array(substr($digits, 0, 1), ['6', '7', '8', '9'])) {
            return '+91 ' . substr($digits, 0, 5) . ' ' . substr($digits, 5, 5);
        }

        // If 12 digits starting with 91 -> +91 XXXXX XXXXX
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $tenDigits = substr($digits, 2);
            return '+91 ' . substr($tenDigits, 0, 5) . ' ' . substr($tenDigits, 5, 5);
        }

        // If 11 digits starting with 0 -> +91 XXXXX XXXXX
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $tenDigits = substr($digits, 1);
            return '+91 ' . substr($tenDigits, 0, 5) . ' ' . substr($tenDigits, 5, 5);
        }

        return $hasPlus ? '+' . $digits : $digits;
    }

    /**
     * Clean corrupted text characters (e.g. Jays%hree -> Jayshree).
     */
    public function cleanText(mixed $text): string
    {
        if (!is_string($text)) {
            return (string)$text;
        }

        $cleaned = preg_replace('/([a-zA-Z])%([a-zA-Z])/', '$1$2', $text);
        return trim($cleaned);
    }

    /**
     * Process bulk import for both Excel (.xlsx, .xls) and CSV (.csv, .txt).
     */
    public function processImport(
        string $filePath,
        array $columnMapping,
        int $defaultStageId,
        ?int $defaultStatusId,
        ?int $defaultSourceId,
        string $assignmentMode,
        ?int $specificUserId,
        string $duplicateAction,
        bool $autoAppendUnmapped = true
    ): array {
        if (!file_exists($filePath)) {
            throw new \Exception("Import file not found.");
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $parsed = $this->parseFile($filePath, 1);
        $headers = $parsed['headers'];

        // Determine which users to use for Round-Robin
        $salesReps = [];
        if ($assignmentMode === 'round_robin') {
            $salesRole = Role::whereIn('name', ['Salesperson', 'Telecaller', 'Manager'])->pluck('id');
            $salesReps = User::whereIn('role_id', $salesRole)->where('status', 1)->pluck('id')->toArray();
            if (empty($salesReps)) {
                $salesReps = User::where('status', 1)->pluck('id')->toArray();
            }
        }
        $roundRobinIndex = 0;

        $defaultSource = $defaultSourceId ? LeadSource::find($defaultSourceId) : LeadSource::first();
        $defaultStage = LeadStage::find($defaultStageId) ?? LeadStage::first();
        $defaultStatus = $defaultStatusId ? LeadStatus::find($defaultStatusId) : LeadStatus::first();

        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'created_lead_ids' => [],
        ];

        $currentUserId = Auth::id() ?? User::where('status', 1)->first()?->id ?? 1;

        // Fetch row iterator depending on format
        $rowsGenerator = function () use ($filePath, $ext, $parsed) {
            if ($ext === 'xlsx' || $ext === 'xls') {
                if (isset($parsed['raw_rows'])) {
                    foreach ($parsed['raw_rows'] as $row) {
                        yield $row;
                    }
                }
            } else {
                $delimiter = $parsed['delimiter'] ?? ',';
                $handle = fopen($filePath, 'r');
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                fgetcsv($handle, 10000, $delimiter); // skip header
                while (($row = fgetcsv($handle, 10000, $delimiter)) !== false) {
                    yield $row;
                }
                fclose($handle);
            }
        };

        DB::beginTransaction();
        try {
            $rowNum = 1;
            foreach ($rowsGenerator() as $row) {
                $rowNum++;
                if (empty(array_filter($row, fn($val) => trim((string)$val) !== ''))) {
                    continue;
                }

                $stats['total']++;

                $leadData = [
                    'name' => '',
                    'company_name' => null,
                    'phone' => '',
                    'alternate_phone' => null,
                    'email' => null,
                    'city' => null,
                    'state' => null,
                    'address' => null,
                    'expected_value' => 0.00,
                    'description' => '',
                    'source_id' => $defaultSource?->id,
                    'stage_id' => $defaultStage?->id,
                    'status_id' => $defaultStatus?->id,
                ];

                $notesToAppend = [];
                $customSourceFound = null;

                foreach ($headers as $idx => $headerName) {
                    $cellValue = isset($row[$idx]) ? trim((string)$row[$idx]) : '';
                    if ($cellValue === '') {
                        continue;
                    }

                    $targetField = $columnMapping[$headerName] ?? 'ignore';

                    switch ($targetField) {
                        case 'name':
                            $leadData['name'] = $this->cleanText($cellValue);
                            break;
                        case 'company_name':
                            $leadData['company_name'] = $this->cleanText($cellValue);
                            break;
                        case 'phone':
                            $leadData['phone'] = $this->cleanPhoneNumber($cellValue);
                            break;
                        case 'alternate_phone':
                            $leadData['alternate_phone'] = $this->cleanPhoneNumber($cellValue);
                            break;
                        case 'email':
                            $leadData['email'] = strtolower(trim($cellValue));
                            break;
                        case 'city':
                            $leadData['city'] = $this->cleanText($cellValue);
                            break;
                        case 'state':
                            $leadData['state'] = $this->cleanText($cellValue);
                            break;
                        case 'address':
                            $leadData['address'] = $this->cleanText($cellValue);
                            break;
                        case 'expected_value':
                            $cleanedVal = preg_replace('/[^0-9.]/', '', $cellValue);
                            $leadData['expected_value'] = is_numeric($cleanedVal) ? (float)$cleanedVal : 0.00;
                            break;
                        case 'source':
                            $customSourceFound = $cellValue;
                            break;
                        case 'description':
                            $leadData['description'] .= ($leadData['description'] ? "\n" : '') . $this->cleanText($cellValue);
                            break;
                        case 'append_to_notes':
                            $cleanHeader = ucwords(str_replace(['_', '-'], ' ', $headerName));
                            $notesToAppend[] = "• {$cleanHeader}: " . $this->cleanText($cellValue);
                            break;
                        case 'ignore':
                        default:
                            if ($autoAppendUnmapped) {
                                $cleanHeader = ucwords(str_replace(['_', '-'], ' ', $headerName));
                                $notesToAppend[] = "• {$cleanHeader}: " . $this->cleanText($cellValue);
                            }
                            break;
                    }
                }

                // If name is missing, use company or phone fallback
                if (empty($leadData['name'])) {
                    if (!empty($leadData['company_name'])) {
                        $leadData['name'] = $leadData['company_name'];
                    } elseif (!empty($leadData['phone'])) {
                        $leadData['name'] = 'Lead (' . $leadData['phone'] . ')';
                    } else {
                        $stats['skipped']++;
                        $stats['errors'][] = "Row #{$rowNum}: Skipped because Name and Phone were empty.";
                        continue;
                    }
                }

                if (empty($leadData['phone'])) {
                    $leadData['phone'] = '+91 ' . rand(70000, 99999) . ' ' . rand(10000, 99999);
                }

                // Resolve custom lead source
                if ($customSourceFound) {
                    $normSource = strtolower(trim($customSourceFound));
                    if ($normSource === 'fb' || str_contains($normSource, 'facebook')) {
                        $sourceObj = LeadSource::firstOrCreate(['name' => 'Facebook Ads'], ['status' => 1]);
                        $leadData['source_id'] = $sourceObj->id;
                    } elseif ($normSource === 'ig' || str_contains($normSource, 'instagram')) {
                        $sourceObj = LeadSource::firstOrCreate(['name' => 'Instagram Ads'], ['status' => 1]);
                        $leadData['source_id'] = $sourceObj->id;
                    } else {
                        $sourceObj = LeadSource::firstOrCreate(['name' => ucwords($customSourceFound)], ['status' => 1]);
                        $leadData['source_id'] = $sourceObj->id;
                    }
                }

                // Combine notes
                if (!empty($notesToAppend)) {
                    $formattedNotes = "--- Meta / Campaign Inquiry Details ---\n" . implode("\n", $notesToAppend);
                    $leadData['description'] = trim($leadData['description'] . "\n\n" . $formattedNotes);
                }

                // Determine assigned user
                $assignedUserId = null;
                if ($assignmentMode === 'specific' && $specificUserId) {
                    $assignedUserId = $specificUserId;
                } elseif ($assignmentMode === 'round_robin' && !empty($salesReps)) {
                    $assignedUserId = $salesReps[$roundRobinIndex % count($salesReps)];
                    $roundRobinIndex++;
                }

                // Duplicate Check by Phone or Email
                $existingLead = null;
                if (!empty($leadData['phone']) || !empty($leadData['email'])) {
                    $existingLead = Lead::where(function ($q) use ($leadData) {
                        if (!empty($leadData['phone'])) {
                            $digits = preg_replace('/\D/', '', $leadData['phone']);
                            $lastTen = strlen($digits) >= 10 ? substr($digits, -10) : $digits;
                            $formattedFiveFive = (strlen($lastTen) === 10) ? (substr($lastTen, 0, 5) . ' ' . substr($lastTen, 5)) : $lastTen;

                            $q->where('phone', $leadData['phone'])
                              ->orWhere('phone', 'LIKE', "%{$lastTen}%")
                              ->orWhere('phone', 'LIKE', "%{$formattedFiveFive}%");
                        }
                        if (!empty($leadData['email'])) {
                            $q->orWhere('email', $leadData['email']);
                        }
                    })->first();
                }

                if ($existingLead && $duplicateAction === 'skip') {
                    $stats['skipped']++;
                    continue;
                }

                if ($existingLead && $duplicateAction === 'update') {
                    $existingLead->update([
                        'name' => $leadData['name'] ?: $existingLead->name,
                        'company_name' => $leadData['company_name'] ?: $existingLead->company_name,
                        'city' => $leadData['city'] ?: $existingLead->city,
                        'state' => $leadData['state'] ?: $existingLead->state,
                        'address' => $leadData['address'] ?: $existingLead->address,
                        'description' => trim($existingLead->description . "\n\n" . $leadData['description']),
                    ]);
                    $stats['updated']++;
                    continue;
                }

                // Create New Lead
                $leadSeq = (Lead::max('id') ?? 0) + $stats['created'] + 1;
                $leadNumber = 'LD-' . date('Ymd') . '-' . str_pad((string)$leadSeq, 4, '0', STR_PAD_LEFT);
                while (Lead::where('lead_number', $leadNumber)->exists()) {
                    $leadSeq++;
                    $leadNumber = 'LD-' . date('Ymd') . '-' . str_pad((string)$leadSeq, 4, '0', STR_PAD_LEFT);
                }

                $newLead = Lead::create([
                    'lead_number' => $leadNumber,
                    'name' => $leadData['name'],
                    'company_name' => $leadData['company_name'],
                    'email' => $leadData['email'],
                    'phone' => $leadData['phone'],
                    'alternate_phone' => $leadData['alternate_phone'],
                    'city' => $leadData['city'],
                    'state' => $leadData['state'],
                    'country' => 'India',
                    'address' => $leadData['address'],
                    'expected_value' => $leadData['expected_value'],
                    'description' => $leadData['description'],
                    'source_id' => $leadData['source_id'],
                    'stage_id' => $leadData['stage_id'],
                    'status_id' => $leadData['status_id'],
                    'assigned_to' => $assignedUserId,
                    'created_by' => $currentUserId,
                    'status' => 1,
                ]);

                if ($assignedUserId) {
                    LeadAssignment::create([
                        'lead_id' => $newLead->id,
                        'assigned_by' => $currentUserId,
                        'assigned_to' => $assignedUserId,
                        'assigned_at' => now(),
                        'remarks' => 'Bulk imported and assigned automatically.',
                    ]);
                }

                $stats['created']++;
                $stats['created_lead_ids'][] = $newLead->id;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lead Import Failed: " . $e->getMessage());
            throw $e;
        }

        return $stats;
    }
}
