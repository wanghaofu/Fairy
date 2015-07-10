<?php
namespace Fairy\Libs\Db;
// namespace data;
/**
 * ****************************************************************
 * Name: 数据库操作类 ( 基于 PDO )
 * Author: 王涛 ( Tony )
 * Email: wanghaofu@163.com
 * QQ: 595900598
 *
 * ****************************************************************
 */

/*
 * 示例 : $db = new db ( 'mysql:host=127.0.0.1;port=3306;', 'root', 'password',
 * 'database_name', true, 'utf8' );
 */

class Db
{

    var $id;

    var $dsn;

    var $user;

    var $password;

    var $database;

    var $charSet;

    var $ignoreError;

    var $attributes;

    var $conn;
    // 数据库连接
    var $queryCount;
    // 查询次数
    var $affectedRows;
    // 影响行数 ( 每次 Query 后改变 )
    var $debug;
    // 调试模式
    var $debugLineSplit = '<br />';

    var $charSplit = '`';

    var $transaction = false;

    var $inTransaction = false;

    var $readOnly = false;
    // 是否只读
    var $startTime;

    var $endTime;

    var $statSql;

    var $statSqlLimit = 10;
    // 历史 Query 条数
    var $traceEnabled = false;

    var $tracer = null;

    var $dbConfigs = array();
    // 预备数据库配置
    var $dbIdxArray = array();

    var $multiFlag = false;

    var $split_value;

    var $dbIdx = null;

    var $tableIdx = null;

    var $sqlCallFun = array();

    var $writeOperations = array(
        'ALTER ',
        'CREATE ',
        'DROP ',
        'DELETE ',
        'INSERT ',
        'REPLACE ',
        'TRUNCATE ',
        'UPDATE '
    );
    // 定义写操作
    static $iquery = array();

    var $nodeQuery = false;

    static $dbNodeConns = array();
    
   
  
    //构造默认 
    public function __construct($dsn = null, $user = '', $password = '', $database = null, $autoCommit = false, $charSet = null, $persistent = false, $ignoreError = false, $timeout = 10)
    {
      
        if (is_array($dsn)) {
            $this->db_multi($dsn);
        }elseif(!empty($dsn)) {
            $this->dsn = $dsn;
            $this->user = $user;
            $this->password = $password;
            $this->database = $database;
            $this->charSet = $charSet;
            $this->ignoreError = $ignoreError;
        
            $this->id = md5($this->dsn . $this->user . $this->database);
        
            $this->attributes = array(
                PDO::ATTR_AUTOCOMMIT => $autoCommit,
                PDO::ATTR_PERSISTENT => $persistent,
                PDO::ATTR_TIMEOUT => $timeout
            );
        
            $this->conn = null;
            $this->queryCount = 0;
            $this->startTime = '';
            $this->statSql = array();
            $this->endTime = '';
        }
    }
    
    public function connByPdo(\PDO $pdo=null)
    {
        $this->conn = null;
        $this->queryCount = 0;
        $this->startTime = '';
        $this->statSql = array();
        $this->endTime = '';
        // 如果外部传入连接则直接给支
        if ($pdo instanceof \PDO  && !empty( $pdo )) {
            $this->conn = $pdo;
        }else{
            throw new Exception(' Is not Pdo object.');
        }
    }
    
    public function setDatabase($database)
    {
        $this->database = $database;
    }
    
    // 初始化随极数据库配置
    public function db_multi($dbConfigs = null)
    {
        $this->multiFlag = true;
        
        if (is_array($dbConfigs)) {
            $this->dbConfigs = $dbConfigs;
            $this->dbIdxArray = array_keys($this->dbConfigs);
            shuffle($this->dbIdxArray);
        }
        
        $dbIdxSession = $_COOKIE['db_idx_session']; // 直接获取用户位置
        if (is_numeric($dbIdxSession)) //
{
            $this->dbIdx = intval($dbIdxSession);
        } else {
            $this->dbIdx = array_shift($this->dbIdxArray);
        }
        
        $dbConfig = $this->dbConfigs[$this->dbIdx];
        $this->db($dbConfig['dsn'], $dbConfig['user'], $dbConfig['password'], $dbConfig['database'], $dbConfig['auto_commit'], $dbConfig['charset'], $dbConfig['persistent']);
    }
    
