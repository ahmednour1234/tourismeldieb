<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\BookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the operator a new request has come in.
 *
 * The customer's own confirmation is BookingRequestReceived. This is the other
 * half: until it existed, a request landed in the database and nobody was told,
 * so it was only found by someone happening to open the admin.
 *
 * Written for staff, not for the customer — it carries the contact details and
 * the guest breakdown needed to act on the request, and it is always in the
 * operator's own language rather than the visitor's, since the person reading
 * it is the same person every time.
 */
final class BookingRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(
        private readonly BookingRequest $booking,
    ) {}

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

        $message = (new MailMessage)
            ->subject(__('website.booking.staff_mail.subject', [
                'reference' => $this->booking->reference,
                'tour' => $tourName,
            ]))
            ->greeting(__('website.booking.staff_mail.greeting'))
            ->line(__('website.booking.staff_mail.intro'))
            ->line(__('website.booking.staff_mail.tour', ['tour' => $tourName]))
            ->line(__('website.booking.staff_mail.reference', ['reference' => $this->booking->reference]))
            ->line(__('website.booking.staff_mail.date', [
                'date' => $this->booking->preferred_date->isoFormat('D MMMM YYYY'),
            ]))
            ->line(__('website.booking.staff_mail.guests', [
                'adults' => $this->booking->adults,
                'children' => $this->booking->children,
                'infants' => $this->booking->infants,
            ]))
            ->line(__('website.booking.staff_mail.customer', ['name' => $this->booking->customer_name]))
            ->line(__('website.booking.staff_mail.email', ['email' => $this->booking->customer_email]));

        // Both are optional on the form, so neither line is rendered unless the
        // customer actually gave one — an email reading "Phone: " is noise.
        if ($this->booking->customer_phone !== null && $this->booking->customer_phone !== '') {
            $message->line(__('website.booking.staff_mail.phone', ['phone' => $this->booking->customer_phone]));
        }

        if ($this->booking->notes !== null && $this->booking->notes !== '') {
            $message->line(__('website.booking.staff_mail.notes', ['notes' => $this->booking->notes]));
        }

        // Deep-links to the row rather than the list: staff open this on a
        // phone and should not have to hunt for the reference.
        $message->action(
            __('website.booking.staff_mail.action'),
            route('admin.bookings.edit', $this->booking->getKey()),
        );

        // Replying to the notification should reach the customer, which is what
        // anyone reading it will instinctively try to do.
        return $message->replyTo($this->booking->customer_email, $this->booking->customer_name);
    }
}
