<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('location')->default('CLSU Campus, Science City of Muñoz, Nueva Ecija');
            $table->decimal('latitude', 10, 7)->default(15.7144);
            $table->decimal('longitude', 10, 7)->default(120.9307);
            $table->integer('storeys')->default(5);
            $table->integer('duration_weeks')->default(12);
            $table->date('start_date')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->string('status')->default('In Progress');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
