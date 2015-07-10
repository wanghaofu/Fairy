<?php
namespace Fairy\Libs\Db;

/**
 * ****************************************************************
 * Name: 缓存类 ( 支持 Redis / 本地 IO 缓存 )
 * Author: 王涛 ( Tony )
 * Email: wanghaofu@163.com
 * QQ: 595900598
 * ****************************************************************
 */
class Cache
{

    var $db;
    // 数据库类
    var $dataList = array();

    var $filePrefix;

    var $fileExt = '.php';

    var $cachePath;

    var $data;

    var $syncRedisEnabled = false;
    // 是否使用 Redis 进行同步控制
    var $syncRedisPrefix = 'sync-';
    // 同步控制 Redis 键名前缀
    var $syncRedis = null;

    var $storeRedisEnabled = false;
    // 是否使用 Redis 进行数据存储
    var $storeRedisPrefix = 'store-';
    // 数据存储 Redis 键名前缀
    var $storeRedisTimePrefix = 'store-t-';
    // 数据时间戳存储 Redis 键名前缀
    var $storeRedis = null;

    var $cacheFileHeader = "<?php\n// Cache file, DO NOT modify me!\n";

    var $cacheFileFooter = "\n?>";

    /**
     * **** 初始化参数说明 ******
     * $cachePath: 缓存保存路径
     * $db: 数据库连接
     * $syncRedis: 同步控制所使用 Redis 配置
     * $storeRedis: 保存缓存数据所使用 Redis 配置
     * $filePrefix: 缓存文件前缀
     * $fileExt: 缓存文件后缀
     * **********************
     */
    public function Cache($cachePath = null, &$db = null, $syncRedis = null, $storeRedis = null, $filePrefix = null, $fileExt = null)
    {
        $this->cachePath = $cachePath;
        
        if ($filePrefix)
            $this->filePrefix = $filePrefix;
        if ($fileExt)
            $this->fileExt = $fileExt;
        
        if (is_array($syncRedis)) {
            $this->syncRedisEnabled = true;
            $this->syncRedis = $syncRedis;
        }
        if (is_array($storeRedis)) {
            $this->storeRedisEnabled = true;
            $this->storeRedis = $storeRedis;
        }
        
        $this->RedisSupport = class_exists('Redis'); // 是否支持 Redis
        
        $this->db = $db;
    }

  
#### 缓存配置准备 
    /**
     * **** 添加缓存项目 ******
     * $cacheSetting = array (
     * selectFields => 查询字段
     * selectFrom => 数据库查询
     * keyField => 作为缓存键的字段
     * subKeyField => 作为二级键名的字段 ( 指定此项将生成二维数组 )
     * valueField => 作为缓存值的字段, 缺省表示使用整个数组作为缓存值
     * makeArray => 是否生成数组
     * lifeTime => 缓存存活时间 (单位:秒) 设置为 0 永久使用缓存，设置为 -1 时则不使用缓存
     * cachePath => 缓存保存目录
     * first => 是否只缓存第一条记录
     * noSync => true OR false 是否禁止同步控制 ( 永远使用本地缓存 )
     * data => data (直接缓存的数据)
     * cache_type => (Redis/apc/file)
     * );
     * **********************
     */
    public function register($key, $cacheSetting = null)
    {
        $this->dataList[$key] = $cacheSetting;
    }
/**
 *   清理缓存项目
 */
    public function flushRegistData()
    {
        $this->dataList = array();
    }

    
 #### 获取缓存数据
    /**
     * **** 获取缓存内容 *****
     */
    public function getData($key, $db = null, $forceCache = null, $forceDb = null, $makeCache = null)
    {
        $forceCache = ! is_null($forceCache) ? $forceCache : false;
        $forceDb = ! is_null($forceDb) ? $forceDb : false;
        $makeCache = ! is_null($makeCache) ? $makeCache : true;
        
        if (! empty($this->data) && array_key_exists($key, $this->data) && $this->data[$key]) {
            return $this->data[$key];
        }
        
        if (! array_key_exists($key, $this->dataList)) {
            throw new Exception("$key key is not in datalist");
            return false;
        }
        
        $chkCacheLife = true; // 缓存存活时间
        $cacheFile = $this->getCacheFile($key); // 获取缓存文件
        $cacheData = $this->dataList[$key]; // 获取缓存信息
        
        $curTime = time();
        $gotCache = false;
        
        if ($gotCache) {
            throw Exception("gotCache is ture return");
            return false;
        }
        
        // 从缓存文件读取
        if ((empty($forceDb) || empty($cacheData['selectFrom'])) && $cacheData['lifeTime'] != - 1) {
            $cacheValue = $this->readCacheFile($cacheFile, $key, $cacheData['lifeTime']);
            if ($cacheValue !== false) {
                $this->data[$key] = $cacheValue;
                $gotCache = true;
            }
        }
        
        if (empty($gotCache) && (! is_array($this->data) || ! array_key_exists($key, $this->data) || is_null($this->data[$key])) || $forceDb) {
            if (! is_object($db)) {
                if (isset($cacheData['db']) && is_object($cacheData['db'])) {
                    $db = &$cacheData['db'];
                } else {
                    $db = &$this->db;
                }
            }
            
            if ($db && $cacheData['selectFrom']) {
                if (array_key_exists('selectFields', $cacheData) && $cacheData['selectFields']) {
                    $fields = $cacheData['selectFields'];
                } else {
                    $fields = '*';
                }
                
                $this->data[$key] = $db->select($cacheData['selectFrom'], '', '', 0, 0, $fields);
                if (array_key_exists('first', $this->dataList[$key]) && $this->dataList[$key]['first']) // 是否只缓存第一条记录
{
                    $this->data[$key] = @array_shift($this->data[$key]);
                    unset($cacheData['keyField']);
                }
                
                // if (is_null($this->data[$key]))
                // $this->data[$key] = false;
                $this->data[$key] = (! is_null($this->data[$key])) ? $this->data[$key] : false;
                $cacheData['keyField'] = array_key_exists('keyField', $cacheData) ? $cacheData['keyField'] : '';
                $cacheData['valueField'] = array_key_exists('valueField', $cacheData) ? $cacheData['valueField'] : '';
                
                $this->data[$key] = $this->makeCache($key, $this->data[$key], $cacheData['keyField'], $cacheData['valueField'], ! $forceDb && $makeCache && $cacheData['lifeTime'] != - 1);
            }
        }
        
        return ($this->data[$key]);
    }

