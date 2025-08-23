<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class StatisticsSnapshot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'snapshot_date',
        'words_reviewed',
        'correct_answers',
        'incorrect_answers',
        'new_words',
        'revise_words',
        'forgot_words',
        'mastered_words',
        'difficult_words'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'snapshot_date' => 'date',
        'difficult_words' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the snapshot.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include snapshots after a given date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAfterDate($query, $date)
    {
        return $query->where('snapshot_date', '>=', $date);
    }

    /**
     * Scope a query to only include snapshots before a given date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBeforeDate($query, $date)
    {
        return $query->where('snapshot_date', '<=', $date);
    }

    /**
     * Calculate accuracy percentage for the snapshot.
     *
     * @return float
     */
    public function getAccuracyPercentageAttribute()
    {
        $total = $this->correct_answers + $this->incorrect_answers;
        return $total > 0 ? round(($this->correct_answers / $total) * 100, 2) : 0;
    }

    /**
     * Get the total words in learning (new + revise + forgot).
     *
     * @return int
     */
    public function getTotalWordsInLearningAttribute()
    {
        return $this->new_words + $this->revise_words + $this->forgot_words;
    }
}