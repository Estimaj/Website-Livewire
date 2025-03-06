<div x-data="{ clickableTip: true, selectedImage: 0 }" x-show="isAdminPlatformModalActive" class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">

  <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>

  <div class="fixed inset-0 z-10 w-screen overflow-y-auto" x-on:click="isAdminPlatformModalActive = false">
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
      <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 w-full lg:max-w-6xl" x-on:click.stop="">
        <div x-show="clickableTip" class="absolute pt-6 text-center text-sm text-white w-full z-50">click for next image</div>
        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4" x-on:click="selectedImage = selectedImage > 3 ? 0 : selectedImage + 1; clickableTip = false">
            <!-- Water mark to indicate that the image is clickable -->
            <img x-show="selectedImage == 0" src="website/img/admin-platform-login.png" alt="Admin Platform - Login" class="h-full w-full object-cover">
            <img x-show="selectedImage == 1" src="website/img/admin-platform-dashboard.png" alt="Admin Platform - Dashboard" class="h-full w-full object-cover">
            <img x-show="selectedImage == 2" src="website/img/admin-platform-user-list.png" alt="Admin Platform - User List" class="h-full w-full object-cover">
            <img x-show="selectedImage == 3" src="website/img/admin-platform-user-create.png" alt="Admin Platform - User Create" class="h-full w-full object-cover">
            <img x-show="selectedImage == 4" src="website/img/admin-platform-tokens.png" alt="Admin Platform - Token" class="h-full w-full object-cover">
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
          <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto" x-on:click="selectedImage = selectedImage > 3 ? 0 : selectedImage + 1">Next</button>
          <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto" x-on:click="isAdminPlatformModalActive = false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>
