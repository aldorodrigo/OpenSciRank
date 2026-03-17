<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

$user = User::firstOrCreate(
    ['email' => 'admin@openscirank.com'],
    [
        'name' => 'Admin User',
        'password' => Hash::make('password'),
    ]
);

$user->password = Hash::make('password');
$user->save();

// Assign Super Admin role if it exists (Shield)
if (Role::where('name', 'super_admin')->exists()) {
    $user->assignRole('super_admin');
    echo "Assigned super_admin role.\n";
} else {
    echo "Super Admin role not found. Ensure Shield is generated.\n";
}

echo "User created/updated: {$user->email}\n";
