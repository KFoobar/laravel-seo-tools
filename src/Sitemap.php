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

/** Builds a sitemap from config URLs, runtime additions, and Sitemapable models. */
class Sitemap
{
    /**
     * @var list<SitemapUrl>
     */
    protected array $urls = [];

    /**
     * @var list<class-string<Model&Sitemapable>>
     */
    protected array $models = [];

    /**
     * Add a URL to the sitemap.
     */
    public function add(string $url, DateTimeInterface|string|null $lastmod = null): static
    {
        $this->urls[] = new SitemapUrl(
            url($url),
            $this->formatLastmod($lastmod),
        );

        return $this;
    }

    /**
     * Register Eloquent models that implement Sitemapable.
     *
     * @param  list<class-string<Model&Sitemapable>>  $models
     */
    public function models(array $models): static
    {
        $this->models = collect($this->models)
            ->merge($models)
            ->unique()
            ->values()
            ->all();

        return $this;
    }

    /**
     * Render the sitemap as XML.
     */
    public function toXml(): string
    {
        $ttl = config('seo.sitemap.cache');

        if ($ttl) {
            return Cache::remember('seo.sitemap.xml', (int) $ttl, $this->renderXml(...));
        }

        return $this->renderXml();
    }

    protected function renderXml(): string
    {
        return view('seo::sitemap', [
            'urls' => $this->allUrls(),
        ])->render();
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    protected function allUrls(): Collection
    {
        return collect($this->urlsFromConfig())
            ->concat($this->urls)
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
            ->map(function (mixed $entry): ?SitemapUrl {
                if (is_string($entry)) {
                    return new SitemapUrl(url($entry));
                }

                if (! is_array($entry)) {
                    return null;
                }

                $loc = $entry['loc'] ?? $entry['url'] ?? null;

                if (! is_string($loc) || $loc === '') {
                    return null;
                }

                return new SitemapUrl(
                    url($loc),
                    $this->formatLastmod($entry['lastmod'] ?? null),
                );
            })
            ->filter(fn (?SitemapUrl $url): bool => $url instanceof SitemapUrl)
            ->values();
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    protected function urlsFromModels(): Collection
    {
        return collect($this->models)->flatMap(function (string $class) {
            return $class::query()->get()->map(function (Model $model) use ($class): SitemapUrl {
                if (! $model instanceof Sitemapable) {
                    throw new InvalidArgumentException("[{$class}] must implement ".Sitemapable::class.'.');
                }

                return $this->urlFromTag($model->toSitemapTag(), $model);
            });
        });
    }

    /**
     * @param  string|array{loc?: string, url?: string, lastmod?: mixed}  $tag
     */
    protected function urlFromTag(string|array $tag, Model $model): SitemapUrl
    {
        if (is_string($tag)) {
            return new SitemapUrl(
                url($tag),
                $this->formatLastmod($model->updated_at),
            );
        }

        $loc = $tag['loc'] ?? $tag['url'] ?? '';

        return new SitemapUrl(
            url($loc),
            $this->formatLastmod($tag['lastmod'] ?? $model->updated_at),
        );
    }

    protected function formatLastmod(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Date::instance($value)->toDateString();
        }

        return Date::parse((string) $value)->toDateString();
    }
}
