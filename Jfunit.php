<?php
namespace Jfunit;

/**
 * 框架入口文件
 */
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set('PRC');

define('JFUNITPHP_VERSION', '1.0');

// 固定常量
define("IN_SYS", true);
define('NOW_TIME', time());
defined("DEBUG")                or define("DEBUG", FALSE);//调试模式
defined('DS')                   or define('DS', DIRECTORY_SEPARATOR);
defined('ALLOW_REWRITE')        or define("ALLOW_REWRITE", false);
if (DEBUG) {
    define('DEBUG_STARTTIME', microtime(true));
}

//框架核心设置
define("JFUNIT_PATH", str_replace('\\', '/', dirname(__FILE__)). '/');
define("JFUNIT_LIB_PATH", JFUNIT_PATH . 'Lib/');
define("JFUNIT_CORE_PATH", JFUNIT_LIB_PATH . 'Core/');
define("JFUNIT_DRIVER_PATH", JFUNIT_LIB_PATH . 'Driver/');
define("JFUNIT_FUNC_PATH", JFUNIT_LIB_PATH . 'Function/');
define("JFUNIT_TOOL_PATH", JFUNIT_LIB_PATH . 'Tool/');
define("JFUNIT_ORG_PATH", JFUNIT_PATH . 'Org/');
define("JFUNIT_EXTEND_PATH", JFUNIT_PATH . 'Extend/');

//项目路径
define("ROOT_PATH", dirname(JFUNIT_PATH). '/');
define("DATA_PATH", ROOT_PATH . 'Data/');
//公共扩展
defined("EXTEND_PATH")          or define("EXTEND_PATH", ROOT_PATH . 'Extend/');

//网站入口所在路径
defined('SITE_PATH')            or define('SITE_PATH',ROOT_PATH.'Web/');


//应用设置
defined('APP_NAME') 		    or define('APP_NAME', 'Application');
defined('APP_PATH') 		    or define('APP_PATH', ROOT_PATH.APP_NAME.'/');//应用目录
defined('RUNTIME_PATH')    	    or define('RUNTIME_PATH', ROOT_PATH.'Runtime/');
defined('LOG_PATH')             or define('LOG_PATH', RUNTIME_PATH . 'Log/'); // 应用日志目录
defined('RUNTIME_PHP')    	    or define('RUNTIME_PHP',RUNTIME_PATH.'~Runtime.php');//编译文件
defined('RUNTIME_CARHE')    	or define('RUNTIME_CARHE',RUNTIME_PATH.'Cache/');//缓存目录

// 公共模块
defined("APP_COMMON_PATH")      or define("APP_COMMON_PATH", APP_PATH. 'Common/'); //应用公共目录
defined("APP_CONFIG_PATH")      or define("APP_CONFIG_PATH", APP_COMMON_PATH . 'Config/' ); //应用公共目录
defined("APP_MODEL_PATH")       or define("APP_MODEL_PATH",  APP_COMMON_PATH . 'Model/' ); //应用公共目录
defined("APP_CONTROLLER_PATH")  or define("APP_CONTROLLER_PATH",  APP_COMMON_PATH . 'Controller/'); //应用公共目录
defined("APP_LANG_PATH")        or define("APP_LANG_PATH", APP_COMMON_PATH . 'Lang/'); //应用语言包目录



// 环境常量
define('IS_CGI', strpos(PHP_SAPI, 'cgi') === 0 ? 1 : 0);
define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);
define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
define('IS_MAC', strstr(PHP_OS, 'Darwin') ? 1 : 0);


$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
defined('__HOST__') or define("__HOST__", $http_type. $host);
define ( 'HOST_NAME', strip_tags ( $_SERVER ['HTTP_HOST'] ) );

if (! defined ( '_PHP_FILE_' )) {
    if (IS_CGI) {
        // CGI/FASTCGI模式下
        $_temp = explode ( '.php', $_SERVER ['PHP_SELF'] );
        define ( '_PHP_FILE_', rtrim ( str_replace ( $_SERVER ['HTTP_HOST'], '', $_temp [0] . '.php' ), '/' ) );
    } else {
        define ( '_PHP_FILE_', rtrim ( $_SERVER ['SCRIPT_NAME'], '/' ) );
    }
}
if (! defined ( '__ROOT__' )) {
   //print_r('_PHP_FILE_:',_PHP_FILE_);
	//print_r(dirname ( _PHP_FILE_ ));
    $_root = rtrim ( dirname ( _PHP_FILE_ ), '/' );
    //$_root = dirname ( _PHP_FILE_ ).'/';
    define ( '__ROOT__', (($_root == '/' || $_root == '\\') ? '' : $_root).'/.' );
}

define ( '__SITE__', __HOST__.__ROOT__ );

//加载核心编译文件
if (!DEBUG and is_file(RUNTIME_PHP)) {
    require RUNTIME_PHP;
} else {
    require JFUNIT_PATH . 'Lib/Core/Boot.class.php';
    \Jfunit\Lib\Core\Boot::run();
}