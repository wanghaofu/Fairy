<?php
namespace Fairy\Sys;

use King\Core\CoreFactory;
use Fairy\Adapt\NoahDbFactory\DbFactory;
use Fairy\Libs\Db\Cache;

/**
 * 这个是系统入口 bridget 
 */

#exception
class Noah extends Sys {
	static $db = array ();
	static $cache;
	const CACHE_PATH = "";
	//主库
	public static function db() {
		self::$db = DbFactory::db(CoreFactory::instance()->pdo());
		return self::$db;
	}
	/**
	 * 数据库中配置的键 ！
	 * @param String $dbKey
	 * @return cacheData Object
	 */
	public static function cache()
	{
	    $db = self::db();
	    $cache = new cacheData ( self::CACHE_PATH,  $db,  $GLOBALS['syncMcConfig'], $GLOBALS['storeMcConfig'] );
	    return $cache;
	}
	
	public static function redis()
	{
	    
	}
	
	public static function get($key) {
	    return self::$cache->getData ( $key );
	}
	
	//out alis
	public static function ac($key) {
	}
	/**
	 * 增加查询配置
	 * @param unknown $key
	 * @param unknown $value
	 */
	public static function regist($key, $value) {
		self::$cache->addData ( $key, $value );
	}

	
	

}
