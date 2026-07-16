<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Testimonial;
use App\Models\Tour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds the blog post and testimonials that used to be hardcoded arrays in
 * PublicPageService, so the public site keeps the content it had.
 *
 * `firstOrCreate` on the code: re-seeding must not overwrite edits.
 */
final class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPosts();
        $this->seedTestimonials();
    }

    private function seedPosts(): void
    {
        $post = BlogPost::withTrashed()->firstOrCreate(
            ['code' => 'red-sea-first-timers'],
            [
                'status' => 'published',
                'is_featured' => true,
                'sort_order' => 0,
                'published_at' => Carbon::now()->subDays(7),
            ],
        );

        if ($post->translations()->exists()) {
            return;
        }

        $post->translations()->createMany([
            [
                'locale' => 'en',
                'title' => 'A first-timer’s guide to the Red Sea',
                'slug' => 'first-timers-guide-red-sea',
                'excerpt' => 'What to pack, when to go, and which trips are worth your only free day.',
                'body' => "The Red Sea rewards planning. Reefs are calmest before noon, so an early boat means clearer water and fewer crowds.\n\nBring reef-safe sunscreen, a hat, and more water than you think you need. Most day trips include lunch, but few include a towel.\n\nIf you only have one free day, choose between the sea and the desert rather than trying to do both — the drive alone will eat the difference.",
            ],
            [
                'locale' => 'ar',
                'title' => 'دليل المبتدئين إلى البحر الأحمر',
                'slug' => 'dalil-almubtadiin-albahr-alahmar',
                'excerpt' => 'ماذا تحزم، ومتى تذهب، وأي الرحلات تستحق يومك الحر الوحيد.',
                'body' => "البحر الأحمر يكافئ التخطيط. الشعاب المرجانية تكون أهدأ قبل الظهر، لذا فإن القارب المبكر يعني مياهاً أصفى وزحاماً أقل.\n\nأحضر واقي شمس آمن للشعاب، وقبعة، ومياهاً أكثر مما تظن أنك تحتاج. معظم الرحلات اليومية تشمل الغداء، لكن قليلاً منها يشمل منشفة.\n\nإذا كان لديك يوم حر واحد فقط، اختر بين البحر والصحراء بدلاً من محاولة الجمع بينهما — القيادة وحدها ستلتهم الفارق.",
            ],
        ]);
    }

    private function seedTestimonials(): void
    {
        $orangeBay = Tour::query()->where('code', 'orange-bay')->value('id');

        $seeded = [
            [
                'code' => 'maya-2026',
                'author_name' => 'Maya',
                'author_country' => 'DE',
                'tour_id' => $orangeBay,
                'rating' => 5,
                'sort_order' => 0,
                'quotes' => [
                    'en' => 'The boat left on time, the guide knew every reef, and lunch was better than our hotel’s. Worth every pound.',
                    'ar' => 'انطلق القارب في موعده، وكان المرشد يعرف كل شعبة مرجانية، وكان الغداء أفضل من فندقنا. يستحق كل قرش.',
                ],
            ],
            [
                'code' => 'omar-2026',
                'author_name' => 'Omar',
                'author_country' => 'EG',
                'tour_id' => null,
                'rating' => 5,
                'sort_order' => 1,
                'quotes' => [
                    'en' => 'Booked three trips across a week. Every pickup was on time and every price was exactly what I was quoted.',
                    'ar' => 'حجزت ثلاث رحلات خلال أسبوع. كل موعد للاستلام كان في وقته، وكل سعر كان مطابقاً تماماً لما عُرض عليّ.',
                ],
            ],
        ];

        foreach ($seeded as $row) {
            $quotes = $row['quotes'];
            unset($row['quotes']);

            $testimonial = Testimonial::withTrashed()->firstOrCreate(
                ['code' => $row['code']],
                array_merge($row, ['is_featured' => true, 'is_active' => true, 'reviewed_on' => Carbon::now()->subDays(14)]),
            );

            if ($testimonial->translations()->exists()) {
                continue;
            }

            foreach ($quotes as $locale => $quote) {
                $testimonial->translations()->create(['locale' => $locale, 'quote' => $quote]);
            }
        }
    }
}
