<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HACCP prerequisite programs (PRP-01 … PRP-22) and their 7 groups — Sprint 3.
     * Text columns are translatable JSON ({"ar": …, "en": …}); `checks` holds a list per locale.
     */
    public function up(): void
    {
        Schema::create('haccp_program_groups', function (Blueprint $table) {
            $table->id();
            $table->string('key', 32)->unique();
            $table->json('label');
            $table->string('icon', 64);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('haccp_programs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->foreignId('group_id')->constrained('haccp_program_groups')->restrictOnDelete();
            $table->json('name');
            $table->string('icon', 64);
            $table->json('summary')->nullable();
            $table->json('record_name')->nullable();
            $table->json('acceptance')->nullable();
            $table->json('limit_text')->nullable();
            $table->json('steps')->nullable();
            $table->json('evidence')->nullable();
            $table->json('failure')->nullable();
            $table->json('haccp_link')->nullable();
            $table->json('responsible')->nullable();
            $table->json('frequency')->nullable();
            $table->json('checks')->nullable();
            $table->string('status', 16)->default('active')->index();
            $table->boolean('is_custom')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('haccp_programs');
        Schema::dropIfExists('haccp_program_groups');
    }
};
