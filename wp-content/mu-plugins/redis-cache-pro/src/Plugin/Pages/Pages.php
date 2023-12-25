<?php
/**
 * Copyright © 2019-2023 Rhubarb Tech Inc. All Rights Reserved.
 *
 * The Object Cache Pro Software and its related materials are property and confidential
 * information of Rhubarb Tech Inc. Any reproduction, use, distribution, or exploitation
 * of the Object Cache Pro Software and its related materials, in whole or in part,
 * is strictly forbidden unless prior permission is obtained from Rhubarb Tech Inc.
 *
 * In addition, any reproduction, use, distribution, or exploitation of the Object Cache Pro
 * Software and its related materials, in whole or in part, is subject to the End-User License
 * Agreement accessible in the included `LICENSE` file, or at: https://objectcache.pro/eula
 */

declare(strict_types=1);

namespace RedisCachePro\Plugin\Pages;

use Traversable;
use ArrayIterator;
use IteratorAggregate;

use RedisCachePro\Plugin;

/**
 * @implements \IteratorAggregate<\RedisCachePro\Plugin\Pages\Page>
 */
class Pages implements IteratorAggregate
{
    /**
     * The page instances.
     *
     * @var array<\RedisCachePro\Plugin\Pages\Page>
     */
    protected $pages;

    /**
     * Creates a new instance.
     *
     * @param  \RedisCachePro\Plugin  $plugin
     * @return void
     */
    public function __construct(Plugin $plugin)
    {
        $this->pages = [
            new Dashboard($plugin),
            new Updates($plugin),
            new Tools($plugin),
        ];

        if (! $this->current()) {
            $_GET['subpage'] = 'dashboard';
        }

        foreach ($this->pages as $page) {
            $page->boot();
        }
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator<int, \RedisCachePro\Plugin\Pages\Page>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->pages);
    }

    /**
     * Returns the current page, if available.
     *
     * @return \RedisCachePro\Plugin\Pages\Page|false
     */
    public function current()
    {
        $pages = array_filter($this->pages, static function ($page) {
            return $page->isCurrent();
        });

        return reset($pages);
    }
}
