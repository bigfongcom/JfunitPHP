<?php
namespace Jfunit\Lib\Core;

class Boot
{

    public static function run()
    {
        if (DEBUG) {
            self::_create_dir(); // 创建目录
            self::_import_file(); // 引用文件
        }else {
            error_reporting(0); // 不报告错误
            require RUNTIME_PHP;
        }
        
        //self::_init();
        \Application::run();
    }

    private static function _create_dir()
    {
        $arr = array(
            APP_PATH,
            RUNTIME_PATH,
            RUNTIME_PATH.'Tpl/',
            RUNTIME_PATH.'Log/',
            RUNTIME_PATH.'Fcache/',
            DATA_PATH,
            DATA_PATH.'Session/',
            DATA_PATH.'Backup/',
            APP_COMMON_PATH,
            APP_CONFIG_PATH,
            APP_MODEL_PATH,
            APP_CONTROLLER_PATH,
            APP_LANG_PATH
        )
        ;
        
        foreach ($arr as $v) {
            is_dir($v) || mkdir($v, 0777, true);
        }
    }

    private static function _import_file()
    {
        $fileArr = array(
            JFUNIT_FUNC_PATH . 'functions.php',
            JFUNIT_CORE_PATH . 'Application.class.php',
        );
        $str = '';
        foreach ($fileArr as $v) {
            $str .= trim(substr(file_get_contents($v), 5, - 2)) . "\r\n";
            require_once $v;
        }
        
        file_put_contents(RUNTIME_PHP, compress("<?php\r\n" . $str."\r\n"."Application::run();")) || die('access not allow');
    }

    /* private static function _init()
    {
        //默认配置文件
        C(include JFUNIT_PATH . 'config/config.php');
        //公用配置文件
        file_exists(APP_PATH . 'config.php') && C(include APP_PATH . 'config.php');
        //数据库
        file_exists(APP_PATH . 'database.php') && C(include APP_PATH . 'database.php');
        
        //系统语言包
        file_exists(APP_COMMON_PATH . 'Lang/zh-cn.php') && L(include APP_COMMON_PATH . 'Lang/zh-cn.php');
        
        // 设置Debug模式
        if (DEBUG) {
            error_reporting(E_ALL); // 输出除了注意的所有错误报告
            include_once  JFUNIT_TOOL_PATH . 'Debug.class.php'; // 包含debug类
           //\Jfunit\Tool\Debug::start(); //开启脚本计算时间
           set_error_handler(array(
                "\Jfunit\Lib\Tool\Debug",
                'Catcher'
            ));  // 设置捕获系统异常
        } else {
            ini_set('display_errors', 'Off'); // 屏蔽错误输出
            if (C('log_record')) {
                ini_set('log_errors', 'On'); // 开启错误日志，将错误报告写入到日志中
                ini_set('error_log', RUNTIME_PATH . 'error_log'); // 指定错误日志文件
            }
        }
        
        if (C('session_redis_on')) {
            if (! extension_loaded('redis')) {
                exit('服务器不支持redis扩展');
            }
            ini_set('session.save_handler', 'Redis');
            ini_set('session.save_path', 'tcp://127.0.0.1:6379');
        } else {
            session_save_path(DATA_PATH . DS . 'session');
            session_cache_limiter('private, must-revalidate');
        }
        
         session_start();
    } */
}

//Jfunit::run();



