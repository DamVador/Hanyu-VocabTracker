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
        Schema::create('study_session_word', function (Blueprint $table) {
            $table->foreignId('study_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('word_id')->constrained()->onDelete('cascade');

            $table->primary(['study_session_id', 'word_id']); // Composite primary key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_session_word');
    }
};