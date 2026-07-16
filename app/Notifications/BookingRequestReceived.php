<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\BookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirms to the customer that we have their request.
 *
 * Careful with the wording: this is not a confirmed booking, and saying "your
 * booking is confirmed" when no seat is held would be a promise the system
 * cannot keep.
 */
final class BookingRequestReceived extends Notification
{
    use Queueable;

    public function __construct(
        private readonly BookingRequest $booking,
    ) {
        // Send in the locale the customer was browsing, not the server's.
        // `locale()` belongs to the Notification, not to MailMessage.
        $this->locale($booking->locale);
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tourName = $this->booking->tour?->translation?->name ?? $this->booking->tour?->code ?? '';

        return (new MailMessage)
            ->subject(__('website.booking.mail.subject', ['reference' => $this->booking->reference]))
            ->greeting(__('website.booking.mail.greeting', ['name' => $this->booking->customer_name]))
            ->line(__('website.booking.mail.intro', ['tour' => $tourName]))
            ->line(__('website.booking.mail.reference', ['reference' => $this->booking->reference]))
            ->line(__('website.booking.mail.date', ['date' => $this->booking->preferred_date->isoFormat('D MMMM YYYY')]))
            ->line(__('website.booking.mail.guests', ['count' => $this->booking->total_guests]))
            ->line(__('website.booking.mail.next_steps'))
            ->salutation(__('website.booking.mail.salutation'));
    }
}
