<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chinese_word',
        'pinyin',
        'translation',
        'notes',
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

    /**
     * Get the history records for the word.
     * This defines the relationship between a Word and its History records.
     */
    public function histories()
    {
        return $this->hasMany(History::class); // A Word has many History records
    }

    /**
     * The study sessions that this word belongs to.
     */
    public function studySessions(): BelongsToMany
    {
        return $this->belongsToMany(StudySession::class);
    }

    /**
     * Get the latest history entry for the word.
     */
    public function latestHistory(): HasOne
    {
        return $this->hasOne(History::class)->latestOfMany();
    }
}