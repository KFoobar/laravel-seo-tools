<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools;

/**
 * A single URL entry in an XML sitemap.
 */
readonly class SitemapUrl
{
    public function __construct(
        public string $loc,
        public ?string $lastmod = null,
    ) {}
}
