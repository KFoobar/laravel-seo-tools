<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use KFoobar\LaravelSeoTools\View\Components\Breadcrumbs as BreadcrumbsComponent;

class SeoToolsServiceProvider extends ServiceProvider
{
    /**
     * Register package bindings and config.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/seo.php', 'seo');

        $this->app->scoped(BreadcrumbTrail::class);
        $this->app->singleton(Sitemap::class);
    }

    /**
     * Bootstrap views, routes, and publishable assets.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'seo');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Blade::component(BreadcrumbsComponent::class, 'seo::breadcrumbs');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/seo.php' => config_path('seo.php'),
            ], 'seo-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/seo'),
            ], 'seo-views');
        }
    }
}
