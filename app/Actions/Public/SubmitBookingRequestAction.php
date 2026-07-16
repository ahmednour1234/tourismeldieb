<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Models\BookingRequest;
use App\Notifications\BookingRequestReceived;
use App\Services\Public\BookingRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Records a booking request and confirms it to the customer.
 */
final class SubmitBookingRequestAction
{
    public function __construct(
        private readonly BookingRequestService $service,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data): BookingRequest
    {
        $booking = DB::transaction(fn (): BookingRequest => $this->service->create($data));

        // Sent after the transaction commits, not inside it: a mail failure
        // must not roll back a request the customer has already been told we
        // received, and there is nothing to email about until the row exists.
        Notification::route('mail', $booking->customer_email)
            ->notify(new BookingRequestReceived($booking));

        return $booking;
    }
}
