<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use App\Models\Word;
use App\Http\Requests\StoreWordRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WordController extends Controller
{
    public function index2()
    {
        $words = Word::all();

        return inertia('Words/Index2', compact('words'));
    }
    
    public function index()
    {
        $words = Auth::user()->words()->latest()->get(); // Assuming a 'words' relationship on User model
        return Inertia::render('Words/Index', [
            'words' => $words,
        ]);
    }

    public function create()
    {
        return Inertia::render('Words/Create'); // Assuming you have a Vue page at resources/js/Pages/Words/Create.vue
    }

    public function save(StoreWordRequest $request)
    {
        // The validated() method automatically retrieves the validated data
        // and handles redirection back with errors if validation fails.
        $validatedData = $request->validated();

        // Create a new Word instance
        $word = new Word();

        // Assign attributes from validated data
        $word->chinese_word = $validatedData['chinese_word'];
        $word->pinyin = $validatedData['pinyin'];
        $word->translation = $validatedData['translation'];

        // Assign the authenticated user's ID
        $word->user_id = Auth::id(); // Or use $request->user()->id;

        // Assign tags (it will be automatically cast to JSON by the model due to $casts property)
        $word->tags = $validatedData['tags'] ?? []; // Ensure it's an empty array if null

        // Save the word to the database
        $word->save();

        // Redirect with a success message (Inertia will pick this up as flash message)
        return redirect()->route('dashboard')->with('success', 'Word added successfully!');

        // Alternatively, if you want to redirect back to the form page:
        // return redirect()->back()->with('success', 'Word added successfully!');
    }

}
