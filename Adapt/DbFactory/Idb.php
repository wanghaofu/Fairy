<?php
namespace\King\Core\Fairy\Adapt\DbFactory;
/**
 * index db 扩展规则 ，根据传入的sql语句  通过配置文件，以及切分的key值 以及切分算法最后生成的idx 生成最后的sql语句
 * @author wangtao
 *
 */
class Idb  {
	var $dbIdx; // 分库序号
	var $tableIdx; // 分表序号
	var $tableName;
	var $where;
	var $data;
	
	var $db; //db 连接对象
	
	var $db_split_call_fun;
	
	var $split_value;
	
	var $table_split_call_fun = array ();
	/**
	 * 反回sql语句？！
	 * @param unknown $dbConfig
	 * @param string $split_value
	 */
	function __construct($dbConfig, $split_value = null) {
	    $this->dbConfig = $dbConfig;
	    $this->split_value = $split_value;
	
	    $this->db = DbFactory::init_db_node ( $dbConfig );
	    $this->db->setSplitValue($split_value) ;
	    $this->db->database = $dbConfig['database'];
	    $this->db->setSqlCallFun ( array (
	        $this,
	        'nodeQuery'
	    ) );
	}
	
	// this variable for db use from the statement
	public function setSplitValue($split_value)
	{
	    $this->split_value = $split_value;
	}
	
	

	/**
	 * //获取db 入口 
	 * @param unknown $key
	 * @param string $split_value
	 * @throws Exception
	 */
	static function getDb($key, $split_value = null) {
		if (empty ( $key )) {
			throw new Exception ( 'db key is not null' );
		}
		$dbConfig = DefaultDbConfig::getDbConfig ( $key, $split_value );
		$db = new Static ( $dbConfig, $split_value );
		
		$db = $db->_getDb ();
		return $db;
	}
	
	
	
	/**
	 * 提供给回调用 why not have the sql give
	 * @param unknown $sql
	 * @return mixed
	 */
	public function nodeQuery($sql) {
		$tableIdx = $this->getTableIdx ( $sql );
		$dbName = $this->db->database;  //根据配置生成扩展规则
		
		if ($tableIdx) {
			$tableIdx = Split::LINK_TAG . $tableIdx; //生成表扩展
		} else {
			$tableIdx = '';
		}
		
		$pattern = '/(?![\'\"][\w\s]*)(update|into|from)\s+([\w]+)\s*(?![\w\s]*[\'\"])/usi';
		$replacement = "\$1 {$dbName}.\${2}{$tableIdx}  ";
		$sql = preg_replace ( $pattern, $replacement, $sql );
		
		// 返回默认值
		return $sql;
	}
	
	/**
	 * 反回节点索引 where use this 
	 * @param unknown $sql
	 * @throws Exception
	 * @return boolean|mixed
	 */
	private function getTableIdx($sql) {
		$tableCallFun = $this->getTableCallFun ( $sql );
		if (empty ( $tableCallFun )) {
			return false;
		}
		
		if (empty (  $this->db->split_value )) {
			throw new Exception ( 'Table_split_value is null please set check init method !' );
		}
		$tableIdx = call_user_func ( $tableCallFun, $this->db->split_value );
		
		return $tableIdx;
	}
	
	private function getTableCallFun($sql) {
		if (array_key_exists ( 'data_split', $this->dbConfig )) {
			$callFunInfo = $this->dbConfig ['data_split'];
		} else {
			return false;
		}
		
		if (array_key_exists ( 'table_split_call_fun', $callFunInfo )) {
			$table_split_call_fun_arr = $callFunInfo ['table_split_call_fun'];
		} else {
			return false;
		}
		$tableName = $this->getTableName ( $sql );
		if ($tableName && array_key_exists ( $tableName, $table_split_call_fun_arr )) {
			$tableCallFun = $table_split_call_fun_arr [$tableName];
		} elseif (array_key_exists ( 'default', $table_split_call_fun_arr )) {
			$tableCallFun = $table_split_call_fun_arr ['default'];
		} else {
			$tableCallFun = '';
		}
		$this->table_split_call_fun = $tableCallFun;
		return $tableCallFun;
	
	}
	private function getTableName($sql) {
		$pattern = '/(?![\'\"][\w\s]*)(?:update|into|from)\s+([\w]+)\s*(?![\w\s]*[\'\"])/usi';
		// $replacement = "\$1 {$dbName}.\${2}{$tableIdx} ";
		if (preg_match ( $pattern, $sql, $matches )) {
			return $matches [1];
		} else {
			return false;
		}
	}
}
