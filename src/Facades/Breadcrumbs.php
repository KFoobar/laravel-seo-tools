<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools\Facades;

use Illuminate\Support\Facades\Facade;
use KFoobar\LaravelSeoTools\BreadcrumbItem;
use KFoobar\LaravelSeoTools\BreadcrumbTrail;

/**
 * @method static BreadcrumbTrail add(string $label, ?string $url = null)
 * @method static list<BreadcrumbItem> items()
 * @method static bool isEmpty()
 *
 * @see BreadcrumbTrail
 */
class Breadcrumbs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BreadcrumbTrail::class;
    }
}
