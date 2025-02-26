@props(['name', 'logo', 'overlayText'])

<div x-data="{ showOverlay: false }" 
    @click="showOverlay = !showOverlay"
    class="relative border border-gray-600 flex h-full items-center rounded-md w-full p-2 cursor-pointer bg-gray-800 hover:bg-gray-700 transition-colors">
    <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1"
        src="{{ $logo }}" alt="{{ $name }}" title="{{ $name }}" width="158" height="48">
    <div x-show="showOverlay" 
        x-transition
        class="absolute inset-0 flex items-center justify-center bg-gray-800 bg-opacity-90 rounded-md">
        <span class="text-white font-semibold">{{ $overlayText }}</span>
    </div>
</div>