    /**
     * **** 写入缓存文件 *****
     */
    public function makeCache($key, $data, $keyField = null, $valField = null, $write = true)
    {
        $keyIndex = - 1;
        if ($keyField || $valField) {
            while (list ($var, $val) = @each($data)) {
                if ($keyField) {
                    $keyIndex = $val[$keyField];
                } else {
                    $keyIndex ++;
                }
                
                $dataValue = ! $valField ? $val : $val[$valField];
                if (isset($this->dataList[$key]['subKeyField']) && $subKeyField = $this->dataList[$key]['subKeyField']) {
                    if ($this->dataList[$key]['makeArray'])
                        $NewData[$keyIndex][$val[$subKeyField]][] = $dataValue;
                    else
                        $NewData[$keyIndex][$val[$subKeyField]] = $dataValue;
                } else {
                    if (isset($this->dataList[$key]['makeArray']) && $this->dataList[$key]['makeArray'])
                        $NewData[$keyIndex][] = $dataValue;
                    else
                        $NewData[$keyIndex] = $dataValue;
                }
            }
            
            $data = isset($NewData) ? $NewData : '';
        }
        
        if ($write) {
            // 写入缓存文件
            $cachePath = $this->dataList[$key]['cachePath'] ? $this->dataList[$key]['cachePath'] : $this->cachePath;
            if (! $this->storeRedisEnabled && ! is_dir($cachePath)) {
                File::batch_mkdir('', $cachePath);
            }
            $this->_write($key, $data, $this->getCacheFile($key), $this->dataList[$key]['lifeTime']);
        }
        
        return $data;
    }