    // 连接数据库
    public function connect()
    {
      
        
        $dsn = $this->dsn;
        $connKey = self::getConnKey($dsn);
        
        // 分布式连接
        if ($this->nodeQuery == true) {
            if (array_key_exists($connKey, self::$dbNodeConns)) {
                $conn = self::$dbNodeConns[$connKey];
                $this->conn = &$conn;
            }
        }
        
        $conn = &$this->conn;
        if (! $conn) {
            try {
                $conn = new \PDO($dsn, $this->user, $this->password, $this->attributes);
                self::$dbNodeConns[$connKey] = $conn;
                if ($conn && $this->multiFlag)
                    setcookie('db_idx_session', $this->dbIdx); // 保存酷定位用于分布式
            } catch (PDOException $e) {
                if (count($this->dbIdxArray) > 0 && $this->multiFlag) {
                    setcookie('db_idx_session', null);
                    $_COOKIE['db_idx_session'] = null;
                    $this->db_multi();
                    $this->connect();
                } elseif (! $this->ignoreError) {
                    echo ("<ERROR><div style='padding:20px;'>服务器忙，请稍后访问！</div>");
                    throw ($e);
                }
            }
            
            $this->useDatabase($this->database, $conn);
            if ($this->charSet && $conn) {
                $conn->query("set names '{$this->charSet}';");
            }
            if ($this->transaction) {
                $this->begin();
            }
        }
        return $conn;
    }

  
    // 使用数据库选择库
    public function useDatabase($database, $conn = null)
    {
        if (! $conn)
            $conn = $this->conn;
        if (! $conn)
            return false;
        if ($database) {
            $conn->query("USE $database;");
            return ! $conn->errorCode() ? true : false;
        }
    }
  ######事务处理代码 
    // 开始事务
    public function begin()
    {
        if (! $this->conn)
            return false;
        if (! $this->inTransaction) {
            $this->conn->beginTransaction();
            $this->inTransaction = true;
        }
    }
    
    // 提交事务
    public function commit()
    {
        if (! $this->conn)
            return false;
        if ($this->inTransaction) {
            $this->conn->commit();
            $this->inTransaction = false;
            $this->begin();
        }
        if ($this->debug) {
            $this->showDebug();
        }
    }
    
    // 回滚事务
    function rollBack()
    {
        if (! $this->conn)
            return false;
        if ($this->inTransaction) {
            $this->conn->rollBack();
            $this->inTransaction = false;
            $this->begin();
        }
    }
 ######查询相关代码 
 
    // 提交数据库查询 
    public function query($strSql)
    {
        $strSql = $this->execSqlCallBack($strSql);
        $strSql = trim($strSql);
        
        if ($this->debug) {
//             File::debug_log($strSql);
        }
        //只做操作
        if ($this->readOnly) {
            $writeOperations = $this->writeOperations;
            while (list ($key, $item) = @each($writeOperations)) {
                if (preg_match("/^$item/is", $strSql)) {
                    $this->affectedRows = 1;
                    return true;
                }
            }
        }
        
        $conn = $this->connect();
        if (! $conn)
            return false;
        
        $this->queryCount ++; // 查询次数增加
        $this->affectedRows = 0; // 重置影响行数
        
        if (empty($this->startTime)) {
            $this->startTime = array_sum(explode(' ', microtime()));
        }
        
        array_push($this->statSql, $strSql);
        if (count($this->statSql) > $this->statSqlLimit)
            array_shift($this->statSql);
        try {
            $res = $conn->query($strSql);
        } catch (PDOException $e) {
            throw new Exception("SQL_ERROR: $strSql !");
//             File::debug_log($e);
            
            if (! $this->ignoreError) {
                echo ("<ERROR><div style='padding:20px;'>服务器忙，请稍后访问！</div>");
            }
        }
        
        if ($conn->errorCode() > 0)
            $res = false;
        
        if (! $res) {
            $errorInfo = $conn->errorInfo();
            throw new Exception($errorInfo[2]);
            // File::debug_log ( $errorInfo [2] );
            if ($this->debug) {
                echo ('Error: ' . $errorInfo[2] . '<br /><br />SQL: ' . $strSql);
            }
        } else {
            $this->affectedRows += intval($res->rowCount());
        }
        return $res;
    }

 

   
    // 执行sql delete updata insert 返回影响行数
    public function exec($sql, $commit = true)
    {
        $sql = $this->execSqlCallBack($sql);
        if (! $this->conn) {
            $this->conn = $this->connect();
        }
        $this->statSql[] = $sql;
        $res = $this->conn->exec($sql);
        if ($commit == true) {
            $this->commit();
        }
        return $res;
    }

