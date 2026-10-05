<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;
use KFoobar\LaravelSeoTools\Contracts\Sitemapable;

/**
 * Builds a sitemap from configured URLs and Sitemapable models.
 */
class Sitemap
{
    /**
     * Render the sitemap as XML.
     */
    public function toXml(): string
    {
        $ttl = $this->cacheTtl();

        if ($ttl === null) {
            return $this->renderXml();
        }

        return Cache::remember($this->cacheKey(), $ttl, $this->renderXml(...));
    }

    /**
     * Render the sitemap view.
     */
    protected function renderXml(): string
    {
        return view('seo::sitemap', [
            'urls' => $this->allUrls(),
        ])->render();
    }

    /**
     * Resolve the cache lifetime in seconds.
     */
    protected function cacheTtl(): ?int
    {
        $ttl = config('seo.sitemap.cache');

        if ($ttl === false || $ttl === null || $ttl === 0) {
            return null;
        }

        if (! is_int($ttl)) {
            throw new InvalidArgumentException('Sitemap cache must be a number of seconds or false.');
        }

        return $ttl;
    }

    /**
     * Cache key changes when the configured URLs change or a listed model's rows change.
     */
    protected function cacheKey(): string
    {
        $fingerprint = json_encode([
            config()->array('seo.sitemap.urls'),
            $this->modelFingerprints(),
        ], JSON_THROW_ON_ERROR);

        return 'seo.sitemap.'.hash('xxh128', $fingerprint);
    }

    /**
     * @return array<class-string<Model&Sitemapable>, string>
     */
    protected function modelFingerprints(): array
    {
        return collect($this->configuredModels())
            ->mapWithKeys(function (string $class): array {
                $count = $class::query()->count();
                $newest = $class::query()->max('updated_at');

                return [$class => $count.'|'.($newest ?? '')];
            })
            ->all();
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    protected function allUrls(): Collection
    {
        return $this->urlsFromConfig()
            ->concat($this->urlsFromModels())
            ->unique(fn (SitemapUrl $url): string => $url->loc)
            ->values();
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    protected function urlsFromConfig(): Collection
    {
        return collect(config()->array('seo.sitemap.urls'))
            ->map(function (mixed $entry): SitemapUrl {
                if (! is_string($entry) && ! is_array($entry)) {
                    throw new InvalidArgumentException('Sitemap entries must be a URL or an array with a loc.');
                }

                return $this->urlFromEntry($entry);
            })
            ->values();
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    protected function urlsFromModels(): Collection
    {
        return collect($this->configuredModels())->flatMap(function (string $class): Collection {
            return $class::query()->lazy()->map(function (Model&Sitemapable $model): SitemapUrl {
                return $this->urlFromEntry($model->toSitemapTag(), $model->updated_at);
            })->collect();
        });
    }

    /**
     * @return list<class-string<Model&Sitemapable>>
     */
    protected function configuredModels(): array
    {
        return collect(config()->array('seo.sitemap.models'))
            ->map(function (mixed $class): string {
                if (! is_string($class) || ! is_a($class, Model::class, true) || ! is_a($class, Sitemapable::class, true)) {
                    throw new InvalidArgumentException('Sitemap models must be Eloquent models that implement '.Sitemapable::class.'.');
                }

                return $class;
            })
            ->values()
            ->all();
    }

    /**
     * @param  string|array{loc?: string, lastmod?: mixed}  $entry
     */
    protected function urlFromEntry(string|array $entry, DateTimeInterface|string|null $fallbackLastmod = null): SitemapUrl
    {
        if (is_string($entry)) {
            return new SitemapUrl($this->location($entry), $this->formatLastmod($fallbackLastmod));
        }

        $loc = $entry['loc'] ?? null;

        if (! is_string($loc)) {
            throw new InvalidArgumentException('Sitemap entries require a URL.');
        }

        return new SitemapUrl(
            $this->location($loc),
            $this->formatLastmod($entry['lastmod'] ?? $fallbackLastmod),
        );
    }

    /**
     * Normalize a sitemap URL.
     */
    protected function location(string $url): string
    {
        if (trim($url) === '') {
            throw new InvalidArgumentException('Sitemap entries require a URL.');
        }

        return url($url);
    }

    /**
     * Format a lastmod value as a date, or null when it is omitted.
     */
    protected function formatLastmod(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! $value instanceof DateTimeInterface && ! is_string($value)) {
            throw new InvalidArgumentException('Sitemap lastmod must be a date string or a date instance.');
        }

        return Date::parse($value)->toDateString();
    }
}
