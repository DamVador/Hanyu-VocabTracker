<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    /**
     * Get the user that owns the study session.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The words that belong to the study session.
     */
    public function words(): BelongsToMany
    {
        return $this->belongsToMany(Word::class);
    }
}