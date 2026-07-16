<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\DeleteResourceAction;
use App\Actions\Admin\StoreResourceAction;
use App\Actions\Admin\UpdateResourceAction;
use App\Admin\ResourceSchema;
use App\DataTransferObjects\ResourceData;
use App\Exceptions\DomainActionException;
use App\Http\Requests\Admin\ResourceRequest;
use App\Models\Language;
use App\Shared\Contracts\ResourceRepositoryContract;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

final class AdminResourceController
{
    public function __construct(
        private readonly ResourceRepositoryContract $repository,
    ) {}

    public function index(Request $request, string $resource): View
    {
        $payload = $this->payload($resource);

        // `settings` is routed and in the sidebar but has no table behind it,
        // so there is nothing to paginate. It renders its own placeholder view.
        if (! ResourceSchema::hasModel($resource)) {
            return view("admin.{$resource}.index", $payload);
        }

        $search = $request->string('search')->trim()->value();
        $search = $search === '' ? null : $search;

        return view("admin.{$resource}.index", array_merge($payload, [
            'items' => $this->rows($resource, $search),
            'search' => $search,
        ]));
    }

    public function create(string $resource): View
    {
        // Gated on `.create`, not `.view` — otherwise a read-only role could
        // open the form and only discover it was forbidden on submit.
        $payload = $this->payload($resource, 'create');

        return view("admin.{$resource}.create", $payload);
    }

    /**
     * `$id` comes first on every {id} route: `resource` is a route default, so
     * Laravel binds it after the URI parameters. See routes/web.php.
     */
    public function show(string $id, string $resource): View
    {
        $payload = $this->payload($resource);
        $model = $this->repository->find($resource, $id);

        abort_if($model === null, 404);

        return view("admin.{$resource}.show", array_merge($payload, [
            'id' => $id,
            'item' => $model,
        ], $this->existing($resource, $model, $payload['fields'])));
    }

    public function edit(string $id, string $resource): View
    {
        $payload = $this->payload($resource, 'update');
        $model = $this->repository->find($resource, $id);

        abort_if($model === null, 404);

        return view("admin.{$resource}.edit", array_merge($payload, [
            'id' => $id,
            // The row itself, for views that render more than their editable
            // fields — a booking shows the customer's whole request.
            'model' => $model,
        ], $this->existing($resource, $model, $payload['fields'])));
    }

    public function store(ResourceRequest $request, string $resource, StoreResourceAction $store): RedirectResponse
    {
        // Authorization already ran in ResourceRequest::authorize().
        try {
            $model = $store(ResourceData::fromRequest($resource, $request->validated()));
        } catch (DomainActionException $exception) {
            return back()->withInput()->withErrors(['resource' => $exception->getMessage()]);
        }

        return $this->afterWrite($request, $resource, $model->getKey(), __('admin.crud.created'));
    }

    public function update(ResourceRequest $request, string $id, string $resource, UpdateResourceAction $update): RedirectResponse
    {
        $model = $this->repository->find($resource, $id);

        abort_if($model === null, 404);

        try {
            $update(ResourceData::fromRequest($resource, $request->validated()), $model);
        } catch (DomainActionException $exception) {
            return back()->withInput()->withErrors(['resource' => $exception->getMessage()]);
        }

        return $this->afterWrite($request, $resource, $id, __('admin.crud.updated'));
    }

    /**
     * "Save and continue" returns to the form; a plain save returns to the list.
     */
    private function afterWrite(Request $request, string $resource, int|string $id, string $message): RedirectResponse
    {
        $target = $request->boolean('continue')
            ? redirect()->route("admin.{$resource}.edit", $id)
            : redirect()->route("admin.{$resource}.index");

        return $target->with('success', $message);
    }

