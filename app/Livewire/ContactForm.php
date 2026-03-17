<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;

class ContactForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $institution = '';
    public string $subject = '';
    public string $message = '';
    public bool $sent = false;

    protected $rules = [
        'name' => 'required|min:2|max:100',
        'email' => 'required|email|max:255',
        'institution' => 'nullable|max:255',
        'subject' => 'required|min:3|max:255',
        'message' => 'required|min:10|max:2000',
    ];

    public function submit()
    {
        $this->validate();

        // Send notification email
        Mail::raw(
            "Nuevo mensaje de contacto:\n\n" .
            "Nombre: {$this->name}\n" .
            "Email: {$this->email}\n" .
            "Institución: {$this->institution}\n" .
            "Asunto: {$this->subject}\n\n" .
            "Mensaje:\n{$this->message}",
            function ($mail) {
                $mail->to(config('mail.from.address', 'admin@openscirank.com'))
                     ->subject("Contacto OpenSciRank: {$this->subject}")
                     ->replyTo($this->email, $this->name);
            }
        );

        $this->sent = true;
        $this->reset(['name', 'email', 'institution', 'subject', 'message']);
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
