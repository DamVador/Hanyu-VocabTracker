<?php

namespace App\Http\Controllers;

use App\Http\Requests\CsvImportRequest;
use App\Models\Word;
use App\Models\StudySession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WordImportController extends Controller
{
    public function importCsv(CsvImportRequest $request)
    {
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));

        // Assuming the first row is the header
        $header = array_map('trim', array_shift($data)); // Trim whitespace from headers

        // Define expected headers and their required status
        $expectedHeaders = [
            'chinese_character' => true,
            'pinyin' => true,
            'translation' => true,
            'study_session_name' => false, // Study session name is optional for each word
        ];

        // Validate headers
        foreach ($expectedHeaders as $expectedHeader => $required) {
            if ($required && !in_array($expectedHeader, $header)) {
                return Inertia::render('Dashboard', [
                    'errors' => ['general' => "Missing required column: '{$expectedHeader}' in CSV."],
                ])->toResponse($request)->setStatusCode(422);
            }
        }

        $importedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $rowNum => $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $rowData = [];
                foreach ($header as $index => $colName) {
                    $rowData[$colName] = $row[$index] ?? null;
                }

                // Basic validation for word data
                if (empty($rowData['chinese_character']) || empty($rowData['pinyin']) || empty($rowData['translation'])) {
                    $errors[] = "Row " . ($rowNum + 2) . ": Missing required word data (chinese_character, pinyin, or translation).";
                    continue;
                }

                // Create or find the word
                $word = Word::firstOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'chinese_word' => $rowData['chinese_character'],
                        'pinyin' => $rowData['pinyin'],
                        'translation' => $rowData['translation'],
                    ]
                );

                // Handle study session
                if (!empty($rowData['study_session_name'])) {
                    $sessionName = trim($rowData['study_session_name']);
                    if (!empty($sessionName)) {
                        $studySession = StudySession::firstOrCreate(
                            [
                                'user_id' => auth()->id(),
                                'name' => $sessionName,
                            ],
                            [
                                'description' => "Session created from CSV import: {$sessionName}",
                            ]
                        );

                        // Attach word to session if not already attached
                        if (!$studySession->words->contains($word->id)) {
                            $studySession->words()->attach($word->id);
                        }
                    }
                }
                $importedCount++;
            }

            DB::commit();
            return Inertia::render('Dashboard', [
                'status' => "Successfully imported {$importedCount} words. " . count($errors) . " errors encountered.",
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("CSV Import Error: " . $e->getMessage(), ['user_id' => auth()->id(), 'exception' => $e]);
            return Inertia::render('Dashboard', [
                'errors' => ['general' => 'An error occurred during import: ' . $e->getMessage()],
            ])->toResponse($request)->setStatusCode(500);
        }
    }
}