    /**
     * **** 写入缓存数据*****
     */
   private function _write($varName, $data, $path, $cacheLifeTime = null)
    {
        $noSync = $this->dataList[$varName]['noSync'];
        
        // 使用 Redis 存储数据
        if ($this->storeRedisEnabled) {
            if ($this->RedisSupport) {
                $Redis = &$this->RedisConnect($this->storeRedis);
                
                $md5Path = md5($path);
                $updateTime = time();
                
                $mcKey = $this->storeRedisPrefix . $md5Path;
                
                $flag = array_key_exists('flag', $this->storeRedis) ? $this->storeRedis['flag'] : Redis_COMPRESSED;
                $Redis->set($mcKey, $data, $flag, $cacheLifeTime);
                
                $mcTimeKey = $this->storeRedisTimePrefix . $md5Path;
                $Redis->set($mcTimeKey, $updateTime, $flag, $cacheLifeTime);
            }
        } else {
            $cacheData = '$' . $varName . ' = ' . @var_export($data, true);
            $updateTime = $this->_writeCacheFile($path, $cacheData, $cacheLifeTime);
        }
        
        if (! $noSync && $this->syncRedisEnabled) {
            if ($this->RedisSupport) {
                $syncRedis = &$this->RedisConnect($this->syncRedis);
                $mcKey = $this->syncRedisPrefix . $md5Path;
                $mcValue = $syncRedis->get($mcKey);
                if ($updateTime > 0 && $updateTime - $mcValue > $cacheLifeTime) {
                    $syncRedis->set($mcKey, $updateTime, $this->syncRedis['flag'], $this->syncRedis['expire']);
                }
            }
        }
    }

    /**
     * **** 清除缓存 ***** 刷新缓存
     */
    private function refreshCache($key)
    {
        if ($key && isset($this->dataList[$key]) && $this->dataList[$key]['lifeTime'] >= 0) {
            $noSync = $this->dataList[$key]['noSync'];
            
            
            $mcFile = $this->getCacheFile($key);
            if (! $noSync && $this->syncRedisEnabled && $this->RedisSupport) {
                $syncRedis = &$this->RedisConnect($this->syncRedis);
                $mcKey = $this->syncRedisPrefix . md5($mcFile);
                $syncRedis->delete($mcKey);
            }
            
            if ($this->storeRedisEnabled && $this->RedisSupport) {
                $storeRedis = &$this->RedisConnect($this->storeRedis);
                $mcKey = $this->storeRedisPrefix . md5($mcFile);
                $storeRedis->delete($mcKey);
            } else {
                $this->removeCacheFile($this->getCachePath($key), $key);
            }
            //! 依赖String 取消掉
            $regKey = String::to_reg_pattern($key);
            $regKey = "/$regKey/";
            $dataList = $this->data;
            
            while (list ($dataKey, $dataItem) = @each($dataList)) {
                if (preg_match($regKey, $dataKey)) {
                    unset($this->data[$dataKey]);
                }
            }
        }
        unset($this->data[$key]);
    }

   

   

