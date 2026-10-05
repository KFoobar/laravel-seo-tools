<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use KFoobar\LaravelSeoTools\BreadcrumbTrail;

/**
 * Renders the breadcrumb trail.
 */
class Breadcrumbs extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public BreadcrumbTrail $trail) {}

    /**
     * Determine whether the trail should be rendered.
     */
    public function shouldRender(): bool
    {
        return ! $this->trail->isEmpty();
    }

    /**
     * Get the view that represents the component.
     */
    public function render(): View
    {
        return view(config()->string('seo.breadcrumbs.view', 'seo::breadcrumbs'));
    }
}
