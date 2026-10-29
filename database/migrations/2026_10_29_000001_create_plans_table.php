<?php

declare(strict_types=1);

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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->json('name');
            $table->string('tier', 32);
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_annual', 10, 2);
            $table->unsignedSmallInteger('included_branches')->default(1);
            $table->decimal('extra_branch_monthly', 10, 2)->default(0);
            $table->decimal('extra_branch_annual', 10, 2)->default(0);
            $table->unsignedInteger('max_sites')->nullable();
            $table->unsignedInteger('max_users')->nullable();
            $table->foreignId('includes_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('plan_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('module_key', 32);

            $table->unique(['plan_id', 'module_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_modules');
        Schema::dropIfExists('plans');
    }
};