    public function getRow($sql)
    {
        $sql .= ' limit 1';
        $stmt = $this->query($sql);
        if (! $stmt)
            return false;
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result;
    }
    // 获取多行
    public function getRows($sql)
    {
        $stmt = $this->query($sql);
        if (! $stmt) {
            return false;
        }
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }
    
    // 统计符合条件的记录条数
    public function count($dbTable, $condition = '', $fields = '*')
    {
        if ($condition != '') {
            $condition = "WHERE $condition";
        }
        $strSql = " SELECT COUNT($fields) AS count_records FROM $dbTable $condition";
        $res = $this->query($strSql);
        if ($res) {
            $countRecords = $res->fetch(\PDO::FETCH_ASSOC);
            return $countRecords['count_records'];
        } else {
            return false;
        }
    }
    
    // 最后插入 ID 反回最后插入的id
    public function lastInsertId()
    {
        if (! $this->conn)
            return false;
        $lastInsertId = intval($this->conn->lastInsertId());
        return $lastInsertId;
    }
    
    // 关闭连接
    public function close()
    {
        if ($this->traceEnabled && $this->tracer) {
            $this->tracer->close();
        }
        $this->inTransaction = false;
        $this->conn = null;
    }
  ##### 分表分库策略相关函数  
  /**
   * 设置切分主键值
   * @param unknown $value
   */
    public function setSplitValue($value)
    {
        $this->split_value = $value;
    }
    /**
     * $callBackFunc array(obj,method)
     * Enter description here .
     *
     * @param unknown_type $callBackFun
     */
    public function setSqlCallFun($callBackFun)
    {
        $this->sqlCallFun = $callBackFun;
    }
    
    
    
    /**
     * 设置节点 部署服务器一个独立的连接实例
     * @param string $tag
     */
    public function setNode($tag = true)
    {
        $this->nodeQuery = $tag;
    }
    
    private static function getConnKey($dsn)
    {
        $connkey = md5($dsn);
        return $connkey;
    }
   
    /**
     *
     *
     * 执行sql回调 用与数据的分库分表策略
     *
     * @param unknown_type $sql
     */
    function execSqlCallBack($sql)
    {
        //如果存在则处理不存在 则直接执行
        if ($this->sqlCallFun) {
            $sql = call_user_func($this->sqlCallFun, $sql);
        }
        return $sql;
    }
    
    
/*** 错误调试相关代码    *****/
    // 获取错误代码
    function errorCode($conn = null)
    {
        if (! $conn)
            $conn = $this->conn;
        if (! $conn)
            return false;
        $errorCode = intval($conn->errorCode());
        return $errorCode;
    }
     
    
    
    // 获取数据执行错误信息
    public function getErrorMsg($conn = null)
    {
        if (! $conn)
            $conn = $this->conn;
        if (! $conn)
            return false;
        $errorInfo = $conn->errorInfo();
        $errorMsg = $errorInfo[2];
        return $errorMsg;
    }
    
    // 输出调试 SQL
    public function showDebug()
    {
        $this->endTime = array_sum(explode(' ', microtime()));
        while (list ($key, $item) = @each($this->statSql)) {
            echo $item . $this->debugLineSplit;
        }
        echo '<br />Query Time: ' . ($this->endTime - $this->startTime) . $this->debugLineSplit;
        if ($this->conn && $this->conn->errorCode() > 0) {
            $errorInfo = $this->conn->errorInfo();
            echo 'Error: ' . $this->conn->errorCode() . ' - ' . $errorInfo[2] . $this->debugLineSplit;
        }
        echo $this->debugLineSplit;
    
        $this->startTime = '';
        $this->endTime = '';
        $this->statSql = array();
    }
   
}
?>
