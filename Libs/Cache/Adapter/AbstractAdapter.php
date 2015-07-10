<?php
/**
 * Abstract Adapter for Cache
 *
 */
namespace Fairy\Libs\Cache\Adapter;

use Fairy\Libs\Cache\CacheItem;
use Fairy\Libs\Cache\CommonApi\Cache\CacheInterface;

/**
 * Abstract Adapter Cache
 *
 */
abstract class AbstractAdapter implements CacheInterface
{
    /**
     * Cache Adapter
     *
     * @var    string
     * @since  1.0
     */
    protected $cache_handler;

    /**
     * Cache
     *
     * @var    bool
     * @since  1.0
     */
    protected $cache_enabled = false;

    /**
     * Cache Time in seconds
     *
     * @var    Integer
     * @since  1.0
     */
    protected $cache_time = 86400;

    /**
     * Constructor
     *
     * @param   array $options
     *
     * @since   1.0
     */
    public function __construct(array $options = array())
    {
        $this->connect($options);
    }

    /**
     * Connect to Cache
     *
     * @param   array $options
     *
     * @return  $this
     * @since   1.0
     */
    public function connect($options = array())
    {
        if (isset($options['cache_enabled'])) {
            $this->cache_enabled = (boolean)$options['cache_enabled'];
        }

        if (isset($options['cache_time'])) {
            $this->cache_time = $options['cache_time'];
        }

        if ((int)$this->cache_time === 0) {
            $this->cache_time = 86400;
        }

        return $this;
    }

    /**
     * Return cached value
     *
     * @param   string $key
     *
     * @return  CacheItem
     * @since   1.0
     */
    public function get($key)
    {
        if ($this->cache_enabled == 0) {
            return false;
        }

        list($value, $exists) = $this->createCacheItem($key);

        return new CacheItem($key, $value, $exists);
    }

    /**
     * Create a cache entry
     *
     * @param   string  $key
     * @param   mixed   $value
     * @param   integer $ttl (number of seconds)
     *
     * @return  $this
     * @since   1.0
     */
    abstract public function set($key, $value, $ttl = 0);

    /**
     * Remove cache for specified $key value
     *
     * @param   string $key
     *
     * @return  $this
     * @since   1.0
     */
    abstract public function remove($key = null);

    /**
     * Clear all cache
     *
     * @return  $this
     * @since   1.0
     */
    abstract public function clear();

    /**
     * Get File Contents
     *
     * @param   string $key
     *
     * @return  $this
     * @since   1.0
     */
    abstract protected function createCacheItem($key);
}
