<?php

namespace App\Livewire;

use App\Enums\ActivityType;
use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Facades\App\Services\ActivityLoggerService;
use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
class ContactForm extends Component
{
    public $firstName = '';
    public $lastName = '';
    public $email = '';
    public $phone = '';
    public $message = '';

    protected array $rules = [
        'firstName' => ['required', 'min:2'],
        'lastName' => ['required', 'min:2'],
        'email' => ['required', 'email'],
        'phone' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
        'message' => ['required', 'min:10'],
    ];

    public function submit()
    {
        $validated = $this->validate();

        ActivityLoggerService::withProperties($validated)
            ->log(ActivityType::CONTACT_FORM_SUBMISSION);

        Mail::to(config('mail.from.address'))->queue(new ContactFormSubmitted($validated));
        Mail::to($validated['email'])->queue(new ContactFormConfirmation(
            name: "{$validated['firstName']} {$validated['lastName']}",
            note: $validated['message']
        ));

        // Here you would add your Telegram notification logic
        // Notification::route('telegram', 'TELEGRAM_CHAT_ID')
        //     ->notify(new ContactFormNotification($validated));

        $this->reset();
        session()->flash('success', 'Thank you for your message. We\'ll get back to you soon!');
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
