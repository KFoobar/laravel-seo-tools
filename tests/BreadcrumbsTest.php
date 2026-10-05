<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use KFoobar\LaravelSeoTools\Facades\Breadcrumbs;
use Laravel\Head\Facades\Head;

it('renders breadcrumb links and the current page', function () {
    Breadcrumbs::add('Home', 'https://example.test')
        ->add('Blog', 'https://example.test/blog')
        ->add('Hello world');

    $html = Blade::render('<x-seo::breadcrumbs />');

    expect($html)
        ->toContain('aria-label="Breadcrumb"')
        ->toContain('href="https://example.test"')
        ->toContain('Home')
        ->toContain('href="https://example.test/blog"')
        ->toContain('Blog')
        ->toContain('Hello world')
        ->toContain('aria-current="page"')
        ->not->toContain('href=""');
});

it('renders nothing when the trail is empty', function () {
    $html = trim(Blade::render('<x-seo::breadcrumbs />'));

    expect($html)->toBe('');
});

it('does not link the current page even when a url is given', function () {
    Breadcrumbs::add('Home', 'https://example.test')
        ->add('About', 'https://example.test/about');

    $html = Blade::render('<x-seo::breadcrumbs />');

    expect($html)
        ->toContain('href="https://example.test"')
        ->not->toContain('href="https://example.test/about"')
        ->toContain('aria-current="page"');
});

it('syncs the trail to laravel head as a breadcrumb list', function () {
    Breadcrumbs::add('Home', 'https://example.test')
        ->add('About');

    $head = Head::toHtml();

    expect($head)
        ->toContain('application/ld+json')
        ->toContain('BreadcrumbList')
        ->toContain('Home')
        ->toContain('https://example.test')
        ->toContain('About');
});

it('does not add breadcrumb schema when the trail is empty', function () {
    expect(Head::toHtml())->not->toContain('BreadcrumbList');
});

it('does not duplicate breadcrumb schema when items are added incrementally', function () {
    Breadcrumbs::add('Home', 'https://example.test');
    Breadcrumbs::add('About');

    $head = Head::toHtml();

    expect(substr_count($head, 'BreadcrumbList'))->toBe(1);
});
