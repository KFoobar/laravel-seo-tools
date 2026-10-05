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

No crawler. URLs come from config, `Sitemap::add()`, and Eloquent models that implement `Sitemapable`. Each entry is a URL plus optional `lastmod` — Google ignores `changefreq` and `priority`.

```php
use KFoobar\LaravelSeoTools\Contracts\Sitemapable;
use KFoobar\LaravelSeoTools\Facades\Sitemap;

Sitemap::add(route('home'));
Sitemap::models([Post::class]);
```

```php
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

`toSitemapTag()` may also return a URL string. Register models in a service provider so they are included on every sitemap request.

Static URLs in config:

```php
'sitemap' => [
    'enabled' => true,
    'route' => '/sitemap.xml',
    'cache' => 3600, // seconds; false to disable
    'urls' => [
        '/',
        ['loc' => '/contact', 'lastmod' => '2026-03-01'],
    ],
],
```

## Testing

```bash
composer test
```
