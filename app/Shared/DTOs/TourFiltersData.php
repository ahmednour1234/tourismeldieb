<?php

declare(strict_types=1);

namespace App\Shared\DTOs;

final readonly class TourFiltersData implements DataTransferObject
{
    public function __construct(
        public ?string $search,
        public ?string $destination,
        public ?string $category,
        public ?string $duration,
        public ?string $language,
        public ?bool $featured,
        public ?bool $bestSeller,
        public ?string $tourType,
        public string $sort,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            search: filled($data['search'] ?? null) ? trim((string) $data['search']) : null,
            destination: filled($data['destination'] ?? null) ? (string) $data['destination'] : null,
            category: filled($data['category'] ?? null) ? (string) $data['category'] : null,
            duration: filled($data['duration'] ?? null) ? (string) $data['duration'] : null,
            language: filled($data['language'] ?? null) ? (string) $data['language'] : null,
            featured: ($data['featured'] ?? null) !== null ? filter_var($data['featured'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) : null,
            bestSeller: ($data['best_seller'] ?? null) !== null ? filter_var($data['best_seller'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) : null,
            tourType: filled($data['tour_type'] ?? null) ? (string) $data['tour_type'] : null,
            sort: in_array($data['sort'] ?? null, ['recommended', 'name', 'duration'], true) ? (string) $data['sort'] : 'recommended',
        );
    }

    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'destination' => $this->destination,
            'category' => $this->category,
            'duration' => $this->duration,
            'language' => $this->language,
            'featured' => $this->featured,
            'best_seller' => $this->bestSeller,
            'tour_type' => $this->tourType,
            'sort' => $this->sort,
        ];
    }
}
