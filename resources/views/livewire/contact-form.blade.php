<div>

    {{--
        TODO: Build the form
        [x] Export this to a Livewire component.
        [x] Add Validation (Success message, Error message).
        [x] Save the event in the activity log.
        [x] Send an email to the user confirming the message was sent.
        [x] Send an email to me about the contact.
        [] Send an telegram message to me about the contact.
        --}}
    <h2 class="text-4xl font-semibold tracking-tight text-white">Contact Form</h2>
    <form wire:submit="submit" class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
        <div class="sm:col-span-3">
            <label for="firstName" class="block text-sm/6 font-medium text-gray-400">First name</label>
            <div class="mt-2">
                <input wire:model="firstName" type="text" id="firstName" autocomplete="given-name"
                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                @error('firstName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="sm:col-span-3">
            <label for="lastName" class="block text-sm/6 font-medium text-gray-400">Last name</label>
            <div class="mt-2">
                <input wire:model="lastName" type="text" id="lastName" autocomplete="family-name"
                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                @error('lastName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="sm:col-span-4">
            <label for="email" class="block text-sm/6 font-medium text-gray-400">Email address</label>
            <div class="mt-2">
                <input wire:model="email" type="email" id="email" autocomplete="email"
                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="sm:col-span-2">
            <label for="phone" class="block text-sm/6 font-medium text-gray-400">Phone Number</label>
            <div class="mt-2">
                <input wire:model="phone" type="tel" id="phone" autocomplete="tel"
                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="col-span-full">
            <label for="message" class="block text-sm/6 font-medium text-gray-400">Message</label>
            <div class="mt-2">
                <textarea wire:model="message" id="message" rows="2"
                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
                @error('message') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="col-span-full">
            <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Submit
            </button>
        </div>

        @if (session()->has('success'))
            <div class="col-span-full">
                <div class="rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </form>
</div>
