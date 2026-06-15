<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('target_weight', 5, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('default_pilot_limit')->default(12);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status', 30)->default('planned');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('season_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('pilot_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['season_id', 'category_id']);
        });

        Schema::create('pilots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nickname')->nullable();
            $table->string('photo_path')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->decimal('base_weight', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('season_category_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_category_id')->constrained('season_categories')->cascadeOnDelete();
            $table->foreignId('pilot_id')->constrained()->restrictOnDelete();
            $table->string('registration_type', 20)->default('annual');
            $table->string('status', 20)->default('confirmed');
            $table->date('registered_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedInteger('waitlist_order')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['season_category_id', 'pilot_id'], 'season_category_pilot_unique');
            $table->index(['status', 'registration_type']);
        });

        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('stage_number');
            $table->date('stage_date');
            $table->time('briefing_time')->nullable();
            $table->time('draw_time')->nullable();
            $table->string('location')->nullable();
            $table->string('track_layout')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('planned');
            $table->timestamps();

            $table->unique(['season_id', 'stage_number']);
        });

        Schema::create('stage_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_category_id')->constrained('season_categories')->cascadeOnDelete();
            $table->string('status', 20)->default('planned');
            $table->timestamp('management_locked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['stage_id', 'season_category_id']);
        });

        Schema::create('stage_category_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_category_id')->constrained('stage_categories')->cascadeOnDelete();
            $table->foreignId('pilot_id')->constrained()->restrictOnDelete();
            $table->foreignId('season_category_registration_id')->nullable()->constrained('season_category_registrations')->nullOnDelete();
            $table->string('confirmation_status', 20)->default('confirmed');
            $table->string('attendance_status', 20)->default('pending');
            $table->string('briefing_status', 20)->default('pending');
            $table->unsignedSmallInteger('briefing_penalty_grid_positions')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();

            $table->unique(['stage_category_id', 'pilot_id'], 'stage_category_pilot_entry_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_category_entries');
        Schema::dropIfExists('stage_categories');
        Schema::dropIfExists('stages');
        Schema::dropIfExists('season_category_registrations');
        Schema::dropIfExists('pilots');
        Schema::dropIfExists('season_categories');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('categories');
    }
};
