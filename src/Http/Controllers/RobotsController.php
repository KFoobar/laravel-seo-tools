<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools\Http\Controllers;

use Illuminate\Http\Response;
use KFoobar\LaravelSeoTools\Robots;

/** Serves the generated robots.txt. */
class RobotsController
{
    public function __invoke(Robots $robots): Response
    {
        abort_unless(config()->boolean('seo.robots.enabled'), 404);

        return response($robots->toString(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
