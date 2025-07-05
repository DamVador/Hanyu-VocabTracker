<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $table = 'histories';

    protected $fillable = [
        'word_id',
        'user_id',
        'last_revision',
        'revision_interval',
        'consecutive_correct_revisions',
        'total_incorrect_revisions',
        'learning_status',
        'next_revision',
    ];

    protected $casts = [
        'last_revision' => 'datetime',
        'next_revision' => 'datetime',
    ];

    /**
     * Get the word that this history record belongs to.
     */
    public function word()
    {
        return $this->belongsTo(Word::class);
    }

    /**
     * Get the user that owns this history record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}