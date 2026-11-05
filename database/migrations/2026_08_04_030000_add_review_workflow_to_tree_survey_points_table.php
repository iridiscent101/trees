<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tree_survey_points', function (Blueprint $table) {
            $table->string('review_status', 30)->default('pending')->index()->after('coordinate_status');
            $table->foreignId('registered_tree_id')->nullable()->unique()->after('review_status')->constrained('trees')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->after('registered_tree_id')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->index()->after('reviewed_by');
            $table->text('review_notes')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('tree_survey_points', function (Blueprint $table) {
            $table->dropForeign(['registered_tree_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['review_status', 'registered_tree_id', 'reviewed_by', 'reviewed_at', 'review_notes']);
        });
    }
};
