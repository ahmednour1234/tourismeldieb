<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Blog posts and testimonials — the last two pieces of public content that were
 * hardcoded PHP arrays, because they had no tables to read from.
 *
 * Both follow the catalogue's conventions: a base row carrying the structural
 * columns, a sibling *_translations table carrying the per-locale copy, unique
 * (locale, slug) for URLs, and soft deletes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('image_url')->nullable();
            $table->string('status')->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blog_post_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->unique(['blog_post_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('author_name');
            $table->string('author_country', 2)->nullable();
            $table->string('avatar_url')->nullable();
            // A testimonial may praise a specific tour, or the company at large.
            $table->foreignId('tour_id')->nullable()->constrained('tours')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->date('reviewed_on')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('testimonial_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('testimonial_id')->constrained('testimonials')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->text('quote');
            $table->timestamps();

            $table->unique(['testimonial_id', 'locale']);
        });

        // A 1-5 star rating or nothing at all. Enforced in the database as well
        // as the form: the catalogue is edited by more than one code path.
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('alter table testimonials add constraint testimonials_rating_check check (rating is null or (rating between 1 and 5))');
            DB::statement("alter table blog_posts add constraint blog_posts_status_check check (status in ('draft', 'published', 'archived'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonial_translations');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('blog_post_translations');
        Schema::dropIfExists('blog_posts');
    }
};
