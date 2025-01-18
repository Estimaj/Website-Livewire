<div class="py-24 sm:py-32">
  <div class="mx-auto max-w-2xl px-6 lg:max-w-7xl lg:px-8">
    <p class="mx-auto mt-2 max-w-lg text-balance text-center text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl">Projects</p>
    <div class="mt-10 grid gap-4 sm:mt-16 lg:grid-cols-3 lg:grid-rows-2">
      <div class="relative lg:row-span-2">
        <div class="absolute inset-px rounded-lg bg-white"></div>
        <div class="relative flex h-full flex-col px-8 sm:px-10 overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)]">
          <div class="pb-3 pt-8 sm:pb-0 sm:pt-10">
            <p class="mt-2 text-lg font-medium tracking-tight text-gray-950 max-lg:text-center">APIs & Integrations</p>
            <p class="mt-2 max-w-lg text-sm/6 text-gray-600 max-lg:text-center">I’ve integrated various APIs into different systems, including ERPs, carrier APIs, payment gateways, social media platforms, and more. Here are a few of the more popular ones:</p>
          </div>
          <div class="relative my-8 w-full grid gap-4 lg:grid-cols-2 grid-cols-1 justify-center">
            <template x-for="integration in ['Stripe','Twitter','Telegram','DHL','DDMS ERP','TIMS ERP','Horizon ERP','D365 Flintech','Business Central','Custom APIs','Website Scraping']">
              <p class="text-center text-sm/6 text-gray-600 border rounded-lg py-1" x-text="integration"></p>
            </template>
          </div>
        </div>
        <div class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5"></div>
      </div>
      <div class="relative lg:col-span-2">
        <div class="absolute inset-px rounded-lg bg-white"></div>
        <div class="relative h-full rounded-[calc(theme(borderRadius.lg)+1px)] overflow-y-hidden">
          <a href="https://raizarte.com" target="_blank" class="flex flex-col h-full overflow-hidden">
            <div class="pb-3 pt-8 sm:px-10 sm:pt-10">
              <p class="mt-2 text-lg font-medium tracking-tight text-gray-950 max-lg:text-center flex items-center gap-1 justify-between">
                Raizarte 
                <x-icons.arrow-top-right-on-square class="size-5" />
              </p>
              <p class="mt-2 max-w-lg text-sm/6 text-gray-600 max-lg:text-center">I built a custom website for a Portugues company, this project helped me learn VueJs and improved my overall skills.</p>
            </div>
            <div class="relative sm:min-h-[10rem] w-full grow [container-type:inline-size]">
              <img src="website/img/raizarte-website-zoom.png" alt="Raizarte Website" class="absolute h-full top-10 object-cover shadow-2xl top-10">
            </div>
          </a>
        </div>
        <div class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5"></div>
      </div>
      <div class="relative max-lg:row-start-3 lg:col-start-2 lg:row-start-2">
        <div class="absolute inset-px rounded-lg bg-white"></div>
        <div class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)]">
          <div class="p-8 sm:px-10 sm:pt-10">
            <p class="mt-2 text-lg font-medium tracking-tight text-gray-950 max-lg:text-center">Amazon Web Services</p>
            <p class="mt-2 max-w-lg text-sm/6 text-gray-600 max-lg:text-center">I have been working with and learning about AWS infrastructure in my projects, as well as in my day-to-day job. I have come to appreciate the power of microservices.</p>
          </div>
        </div>
        <div class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5"></div>
      </div>
      <div class="relative max-lg:row-start-4 lg:col-start-3 lg:row-start-2">
        <div class="absolute inset-px rounded-lg bg-white"></div>
        <div class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)]">
          <div class="px-8 pt-8 sm:px-10 sm:pt-10">
            <p class="mt-2 text-lg font-medium tracking-tight text-gray-950 max-lg:text-center">
              <a href="https://old.joaoestima.com" target="_blank" class="flex items-center gap-1 justify-between">
                Old Website
                <x-icons.arrow-top-right-on-square class="size-5" />
              </a>
            </p>
            <p class="mt-2 max-w-lg text-sm/6 text-gray-600 max-lg:text-center">I built this website to learn the stack Inertia & VueJS. Currently deprecated.</p>
          </div>
          <div class="flex flex-1 items-center [container-type:inline-size] max-lg:py-6 lg:pb-2">
            <img class="mx-auto w-1/2" src="website/img/Laravel_Vue_Inertia.png" alt="Stack">
          </div>
        </div>
        <div class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5"></div>
      </div>
      <div class="relative lg:col-span-full" x-on:click="isAdminPlatformModalActive = true">
        <div class="absolute inset-px rounded-lg bg-white"></div>
        <div class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)] cursor-pointer">
          <div class="px-8 pt-8 sm:px-10 sm:pt-10">
            <p class="mt-2 text-lg font-medium tracking-tight text-gray-950 max-lg:text-center">
              <p class="flex items-center gap-1 justify-between">
                PIM (Personal Information Manager)
                <x-icons.arrows-pointing-out class="size-5" />
              </a>
            </p>
            <p class="mt-2 max-w-lg text-sm/6 text-gray-600 max-lg:text-center">This platform is an all-in-one Personal Information Management (PIM) system that helps streamline daily tasks.</p>
          </div>
          <div class="relative sm:min-h-[10rem] w-full grow [container-type:inline-size]">
            <img src="website/img/admin-platform-login.png" alt="Admin Platform" class="absolute h-full w-full top-10 object-cover shadow-2xl top-10">
          </div>
        </div>
        <div class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5"></div>
      </div>
      {{-- <div class="relative opacity-50">
        <div class="absolute inset-px rounded-lg bg-white"></div>
        <div class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)]">
          <div class="px-8 pt-8 sm:px-10 sm:pt-10">
            <p class="mt-2 text-lg font-medium tracking-tight text-gray-950 text-center">
              Coming Soon...
            </p>
          </div>
          <div class="flex flex-1 items-center [container-type:inline-size] max-lg:py-6 lg:pb-2 opacity-30">
            <img class="mx-auto w-1/4" src="website/img/loading-bar.png" alt="loading img">
          </div>
        </div>
        <div class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5"></div>
      </div> --}}
    </div>
  </div>
</div>

@include('components.website.projects.admin-platform')
