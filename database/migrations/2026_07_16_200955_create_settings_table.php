<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Key/value rather than a column per setting: settings are heterogeneous
     * and arrive in batches, so a column-based table needs a migration every
     * time someone wants a new field.
     *
     * `value` is JSON so a setting can hold a scalar, a per-locale map
     * (company description, address), or a nested structure (social links)
     * without a second table.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group', 64)->default('general');
            $table->string('key', 128);
            $table->json('value')->nullable();
            $table->boolean('is_translatable')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // One row per key. The group is metadata for display order, not
            // part of a setting's identity — otherwise the same key could exist
            // twice under different groups and reads would be ambiguous.
            $table->unique('key');
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
