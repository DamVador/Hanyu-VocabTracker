<?php

namespace App\Http\Controllers;

use App\Http\Requests\CsvImportRequest;
use App\Models\Word;
use App\Models\StudySession;
use App\Models\Tag;
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

        $header = array_map('trim', array_shift($data));

        $expectedHeaders = [
            'chinese_character' => true,
            'pinyin' => true,
            'translation' => true,
            'study_session_name' => false,
            'tags' => false,
        ];

        foreach ($expectedHeaders as $expectedHeader => $required) {
            if ($required && !in_array($expectedHeader, $header)) {
                return Inertia::render('Dashboard', [
                    'errors' => ['general' => "Missing required column: '{$expectedHeader}' in CSV."],
                ])->toResponse($request)->setStatusCode(422);
            }
        }

        $importedCount = 0;
        $errors = [];
        $skippedRows = 0;

        DB::beginTransaction();
        try {
            foreach ($data as $rowNum => $row) {
                if (empty(array_filter($row))) {
                    $skippedRows++;
                    continue;
                }

                $rowData = [];
                foreach ($header as $index => $colName) {
                    $rowData[$colName] = $row[$index] ?? null;
                }

                if (empty($rowData['chinese_character']) || empty($rowData['pinyin']) || empty($rowData['translation'])) {
                    $errors[] = "Row " . ($rowNum + 2 - $skippedRows) . ": Missing required word data (chinese_character, pinyin, or translation).";
                    continue;
                }

                $word = Word::firstOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'chinese_word' => $rowData['chinese_character'],
                    ],
                    [
                        'pinyin' => $rowData['pinyin'],
                        'translation' => $rowData['translation'],
                        // Add other default word properties here if needed for new creation
                    ]
                );
                if ($word->wasRecentlyCreated) {
                    $word->pinyin = $rowData['pinyin'];
                    $word->translation = $rowData['translation'];
                    $word->save();
                }

                // --- Handle Study Session (existing logic) ---
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

                        $studySession->words()->syncWithoutDetaching($word->id);
                    }
                }

                if (!empty($rowData['tags'])) {
                    $tagNames = explode(',', $rowData['tags']);
                    $tagNames = array_map('trim', $tagNames);
                    $tagNames = array_filter($tagNames);

                    if (!empty($tagNames)) {
                        $tagIds = [];
                        foreach ($tagNames as $tagName) {
                            $tag = Tag::firstOrCreate(
                                ['name' => $tagName],
                                ['user_id' => auth()->id()]
                            );
                            $tagIds[] = $tag->id;
                        }
                        $word->tags()->syncWithoutDetaching($tagIds);
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