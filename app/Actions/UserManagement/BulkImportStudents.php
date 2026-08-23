<?php

namespace App\Actions\UserManagement;

use App\Models\StudentProfile;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class BulkImportStudents
{
    public function __construct(
        protected CreateStudentAccount $createStudentAction
    ) {}

    /**
     * Parse and import students from a CSV file.
     *
     * @return array{
     *     created: array<int, array{row: int, lrn: string, name: string}>,
     *     skipped: array<int, array{row: int, lrn: string, name: string, reason: string}>,
     *     failed: array<int, array{row: int, lrn: string, name: string, errors: array<int, string>}>
     * }
     */
    public function handle(UploadedFile|string $file, ?int $sectionId = null): array
    {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! file_exists($filePath) || ! is_readable($filePath)) {
            throw new InvalidArgumentException('CSV file could not be read.');
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException('Unable to open CSV file stream.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            throw new InvalidArgumentException('CSV file is empty.');
        }

        // Clean UTF-8 BOM if present on first header
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header = array_map(fn ($col) => strtolower(trim((string) $col)), $header);

        $created = [];
        $skipped = [];
        $failed = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip completely empty lines
            if (empty(array_filter($row))) {
                continue;
            }

            // Map row data using headers if count matches
            $rowData = [];
            foreach ($header as $index => $key) {
                $rowData[$key] = isset($row[$index]) ? trim($row[$index]) : null;
            }

            $lrn = (string) ($rowData['lrn'] ?? '');
            $firstName = (string) ($rowData['first_name'] ?? '');
            $lastName = (string) ($rowData['last_name'] ?? '');
            $middleName = $rowData['middle_name'] ?: null;
            $gender = strtoupper((string) ($rowData['gender'] ?? ''));
            $birthDate = (string) ($rowData['birth_date'] ?? '');
            $fullName = trim("{$firstName} {$lastName}") ?: ($lrn ?: "Row #{$rowNumber}");

            // 1. Validation check
            $validator = Validator::make([
                'lrn' => $lrn,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $gender,
                'birth_date' => $birthDate,
            ], [
                'lrn' => ['required', 'string', 'size:12', 'regex:/^[0-9]{12}$/'],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'gender' => ['required', 'string', 'in:M,F'],
                'birth_date' => ['required', 'date'],
            ], [
                'lrn.size' => 'LRN must be exactly 12 digits.',
                'lrn.regex' => 'LRN must contain only numbers.',
                'gender.in' => 'Gender must be M or F.',
            ]);

            if ($validator->fails()) {
                $failed[] = [
                    'row' => $rowNumber,
                    'lrn' => $lrn,
                    'name' => $fullName,
                    'errors' => $validator->errors()->all(),
                ];

                continue;
            }

            // 2. Duplicate check (Skip if already exists)
            $exists = User::where('credential', $lrn)->exists() || StudentProfile::where('lrn', $lrn)->exists();
            if ($exists) {
                $skipped[] = [
                    'row' => $rowNumber,
                    'lrn' => $lrn,
                    'name' => $fullName,
                    'reason' => 'LRN is already registered in the system.',
                ];

                continue;
            }

            // 3. Create account
            try {
                $this->createStudentAction->handle([
                    'lrn' => $lrn,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'middle_name' => $middleName,
                    'gender' => $gender,
                    'birth_date' => $birthDate,
                    'section_id' => $sectionId,
                ]);

                $created[] = [
                    'row' => $rowNumber,
                    'lrn' => $lrn,
                    'name' => $fullName,
                ];
            } catch (\Throwable $e) {
                $failed[] = [
                    'row' => $rowNumber,
                    'lrn' => $lrn,
                    'name' => $fullName,
                    'errors' => [$e->getMessage()],
                ];
            }
        }

        fclose($handle);

        AuditLogger::log(
            'student.bulk_import',
            'Bulk student import: '.count($created).' created, '.count($skipped).' skipped, '.count($failed).' failed.'
        );

        return [
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
        ];
    }
}
