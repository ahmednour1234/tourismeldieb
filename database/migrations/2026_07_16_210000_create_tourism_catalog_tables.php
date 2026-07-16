<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('native_name');
            $table->string('direction', 3)->default('ltr');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->char('code', 3)->unique();
            $table->string('name');
            $table->string('symbol', 12);
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->char('code', 2)->unique();
            $table->string('name');
            $table->string('phone_code', 12)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('destinations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('timezone')->default('Africa/Cairo');
            $table->string('image_url')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('destination_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('destination_id')->constrained('destinations')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('slug');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();

            $table->unique(['destination_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('tour_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('tour_categories')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('image_url')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tour_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_category_id')->constrained('tour_categories')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tour_category_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('tours', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('destination_id')->constrained('destinations')->restrictOnDelete();
            $table->foreignId('tour_category_id')->nullable()->constrained('tour_categories')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('status')->default('draft')->index();
            $table->unsignedSmallInteger('duration_value')->nullable();
            $table->string('duration_unit', 20)->nullable();
            $table->string('tour_type', 20)->default('shared')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_best_seller')->default(false)->index();
            $table->boolean('is_last_minute')->default(false)->index();
            $table->unsignedTinyInteger('minimum_age')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['destination_id', 'status']);
            $table->index(['tour_category_id', 'status']);
        });

        Schema::create('tour_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('slug');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->json('highlights')->nullable();
            $table->json('itinerary')->nullable();
            $table->json('included')->nullable();
            $table->json('excluded')->nullable();
            $table->json('faqs')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->unique(['tour_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('tour_operating_languages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['tour_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_operating_languages');
        Schema::dropIfExists('tour_translations');
        Schema::dropIfExists('tours');
        Schema::dropIfExists('tour_category_translations');
        Schema::dropIfExists('tour_categories');
        Schema::dropIfExists('destination_translations');
        Schema::dropIfExists('destinations');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('languages');
    }
};
