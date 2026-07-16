<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Booking *requests*, not confirmed bookings.
 *
 * "Book Now" was a dead `type="button"`, and the only other route to the
 * business was a Contact form that also posted nowhere — so every enquiry a
 * visitor made was silently discarded.
 *
 * This is deliberately a request queue rather than a reservation system: no
 * seats are held and no money is taken. An operator reviews each request and
 * confirms it. Capacity reservation and payment belong to a later batch (see
 * batch-01.md), and pretending to hold a seat we have not actually reserved
 * would be worse than not holding one at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table): void {
            $table->id();

            // Human-facing reference, shown to the customer and quoted back to
            // support. Never the raw id.
            $table->string('reference', 16)->unique();

            $table->foreignId('tour_id')->constrained('tours')->restrictOnDelete();
            $table->foreignId('tour_option_id')->nullable()->constrained('tour_options')->nullOnDelete();

            // A logged-in customer, when there is one. A guest booking has none,
            // which is why the contact columns below are not derived from users.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();

            $table->date('preferred_date');
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->unsignedSmallInteger('infants')->default(0);

            $table->text('notes')->nullable();
            $table->string('locale', 10)->default('en');

            $table->string('status')->default('pending')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'preferred_date']);
            $table->index('customer_email');
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("alter table booking_requests add constraint booking_requests_status_check check (status in ('pending', 'confirmed', 'cancelled', 'completed'))");
            // A booking for nobody is meaningless; infants and children alone
            // cannot travel unaccompanied.
            DB::statement('alter table booking_requests add constraint booking_requests_adults_check check (adults >= 1)');
            DB::statement('alter table booking_requests add constraint booking_requests_guests_check check (adults + children + infants <= 60)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
