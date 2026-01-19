<?php

use Illuminate\Support\Facades\DB;
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
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('academic_year');
            $table->integer('semester');
            $table->integer('calendar_year');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("
                CREATE UNIQUE INDEX periods_unique_active
                ON periods (university_id, academic_year, semester, calendar_year)
                WHERE deleted_at IS NULL
            ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periods', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
