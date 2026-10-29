<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot of the default role permissions at the time of this migration
     * (docs/sprints/03_BUSINESS_RULES.md §3). The live defaults are in App\Models\ClientRole::DEFAULTS.
     */
    private const array DEFAULTS = [
        'branch_manager' => [
            'records.view', 'records.create', 'records.edit', 'records.delete',
            'prp.access', 'haccp_plans.manage', 'control_points.monitor', 'critical_limits.record',
            'traceability.manage', 'iso.access', 'products.manage', 'product_requests.create',
        ],
        'branch_user' => [
            'records.view', 'records.create', 'records.edit',
            'prp.access', 'control_points.monitor', 'critical_limits.record', 'iso.access',
            'product_requests.create',
        ],
    ];

    /**
     * Per-client roles. Every client gets editable "branch manager" and "branch user" roles;
     * existing branch managers/users move onto them and become "member" accounts.
     */
    public function up(): void
    {
        Schema::create('client_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('key', 32)->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->json('permissions');
            $table->timestamps();

            $table->unique(['client_id', 'name']);
            $table->unique(['client_id', 'key']);
        });

        Schema::table('client_users', function (Blueprint $table) {
            $table->foreignId('client_role_id')->nullable()->after('role')->constrained('client_roles')->restrictOnDelete();
        });

        $this->backfill();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (DB::table('client_users')->where('role', 'member')->get(['id', 'client_role_id']) as $user) {
            $key = DB::table('client_roles')->where('id', $user->client_role_id)->value('key');
            DB::table('client_users')->where('id', $user->id)->update(['role' => $key ?? 'branch_user']);
        }

        Schema::table('client_users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_role_id');
        });

        Schema::dropIfExists('client_roles');
    }

    private function backfill(): void
    {
        $names = ['branch_manager' => 'مسؤول فرع', 'branch_user' => 'مستخدم فرع'];

        foreach (DB::table('clients')->pluck('id') as $clientId) {
            foreach (self::DEFAULTS as $key => $permissions) {
                $roleId = DB::table('client_roles')->insertGetId([
                    'client_id' => $clientId,
                    'key' => $key,
                    'name' => $names[$key],
                    'permissions' => json_encode($permissions),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('client_users')
                    ->where('client_id', $clientId)
                    ->where('role', $key)
                    ->update(['role' => 'member', 'client_role_id' => $roleId]);
            }
        }
    }
};
