<?php

declare(strict_types=1);

namespace Tests\Feature\Blade;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class BladeStaticAnalysisTest extends TestCase
{
    public function test_blade_views_do_not_contain_direct_database_queries(): void
    {
        $patterns = ['::query(', 'DB::', '::where(', '::all(', '::get('];
        $violations = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            $contents = File::get($file->getPathname());

            foreach ($patterns as $pattern) {
                if (str_contains($contents, $pattern)) {
                    $violations[] = $file->getRelativePathname().' contains '.$pattern;
                }
            }
        }

        $this->assertSame([], $violations);
    }
}
