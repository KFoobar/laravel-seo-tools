<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools\Facades;

use DateTimeInterface;
use Illuminate\Support\Facades\Facade;
use KFoobar\LaravelSeoTools\Sitemap as SitemapGenerator;

/**
 * @method static SitemapGenerator add(string $url, DateTimeInterface|string|null $lastmod = null)
 * @method static SitemapGenerator models(array $models)
 * @method static string toXml()
 *
 * @see SitemapGenerator
 */
class Sitemap extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SitemapGenerator::class;
    }
}
