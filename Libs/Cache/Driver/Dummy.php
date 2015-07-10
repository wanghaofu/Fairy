<?php
/**
 * Dummy Cache
 *
 */
namespace Fairy\Libs\Cache\Driver;

use Fairy\Libs\Cache\CacheItem;
use Fairy\Libs\Cache\CommonApi\Cache\CacheInterface;
use Fairy\Libs\Cache\CommonApi\Exception\RuntimeException;

class Dummy extends AbstractAdapter implements CacheInterface
{
    /**
     * @covers  Fairy\Cache\Adapter\Dummy::__construct
     * @covers  Fairy\Cache\Adapter\Dummy::__construct
     * @covers  Fairy\Cache\Adapter\Dummy::connect
     * @covers  Fairy\Cache\Adapter\Dummy::connect
     * @covers  Fairy\Cache\Adapter\Dummy::get
     * @covers  Fairy\Cache\Adapter\Dummy::get
     * @covers  Fairy\Cache\Adapter\Dummy::set
     * @covers  Fairy\Cache\Adapter\Dummy::set
     * @covers  Fairy\Cache\Adapter\Dummy::remove
     * @covers  Fairy\Cache\Adapter\Dummy::remove
     * @covers  Fairy\Cache\Adapter\Dummy::clear
     * @covers  Fairy\Cache\Adapter\Dummy::clear
     *
     * @since   1.0
     */
    public function __construct(array $options = array())
    {
        $this->cache_handler = 'Dummy';

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
        parent::connect($options);

        return $this;
    }

    /**
     * Return cached value
     *
     * @param   string $key
     *
     * @return  bool|CacheItem
     * @since   1.0
     */
    public function get($key)
    {
        return $this;
    }

    /**
     * Create a cache entry
     *
     * @param   null    $key
     * @param   null    $value
     * @param   integer $ttl (number of seconds)
     *
     * @return  $this
     * @since   1.0
     */
    public function set($key, $value = null, $ttl = 0)
    {
        return $this;
    }

    /**
     * Remove cache for specified $key value
     *
     * @param string $key
     *
     * @return  object
     * @since   1.0
     */
    public function remove($key = null)
    {
        return $this;
    }

    /**
     * Clear all cache
     *
     * @return  $this
     * @since   1.0
     */
    public function clear()
    {
        return $this;
    }
}
