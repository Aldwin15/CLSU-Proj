<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('master_activity_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('max_rain_probability', 5, 2)->default(40.00);
            $table->decimal('max_rain_volume_mm', 5, 2)->default(2.50);
            $table->decimal('max_wind_speed_kmh', 5, 2)->default(40.00);
            $table->decimal('max_temperature_c', 5, 2)->default(36.00);
            $table->string('worker_safety_trigger')->default('Rainfall > 3mm/hr');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_thresholds');
    }
};
