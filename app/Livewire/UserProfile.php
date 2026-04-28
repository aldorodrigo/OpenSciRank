<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        Auth::user()->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('profile_message', __('Profile updated successfully.'));
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('password_message', __('Password updated successfully.'));
    }

    public function render()
    {
        return view('livewire.user-profile')->layout('components.layouts.app', [
            'title' => __('My Profile') . ' - Editorial Standards Platform',
        ]);
    }
}
