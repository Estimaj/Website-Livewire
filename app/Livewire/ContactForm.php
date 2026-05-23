<?php

namespace App\Livewire;

use App\Enums\ActivityType;
use App\Mail\ContactFormConfirmation;
use App\Notifications\ContactFormSubmitted as ContactFormSubmittedNotification;
use Facades\App\Services\ActivityLoggerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ContactForm extends Component
{
    public $firstName = '';

    public $lastName = '';

    public $email = '';

    public $phone = '';

    public $message = '';

    protected array $rules = [
        'firstName' => ['required', 'min:2', 'max:100'],
        'lastName' => ['required', 'min:2', 'max:100'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:30'],
        'message' => ['required', 'min:10', 'max:2000'],
    ];

    public function submit(): void
    {
        $limit = $this->contactFormRateLimit();

        if (! RateLimiter::attempt(
            $limit->key,
            $limit->maxAttempts,
            fn () => $this->processValidatedSubmission(),
            $limit->decaySeconds,
        )) {
            $this->throwThrottleValidationException($limit->key);
        }

        $this->reset();
        session()->flash('success', 'Thank you for your message. We\'ll get back to you soon!');
    }

    protected function processValidatedSubmission(): void
    {
        $validated = $this->validate();

        ActivityLoggerService::withProperties($validated)
            ->log(ActivityType::CONTACT_FORM_SUBMISSION);

        Notification::route('telegram', config('services.telegram-bot-api.chat_id'))
            ->route('mail', config('mail.from.address'))
            ->notify(new ContactFormSubmittedNotification($validated));

        Mail::to($validated['email'])->queue(new ContactFormConfirmation(
            name: "{$validated['firstName']} {$validated['lastName']}",
            note: $validated['message']
        ));
    }

    /**
     * Resolve the contact-form limiter the same way throttle middleware does.
     *
     * @return object{key: string, maxAttempts: int, decaySeconds: int}
     */
    protected function contactFormRateLimit(): object
    {
        $limiterName = 'contact-form';
        $limit = Collection::wrap(RateLimiter::limiter($limiterName)(request()))->first();

        return (object) [
            'key' => md5($limiterName.$limit->key),
            'maxAttempts' => $limit->maxAttempts,
            'decaySeconds' => $limit->decaySeconds,
        ];
    }

    protected function throwThrottleValidationException(string $key): never
    {
        $seconds = RateLimiter::availableIn($key);
        $minutes = max(1, (int) ceil($seconds / 60));

        throw ValidationException::withMessages([
            'email' => ["Too many contact attempts. Please try again in {$minutes} minute(s)."],
        ]);
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
