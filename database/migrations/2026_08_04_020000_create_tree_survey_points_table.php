<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tree_survey_points', function (Blueprint $table) {
            $table->id();
            $table->string('source_file');
            $table->unsignedInteger('source_row');
            $table->string('raw_species_name')->nullable()->index();
            $table->decimal('easting', 14, 3)->nullable();
            $table->decimal('northing', 14, 3)->nullable();
            $table->decimal('height_m', 8, 2)->nullable();
            $table->decimal('latitude', 12, 9)->nullable()->index();
            $table->decimal('longitude', 12, 9)->nullable()->index();
            $table->string('coordinate_status', 20)->default('valid')->index();
            $table->timestamps();

            $table->unique(['source_file', 'source_row']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tree_survey_points');
    }
};
