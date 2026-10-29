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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->nullable()->unique();
            $table->string('company');
            $table->string('cr_number', 15)->index();
            $table->string('contact_name');
            $table->string('email')->index();
            $table->string('phone', 32)->nullable();
            $table->foreignId('sector_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 16)->default('pending')->index();
            $table->text('status_reason')->nullable();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
