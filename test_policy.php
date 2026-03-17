<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Journal;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Ensure permissions exist
try {
    Permission::firstOrCreate(['name' => 'Delete:Journal']);
} catch (\Exception $e) {}

// Create a regular user with delete permission
$user = User::factory()->create();
$user->givePermissionTo('Delete:Journal');

// Create journals
$draft = Journal::factory()->create(['user_id' => $user->id, 'status' => 'draft']);
$submitted = Journal::factory()->create(['user_id' => $user->id, 'status' => 'submitted']);

echo "--- Policy Verification ---\n";
echo "User can delete draft: " . ($user->can('delete', $draft) ? 'YES' : 'NO') . "\n";
echo "User can delete submitted: " . ($user->can('delete', $submitted) ? 'YES' : 'NO') . "\n";

// Create super admin
$admin = User::firstOrCreate(['email' => 'admin_test_uniq_' . time() . '@example.com'], ['name' => 'Admin', 'password' => bcrypt('password')]);
$role = Role::firstOrCreate(['name' => 'super_admin']);
$admin->assignRole($role);

echo "Admin can delete submitted: " . ($admin->can('delete', $submitted) ? 'YES' : 'NO') . "\n";

// Cleanup
$draft->forceDelete();
$submitted->forceDelete();
// Users cleanup skipped
