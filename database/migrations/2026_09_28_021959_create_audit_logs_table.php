<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->default('System');
            $table->string('user_role')->default('Engineer');
            $table->string('category')->default('Weather Decision');
            $table->string('action_title');
            $table->string('target_activity');
            $table->json('weather_snapshot')->nullable();
            $table->string('outcome_type')->default('Continue Same Day');
            $table->text('outcome_details')->nullable();
            $table->integer('response_time_minutes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
