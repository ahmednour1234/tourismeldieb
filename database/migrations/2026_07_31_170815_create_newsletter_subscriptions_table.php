<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Newsletter sign-ups. The Subscribe button — in the footer and on the home
 * page — was a `type="button"` wired to nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscriptions', function (Blueprint $table): void {
            // One row per address; re-subscribing is idempotent, not a
            // duplicate. Case-insensitive dedupe is handled by lowercasing the
            // email before it reaches here.
            $table->id();
            $table->string('email')->unique();
            $table->string('locale', 10)->default('en');
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscriptions');
    }
};
