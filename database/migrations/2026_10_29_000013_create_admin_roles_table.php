<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staff roles with code-defined permissions (App\Enums\AdminPermission values) stored as JSON.
     * Admins created before roles existed keep full access as super admins.
     */
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->json('permissions');
            $table->timestamps();
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->foreignId('admin_role_id')->nullable()->after('locale')->constrained('admin_roles')->restrictOnDelete();
            $table->boolean('is_super_admin')->default(false)->after('admin_role_id');
            $table->boolean('active')->default(true)->after('is_super_admin');
        });

        DB::table('admins')->update(['is_super_admin' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_role_id');
            $table->dropColumn(['is_super_admin', 'active']);
        });

        Schema::dropIfExists('admin_roles');
    }
};
