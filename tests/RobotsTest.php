<?php

declare(strict_types=1);

it('serves robots.txt from config', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk();

    expect($response->headers->get('Content-Type'))->toStartWith('text/plain');

    $response
        ->assertSee('User-agent: *', false)
        ->assertSee('Allow: /', false)
        ->assertSee('Disallow: /admin', false)
        ->assertSee('Disallow: /horizon', false)
        ->assertSee('Disallow: /telescope', false)
        ->assertSee('Sitemap: https://example.test/sitemap.xml', false);
});

it('omits the sitemap line when disabled', function () {
    config(['seo.robots.sitemap' => false]);

    $this->get('/robots.txt')
        ->assertOk()
        ->assertDontSee('Sitemap:', false);
});

it('can be disabled', function () {
    config(['seo.robots.enabled' => false]);

    $this->get('/robots.txt')->assertNotFound();
});
