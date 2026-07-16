<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The translated fields on a create/edit form are only required for the
 * fallback locale.
 *
 * Marking every locale's field `required` in the HTML made the browser refuse
 * to submit the form — and because the blocking input sits on a hidden language
 * tab, it reported "please fill out this field" against an element nobody could
 * see. The Save button simply appeared to do nothing, with no error anywhere.
 */
final class TranslationFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::query()->create(['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 1]);
        Language::query()->create(['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'is_active' => true, 'sort_order' => 2]);
        Language::query()->create(['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 3]);
    }

    private function admin(string $ability): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Permission::findOrCreate($ability, 'web');
        $user->givePermissionTo($ability);

        return $user;
    }

    public function test_only_the_fallback_locale_field_is_marked_required(): void
    {
        $html = $this->actingAs($this->admin('posts.create'))
            ->get('/admin/posts/create')
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/name="translations\[en\]\[title\]"[^>]*\srequired/',
            $html,
            'the fallback locale must stay required',
        );

        foreach (['ar', 'ru'] as $locale) {
            $this->assertDoesNotMatchRegularExpression(
                '/name="translations\['.$locale.'\]\[title\]"[^>]*\srequired/',
                $html,
                "the {$locale} field must not be required — it sits on a hidden tab and silently blocks submission",
            );
        }
    }

    public function test_a_post_saves_with_only_the_fallback_locale_filled(): void
    {
        $this->actingAs($this->admin('posts.create'))
            ->post('/admin/posts', [
                'code' => 'solo-locale',
                'status' => 'published',
                'sort_order' => 0,
                'is_featured' => '0',
                'translations' => [
                    'en' => ['title' => 'English only', 'slug' => '', 'excerpt' => '', 'body' => ''],
                    'ar' => ['title' => '', 'slug' => '', 'excerpt' => '', 'body' => ''],
                    'ru' => ['title' => '', 'slug' => '', 'excerpt' => '', 'body' => ''],
                ],
            ])
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('blog_post_translations', ['locale' => 'en', 'title' => 'English only']);
        $this->assertDatabaseMissing('blog_post_translations', ['locale' => 'ar']);
    }

    public function test_the_fallback_locale_is_still_required_server_side(): void
    {
        $this->actingAs($this->admin('posts.create'))
            ->post('/admin/posts', [
                'code' => 'no-english',
                'status' => 'draft',
                'sort_order' => 0,
                'translations' => [
                    'en' => ['title' => '', 'slug' => '', 'excerpt' => '', 'body' => ''],
                    'ar' => ['title' => 'عربي فقط', 'slug' => '', 'excerpt' => '', 'body' => ''],
                ],
            ])
            ->assertSessionHasErrors('translations.en.title');
    }

    /**
     * A blog post's label column is `title`, not `name`. Reading the wrong key
     * silently produced "item", "item-2", "item-3"… for every post.
     */
    public function test_a_blank_slug_is_generated_from_the_title(): void
    {
        $this->actingAs($this->admin('posts.create'))
            ->post('/admin/posts', [
                'code' => 'slug-from-title',
                'status' => 'draft',
                'sort_order' => 0,
                'translations' => [
                    'en' => ['title' => 'Diving the Giftun Reefs', 'slug' => '', 'excerpt' => '', 'body' => ''],
                ],
            ]);

        $this->assertDatabaseHas('blog_post_translations', [
            'locale' => 'en',
            'slug' => 'diving-the-giftun-reefs',
        ]);
    }

    /**
     * Testimonial translations carry only a quote — there is no slug column to
     * write to, and generating one would throw.
     */
    public function test_a_testimonial_saves_without_a_slug(): void
    {
        $this->actingAs($this->admin('testimonials.create'))
            ->post('/admin/testimonials', [
                'code' => 'happy-guest',
                'author_name' => 'Maya',
                'sort_order' => 0,
                'is_active' => '1',
                'is_featured' => '0',
                'rating' => 5,
                'translations' => [
                    'en' => ['quote' => 'The boat left on time.'],
                ],
            ])
            ->assertRedirect(route('admin.testimonials.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('testimonial_translations', ['quote' => 'The boat left on time.']);
    }

    public function test_a_rating_outside_one_to_five_is_rejected(): void
    {
        $this->actingAs($this->admin('testimonials.create'))
            ->post('/admin/testimonials', [
                'code' => 'bad-rating',
                'author_name' => 'Maya',
                'sort_order' => 0,
                'rating' => 9,
                'translations' => ['en' => ['quote' => 'x']],
            ])
            ->assertSessionHasErrors('rating');
    }
}
