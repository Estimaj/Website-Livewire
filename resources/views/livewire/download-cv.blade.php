<div>
    <div class="max-w-2xl lg:text-center">
        <h2 class="heading_container mt-2 text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl lg:text-balance">
        CV
        </h2>
        <livewire:download-cv-counter :count="$count" />
    </div>
    <div class="mt-6 w-40">
        <div class="border border-gray-600 rounded-md w-full p-2 cursor-pointer bg-gray-800">
            <livewire:download-cv-button />
        </div>
    </div>
</div>