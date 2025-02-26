<?php

namespace App\Livewire;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class DownloadCvCounter extends Component
{
    #[Reactive]
    public int $count;

    public function mount(int $count): void	
    {
        $this->count = $count;
    }

    public function render()
    {
        return view('livewire.download-cv-counter');
    }
}
