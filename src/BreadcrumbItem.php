<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools;

/**
 * A single crumb in a visible breadcrumb trail.
 */
readonly class BreadcrumbItem
{
    public function __construct(
        public string $label,
        public ?string $url = null,
    ) {}
}
