<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class ResourcesListController extends Controller
{
    public function index()
    {
        $resourcesLists = [
            [
                'name' => 'HSK 1 Vocabulary',
                'description' => 'Contains 150 essential words for HSK Level 1.',
                'file_url' => '/resources_files/HSK1_Vocabulary.csv',
            ],
            [
                'name' => 'HSK 2 Vocabulary (New Words)',
                'description' => 'New 150 words for HSK Level 2, building upon HSK 1.',
                'file_url' => '/resources_files/HSK2_Vocabulary.csv',
            ],
            [
                'name' => 'HSK 3 Vocabulary (New Words)',
                'description' => 'New 300 words for HSK Level 3, building upon HSK 2.',
                'file_url' => '/resources_files/HSK3_Vocabulary.csv',
            ],
            [
                'name' => 'TOCFL 1 Vocabulary',
                'description' => 'List of words for TOCFL 1.',
                'file_url' => '/resources_files/TOCFL1_Vocabulary.csv',
            ],
        ];

        return Inertia::render('ResourcesLists/Index', [
            'resourcesLists' => $resourcesLists,
        ]);
    }

    // Vous n'aurez pas besoin d'une méthode 'download' ici si vous liez directement à des fichiers statiques.
    // Si un jour vous voulez gérer les téléchargements via Laravel (par ex. pour des statistiques ou une authentification),
    // vous pourrez ajouter une méthode 'download' dans ce contrôleur et une route correspondante.
}