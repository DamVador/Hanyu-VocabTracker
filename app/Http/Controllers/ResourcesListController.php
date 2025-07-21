<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class ResourcesListController extends Controller
{
    public function index()
    {
        // TODO - Ajout d'une table ressources
        $resourcesLists = [
            [
                'name' => 'HSK 1 Vocabulary (simplified Chinese)',
                'description' => 'Contains 150 essential words for HSK Level 1.',
                'file_url' => 'https://hanyu-vocabtracker.com/n0c-storage/HanyuVocabTracker/Vocabulary%20list/HSK1_list.csv',
            ],
            [
                'name' => 'HSK 2 Vocabulary (simplified Chinese)',
                'description' => 'New 150 words for HSK Level 2, building upon HSK 1.',
                'file_url' => 'https://hanyu-vocabtracker.com/n0c-storage/HanyuVocabTracker/Vocabulary%20list/HSK2_list.csv',
            ],
            [
                'name' => 'HSK 3 Vocabulary (simplified Chinese)',
                'description' => 'New 300 words for HSK Level 3, building upon HSK 2.',
                'file_url' => 'https://hanyu-vocabtracker.com/n0c-storage/HanyuVocabTracker/Vocabulary%20list/HSK3_list.csv',
            ],
            [
                'name' => 'HSK 1 Vocabulary (traditional Chinese)',
                'description' => 'Contains 150 essential words equivalent to HSK Level 1 in traditional Chinese.',
                'file_url' => 'https://hanyu-vocabtracker.com/n0c-storage/HanyuVocabTracker/Vocabulary%20list/HSK1_trad_list.csv',
            ],
            [
                'name' => 'HSK 2 Vocabulary (traditional Chinese)',
                'description' => 'New 150 words equivalent to HSK Level 2 in traditonal Chinese.',
                'file_url' => 'https://hanyu-vocabtracker.com/n0c-storage/HanyuVocabTracker/Vocabulary%20list/HSK2_trad_list.csv',
            ],
            [
                'name' => 'HSK 3 Vocabulary (traditional Chinese)',
                'description' => 'New 300 words equivalent to HSK Level 3 in traditional Chinese.',
                'file_url' => 'https://hanyu-vocabtracker.com/n0c-storage/HanyuVocabTracker/Vocabulary%20list/HSK3_trad_list.csv',
            ],
        ];

        return Inertia::render('ResourcesLists/Index', [
            'resourcesLists' => $resourcesLists,
        ]);
    }

    // TODO
    // Pour gérer les téléchargements via Laravel (par ex. pour des statistiques ou une authentification),
    // Ajouter une méthode 'download' et une route correspondante.
}