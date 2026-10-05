<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools\Contracts;

/** Eloquent models that can appear in the XML sitemap. */
interface Sitemapable
{
    /**
     * Return a URL string or a sitemap tag array for this model.
     *
     * @return string|array{loc?: string, url?: string, lastmod?: mixed}
     */
    public function toSitemapTag(): string|array;
}
