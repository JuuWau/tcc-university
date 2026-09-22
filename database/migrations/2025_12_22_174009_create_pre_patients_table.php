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
        Schema::create('pre_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('cpf')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('biological_sex', 20);
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('patient_type');
            $table->string('status', 30)->default('waiting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_patients');
    }
};
