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
        Schema::table('tree_survey_points', function (Blueprint $table) {
            $table->string('approved_species_name')->nullable()->index()->after('raw_species_name');
            $table->foreignId('campus_area_id')->nullable()->after('coordinate_status')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tree_survey_points', function (Blueprint $table) {
            $table->dropForeign(['campus_area_id']);
            $table->dropColumn(['approved_species_name', 'campus_area_id']);
        });
    }
};
