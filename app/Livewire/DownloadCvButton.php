<?php

namespace App\Livewire;

use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadCvButton extends Component
{
    /** 
     * Emit the downloadCv event
     */
    public function downloadCv(): BinaryFileResponse
    {
        // Send an event for the cv download
        $this->dispatch('download-cv');

        // returns file to download
        return response()->download(
            public_path('website/cv.pdf'),
            'cv_software_engineer_joao_estima.pdf'
        );
    }

    public function render()
    {
        return view('livewire.download-cv-button');
    }
}
