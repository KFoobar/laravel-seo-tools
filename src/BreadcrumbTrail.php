<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools;

use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\Schema\Breadcrumbs as BreadcrumbSchema;

/**
 * Request-scoped trail that renders HTML and syncs JSON-LD to Laravel Head.
 */
class BreadcrumbTrail
{
    /**
     * @var list<BreadcrumbItem>
     */
    protected array $items = [];

    protected ?BreadcrumbSchema $schema = null;

    /**
     * Append a crumb. Pass no URL for the current page.
     */
    public function add(string $label, ?string $url = null): static
    {
        $this->items[] = new BreadcrumbItem($label, $url);

        $this->syncToHead($label, $url ?? url()->current());

        return $this;
    }

    /**
     * @return list<BreadcrumbItem>
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Determine whether the trail has no crumbs.
     */
    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /**
     * Register or mutate a single Head BreadcrumbList so JSON-LD stays in sync.
     */
    protected function syncToHead(string $label, string $url): void
    {
        if ($this->schema === null) {
            $this->schema = Schema::breadcrumbs();
            Head::schema($this->schema);
        }

        $this->schema->item($label, $url);
    }
}
