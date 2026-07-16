<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A tour had no image column, so its photo could only come from a hardcoded
 * match on the tour's code in PublicPageService. That meant an admin could not
 * change a tour's picture at all — and when the external host reassigned the
 * photo ID behind one of those URLs, a gym photo appeared on a Luxor temple
 * tour with no way to correct it from the admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table): void {
            $table->string('image_url')->nullable()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table): void {
            $table->dropColumn('image_url');
        });
    }
};
