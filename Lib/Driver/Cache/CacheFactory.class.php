<?php
namespace Jfunit\Lib\Driver\Cache;
/**
 * 缓存驱动工厂
 * @package     Cache
 */
final class CacheFactory
{

    public static $cacheFactory = null; //静态工厂实例
    protected $cacheList = array(); //驱动链接组

    /**
     * 构造函数
     */

    private function __construct()
    {

    }

    /**
     * 返回工厂实例，单例模式
     */
    public static function factory($options)
    {
        $options = is_array($options) ? $options : array();
        //只实例化一个对象
        if (is_null(self::$cacheFactory)) {
            self::$cacheFactory = new CacheFactory();
        }
        $driver = isset($options['driver']) ? $options['driver'] : C("CACHE_TYPE");
        //静态缓存实例名称
        $driverName = md5_d($options);
        //对象实例存在
        if (isset(self::$cacheFactory->cacheList[$driverName])) {
            return self::$cacheFactory->cacheList[$driverName];
        }
        $class = 'Cache' . ucwords(strtolower($driver)); //缓存驱动
        if(!class_exists($class)){
            $classFile = HDPHP_DRIVER_PATH . 'Cache/' . $class . '.class.php'; //加载驱动类库文件
            if (!require_cache($classFile)) {
                halt("缓存类型指定错误，不存在缓存驱动文件:" . $classFile);
            }
        }
        $cacheObj = new $class($options);
        self::$cacheFactory->cacheList[$driverName] = $cacheObj;
        return self::$cacheFactory->cacheList[$driverName];
    }

}
