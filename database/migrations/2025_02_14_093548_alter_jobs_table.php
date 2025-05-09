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
        // Check if the column does not exist before adding it
        if (!Schema::hasColumn('jobs', 'user_id')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade')->after('job_type_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign('user_id');
            $table->dropColumn('user_id');
        });
    }
};
