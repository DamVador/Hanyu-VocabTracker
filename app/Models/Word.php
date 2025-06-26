<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // This is technically filled automatically in the controller, but good practice
        'chinese_word',
        'pinyin',
        'translation',
        'tags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tags' => 'array', // This will automatically serialize/deserialize the tags array to/from JSON
    ];

    /**
     * Get the user that owns the word.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}