<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tours;

use App\Services\Support\UiSettingsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class TourWizard extends Component
{
    public int $step = 1;

    /**
     * @var array<string, mixed>
     */
    public array $form = [
        'code' => '',
        'status' => 'draft',
        'translations' => [],
        'highlights' => [''],
        'itinerary' => [['title' => '', 'description' => '']],
        'included' => [''],
        'excluded' => [''],
        'faqs' => [['question' => '', 'answer' => '']],
        'media' => [],
        'language_ids' => [],
        'related_tours' => [],
        'seo_title' => '',
    ];

    public function next(): void
    {
        $this->validateStep();
        $this->step = min(12, $this->step + 1);
    }

    public function previous(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function addHighlight(): void
    {
        $this->form['highlights'][] = '';
    }

    public function addFaq(): void
    {
        $this->form['faqs'][] = ['question' => '', 'answer' => ''];
    }

    public function saveDraft(): void
    {
        $this->validate(['form.code' => ['nullable', 'string', 'max:50']]);
        session()->flash('success', __('admin.tours.draft_saved'));
    }

    public function publish(): void
    {
        $this->validate([
            'form.code' => ['required', 'string', 'max:50'],
            'form.translations.en.name' => ['required', 'string', 'max:160'],
            'form.translations.en.slug' => ['required', 'string', 'max:180'],
            'form.translations.en.description' => ['required', 'string', 'max:5000'],
        ]);

        session()->flash('success', __('admin.tours.publish_placeholder'));
    }

    public function render(UiSettingsService $settingsService): View
    {
        return view('livewire.admin.tours.tour-wizard', [
            'languages' => $settingsService->activeLanguages(),
            'steps' => $this->steps(),
        ]);
    }

    private function validateStep(): void
    {
        if ($this->step === 1) {
            $this->validate(['form.code' => ['nullable', 'string', 'max:50']]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function steps(): array
    {
        return [
            1 => __('admin.tours.steps.basic'),
            2 => __('admin.tours.steps.translations'),
            3 => __('admin.tours.steps.details'),
            4 => __('admin.tours.steps.highlights'),
            5 => __('admin.tours.steps.itinerary'),
            6 => __('admin.tours.steps.includes'),
            7 => __('admin.tours.steps.requirements'),
            8 => __('admin.tours.steps.faqs'),
            9 => __('admin.tours.steps.media'),
            10 => __('admin.tours.steps.languages'),
            11 => __('admin.tours.steps.related'),
            12 => __('admin.tours.steps.seo'),
        ];
    }
}
