<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chinese_word', // Your new column
        'pinyin',
        'translation',
        // 'notes', // Removed as per your clarification
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The tags that belong to the word.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}