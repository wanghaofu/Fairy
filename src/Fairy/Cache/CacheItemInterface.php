<?php
/**
 * Cache Item Interface
 *
 * @package    Cache
 */
namespace Fairy\Cache;

/**
 * Cache Item Interface
 *
 * @package    Cache
 * @since      0.1
 */
interface CacheItemInterface
{
    /**
     * Get the Key associated with this Cache Item
     *
     * @return  string  $key
     * @since   1.0.0
     */
    public function getKey();

    /**
     * Get the Value associated with this Cache Item
     *
     * @return  mixed  $key
     * @since   1.0.0
     */
    public function getValue();

    /**
     * True or false value as to whether or not the item exists in current cache
     *
     * @return  boolean
     * @since   1.0.0
     */
    public function isHit();
}
