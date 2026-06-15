<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kart_draw_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_category_id')->constrained('stage_categories')->cascadeOnDelete();
            $table->string('session_key', 30)->default('qualifying');
            $table->unsignedInteger('sequence')->default(1);
            $table->string('status', 20)->default('draft');
            $table->unsignedSmallInteger('range_start')->default(1);
            $table->unsignedSmallInteger('range_end')->default(15);
            $table->timestamp('drawn_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recreated_from_batch_id')->nullable()->constrained('kart_draw_batches')->nullOnDelete();
            $table->timestamps();

            $table->unique(['stage_category_id', 'session_key', 'sequence']);
        });

        Schema::create('kart_draws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kart_draw_batch_id')->constrained('kart_draw_batches')->cascadeOnDelete();
            $table->foreignId('stage_category_entry_id')->constrained('stage_category_entries')->cascadeOnDelete();
            $table->unsignedSmallInteger('kart_number');
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_manual')->default(false);
            $table->timestamps();

            $table->unique(['kart_draw_batch_id', 'stage_category_entry_id'], 'kart_draw_batch_entry_unique');
            $table->unique(['kart_draw_batch_id', 'kart_number'], 'kart_draw_batch_kart_unique');
        });

        Schema::create('qualifying_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_category_entry_id')->constrained('stage_category_entries')->cascadeOnDelete();
            $table->unsignedSmallInteger('initial_kart_number')->nullable();
            $table->unsignedSmallInteger('current_kart_number')->nullable();
            $table->unsignedInteger('lap_time_ms')->nullable();
            $table->string('status', 20)->default('valid');
            $table->unsignedSmallInteger('auto_grid_position')->nullable();
            $table->unsignedSmallInteger('final_grid_position')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('kart_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_category_entry_id')->constrained('stage_category_entries')->cascadeOnDelete();
            $table->foreignId('qualifying_result_id')->nullable()->constrained('qualifying_results')->nullOnDelete();
            $table->unsignedSmallInteger('previous_kart_number');
            $table->unsignedSmallInteger('new_kart_number');
            $table->string('reason_type', 20)->default('regular');
            $table->boolean('counts_as_regular_swap')->default(true);
            $table->timestamp('happened_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_category_id')->constrained('stage_categories')->cascadeOnDelete();
            $table->unsignedTinyInteger('number');
            $table->string('name');
            $table->string('grid_generated_from')->nullable();
            $table->boolean('is_grid_confirmed')->default(false);
            $table->boolean('is_result_confirmed')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['stage_category_id', 'number']);
        });

        Schema::create('race_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_category_entry_id')->constrained('stage_category_entries')->cascadeOnDelete();
            $table->unsignedSmallInteger('grid_position')->nullable();
            $table->unsignedSmallInteger('finish_position')->nullable();
            $table->unsignedSmallInteger('kart_number')->nullable();
            $table->string('status', 20)->default('finished');
            $table->decimal('points_awarded', 8, 2)->default(0);
            $table->boolean('is_official')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['race_id', 'stage_category_entry_id']);
        });

        Schema::create('weigh_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_category_entry_id')->constrained('stage_category_entries')->cascadeOnDelete();
            $table->unsignedSmallInteger('kart_number')->nullable();
            $table->decimal('combined_weight', 6, 2)->nullable();
            $table->decimal('tolerance_used', 6, 2)->nullable();
            $table->boolean('within_tolerance')->nullable();
            $table->boolean('exception_no_spare_kart')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('weighed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stage_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_category_entry_id')->constrained('stage_category_entries')->cascadeOnDelete();
            $table->foreignId('race_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40);
            $table->string('penalty_scope', 30)->default('championship_only');
            $table->decimal('points_delta', 8, 2)->default(0);
            $table->string('capped_group', 40)->nullable();
            $table->boolean('is_disqualification')->default(false);
            $table->boolean('affects_discard_block')->default(false);
            $table->text('reason')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });

        Schema::create('race_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_category_entry_id')->constrained('stage_category_entries')->cascadeOnDelete();
            $table->foreignId('race_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40);
            $table->unsignedSmallInteger('seconds_penalty')->nullable();
            $table->boolean('auto_disqualified')->default(false);
            $table->text('description')->nullable();
            $table->string('evidence_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_occurrences');
        Schema::dropIfExists('stage_penalties');
        Schema::dropIfExists('weigh_ins');
        Schema::dropIfExists('race_results');
        Schema::dropIfExists('races');
        Schema::dropIfExists('kart_changes');
        Schema::dropIfExists('qualifying_results');
        Schema::dropIfExists('kart_draws');
        Schema::dropIfExists('kart_draw_batches');
    }
};
