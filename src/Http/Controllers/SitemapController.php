<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools\Http\Controllers;

use Illuminate\Http\Response;
use KFoobar\LaravelSeoTools\Sitemap;

/** Serves the generated XML sitemap. */
class SitemapController
{
    public function __invoke(Sitemap $sitemap): Response
    {
        abort_unless(config()->boolean('seo.sitemap.enabled'), 404);

        return response($sitemap->toXml(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
