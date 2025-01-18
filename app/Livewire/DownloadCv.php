<?php

namespace App\Livewire;

use Facades\App\Services\ActivityLoggerService;
use Livewire\Attributes\On;
use Livewire\Component;

class DownloadCv extends Component
{
    public int $count;

    public function mount(): void	
    {
        $this->count = ActivityLoggerService::count('cv_download');
    }

    public function render()
    {
        return view('livewire.download-cv');
    }

    #[On('download-cv')]
    public function downloadCv(): void
    {
        ActivityLoggerService::log('cv_download');

        // Updates the count based on the database
        $this->count = ActivityLoggerService::count('cv_download');
    }
}
