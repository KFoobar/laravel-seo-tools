<?php

declare(strict_types=1);

namespace KFoobar\LaravelSeoTools\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use KFoobar\LaravelSeoTools\Contracts\Sitemapable;

class Post extends Model implements Sitemapable
{
    protected $guarded = [];

    public function toSitemapTag(): string|array
    {
        return [
            'loc' => 'https://example.test/posts/'.$this->slug,
            'lastmod' => $this->updated_at,
        ];
    }
}
