<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * The words that belong to the tag.
     */
    public function words(): BelongsToMany
    {
        return $this->belongsToMany(Word::class);
    }
}