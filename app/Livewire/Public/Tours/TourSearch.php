<?php

declare(strict_types=1);

namespace App\Livewire\Public\Tours;

use App\Services\Public\PublicPageService;
use App\Services\Public\TourSearchService;
use App\Shared\DTOs\TourFiltersData;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

final class TourSearch extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $destination = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $duration = '';

    #[Url]
    public string $language = '';

    #[Url]
    public bool $featured = false;

    #[Url(as: 'best_seller')]
    public bool $bestSeller = false;

    #[Url(as: 'tour_type')]
    public string $tourType = '';

    #[Url]
    public string $sort = 'recommended';

    public function mount(?string $destinationSlug = null): void
    {
        if ($destinationSlug !== null) {
            $this->destination = $destinationSlug;
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'destination', 'category', 'duration', 'language', 'featured', 'bestSeller', 'tourType']);
        $this->sort = 'recommended';
    }

    public function render(TourSearchService $searchService, PublicPageService $pageService): View
    {
        $filters = TourFiltersData::fromArray([
            'search' => $this->search,
            'destination' => $this->destination,
            'category' => $this->category,
            'duration' => $this->duration,
            'language' => $this->language,
            'featured' => $this->featured ?: null,
            'best_seller' => $this->bestSeller ?: null,
            'tour_type' => $this->tourType,
            'sort' => $this->sort,
        ]);

        return view('livewire.public.tours.tour-search', [
            'tours' => $searchService->search($filters),
            'destinations' => $pageService->destinations(),
            'categories' => $pageService->categories(),
        ]);
    }
}
