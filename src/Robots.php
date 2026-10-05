<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools;

use InvalidArgumentException;
use Stringable;

/**
 * Renders robots.txt from package config.
 */
class Robots implements Stringable
{
    /**
     * Render robots.txt from config.
     */
    public function toString(): string
    {
        $blocks = collect(config()->array('seo.robots.user_agents'))
            ->map(function (mixed $rules, string $agent): string {
                if (! is_array($rules)) {
                    throw new InvalidArgumentException("Robots rules for [{$agent}] must be an array.");
                }

                return collect(["User-agent: {$agent}"])
                    ->concat(collect($rules['allow'] ?? [])->map(fn (string $path): string => "Allow: {$path}"))
                    ->concat(collect($rules['disallow'] ?? [])->map(fn (string $path): string => "Disallow: {$path}"))
                    ->implode("\n");
            });

        if (config()->boolean('seo.robots.sitemap')) {
            $blocks->push('Sitemap: '.url(config()->string('seo.sitemap.route', '/sitemap.xml')));
        }

        return $blocks->implode("\n\n")."\n";
    }

    /**
     * Get the robots.txt contents.
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
