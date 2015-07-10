<?php
/**
 * Cache Connection Interface
 * @package    Cache
 */
namespace  Fairy\Libs\Cache\CommonApi\Cache;

/**
 * Cache Connection Interface
 *
 * @package    Cache
 */
interface ConnectionInterface
{
    /**
     * Connect to the Cache
     *
     * @param   array $options
     *
     * @return  $this
     * @since   1.0.0
     */
    public function connect(array $options = array());

    /**
     * Close the Connection
     *
     * @return  $this
     * @since   1.0.0
     */
    public function close();
}
