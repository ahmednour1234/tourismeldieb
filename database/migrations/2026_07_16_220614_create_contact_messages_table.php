<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Messages sent from the public contact form.
 *
 * The form had no method, no action, no CSRF token, and a `type="button"`
 * submit — so every message a visitor ever sent went nowhere at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->text('message');
            $table->string('locale', 10)->default('en');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Kept for abuse triage: an open form on a public site attracts
            // spam, and "which address sent these 400 messages" is the first
            // question when it does.
            $table->string('ip_address', 45)->nullable();

            $table->string('status')->default('new')->index();
            $table->timestamp('read_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("alter table contact_messages add constraint contact_messages_status_check check (status in ('new', 'read', 'replied', 'spam'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
