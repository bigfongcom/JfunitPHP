<?php
namespace Jfunit\Lib\Core;
//use \Jfunit\Core\Template;

class Controller extends Template
{

    function __construct()
    {
        parent::__construct();
        if (method_exists($this, '__init')) {
            $this->__init();
        }
        
        if (method_exists($this, '__auto')) {
            $this->__auto();
        }
        
        //$this->assign('setting', Setting());
    }
    
    
    /**
     * 执行不存在的函数时会自动执行的魔术方法
     * 编辑器上传时执行php脚本及ispost或_post等都会执行这个方法
     * @param $action 方法名
     * @param $args 方法参数
     */
    public function __call($action, $args)
    {
        /**
         * 控制器方法不存在时
         */
        if (strcasecmp($action, ACTION) == 0) {
            if (method_exists($this, "__empty")) {
                //执行空方法_empty
                $this->__empty($args);
            } else {
                \Jfunit\Lib\Tool\Debug::addmsg('控制器中不存在动作' . $action);
            }
        }
    }
    
    public function __empty_module($module){
        $this->error('模块:'.$module.' 不存在');
    }

    
}