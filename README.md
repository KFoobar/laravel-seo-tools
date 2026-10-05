# Laravel SEO Tools

Breadcrumbs, `robots.txt`, and XML sitemaps for Laravel. Meta tags, Open Graph, and JSON-LD stay in [`laravel/head`](https://laravel.com/docs/head).

## Installation

```bash
composer require kfoobar/laravel-seo-tools
```

The package requires PHP 8.3+, Laravel 13.17+, and `laravel/head`.

Publish the config (optional):

```bash
php artisan vendor:publish --tag=seo-config
```

Publish the breadcrumb view (optional):

```bash
php artisan vendor:publish --tag=seo-views
```

## Layout

```blade
<head>
    @head
</head>
<body>
    <x-seo::breadcrumbs />
```

`/robots.txt` and `/sitemap.xml` are registered automatically.

## Breadcrumbs

Add crumbs in a controller (or anywhere before `@head` renders):

```php
use KFoobar\LaravelSeoTools\Facades\Breadcrumbs;

Breadcrumbs::add('Home', route('home'))
    ->add('Blog', route('posts.index'))
    ->add($post->title);
```

The last crumb is the current page: it is not linked, and it gets `aria-current="page"`. The same trail is pushed to `Head::schema(Schema::breadcrumbs())` so JSON-LD is rendered by `@head` without duplicating data.

If the trail is empty, neither the nav nor the schema is rendered.

## robots.txt

Configured in `config/seo.php`, not per request:

```php
'robots' => [
    'enabled' => true,
    'user_agents' => [
        '*' => [
            'allow' => ['/'],
            'disallow' => ['/admin', '/horizon', '/telescope'],
        ],
    ],
    'sitemap' => true, // Sitemap: {app.url}/sitemap.xml
],
```

This is the site-wide crawl file. Page-level `meta robots` still belongs to Laravel Head.

## Sitemap

No crawler. URLs and models are read from config on each request, so the list stays the same under PHP-FPM and Octane. Each entry is a URL plus optional `lastmod`. Google ignores `changefreq` and `priority`.

```php
'sitemap' => [
    'enabled' => true,
    'route' => '/sitemap.xml',
    'cache' => 3600, // seconds; false to disable. The cache key includes model counts and lastmod.
    'urls' => [
        '/',
        ['loc' => '/contact', 'lastmod' => '2026-03-01'],
    ],
    'models' => [
        App\Models\Post::class,
    ],
],
```

```php
use KFoobar\LaravelSeoTools\Contracts\Sitemapable;

class Post extends Model implements Sitemapable
{
    public function toSitemapTag(): string|array
    {
        return [
            'loc' => route('posts.show', $this),
            'lastmod' => $this->updated_at,
        ];
    }
}
```

`toSitemapTag()` may also return a URL string. Models are read with `lazy()`, so the full table is not loaded at once.

Set `enabled` to `false` before routes are cached if the application serves its own `/sitemap.xml` or `/robots.txt`. Rebuild the route cache after changing that flag.

## Testing

```bash
composer test
```
