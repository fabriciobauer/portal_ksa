<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_kart_queue_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_category_id')->constrained('stage_categories')->cascadeOnDelete();
            $table->unsignedSmallInteger('queue_position');
            $table->unsignedSmallInteger('kart_number')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['stage_category_id', 'queue_position'], 'stage_kart_queue_position_unique');
        });

        Schema::table('kart_draws', function (Blueprint $table) {
            $table->unsignedSmallInteger('queue_position')->nullable()->after('stage_category_entry_id');
            $table->unsignedSmallInteger('draw_order')->nullable()->after('queue_position');
        });

        DB::table('kart_draws')
            ->select(['id', 'kart_draw_batch_id', 'kart_number'])
            ->orderBy('kart_draw_batch_id')
            ->orderBy('id')
            ->get()
            ->groupBy('kart_draw_batch_id')
            ->each(function (Collection $draws): void {
                foreach ($draws->values() as $index => $draw) {
                    DB::table('kart_draws')
                        ->where('id', $draw->id)
                        ->update([
                            'queue_position' => $draw->kart_number,
                            'draw_order' => $index + 1,
                        ]);
                }
            });

        Schema::table('kart_draws', function (Blueprint $table) {
            $table->unique(['kart_draw_batch_id', 'queue_position'], 'kart_draw_batch_queue_unique');
        });
    }

    public function down(): void
    {
        Schema::table('kart_draws', function (Blueprint $table) {
            $table->dropUnique('kart_draw_batch_queue_unique');
            $table->dropColumn(['queue_position', 'draw_order']);
        });

        Schema::dropIfExists('stage_kart_queue_positions');
    }
};
