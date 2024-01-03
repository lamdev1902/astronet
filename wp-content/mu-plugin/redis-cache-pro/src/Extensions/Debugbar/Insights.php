<?php

declare(strict_types=1);

use RedisCachePro\ObjectCaches\ObjectCache;

class RedisCachePro_DebugBar_Insights extends RedisCachePro_DebugBar_Panel
{
    /**
     * The object cache.
     *
     * @var \RedisCachePro\ObjectCaches\ObjectCache
     */
    protected $cache;

    /**
     * Create a new insights panel instance.
     *
     * @param  \RedisCachePro\ObjectCaches\ObjectCache  $cache
     */
    public function __construct(ObjectCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * The title of the panel.
     *
     * @return string
     */
    public function title()
    {
        return 'Object Cache';
    }

    /**
     * Whether the panel is visible.
     *
     * @return bool
     */
    public function is_visible()
    {
        return method_exists($this->cache, 'metrics');
    }

    /**
     * Render the panel.
     *
     * @return void
     */
    public function render()
    {
        $metrics = $this->cache->metrics();

        require __DIR__ . '/templates/insights.phtml';
    }
}
