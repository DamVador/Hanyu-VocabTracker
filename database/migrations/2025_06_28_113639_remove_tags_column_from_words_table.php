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
        Schema::table('words', function (Blueprint $table) {
            // Check if the column exists before trying to drop it, just to be safe
            if (Schema::hasColumn('words', 'tags')) {
                $table->dropColumn('tags');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('words', function (Blueprint $table) {
            // IMPORTANT: If you rollback, data in this column will be lost unless you handle it.
            // Re-add the column with its original type (e.g., string, text) and nullability.
            // Assuming it was a string and nullable. Adjust if your original was different.
            $table->string('tags')->nullable();
        });
    }
};