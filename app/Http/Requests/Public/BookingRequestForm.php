<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use App\Support\DiallingCodes;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates a public booking request.
 *
 * Everything here is attacker-controlled: this form is open to the internet with
 * no login. Nothing is trusted, and nothing about the tour (its price, its
 * capacity) is read from the request — only the tour's id, which is then checked
 * against the catalogue.
 */
final class BookingRequestForm extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Must exist AND be published: a draft or archived tour is not on
            // sale, and its id is trivially guessable.
            'tour_id' => ['required', 'integer', 'exists:tours,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email:rfc', 'max:255'],
            // The code is validated against the offered list rather than
            // trusted: a <select> constrains a browser, not a client.
            'customer_phone_code' => ['nullable', 'string', 'max:8', Rule::in(array_keys(DiallingCodes::options()))],
            // Merged from the two posted halves in prepareForValidation, so
            // `max:32` is checked against what actually reaches the column.
            'customer_phone' => ['nullable', 'string', 'max:32', 'regex:/^[\d+\- ]+$/'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today', 'before:'.now()->addYears(2)->toDateString()],
            'adults' => ['required', 'integer', 'min:1', 'max:40'],
            'children' => ['nullable', 'integer', 'min:0', 'max:40'],
            'infants' => ['nullable', 'integer', 'min:0', 'max:20'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'children' => $this->input('children') ?: 0,
            'infants' => $this->input('infants') ?: 0,
            // The form posts the dialling code and the local number as two
            // controls, but the column stores one dialable string. Joining
            // here — before the rules run — means `max:32` is checked against
            // the value that actually reaches the database, and every consumer
            // downstream keeps seeing the single field it already expects.
            'customer_phone' => PhoneNumber::join(
                is_string($code = $this->input('customer_phone_code')) ? $code : null,
                is_string($local = $this->input('customer_phone')) ? $local : null,
            ),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $total = (int) $this->input('adults') + (int) $this->input('children') + (int) $this->input('infants');

            // Mirrors the database CHECK constraint. Without it the insert
            // would throw a raw QueryException at the customer.
            if ($total > 60) {
                $validator->errors()->add('adults', __('website.booking.too_many_guests'));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name' => __('website.forms.name'),
            'customer_email' => __('website.forms.email'),
            'customer_phone' => __('website.forms.phone'),
            'customer_phone_code' => __('website.forms.dialling_code'),
            'preferred_date' => __('website.booking.preferred_date'),
            'adults' => __('website.booking.adults'),
            'children' => __('website.booking.children'),
            'infants' => __('website.booking.infants'),
            'notes' => __('website.booking.notes'),
        ];
    }
}
