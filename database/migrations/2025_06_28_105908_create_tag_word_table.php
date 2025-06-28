<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tag_word', function (Blueprint $table) {
            // Foreign key for the 'tags' table
            $table->foreignId('tag_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Foreign key for the 'words' table
            $table->foreignId('word_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Define a composite primary key to ensure uniqueness for each tag-word pair
            $table->primary(['tag_id', 'word_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_word');
    }
};