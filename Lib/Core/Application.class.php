<?php
//namespace Jfunit\Core;
        
class Application
{

    static function run()
    {
        // 自动载入
        spl_autoload_register(array(
            __CLASS__,
            '_autoload'
        ));
        self::_init();
        //解析路由
        self::_set_parseUrl();
        // 实例化应用
        self::_app_run();
    }
    
    
    /* private static function _import_file()
    {
        $fileArr = array(
            //APPLICATION_COMMON_PATH . '/functions.php',
            APP_COMMON_PATH . '/functions.php',
            JFUNIT_CORE_PATH . '/Template.class.php',
            JFUNIT_CORE_PATH . '/Controller.class.php'
        );
        $str = '';
        foreach ($fileArr as $v) {
            require_once $v;
        }
    } */

    /**
     * 自动载入函数
     * @param string $className 类名
     * @access private
     * @return void
     */
    static public function _autoload($className)
    {
        //命名空间
        if (strrpos($className,'\\')!==false) {
            //dump('命名空间:'.$className);
            require_cache(ROOT_PATH.$className. '.class.php');
            return ;
        }
        
        //dump('className:'.$className);
        
        $class = ucfirst($className) . '.class.php'; //类文件
        if (substr($className, -5) == 'Model' && require_array(array(
            HDPHP_DRIVER_PATH . 'Model/' . $class,
            MODULE_MODEL_PATH . $class,
            APP_MODEL_PATH . $class
        ))) {return;
        } elseif (substr($className, -10) == 'Controller' && require_array(array(
            JFUNIT_CORE_PATH . $class,
            MODULE_CONTROLLER_PATH . $class,
            APP_CONTROLLER_PATH . $class
        ))) {return;
        } elseif (substr($className, 0, 2) == 'Db' && require_array(array(
            HDPHP_DRIVER_PATH . 'Db/' . $class
        ))) { return;
        } elseif (substr($className, 0, 5) == 'Cache' && require_array(array(
            HDPHP_DRIVER_PATH . 'Cache/' . $class
        ))) {return;
        } elseif (substr($className, 0, 4) == 'View' && require_array(array(
            HDPHP_DRIVER_PATH . 'View/' . $class,
        ))) {return;
        } elseif (substr($className, -4) == 'Hook' && require_array(array(
            MODULE_HOOK_PATH  . $class,
            APP_HOOK_PATH  . $class
        ))) {return;
        } elseif (substr($className, -5) == 'Addon' && require_array(array(
            APP_ADDON_PATH  . $class
        ))) {return;
        } elseif (alias_import($className)) {
            return;
        } elseif (require_array(array(
            MODULE_LIB_PATH . $class,
            APP_LIB_PATH . $class,
            JFUNIT_CORE_CORE_PATH . $class,
            JFUNIT_TOOL_PATH . $class
        ))
        ) {
            return;
        }
    }
    

