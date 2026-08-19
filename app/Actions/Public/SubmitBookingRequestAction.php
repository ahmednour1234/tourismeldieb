<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Models\BookingRequest;
use App\Notifications\BookingRequestReceived;
use App\Notifications\BookingRequestSubmitted;
use App\Services\Public\BookingRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

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

        $this->notifyOperator($booking);

        return $booking;
    }

    /**
     * Tell the operator a request has arrived.
     *
     * Wrapped in its own try/catch rather than left to bubble: the booking is
     * already committed and the customer already has their confirmation, so a
     * failure here is an internal alerting problem, not something to show a
     * visitor as a failed booking. It is logged so the silence is noticed.
     */
    private function notifyOperator(BookingRequest $booking): void
    {
        $recipient = $this->service->operatorEmail();

        if ($recipient === null) {
            return;
        }

        try {
            Notification::route('mail', $recipient)
                ->notify(new BookingRequestSubmitted($booking));
        } catch (Throwable $exception) {
            Log::error('Booking request notification to the operator failed.', [
                'reference' => $booking->reference,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
