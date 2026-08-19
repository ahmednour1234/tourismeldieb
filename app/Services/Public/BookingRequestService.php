<?php

declare(strict_types=1);

namespace App\Services\Public;

use App\Exceptions\DomainActionException;
use App\Models\BookingRequest;
use App\Models\Tour;
use App\Shared\Contracts\SettingRepositoryContract;

/**
 * Business rules for a public booking request.
 */
final class BookingRequestService
{
    private const LOG_NAME = 'bookings';

    public function __construct(
        private readonly SettingRepositoryContract $settings,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): BookingRequest
    {
        $tour = $this->publishedTourOrFail((int) $data['tour_id']);

        $booking = BookingRequest::query()->create([
            'reference' => BookingRequest::generateReference(),
            'tour_id' => $tour->id,
            // Attach the account when the visitor is signed in, so it appears in
            // their bookings list. A guest booking simply has no user.
            'user_id' => auth()->id(),
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'preferred_date' => $data['preferred_date'],
            'adults' => $data['adults'],
            'children' => $data['children'] ?? 0,
            'infants' => $data['infants'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'locale' => app()->getLocale(),
            'status' => 'pending',
        ]);

        activity(self::LOG_NAME)
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->event('created')
            ->withProperties(['reference' => $booking->reference, 'tour' => $tour->code])
            ->log('booking.requested');

        return $booking;
    }

    /**
     * Where new-booking alerts are sent.
     *
     * Read from the `contact_email` setting rather than hardcoded, so the
     * operator can redirect their own alerts from the admin without a deploy —
     * the address changes when staff do, and a constant in the source is the
     * one place they cannot reach.
     *
     * Falls back to the configured MAIL_FROM_ADDRESS so a misconfigured
     * setting means the alert goes somewhere reachable rather than nowhere.
     */
    public function operatorEmail(): ?string
    {
        $candidates = [
            $this->settings->get('contact_email'),
            config('mail.from.address'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && filter_var($candidate, FILTER_VALIDATE_EMAIL) !== false) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * A tour id from a public form proves nothing: it must resolve to a tour
     * that is actually on sale. Ids are sequential and trivially guessable, so
     * a draft or archived tour must not be bookable.
     */
    private function publishedTourOrFail(int $tourId): Tour
    {
        $tour = Tour::query()->published()->find($tourId);

        if ($tour === null) {
            throw new DomainActionException(__('website.booking.tour_unavailable'));
        }

        return $tour;
    }
}
