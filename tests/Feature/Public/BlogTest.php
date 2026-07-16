<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\BlogPost;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Testimonial;
use App\Services\Public\PublicPageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class BlogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::query()->create(['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 1]);
        Language::query()->create(['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'is_active' => true, 'sort_order' => 2]);
        Currency::query()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimal_places' => 2, 'is_default' => true, 'is_active' => true, 'sort_order' => 1]);
    }

    private function makePost(array $attributes = [], array $translations = []): BlogPost
    {
        $post = BlogPost::query()->create(array_merge([
            'code' => 'guide-'.uniqid(),
            'status' => 'published',
            'published_at' => Carbon::now()->subDay(),
            'sort_order' => 0,
        ], $attributes));

        $post->translations()->create(array_merge([
            'locale' => 'en',
            'title' => 'A guide to the Red Sea',
            'slug' => 'guide-red-sea',
            'excerpt' => 'What to pack.',
            'body' => "First paragraph.\n\nSecond paragraph.",
        ], $translations));

        return $post;
    }

    public function test_the_blog_index_lists_published_posts(): void
    {
        $this->makePost();

        $this->get('/en/blog')
            ->assertOk()
            ->assertSee('A guide to the Red Sea');
    }

    public function test_a_post_page_renders_its_body(): void
    {
        $this->makePost();

        $this->get('/en/blog/guide-red-sea')
            ->assertOk()
            ->assertSee('A guide to the Red Sea')
            ->assertSee('First paragraph.')
            ->assertSee('Second paragraph.');
    }

    public function test_a_draft_post_is_not_listed_or_reachable(): void
    {
        $this->makePost(['status' => 'draft']);

        $this->get('/en/blog')->assertOk()->assertDontSee('A guide to the Red Sea');
        $this->get('/en/blog/guide-red-sea')->assertNotFound();
    }

    /**
     * A post dated in the future is scheduled, not live. Without the date guard
     * it would publish the moment it was saved.
     */
    public function test_a_future_dated_post_is_not_published_yet(): void
    {
        $this->makePost(['published_at' => Carbon::now()->addWeek()]);

        $this->get('/en/blog')->assertOk()->assertDontSee('A guide to the Red Sea');
        $this->get('/en/blog/guide-red-sea')->assertNotFound();
    }

    public function test_a_missing_post_returns_404(): void
    {
        $this->get('/en/blog/no-such-post')->assertNotFound();
    }

    public function test_the_blog_renders_the_arabic_translation(): void
    {
        $post = $this->makePost();
        $post->translations()->create([
            'locale' => 'ar',
            'title' => 'دليل البحر الأحمر',
            'slug' => 'dalil-albahr-alahmar',
            'excerpt' => 'ماذا تحزم.',
            'body' => 'الفقرة الأولى.',
        ]);

        $this->get('/ar/blog')
            ->assertOk()
            ->assertSee('دليل البحر الأحمر')
            ->assertSee('dir="rtl"', false);

        $this->get('/ar/blog/dalil-albahr-alahmar')
            ->assertOk()
            ->assertSee('الفقرة الأولى.');
    }

    /**
     * The body is admin-authored plain text rendered into HTML. It must be
     * escaped: an admin account is not a licence to inject script into a
     * public page.
     */
    public function test_the_post_body_is_escaped(): void
    {
        $this->makePost(translations: [
            'locale' => 'en',
            'title' => 'XSS probe',
            'slug' => 'xss-probe',
            'body' => '<script>alert(1)</script>',
        ]);

        $this->get('/en/blog/xss-probe')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_the_blog_index_is_empty_when_nothing_is_published(): void
    {
        $this->get('/en/blog')
            ->assertOk()
            ->assertSee(__('website.blog.empty'));
    }

    public function test_the_home_page_shows_published_posts_and_testimonials(): void
    {
        $this->makePost();

        $testimonial = Testimonial::query()->create([
            'code' => 'maya', 'author_name' => 'Maya', 'rating' => 5,
            'is_active' => true, 'is_featured' => true, 'sort_order' => 0,
        ]);
        $testimonial->translations()->create(['locale' => 'en', 'quote' => 'The boat left on time.']);

        $this->get('/en')
            ->assertOk()
            ->assertSee('A guide to the Red Sea')
            ->assertSee('The boat left on time.')
            ->assertSee('Maya');
    }

    public function test_an_inactive_testimonial_is_hidden(): void
    {
        $testimonial = Testimonial::query()->create([
            'code' => 'hidden', 'author_name' => 'Hidden', 'is_active' => false, 'sort_order' => 0,
        ]);
        $testimonial->translations()->create(['locale' => 'en', 'quote' => 'Should not appear.']);

        $this->get('/en')->assertOk()->assertDontSee('Should not appear.');
    }

    /**
     * PublicPageService used to fall back to hardcoded sample content when a
     * table was unreadable, which is how a misconfigured database silently
     * served invented tours to real visitors.
     */
    public function test_no_content_is_invented_when_the_catalogue_is_empty(): void
    {
        $service = app(PublicPageService::class);

        $this->assertSame([], $service->blogPosts());
        $this->assertSame([], $service->testimonials());
        $this->assertSame([], $service->categories());
    }
}
