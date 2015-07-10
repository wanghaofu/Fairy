<?php
/**
 * Cache Trait
 *
 * @package    Fairy
 */
namespace  Fairy\Libs\Cache\CommonApi\Cache;

/**
 * Cache Trait
 *
 */
trait CacheTrait
{
    /**
     * Function to Get Cache
     *
     * @var    callable
     * @since  1.0
     */
    protected $get_cache_callback;

    /**
     * Function to Set Cache
     *
     * @var    callable
     * @since  1.0
     */
    protected $set_cache_callback;

    /**
     * Function to Delete Cache, either by key or all
     *
     * @var    callable
     * @since  1.0
     */
    protected $delete_cache_callback;

    /**
     * Cache Type
     *
     * @var    string
     * @since  1.0
     */
    protected $cache_type;

    /**
     * Retrieve cache value
     *
     * @param   string $key
     *
     * @return  object CommonApi\Cache\CacheItemInterface
     * @since   1.0.0
     */
    public function getCache($key)
    {
        $cache_function = $this->get_cache_callback;

        return $cache_function($this->cache_type, array('key' => $key));
    }

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
    public function setCache($key, $value, $ttl = 0)
    {
        $cache_function = $this->set_cache_callback;

        return $cache_function($this->cache_type, array('key' => $key, 'value' => $value, 'ttl' => $ttl));
    }

    /**
     * Delete cache for specified $key value or expired cache
     *
     * @param   string $key
     *
     * @return  bool
     * @since   1.0.0
     */
    public function deleteCache($key)
    {
        $cache_function = $this->delete_cache_callback;

        return $cache_function($this->cache_type, array('key' => $key));
    }

    /**
     * Clear all cache
     *
     * @return  bool
     * @since   1.0.0
     */
    public function clearCache()
    {
        $cache_function = $this->delete_cache_callback;

        return $cache_function($this->cache_type, array());
    }
    <?php
/**
 * Cache Trait
 *
 * @package    Fairy
 * @package    Fairy
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace CommonApi\Cache;

/**
 * Cache Trait
 *
 * @package    Cache
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @since      1.0.0
 */
trait CacheTrait
{
    /**
     * Function to Get Cache
     *
     * @var    callable
     * @since  1.0
     */
    protected $get_cache_callback;

    /**
     * Function to Set Cache
     *
     * @var    callable
     * @since  1.0
     */
    protected $set_cache_callback;

    /**
     * Function to Delete Cache, either by key or all
     *
     * @var    callable
     * @since  1.0
     */
    protected $delete_cache_callback;

    /**
     * Cache Type
     *
     * @var    string
     * @since  1.0
     */
    protected $cache_type;

    /**
     * Retrieve cache value
     *
     * @param   string $key
     *
     * @return  object CommonApi\Cache\CacheItemInterface
     * @since   1.0.0
     */
    public function getCache($key)
    {
        $cache_function = $this->get_cache_callback;

        return $cache_function($this->cache_type, array('key' => $key));
    }

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
    public function setCache($key, $value, $ttl = 0)
    {
        $cache_function = $this->set_cache_callback;

        return $cache_function($this->cache_type, array('key' => $key, 'value' => $value, 'ttl' => $ttl));
    }

    /**
     * Delete cache for specified $key value or expired cache
     *
     * @param   string $key
     *
     * @return  bool
     * @since   1.0.0
     */
    public function deleteCache($key)
    {
        $cache_function = $this->delete_cache_callback;

        return $cache_function($this->cache_type, array('key' => $key));
    }

    /**
     * Clear all cache
     *
     * @return  bool
     * @since   1.0.0
     */
    public function clearCache()
    {
        $cache_function = $this->delete_cache_callback;

        return $cache_function($this->cache_type, array());
    }

    /**
     * Determine if Cache is activated for this type
     *
     * @return  boolean
     * @since   1.0
     */
    public function useCache()
    {
        if (is_callable($this->get_cache_callback)
            && is_callable($this->set_cache_callback)
            && is_callable($this->delete_cache_callback)) {
            return true;
        }

        return false;
    }
}
