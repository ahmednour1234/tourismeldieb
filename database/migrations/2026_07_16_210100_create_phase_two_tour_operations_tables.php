<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->restrictOnDelete();
            $table->string('code');
            $table->unsignedInteger('capacity');
            $table->unsignedSmallInteger('minimum_guests')->default(1);
            $table->unsignedSmallInteger('maximum_guests');
            $table->unsignedSmallInteger('minimum_booking_quantity')->default(1);
            $table->unsignedSmallInteger('maximum_booking_quantity');
            $table->unsignedSmallInteger('duration_value')->nullable();
            $table->string('duration_unit', 20)->default('hour');
            $table->boolean('is_private')->default(false)->index();
            $table->boolean('requires_manual_confirmation')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tour_id', 'code']);
            $table->index(['tour_id', 'is_active']);
            $table->index(['tour_id', 'is_default']);
        });

        Schema::create('tour_option_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->foreignId('tour_option_id')->constrained('tour_options')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('slug');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();

            $table->unique(['tour_option_id', 'locale']);
            $table->unique(['tour_id', 'locale', 'slug']);
            $table->index(['locale', 'slug']);
        });

        Schema::create('tour_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_option_id')->constrained('tour_options')->restrictOnDelete();
            $table->string('day_of_week', 20);
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->unsignedInteger('capacity_override')->nullable();
            $table->time('pickup_start_time')->nullable();
            $table->unsignedSmallInteger('booking_cutoff_hours')->default(24);
            $table->unsignedSmallInteger('booking_opens_days_before')->default(90);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tour_option_id', 'day_of_week', 'is_active']);
            $table->index(['valid_from', 'valid_to']);
        });

        Schema::create('tour_departures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_option_id')->constrained('tour_options')->restrictOnDelete();
            $table->foreignId('tour_schedule_id')->nullable()->constrained('tour_schedules')->nullOnDelete();
            $table->date('departure_date')->index();
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime')->nullable();
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('reserved_capacity')->default(0);
            $table->unsignedInteger('confirmed_capacity')->default(0);
            $table->unsignedInteger('available_capacity')->default(0);
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('booking_opens_at')->nullable();
            $table->timestamp('booking_cutoff_at')->nullable()->index();
            $table->text('manual_notes')->nullable();
            $table->boolean('generated_automatically')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tour_option_id', 'start_datetime']);
            $table->index(['status', 'departure_date']);
            $table->index(['departure_date', 'status']);
        });

        Schema::create('tour_blackout_dates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->restrictOnDelete();
            $table->foreignId('tour_option_id')->nullable()->constrained('tour_options')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason')->nullable();
            $table->text('internal_notes')->nullable();
            $table->boolean('cancel_existing_departures')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tour_id', 'start_date', 'end_date']);
            $table->index(['tour_option_id', 'start_date', 'end_date']);
        });

        Schema::create('tour_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_option_id')->constrained('tour_options')->restrictOnDelete();
            $table->string('guest_type', 30);
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->unsignedBigInteger('amount_minor');
            $table->unsignedSmallInteger('minimum_quantity')->default(1);
            $table->unsignedSmallInteger('maximum_quantity')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tour_option_id', 'guest_type', 'currency_id']);
            $table->index(['valid_from', 'valid_to', 'is_active']);
        });

        Schema::create('pricing_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->restrictOnDelete();
            $table->foreignId('tour_option_id')->nullable()->constrained('tour_options')->cascadeOnDelete();
            $table->string('name');
            $table->string('rule_type', 30);
            $table->string('adjustment_type', 40);
            $table->integer('adjustment_value');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->restrictOnDelete();
            $table->integer('priority')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('days_of_week')->nullable();
            $table->unsignedSmallInteger('minimum_guests')->nullable();
            $table->unsignedSmallInteger('maximum_guests')->nullable();
            $table->unsignedSmallInteger('minimum_days_before')->nullable();
            $table->unsignedSmallInteger('maximum_days_before')->nullable();
            $table->string('applies_to_guest_type', 30)->nullable();
            $table->string('stacking_mode', 30)->default('stackable');
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tour_id', 'is_active']);
            $table->index(['tour_option_id', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('currency_rates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('base_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->foreignId('target_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('rate', 20, 10);
            $table->timestamp('effective_at');
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Explicit short names: the auto-generated names exceed MySQL's
            // 64-character identifier limit (PostgreSQL silently truncates at
            // 63, MySQL rejects them outright).
            $table->unique(['base_currency_id', 'target_currency_id', 'effective_at'], 'currency_rates_pair_effective_unique');
            $table->index(['base_currency_id', 'target_currency_id', 'is_active'], 'currency_rates_pair_active_index');
        });

        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('discount_type', 30);
            $table->integer('discount_value');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->restrictOnDelete();
            $table->unsignedBigInteger('maximum_discount_minor')->nullable();
            $table->unsignedBigInteger('minimum_order_minor')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_customer')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'starts_at', 'expires_at']);
        });

        Schema::create('coupon_tour', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['coupon_id', 'tour_id']);
        });

        Schema::create('coupon_tour_option', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('tour_option_id')->constrained('tour_options')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['coupon_id', 'tour_option_id']);
        });

        $this->createConditionalUniqueIndexes();
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_tour_option');
        Schema::dropIfExists('coupon_tour');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('currency_rates');
        Schema::dropIfExists('pricing_rules');
        Schema::dropIfExists('tour_prices');
        Schema::dropIfExists('tour_blackout_dates');
        Schema::dropIfExists('tour_departures');
        Schema::dropIfExists('tour_schedules');
        Schema::dropIfExists('tour_option_translations');
        Schema::dropIfExists('tour_options');
    }

    private function createConditionalUniqueIndexes(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX tour_options_one_default_per_tour ON tour_options (tour_id) WHERE is_default = true AND deleted_at IS NULL');

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX tour_options_one_default_per_tour ON tour_options (tour_id) WHERE is_default = 1 AND deleted_at IS NULL');
        }
    }
};