    private static function _init()
    {
        //默认配置文件
        C(include JFUNIT_PATH . 'Config/config.php');
        //公用配置文件
        file_exists(APP_PATH . 'config.php') && C(include APP_PATH . 'config.php');
        //数据库
        file_exists(APP_PATH . 'database.php') && C(include APP_PATH . 'database.php');
        //URL路由
        if (C('URL_ROUTE_ON') && file_exists(APP_PATH . 'route.php')) {
            $route = include APP_PATH . 'route.php';
            $route && is_array($route) &&  C('ROUTE',$route);
        }
        //加载公共函数
        file_exists(APP_PATH . 'common.php') && require_cache(APP_PATH . 'common.php');
        // 系统语言包
        file_exists(APP_COMMON_PATH . 'Lang/zh-cn.php') && L(include APP_COMMON_PATH . 'Lang/zh-cn.php');
        
        // 设置Debug模式
        if (DEBUG) {
            error_reporting(E_ALL); // 输出除了注意的所有错误报告
            include_once  JFUNIT_TOOL_PATH . 'Debug.class.php'; // 包含debug类
            set_error_handler(array(
                "\Jfunit\Lib\Tool\Debug",
                'Catcher'
            ));  // 设置捕获系统异常
        } else {
            ini_set('display_errors', 'Off'); // 屏蔽错误输出
            if (C('log_record')) {
                ini_set('log_errors', 'On'); // 开启错误日志，将错误报告写入到日志中
                ini_set('error_log', RUNTIME_PATH . 'Log/error_log'); // 指定错误日志文件
            }
        }
        
        if (C('session_redis_on')) {
            if (! extension_loaded('redis')) {
                exit('服务器不支持redis扩展');
            }
            ini_set('session.save_handler', 'Redis');
            ini_set('session.save_path', 'tcp://127.0.0.1:6379');
        } else {
            session_save_path(DATA_PATH  . 'Session');
            session_cache_limiter('private, must-revalidate');
        }
        
        session_start();
        
        //////////
        if (C('LOG_RECORD')) {
            log_record("\r\n[ " . date("Y-m-d H:i:s") . " ] " . get_client_ip() . " " . $_SERVER["REQUEST_URI"]);
        }
        
        if (! C('ignore_ob_start')) {
            ob_start();
        }
        
        //require_cache(JFUNIT_TOOL_PATH . 'Filter.class.php');
        \Jfunit\Lib\Tool\Filter::input();
        
    }

 
    //解析路由
    private static function  _set_parseUrl() {
        
        //require_cache(JFUNIT_CORE_PATH . 'Route.class.php');
        \Jfunit\Lib\Core\Route::parseUrl();
        //常量定义
        if(!defined('MODULE_PATH')){
            if(empty($_GET[C('VAR_GROUP')])){
                //普通模块
                define('MODULE_PATH',APP_PATH.MODULE.'/');
            }else if($_GET[C('VAR_GROUP')]=='Addon'){
                //插件模块
                define('MODULE_PATH',APP_ADDON_PATH.MODULE.'/');
            }else{
                //根据应用组目录识别模块
                define('MODULE_PATH',APP_PATH.$_GET[C('VAR_GROUP')].'/'.MODULE.'/');
            }
        }
        defined('MODULE_CONTROLLER_PATH')                       or define('MODULE_CONTROLLER_PATH', MODULE_PATH . 'Controller/');
        defined('MODULE_MODEL_PATH')                            or define('MODULE_MODEL_PATH', MODULE_PATH . 'Model/');
        //defined('MODULE_CONFIG_PATH')                           or define('MODULE_CONFIG_PATH', MODULE_PATH . 'Config/');
        defined('MODULE_LANG_PATH')                             or define('MODULE_LANG_PATH', MODULE_PATH . 'Lang/');
        defined('MODULE_LIB_PATH')                              or define('MODULE_LIB_PATH', MODULE_PATH . 'Lib/');
        
        //来源URL
        define("__HISTORY__",                                   isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:'javascript:history.go(-1);');
        
         
    }
    
    private static function _app_run()
    {
        //加载模块配置
        file_exists(MODULE_PATH . 'config.php') && C(include MODULE_PATH . 'config.php');
        //加载模块函数
        file_exists(MODULE_PATH . 'common.php') && require_cache(MODULE_PATH . 'common.php');
        
        if(in_array(MODULE,C('DENY_MODULE'))){
            if (DEBUG) {
                \Jfunit\Lib\Tool\Debug::addmsg('模块' .MODULE  . '不允许访问');
                \Jfunit\Lib\Tool\Debug::stop();
                \Jfunit\Lib\Tool\Debug::message();
            }
        
            exit;
        }
        //dump($_REQUEST);
        //dump(get_defined_constants());
        //控制器实例
        $controller = controller(CONTROLLER);
        
        //控制器不存在
        if (!$controller) {
            //模块检测
            if(!is_dir(MODULE_PATH)){
                \Jfunit\Lib\Tool\Debug::addmsg('模块' .MODULE_PATH  . '不存在');
                
                //跳转到首页
                redirect(__SITE__);
                /* $className='\\'.APP.'\\'.MODULE.'\Controller\\'.$class;
                $obj = new \Jfunit\Lib\Core\Controller();
                if (method_exists($obj, '__empty_module')) {
                    call_user_func_array(array($obj, '__empty_module'), array(MODULE));
                } */
            }
            //空控制器
            $controller = Controller("Empty");
            if (!$controller) {
                \Jfunit\Lib\Tool\Debug::addmsg('控制器:' . MODULE_CONTROLLER_PATH.CONTROLLER.C("CONTROLLER_FIX") .'.class.php 不存在');
            }
        }
       
        
        if ($controller) {
            //执行动作
            try {
                $action = new ReflectionMethod($controller, ACTION);
                if ($action->isPublic()) {
                    $action->invoke($controller);
                } else {
                    throw new ReflectionException;
                }
            } catch (ReflectionException $e) {
                $action = new ReflectionMethod($controller, '__call');
                $action->invokeArgs($controller, array(ACTION, ''));
            }
        }
        
        
        // 设置输出Debug模式的信息
        if (DEBUG) {
            \Jfunit\Lib\Tool\Debug::stop();
            \Jfunit\Lib\Tool\Debug::message();
        }
    }
}

?>