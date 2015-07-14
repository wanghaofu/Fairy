<?php
/**
 * Cache Interface
 *
 * @package    Cache
 */
namespace Fairy\Cache;

/**
 * Cache Interface
 *
 * @package    Cache
 */
interface CacheInterface
{
    /**
     * Retrieve cache value
     *
     * @param   string $key
     *
     * @return  bool
     * @since   1.0.0
     */
    public function get($key);

    /**
     * Persist data in cache
     *
     * @param   string  $key
     * @param   mixed   $value
     * @param   integer $ttl (number of seconds)
     *
     * @return  bool
     * @since   1.0.0
     */
    public function set($key, $value, $ttl = 0);

    /**
     * Delete cache for specified $key value or expired cache
     *
     * @param   string $key
     *
     * @return  bool
     * @since   1.0.0
     */
    public function remove($key);

    /**
     * Clear all cache
     *
     * @return  bool
     * @since   1.0.0
     */
    public function clear();
}
