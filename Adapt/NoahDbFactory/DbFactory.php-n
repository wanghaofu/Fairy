<?php
namespace King\Core\Fairy\Adapt\NoahDbFactory;

use King\Core\Fairy\Libs\Db\Db;

class DbFactory
{
    /**
     * 初始化全新的db为每一个数据库操作建立db *
     */
    public static function db(\PDO $pdo)
    {
        $db = new Db();
        $db->connByPdo($pdo);
        return $db;
    }
}

?>
