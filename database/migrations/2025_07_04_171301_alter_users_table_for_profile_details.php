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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'name')) {
                $table->renameColumn('name', 'username');
            }
            $table->string('username')->unique()->nullable(false)->change();

            $table->string('first_name')->nullable()->after('username');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('country')->nullable()->after('last_name');
            $table->string('city')->nullable()->after('country');
            $table->string('native_language')->nullable()->after('email');
            $table->string('chinese_level')->nullable()->after('native_language');
            $table->json('languages_studied')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'country',
                'city',
                'native_language',
                'chinese_level'
            ]);

            if (Schema::hasColumn('users', 'username')) {
                $table->renameColumn('username', 'name');
            }
            $table->string('name')->nullable()->change();
        });
    }
};