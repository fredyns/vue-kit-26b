<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * The roles to be inserted.
     *
     * @var string[]
     */
    public array $roles = [
        'super-admin',
        'employee',
        'user'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (UserRole::values() as $role) {
            app(\App\Models\RBAC\Role::class)->findOrCreate($role, 'web');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::whereIn('name', UserRole::values())->whereIn('guard_name', ['web'])->delete();
    }
};
