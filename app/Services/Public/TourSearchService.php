<?php

declare(strict_types=1);

namespace App\Services\Public;

use App\Shared\DTOs\TourFiltersData;

final class TourSearchService
{
    public function __construct(
        private readonly PublicPageService $pageService,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function search(TourFiltersData $filters): array
    {
        $tours = collect($this->pageService->tours())
            ->where('status', 'published')
            ->when($filters->search, fn ($items, string $search) => $items->filter(fn (array $tour): bool => str_contains(mb_strtolower($tour['name'].' '.$tour['short_description']), mb_strtolower($search))))
            ->when($filters->destination, fn ($items, string $destination) => $items->where('destination_slug', $destination))
            ->when($filters->category, fn ($items, string $category) => $items->filter(fn (array $tour): bool => str($tour['category'])->slug()->toString() === $category))
            ->when($filters->duration, fn ($items, string $duration) => $items->filter(fn (array $tour): bool => str_contains($tour['duration'], $duration)))
            ->when($filters->language, fn ($items, string $language) => $items->filter(fn (array $tour): bool => in_array($language, $tour['languages'], true)))
            ->when($filters->featured !== null, fn ($items) => $items->where('featured', $filters->featured))
            ->when($filters->bestSeller !== null, fn ($items) => $items->where('best_seller', $filters->bestSeller))
            ->when($filters->tourType, fn ($items, string $tourType) => $items->where('type', $tourType));

        if ($filters->sort === 'name') {
            $tours = $tours->sortBy('name');
        }

        if ($filters->sort === 'duration') {
            $tours = $tours->sortBy('duration');
        }

        return $tours->values()->all();
    }
}
