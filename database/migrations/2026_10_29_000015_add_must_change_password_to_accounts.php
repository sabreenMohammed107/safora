<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Accounts whose password was set by someone else (an admin or company manager)
     * must choose their own password at their next sign-in.
     */
    public function up(): void
    {
        foreach (['admins', 'client_users'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->boolean('must_change_password')->default(false)->after('password');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['admins', 'client_users'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('must_change_password');
            });
        }
    }
};
