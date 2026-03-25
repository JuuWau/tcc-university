<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("
            CREATE UNIQUE INDEX clinics_university_name_unique
            ON clinics (university_id, name)
            WHERE deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
