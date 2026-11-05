<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_areas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('center_latitude', 10, 7)->nullable();
            $table->decimal('center_longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('common_name')->index();
            $table->string('scientific_name')->unique();
            $table->string('family_name')->index();
            $table->string('local_name')->nullable()->index();
            $table->string('classification')->nullable();
            $table->string('conservation_status', 40)->default('not_evaluated')->index();
            $table->string('origin')->nullable();
            $table->string('typical_lifespan')->nullable();
            $table->text('description')->nullable();
            $table->text('educational_value')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('trees', function (Blueprint $table) {
            $table->id();
            $table->string('tree_code', 20)->nullable()->unique();
            $table->foreignId('species_id')->constrained()->restrictOnDelete();
            $table->foreignId('campus_area_id')->constrained()->restrictOnDelete();
            $table->string('descriptive_location');
            $table->string('nearby_landmark')->nullable();
            $table->decimal('latitude', 10, 7)->nullable()->index();
            $table->decimal('longitude', 10, 7)->nullable()->index();
            $table->decimal('trunk_diameter_cm', 7, 2)->nullable();
            $table->decimal('estimated_height_m', 6, 2)->nullable();
            $table->unsignedSmallInteger('estimated_age_years')->nullable();
            $table->date('date_planted')->nullable();
            $table->string('current_condition', 30)->default('healthy')->index();
            $table->string('status', 30)->default('existing')->index();
            $table->string('verification_status', 30)->default('pending')->index();
            $table->text('public_notes')->nullable();
            $table->text('internal_remarks')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['campus_area_id', 'current_condition']);
            $table->index(['status', 'verification_status']);
        });

        Schema::create('tree_monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->constrained()->cascadeOnDelete();
            $table->dateTime('observed_at')->index();
            $table->string('condition', 30)->index();
            $table->decimal('trunk_diameter_cm', 7, 2)->nullable();
            $table->decimal('estimated_height_m', 6, 2)->nullable();
            $table->text('remarks');
            $table->text('public_notes')->nullable();
            $table->boolean('is_public')->default(false)->index();
            $table->string('verification_status', 30)->default('pending')->index();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['tree_id', 'observed_at']);
        });

        Schema::create('tree_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tree_monitoring_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 30)->default('tree')->index();
            $table->string('disk', 30)->default('public');
            $table->string('path');
            $table->string('original_filename')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tree_id', 'is_primary']);
        });

        Schema::create('tree_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tree_monitoring_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('verifier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->index();
            $table->text('remarks')->nullable();
            $table->json('reviewed_fields')->nullable();
            $table->timestamp('decided_at')->nullable()->index();
            $table->timestamps();
            $table->index(['tree_id', 'created_at']);
        });

        Schema::create('tree_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('public_url');
            $table->string('storage_path')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->unsignedInteger('print_count')->default(0);
            $table->string('status', 20)->default('active')->index();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('last_printed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80)->index();
            $table->nullableMorphs('subject');
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('tree_qr_codes');
        Schema::dropIfExists('tree_verifications');
        Schema::dropIfExists('tree_photos');
        Schema::dropIfExists('tree_monitorings');
        Schema::dropIfExists('trees');
        Schema::dropIfExists('species');
        Schema::dropIfExists('campus_areas');
    }
};
