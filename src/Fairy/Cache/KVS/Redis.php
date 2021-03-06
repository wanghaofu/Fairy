<?php
/**
 * Redis Adapter
 *
 */
namespace Fairy\Cache\KVS;

use Exception;
use Fairy\Cache\CacheItem;
use Fairy\Cache\CacheInterface;
use Fairy\Exception\RuntimeException;
use Predis\Client as RedisClient;

class Redis extends AbstractAdapter implements CacheInterface
{

    /**
     * Redis Database Connection
     *
     * @var object
     *
     * @since 1.0
     */
    protected $redis;

    /**
     * Constructor
     *
     * @param string $cache_handler
     *            $params 2 :
     *            is a redis conn obj
     *            
     *            $params 1:
     *            server=>array(
     *            'host'=>'',
     *            'port'=>'6379'
     *            ),
     *            'password'=>'',密码
     *            'index'=>'', //库
     * @since 1.0
     */
    public function __construct($options = null, $cache_enable = 1)
    {
        $this->cache_enabled = $cache_enable; //默认启动缓存
        $this->cache_handler = 'Redis';
        $this->connect($options);
    }

    /**
     * Connect to Cache
     *
     * @param array $options            
     *
     * @return $this
     * @since 1.0
     * @throws \CommonApi\Exception\RuntimeException
     */
    public function connect($options = null)
    {
        try {
            // 配置模式 通过参数初始化
            /**
             * server=>array(
             * 'host'=>'',
             * 'port'=>'6379'
             * ),
             * 'password'=>'',密码
             * 'index'=>'', //库
             */
            
            if (is_array($options)) {
                parent::connect($options);
                if (isset($options['redis'])) {
                    $this->redis = $options['redis'];
                } else {
                    $this->redis = new RedisClient(); //默认使用preids 
                }
                if (! isset( $options['server'] ) ) {
                    $options['servers'] = [
                        'host' => '127.0.0.1',
                        'port' => 6379
                    ];
                }
                foreach ((array) $options['servers'] as $server) {
                    $this->redis->connect($server['host'], $server['port']);
                }
                
                if (isset($options['password'])) {
                    $this->redis->auth($options['password']);
                }
                $this->redis->select($options['index'] ?  : 0);
            } elseif ($options instanceof RedisClient) {
                $this->redis = $options;
            }
        } catch (\RedisException $e) {
            throw new RuntimeException('Cache Redis Adapter: Redis Database dependency not passed into Connect');
        }
        
        return $this;
    }

    /**
     * Return cached value
     *
     * @param string $key            
     *
     * @return bool|CacheItem
     * @since 1.0
     * @throws \CommonApi\Exception\RuntimeException
     */
    public function get($key)
    {
        if ($this->cache_enabled == 0) {
            return false;
        }
        
        try {
            $value = $this->redis->get($key);
            
            $exists = (boolean) $value;
            
            return new CacheItem($key, $value, $exists);
        } catch (Exception $e) {
            throw new RuntimeException('Cache: Get Failed for Redis ' . $e->getMessage());
        }
    }

    /**
     * Create a cache entry
     *
     * @param null $key            
     * @param null $value            
     * @param integer $ttl
     *            (number of seconds)
     *            
     * @return $this
     * @since 1.0
     * @throws \CommonApi\Exception\RuntimeException
     */
    public function set($key, $value = null, $ttl = 0)
    {
        if ($this->cache_enabled == 0) {
            return false;
        }
        
        if ($key === null) {
            $key = serialize($value);
        }
        
        if ((int) $ttl == 0) {
            $ttl = (int) $this->cache_time;
        }
        
        $this->redis->set($key, $value);
        
        $results = $this->redis->expire($key, $ttl);
        
        if ($results === false) {
            throw new RuntimeException('Cache APC Adapter: Set failed for Key: ' . $key);
        }
        return $this;
    }

    /**
     * Remove cache for specified $key value
     *
     * @param string $key            
     *
     * @return object
     * @since 1.0
     * @throws \CommonApi\Exception\RuntimeException
     */
    public function remove($key = null)
    {
        $results = $this->redis->del($key);
        
        if ($results === false) {
            throw new RuntimeException('Cache: Remove cache entry failed');
        }
        
        return $this;
    }

    /**
     * Clear all cache
     *
     * @return $this
     * @since 1.0
     */
    public function clear()
    {
        $this->redis->flushdb();
        
        return $this;
    }
}
