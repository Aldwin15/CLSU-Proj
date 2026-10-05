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
        Schema::table('schedules', function (Blueprint $table) {
            $table->boolean('continued_same_day')->default(false)->after('status');
            $table->string('resume_time')->nullable()->after('continued_same_day');
            $table->text('mitigation_notes')->nullable()->after('resume_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['continued_same_day', 'resume_time', 'mitigation_notes']);
        });
    }
};
