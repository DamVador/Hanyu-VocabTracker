<?php

namespace App\Http\Controllers;

use App\Http\Requests\CsvImportRequest;
use App\Models\Word;
use App\Models\StudySession;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class WordImportController extends Controller
{
    public function importCsv(CsvImportRequest $request)
    {
        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $content = file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // remove BOM
        $lines = explode("\n", $content);

        if (empty($lines)) {
            return Inertia::render('Dashboard', [
                'errors' => ['general' => "The CSV file is empty."],
            ])->toResponse($request)->setStatusCode(422);
        }

        $firstLine = array_shift($lines);
        $firstLine = trim($firstLine);

        // $commaCount = substr_count($firstLine, ',');
        // $semicolonCount = substr_count($firstLine, ';');

        $delimiter = ',';
        if (strpos($firstLine, ';') !== false && strpos($firstLine, ',') === false) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        }

        $header = str_getcsv($firstLine, $delimiter);
        $header = array_map(function($item) {
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', $item)));
        }, $header);


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
            foreach ($lines as $rowNum => $line) {
                $row = str_getcsv($line, $delimiter);

                if (empty(array_filter($row))) {
                    $skippedRows++;
                    continue;
                }

                $rowData = [];
                foreach ($header as $index => $colName) {
                    $rowData[$colName] = $row[$index] ?? null;
                }

                $chineseCharacter = $rowData['chinese_character'] ?? null;
                $pinyin = $rowData['pinyin'] ?? null;
                $translation = $rowData['translation'] ?? null;
                $studySessionName = $rowData['study_session_name'] ?? null;
                $tags = $rowData['tags'] ?? null;

                if (empty($chineseCharacter) || empty($pinyin) || empty($translation)) {
                    $errors[] = "Row " . ($rowNum + 2 - $skippedRows) . ": Missing required word data (chinese_character, pinyin, or translation).";
                    continue;
                }

                $word = Word::firstOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'chinese_word' => $chineseCharacter,
                    ],
                    [
                        'pinyin' => $pinyin,
                        'translation' => $translation,
                    ]
                );

                if (!empty($studySessionName)) {
                    $sessionNameTrimmed = trim($studySessionName);
                    if (!empty($sessionNameTrimmed)) {
                        $studySession = StudySession::firstOrCreate(
                            [
                                'user_id' => auth()->id(),
                                'name' => $sessionNameTrimmed,
                            ],
                            [
                                'description' => "Session created from CSV import: {$sessionNameTrimmed}",
                            ]
                        );

                        $studySession->words()->syncWithoutDetaching($word->id);
                    }
                }

                if (!empty($tags)) {
                    $tagNames = explode(',', $tags);
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
            $message = "Successfully imported {$importedCount} words.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " errors encountered: " . implode(', ', $errors);
            }
            return Redirect::route('dashboard')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("CSV Import Error: " . $e->getMessage(), ['user_id' => auth()->id(), 'exception' => $e]);
            return Redirect::route('dashboard')->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }
}
