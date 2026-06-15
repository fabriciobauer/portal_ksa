<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pilots', function (Blueprint $table) {
            $table->string('cpf', 20)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->text('address')->nullable();
            $table->json('metadata')->nullable();
        });

        Schema::create('pilot_registration_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pilot_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('whatsapp', 30);
            $table->string('cpf', 20);
            $table->string('email');
            $table->text('address');
            $table->boolean('has_kart_experience');
            $table->boolean('has_championship_experience');
            $table->decimal('weight_kg', 5, 2);
            $table->unsignedSmallInteger('age');
            $table->foreignId('pilot_registration_category_id')
                ->constrained('pilot_registration_categories')
                ->restrictOnDelete();
            $table->boolean('payment_status')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('converted_to_pilot_at')->nullable();
            $table->foreignId('pilot_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('payment_marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('payment_unmarked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['payment_status', 'created_at']);
            $table->index(['is_archived', 'created_at']);
            $table->index('whatsapp');
            $table->index('cpf');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilot_registrations');
        Schema::dropIfExists('pilot_registration_categories');

        Schema::table('pilots', function (Blueprint $table) {
            $table->dropColumn(['cpf', 'email', 'address', 'metadata']);
        });
    }
};
