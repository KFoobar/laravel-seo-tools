<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use KFoobar\LaravelSeoTools\Http\Controllers\RobotsController;
use KFoobar\LaravelSeoTools\Http\Controllers\SitemapController;

Route::get('robots.txt', RobotsController::class)->name('seo.robots');
Route::get(
    ltrim(config()->string('seo.sitemap.route', '/sitemap.xml'), '/'),
    SitemapController::class,
)->name('seo.sitemap');
