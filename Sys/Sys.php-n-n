<?php 
namespace Fairy\Sys;

/**
 * 
 * @author tony
 * 通用入口 文件 
 */

class sys
{
    // }}}
    // ----[ Class Constants ]----------------------------------------
    // {{{ SEPARATOR
    const SEPARATOR_PHP_NS = '\\';

    const SEPARATOR_SCHEMA = '#';

    const SEPARATOR_DIRECTORY = '/';

    const SEPARATOR_VAR_NAME_NEST = '.';

    const SEPARATOR_SECTION_NAME = ':';
    
    // }}}
    static $objects = array();
    // out alis
    public static function ac($moduleName)
    {}
    public static function Exception($errorId)
    {}

   /**todo 
   * @description 用于实例划对象 支持最多3个类参数
   * @param class $className
   * @param void* $arguments
   * @example Sys::xx_xx_xx_xx_xx()->xx(); 会被专程Sys::\xx\xx\xx\xx\()->xx();
   */
    public static function __callStatic($className, $arguments)
    {
        $className= str_replace( '_' , '\\' , $className );
        $className =  '\\'. $className;
            
        $obj_key = null;
        if ($arguments) {
            $obj_arg = implode('_', $arguments);
            $obj_key = $className . '#' . $obj_arg;
        } else {
            $obj_key = $className;
        }
        
        if (is_object(self::$objects[$obj_key])) {
            return self::$objects[$obj_key];
        } elseif (class_exists($className)) {
            $arg_num = count($arguments);
            switch ($arg_num) {
                case 1:
                    self::$objects[$obj_key] = new $className($arguments[0]);
                    break;
                case 2:
                    self::$objects[$obj_key] = new $className($arguments[0], $arguments[1]);
                    break;
                case 3:
                    self::$objects[$obj_key] = new $className($arguments[0], $arguments[1], $arguments[2]);
                    break;
                default:
                    self::$objects[$obj_key] = new $className();
            }
        }
        // is_object
        return self::$objects[$obj_key];
    }
}


