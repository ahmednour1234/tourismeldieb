<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Media;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

final class MediaManager extends Component
{
    use WithFileUploads;

    /**
     * @var list<mixed>
     */
    public array $uploads = [];

    public function remove(int $index): void
    {
        unset($this->uploads[$index]);
        $this->uploads = array_values($this->uploads);
    }

    public function render(): View
    {
        return view('livewire.admin.media.media-manager');
    }
}
