<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\App;

/**
 * Resolves a single translation row for the active locale, falling back to the
 * application fallback locale when the active locale has no row.
 *
 * Consuming models must define a `translations()` HasMany relation.
 */
trait HasTranslations
{
    /**
     * All translations for this model.
     *
     * @return HasMany<covariant Model, $this>
     */
    abstract public function translations(): HasMany;

    /**
     * The best-matching translation for the active locale.
     *
     * A real one-of-many HasOne, so it is eager-loadable via `with('translation')`
     * and never issues a per-row query.
     *
     * The winner is chosen by *locale preference*, which is not a stored column.
     * That rules out `ofMany('column', 'MIN')` — it can only aggregate real
     * columns, and aggregating `id` would silently return whichever translation
     * was inserted first rather than the requested locale. So the row is picked
     * by a correlated subquery: for each parent, keep the translation whose
     * locale ranks best in `$locales`.
     *
     * The ranking is a portable CASE ladder. PostgreSQL's `DISTINCT ON` and
     * `array_position()` are hard syntax errors on the SQLite test connection.
     *
     * @return HasOne<covariant Model, $this>
     */
    public function translation(): HasOne
    {
        $locales = $this->preferredLocales();
        $relation = $this->translations();
        $related = $relation->getRelated();
        $table = $related->getTable();
        $key = $related->getKeyName();
        $foreignKey = $relation->getForeignKeyName();
        $rank = $this->localeRankSql($locales);

        return $this->translations()
            ->one()
            ->whereIn('locale', $locales)
            // Keep only the best-ranked locale present for this parent. The
            // bindings are repeated because the CASE ladder appears on both
            // sides of the comparison.
            ->whereRaw(
                $this->localeRankSql($locales, $table).' = (select min('.
                    $this->localeRankSql($locales, 't2').
                ') from '.$table.' as t2'.
                ' where t2.'.$foreignKey.' = '.$table.'.'.$foreignKey.
                ' and t2.locale in ('.rtrim(str_repeat('?,', count($locales)), ',').'))',
                [...$locales, ...$locales, ...$locales],
            );
    }

    /**
     * A CASE ladder ranking candidate locales, most preferred first. The column
     * is qualified per-alias so the same expression works inside the subquery.
     *
     * @param  list<string>  $locales
     */
    protected function localeRankSql(array $locales, string $alias = ''): string
    {
        $column = $alias === '' ? 'locale' : $alias.'.locale';
        $cases = '';

        foreach (array_keys($locales) as $index) {
            $cases .= ' when ? then '.$index;
        }

        return '(case '.$column.$cases.' else '.count($locales).' end)';
    }

    /**
     * The locales to consider, most preferred first.
     *
     * @return list<string>
     */
    protected function preferredLocales(): array
    {
        return array_values(array_unique([
            App::getLocale(),
            (string) config('app.fallback_locale'),
        ]));
    }
}
