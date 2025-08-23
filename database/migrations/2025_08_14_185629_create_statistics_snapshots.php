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
        Schema::create('statistics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date')->index();
            
            // wordsReviewedOverTime
            $table->integer('words_reviewed')->default(0);
            
            // accuracyRateOverTime
            $table->integer('correct_answers')->default(0);
            $table->integer('incorrect_answers')->default(0);
            
            // learningStatusDistribution
            $table->integer('new_words')->default(0);
            $table->integer('revise_words')->default(0);
            $table->integer('forgot_words')->default(0);
            $table->integer('mastered_words')->default(0);
            
            // topDifficultWords (optionnel)
            $table->json('difficult_words')->nullable();
            
            $table->timestamps();
            
            $table->unique(['user_id', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistics_snapshots');
    }
};
