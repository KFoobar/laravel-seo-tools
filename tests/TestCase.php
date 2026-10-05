<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use KFoobar\LaravelSeoTools\Facades\Breadcrumbs;
use KFoobar\LaravelSeoTools\Facades\Sitemap;
use KFoobar\LaravelSeoTools\SeoToolsServiceProvider;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema as HeadSchema;
use Laravel\Head\HeadServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            HeadServiceProvider::class,
            SeoToolsServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Head' => Head::class,
            'Schema' => HeadSchema::class,
            'Breadcrumbs' => Breadcrumbs::class,
            'Sitemap' => Sitemap::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:2fl+Ktvkfl+Fuz4Qp/A75G2RTiWVA/ZoKZvp6fiiM10=');
        $app['config']->set('app.url', 'https://example.test');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('seo.sitemap.cache', false);
    }
}
