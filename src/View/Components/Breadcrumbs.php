<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use KFoobar\LaravelSeoTools\BreadcrumbTrail;

/** Renders the breadcrumb trail, or nothing when it is empty. */
class Breadcrumbs extends Component
{
    public function __construct(public BreadcrumbTrail $trail) {}

    public function render(): View|string
    {
        if ($this->trail->isEmpty()) {
            return '';
        }

        return view(config()->string('seo.breadcrumbs.view', 'seo::breadcrumbs'));
    }
}
