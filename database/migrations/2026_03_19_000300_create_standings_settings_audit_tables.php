<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_category_entry_id')->constrained('stage_category_entries')->cascadeOnDelete();
            $table->decimal('race1_points', 8, 2)->default(0);
            $table->decimal('race2_points', 8, 2)->default(0);
            $table->decimal('completion_bonus', 8, 2)->default(0);
            $table->decimal('gross_stage_points', 8, 2)->default(0);
            $table->decimal('championship_penalty_points', 8, 2)->default(0);
            $table->decimal('manual_adjustment_points', 8, 2)->default(0);
            $table->decimal('manual_override_points', 8, 2)->nullable();
            $table->decimal('championship_points', 8, 2)->default(0);
            $table->unsignedSmallInteger('stage_position')->nullable();
            $table->boolean('is_technical_tie')->default(false);
            $table->boolean('tie_breaker_resolved_manually')->default(false);
            $table->text('tie_break_notes')->nullable();
            $table->boolean('is_disqualified')->default(false);
            $table->text('disqualification_reason')->nullable();
            $table->boolean('discard_blocked')->default(false);
            $table->text('override_reason')->nullable();
            $table->foreignId('override_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('override_at')->nullable();
            $table->timestamp('last_recalculated_at')->nullable();
            $table->timestamps();

            $table->unique('stage_category_entry_id');
        });

        Schema::create('point_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_standing_id')->constrained('stage_standings')->cascadeOnDelete();
            $table->string('type', 30);
            $table->decimal('delta', 8, 2)->nullable();
            $table->decimal('previous_value', 8, 2)->nullable();
            $table->decimal('new_value', 8, 2)->nullable();
            $table->text('reason');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('championship_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_category_id')->constrained('season_categories')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pilot_id')->constrained()->restrictOnDelete();
            $table->foreignId('stage_standing_id')->nullable()->constrained('stage_standings')->nullOnDelete();
            $table->decimal('gross_points', 8, 2)->default(0);
            $table->decimal('adjustment_points', 8, 2)->default(0);
            $table->decimal('valid_points', 8, 2)->default(0);
            $table->boolean('is_discarded')->default(false);
            $table->boolean('discard_blocked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['season_category_id', 'stage_id', 'pilot_id'], 'championship_stage_pilot_unique');
        });

        Schema::create('championship_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_category_id')->constrained('season_categories')->cascadeOnDelete();
            $table->foreignId('pilot_id')->constrained()->restrictOnDelete();
            $table->decimal('total_gross_points', 8, 2)->default(0);
            $table->decimal('discarded_points', 8, 2)->default(0);
            $table->decimal('total_valid_points', 8, 2)->default(0);
            $table->unsignedSmallInteger('final_position')->nullable();
            $table->json('tiebreak_counters')->nullable();
            $table->boolean('promotion_eligible')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('last_recalculated_at')->nullable();
            $table->timestamps();

            $table->unique(['season_category_id', 'pilot_id'], 'championship_category_pilot_unique');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 50);
            $table->string('key')->unique();
            $table->string('label');
            $table->string('type', 30)->default('string');
            $table->json('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('auditable');
            $table->string('action', 40);
            $table->string('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('championship_standings');
        Schema::dropIfExists('championship_points');
        Schema::dropIfExists('point_adjustments');
        Schema::dropIfExists('stage_standings');
    }
};