    /**
     * **** 读取缓存文件内容 *****
     */
    private function readCacheFile($path, $key = null, $cacheLifeTime = null)
    {
        $now = time();
        $md5Path = md5($path);
        $cacheValue = false;
        $noSync = intval($this->dataList[$key]['noSync']);
        
        $fileLife = $now - @filemtime($path);
        if ($cacheLifeTime == 0 || $this->storeRedisEnabled || $fileLife <= $cacheLifeTime) {
            // 使用 Redis 进行同步控制
            if (! $noSync && $this->syncRedisEnabled) {
                if (! $this->RedisSupport) {
                    $cacheValue = false;
                } else {
                    $syncRedis = &$this->RedisConnect($this->syncRedis);
                    
                    $mcKey = $this->syncRedisPrefix . $md5Path;
                    $mcValue = $syncRedis->get($mcKey);
                    
                    if ($mcValue === false || $now - $mcValue > $cacheLifeTime) {
                        $cacheValue = false;
                    } elseif ($this->storeRedisEnabled) {
                        $storeRedis = &$this->RedisConnect($this->storeRedis);
                        $mcTimeKey = $this->storeRedisTimePrefix . $md5Path;
                        $updateTime = $storeRedis->get($mcTimeKey);
                        
                        if ($updateTime < $mcValue) {
                            $cacheValue = false;
                        }
                    }
                }
            }
            
            if (is_null($cacheValue)) {
                if ($this->storeRedisEnabled) {
                    if ($this->RedisSupport) {
                        $storeRedis = &$this->RedisConnect($this->storeRedis);
                        $mcKey = $this->storeRedisPrefix . $md5Path;
                        $mcValue = $storeRedis->get($mcKey);
                        $cacheValue = $mcValue;
                    } else {
                        $cacheValue = false;
                    }
                } else {
                    @include ($path);
                    if (isset($$key)) {
                        $cacheValue = $$key;
                    } else {
                        $cacheValue = false;
                    }
                }
            }
        } else {
            $cacheValue = false;
        }
        return $cacheValue;
    }

    /**
     * **** 关闭 cache / Redis 连接 *****
     */
    public function close()
    {
        global $__gRedisd;
        @reset($__gRedisd);
        while (list ($key, $item) = @each($__gRedisd)) {
            $__gRedisd[$key]->close();
            $__gRedisd[$key] = null;
        }
    }
    /**
     *
     *  * **** 写入文件缓存内容 *****
     * @param unknown $path
     * @param unknown $data
     * @param string $cacheLifeTime
     */
    private function _writeCacheFile($path, $data, $cacheLifeTime = null)
    {
        if ($this->cacheFileHeader) {
            $cacheFileHeader = $this->cacheFileHeader . "\n"; // . date ( "F j, Y, H:i" ) . "\n\n";
        }
        if ($this->cacheFileFooter) {
            $cacheFileFooter = $this->cacheFileFooter;
        }
        $data = $cacheFileHeader . $data . $cacheFileFooter;
    
        File::write_file($path, $data);
        @chmod($path, 0777);
        $updateTime = @filemtime($path);
        return $updateTime;
    }
    
    /**
     * **** 删除缓存文件 *****
     */
    private function removeCacheFile($path, $files)
    {
        $filesName = $this->filePrefix . $files . $this->fileExt;
        File::batch_delete($filesName, $path);
    }
    
    #####文件类缓存支持方法
    /**
     * **** 获取缓存文件路径 *****
     */
    private function getCacheFile($key)
    {
        $cachePath = $this->getCachePath($key);
        $cacheFile = $cachePath . '/' . $this->filePrefix . $key . $this->fileExt;
        return $cacheFile;
    }
    
    /**
     * **** 获取文件路 缓存配置路径 *****
     */
    private function getCachePath($key)
    {
        if (! isset($this->dataList[$key]['cachePath'])) {
            $this->dataList[$key]['cachePath'] = '';
        }
        $cachePath = $this->dataList[$key]['cachePath'] ? $this->dataList[$key]['cachePath'] : $this->cachePath;
        return $cachePath;
    }
}

interface CacheDb
{

    function set($key, $value);

    function get($key);

    function flush($key);

    function close();
}

class CacheRedis implements CacheDb
{

    var $conn;

    function __construct($conn)
    {
        $this->conn = $conn;
    }

    function set($key, $value)
    {}

    function close()
    {}

    function get($key)
    {}

    function flush($key)
    {}
}
/**
 * class CacheMemcache implements CacheDb{
 *
 * }
 *
 */
  class CacheFile implements CacheDb{
      /**
       * **** 获取缓存文件路径 *****
       */
      private function getCacheFile($key)
      {
          $cachePath = $this->getCachePath($key);
          $cacheFile = $cachePath . '/' . $this->filePrefix . $key . $this->fileExt;
          return $cacheFile;
      }
  }
 

?>
