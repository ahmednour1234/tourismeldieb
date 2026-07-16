<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Actions\Public\SubmitBookingRequestAction;
use App\Exceptions\DomainActionException;
use App\Http\Requests\Public\BookingRequestForm;
use App\Models\BookingRequest;
use App\Models\Tour;
use App\Services\Public\PublicPageService;
use App\Services\Support\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class BookingController
{
    public function __construct(
        private readonly PublicPageService $pageService,
        private readonly SeoService $seoService,
    ) {}

    /**
     * The booking form. A tour may be preselected via ?tour=<slug> from a
     * "Book Now" button, or chosen here when the visitor arrived from the
     * header.
     */
    public function create(Request $request, string $locale): View
    {
        $tours = $this->pageService->tours();
        $published = collect($tours)->where('status', 'published')->values();

        $selected = $published->firstWhere('slug', (string) $request->query('tour'));

        return view('public.booking.create', [
            'seo' => $this->seoService->page([
                'title' => __('website.booking.title'),
                'description' => __('website.booking.subtitle'),
                'robots' => 'noindex,follow',
            ]),
            'tours' => $published->all(),
            'selectedTourId' => $selected['id'] ?? null,
        ]);
    }

    public function store(BookingRequestForm $request, string $locale, SubmitBookingRequestAction $submit): RedirectResponse
    {
        try {
            $booking = $submit($request->validated());
        } catch (DomainActionException $exception) {
            return back()->withInput()->withErrors(['tour_id' => $exception->getMessage()]);
        }

        // The reference goes through the session rather than the URL: a booking
        // reference in a shareable link would let anyone read a stranger's name,
        // email and phone number.
        return redirect()
            ->route('booking.confirmed', ['locale' => $locale])
            ->with('booking_reference', $booking->reference);
    }

    public function confirmed(Request $request, string $locale): View
    {
        $reference = $request->session()->get('booking_reference');

        // Nothing to confirm without a fresh submission — a direct visit or a
        // refresh after the flash has gone belongs back on the form.
        if (! is_string($reference)) {
            return $this->create($request, $locale);
        }

        $booking = BookingRequest::query()
            ->with('tour.translation')
            ->where('reference', $reference)
            ->firstOrFail();

        return view('public.booking.confirmed', [
            'seo' => $this->seoService->page([
                'title' => __('website.booking.confirmed_title'),
                'robots' => 'noindex,nofollow',
            ]),
            'booking' => $booking,
        ]);
    }
}