    public function destroy(string $id, string $resource, DeleteResourceAction $delete): RedirectResponse
    {
        abort_unless(ResourceSchema::exists($resource), 404);
        abort_unless(auth()->user()?->can($resource.'.delete'), 403);

        $model = $this->repository->find($resource, $id);

        abort_if($model === null, 404);

        try {
            $delete($resource, $model);
        } catch (DomainActionException $e) {
            return redirect()->route("admin.{$resource}.index")->with('error', $e->getMessage());
        }

        return redirect()->route("admin.{$resource}.index")->with('success', __('admin.crud.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $resource, string $ability = 'view'): array
    {
        abort_unless(ResourceSchema::exists($resource), 404);
        abort_unless(auth()->user()?->can($resource.'.'.$ability), 403);

        // Nothing to create, show, or edit for a resource with no table.
        abort_if($ability !== 'view' && ! ResourceSchema::hasModel($resource), 404);

        // Bookings originate with customers: staff triage them, they do not
        // author or destroy them.
        abort_if(
            in_array($ability, ['create', 'delete'], true) && ResourceSchema::isReadOnlyOrigin($resource),
            404,
        );

        $fields = ResourceSchema::fields($resource);

        return [
            'resource' => $resource,
            'title' => __("admin.resources.{$resource}"),
            'fields' => $fields,
            'translationFields' => ResourceSchema::translationFields($resource),
            'languages' => $this->languages(),
            'relationOptions' => $this->relationOptions($fields),
            'values' => $this->defaults($fields),
            'translationValues' => [],
        ];
    }

    /**
     * The form's starting values for a create: each field's schema default.
     *
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private function defaults(array $fields): array
    {
        return array_map(
            fn (array $field): mixed => $field['default'] ?? null,
            $fields,
        );
    }

    /**
     * The stored values for an edit, with translations keyed by locale so each
     * locale tab renders its own row.
     *
     * @param  array<string, array<string, mixed>>  $fields
     * @return array{values: array<string, mixed>, translationValues: array<string, array<string, mixed>>}
     */
    private function existing(string $resource, Model $model, array $fields): array
    {
        $values = [];

        foreach (array_keys($fields) as $name) {
            // A password is never echoed back into the form.
            $values[$name] = $name === 'password' ? null : $model->getAttribute($name);
        }

        $translationValues = [];

        if (ResourceSchema::isTranslatable($resource)) {
            foreach ($model->translations as $translation) {
                $translationValues[$translation->getAttribute('locale')] = $translation->attributesToArray();
            }
        }

        return ['values' => $values, 'translationValues' => $translationValues];
    }

    /**
     * The active languages the per-locale translation tabs are rendered from.
     *
     * @return array<string, string>
     */
    private function languages(): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Language $language): array => [
                'code' => $language->code,
                'native' => $language->native_name,
                'direction' => $language->direction,
                'flag' => '',
            ])
            ->all();
    }

    /**
     * Option lists for every relation field on the form, resolved through the
     * repository so each select is one query rather than one query per row.
     *
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, array<int|string, string>>
     */
    private function relationOptions(array $fields): array
    {
        $options = [];

        foreach ($fields as $name => $field) {
            if (($field['type'] ?? null) !== 'relation') {
                continue;
            }

            /** @var string $related */
            $related = $field['relation'];

            $options[$name] = $this->repository->options($related);
        }

        return $options;
    }

    /**
     * One page of rows, shaped for the listing table.
     *
     * The repository eager-loads every relation the label needs, so building
     * these rows costs a fixed number of queries no matter the page size.
     *
     * @return LengthAwarePaginator<int, mixed>
     */
    private function rows(string $resource, ?string $search): LengthAwarePaginator
    {
        $paginator = $this->repository->paginate($resource, $search);

        $paginator->getCollection()->transform(fn (Model $model): array => $this->row($resource, $model));

        return $paginator;
    }

    /**
     * @return array{id: int|string, name: string, code: string|null, status: string|null}
     */
    private function row(string $resource, Model $model): array
    {
        return [
            'id' => $model->getKey(),
            'name' => $this->label($resource, $model),
            // Bookings have no `code`; the useful second column is who asked.
            'code' => $resource === 'bookings'
                ? $model->getAttribute('customer_name')
                : $model->getAttribute('code'),
            'status' => $this->status($model),
        ];
    }

    /**
     * A row's display name: the active locale's translation where there is one,
     * otherwise the resource's own label column.
     */
    private function label(string $resource, Model $model): string
    {
        if (ResourceSchema::isTranslatable($resource)) {
            // The column differs per resource: a post has a `title`, a
            // testimonial only a `quote`.
            $name = $model->getRelationValue('translation')
                ?->getAttribute(ResourceSchema::translationLabelColumn($resource));

            if (is_string($name) && $name !== '') {
                return Str::limit($name, 60);
            }

            // Testimonials have no translated title of their own, so fall back
            // to the author rather than showing a bare "#7".
            $fallback = $model->getAttribute(ResourceSchema::labelColumn($resource));

            return is_string($fallback) && $fallback !== '' ? $fallback : '#'.$model->getKey();
        }

        return (string) $model->getAttribute(ResourceSchema::labelColumn($resource));
    }

    private function status(Model $model): ?string
    {
        $status = $model->getAttribute('status');

        if (is_string($status) && $status !== '') {
            return $status;
        }

        $active = $model->getAttribute('is_active');

        return $active === null ? null : ((bool) $active ? 'active' : 'inactive');
    }
}
