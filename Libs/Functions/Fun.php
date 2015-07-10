<?php
namespace Fairy\Functions;
class Fun
{
    static function de($str, $track = 0, $exit = false)
    {
        global $debugnum;
        $debugnum ++;
        
        $debugInfo = debug_backtrace();
        
        echo "<div style='font-size:14px;background-color:#f1f6f7'>";
        echo "<div style='font-size:16px;background-color:dfe5e6;color:#001eff;font-weight:bold'>";
        foreach ($debugInfo as $key => $value) {
            if ($key == 0) {
                echo "*** <span style='font-size:10px'>{$debugnum}</span><span style='font-weight:normal'> {$value['file']}</span>  <span style='font-size:20;color:red'> {$value['line']} </span>(row) </br>";
            } else {
                if ($track) {
                    echo "&nbsp;&nbsp;<span style='font-size:12px;'>>> include in file:{$value['file']} line:{$value['line']} row </br></span>";
                } else {
                    break;
                }
            }
        }
        
        echo "</div>";
        echo '<pre>';
        if (! isset($str)) {
            echo 'the vars in not set!\n\r';
        } elseif (is_numeric($str)) {
            echo $str;
        } elseif (is_object($str)) {
            print_r($str);
        } elseif (is_string($str)) {
            echo $str;
        } elseif (is_array($str)) {
            print_r($str);
        } elseif (is_null($str)) {
            echo 'the vars is null\n\r ';
        } elseif (is_bool($str)) {
            echo $str;
        }
        echo '</pre>';
        echo "</div>";
        if ($exit) {
            exit();
        }
    }
}
