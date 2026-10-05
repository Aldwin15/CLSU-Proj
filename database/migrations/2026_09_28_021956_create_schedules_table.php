<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('master_activity_id')->nullable()->constrained('master_activities')->nullOnDelete();
            $table->foreignId('predecessor_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->string('name');
            $table->integer('duration_days')->default(2);
            $table->date('baseline_start_date');
            $table->date('baseline_end_date');
            $table->date('current_start_date');
            $table->date('current_end_date');
            $table->string('feasibility')->default('Feasible');
            $table->boolean('has_weather_alert')->default(false);
            $table->string('weather_rule_text')->default('Rain < 40%, Wind < 40km/h');
            $table->string('status')->default('Scheduled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
