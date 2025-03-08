<div x-data="{ show: !localStorage.getItem('cookie-consent') }"
    x-show="true || show"
    class="fixed bottom-0 inset-x-0 pb-2 sm:pb-5 z-50">
    <div class="max-w-screen-xl mx-auto px-2 sm:px-6 lg:px-8">
        <div class="p-2 rounded-lg bg-gray-900 shadow-lg sm:p-3">
            <div class="flex items-center justify-between flex-wrap">
                <div class="flex-1 flex items-center">
                    <p class="text-white">
                        We use cookies to improve your experience. See our 
                        <a href="{{ route('policy.show') }}" class="underline">Privacy Policy</a>.
                    </p>
                </div>
                <div class="mt-2 flex-shrink-0 w-full sm:mt-0 sm:w-auto">
                    <button @click="localStorage.setItem('cookie-consent', '1'); show = false"
                            class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md text-sm leading-5 font-medium text-indigo-600 bg-white hover:text-indigo-500 focus:outline-none focus:shadow-outline transition ease-in-out duration-150">
                        Accept
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 