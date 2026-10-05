<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_activities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('Structural');
            $table->integer('default_duration_days')->default(3);
            $table->decimal('max_rain_probability', 5, 2)->default(40.00);
            $table->decimal('max_rain_volume_mm', 5, 2)->default(2.50);
            $table->decimal('max_wind_speed_kmh', 5, 2)->default(40.00);
            $table->decimal('max_temperature_c', 5, 2)->default(36.00);
            $table->string('safety_trigger')->default('Rainfall > 3mm/hr');
            $table->string('predecessor_hint')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_activities');
    }
};
