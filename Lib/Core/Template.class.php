<?php
namespace Jfunit\Lib\Core;

/**
 * @version    $Id: template.class.php 322 2015-02-27 07:03:38Z intdar $
 */

if(!defined('IN_SYS')) exit('Access Denied');

class Template{
    
    public $tpl,$compile,$tVar=  array(); // 模板输出变量;
    public static $tpls=array();
    
	protected $rules = array(
		// template
		'/<(template|include|require)\s+file\=(\"|\')([\.\w\/\\\]+)(\2)\s*\/>/i' => '<?php include  $this->_tTpl("$3"); ?>',
		
		// function
		'/(\{|<\!--):(\s*[\w]+\(.*?\)\s*)(-->|\})/ie' => "\Jfunit\Lib\Core\Tag::_parse_function('$2')",
		
		// echo 
		'/\{\s*@?\$[\w\.?\$\[\]\"\']+\s*\}/sie' => "\Jfunit\Lib\Core\Tag::_parse_echo('$0')",
	    '/\{\s*@?(\$[\w]+(\[(\"|\')[\w]+(\3)\])+)\s*\}/sie' => "\Jfunit\Lib\Core\Tag::_parse_echo('-$1-')",
	    
	    '/\{(title|keywords|description)\}/' => '<?php echo $1; ?>',
		//显示常量
	    '/\{define\.([\w]+)\s*\}/' => '<?php echo $1; ?>',
	    
		// { to <!--
		'/<\!--\s*.*?\s*-->/sie' => "\Jfunit\Lib\Core\Tag::_parse_tag('$0')",
		
		// sql
		'/<sql\=(\"|\')(.+?)(\1)\s*>/i' => '<?php  $_sql_result =  M()->getAll("$2");$_sql_result = \Jfunit\Lib\Core\Tag::sql_select($_sql_result); ?><foreach name="$_sql_result">',
		'/<\s*\/sql\s*>/i' => '</foreach>',
		//php
	    '/<\s*php\s*>/i'=>'<?php ',
	    '/<\s*\/php\s*>/i' => ' ?>',
	    
		// if
		'/<\s*if\s+condition\=(\"|\')(.+?)(\1)\s*>/i' => '<?php if($2) { ?>',
	    /*
	    '/<\s*if\s+condition\=(\"|\')(.+?)(\1)\s*>/i' => "<?php \$_ifcondition=\Jfunit\Lib\Core\Tag::_parse_if(\"$2\");if(\$_ifcondition) { ?>", 
	    */
	    '/<\s*else\s*(\/)?\s*>/i' => '<?php } else { ?>',
		'/<\s*elseif\s+condition\=(\"|\')(.+?)(\1)\s*>/i' => "<?php } elseif ($2) { ?>",
		'/<\s*\/if\s*>/i' => '<?php } ?>',
		
		// foreach
        '/<\s*foreach\s+name\=(\"|\')(\S+)(\1)\s*>/i' => '<?php \Jfunit\Lib\Core\Tag::var_protect("IN"); $index=0; if(is_array($2)) foreach($2 as \$__i => \$__value) { if(is_array(\$__value)) { $index++; foreach(\$__value as \$__k=>\$__v){ \${\$__k}=\$__v; } } ?>',
		'/<\s*foreach\s+name\=(\"|\')(\S+)(\1)\s+item\=(\"|\')(\S+)(\4)\s*>/i' => '<?php \Jfunit\Lib\Core\Tag::var_protect("IN"); $index=0;if(is_array(\$$2)) foreach(\$$2 as \$$5) { $index++;?>',
		'/<\s*foreach\s+name\=(\"|\')(\S+)(\1)\s+item\=(\"|\')(\S+)(\4)\s+key\=(\"|\')(\S+)(\7)\s*>/i' => '<?php \Jfunit\Lib\Core\Tag::var_protect("IN"); $index=0;if(is_array(\$$2)) foreach(\$$2 as \$$8 => \$$5) { $index++;?>',
		'/<\s*\/foreach\s*>/i' => '<?php };  \Jfunit\Lib\Core\Tag::var_protect("OUT"); ?>',
		
		//volist
	    '/<\s*volist\s+name\=(\"|\')(\S+)(\1)\s+id\=(\"|\')(\S+)(\4)\s*>/i' => '<?php \Jfunit\Lib\Core\Tag::var_protect("IN"); $index=0;if(is_array(\$$2)) foreach(\$$2 as \$$5) { $index++;?>',
	    '/<\s*volist\s+name\=(\"|\')(\S+)(\1)\s+id\=(\"|\')(\S+)(\4)\s+key\=(\"|\')(\S+)(\7)\s*>/i' => '<?php \Jfunit\Lib\Core\Tag::var_protect("IN"); $index=0;if(is_array(\$$2)) foreach(\$$2 as \$$8 => \$$5) { $index++;?>',
	    '/<\s*\/volist\s*>/i' => '<?php };  \Jfunit\Lib\Core\Tag::var_protect("OUT"); ?>',
	    
		// eval
		'/\{\s*eval\s+(.+?)\s*\}/is' => '<?php $1 ?>',
	    
	    /* '/(\"|\')(__MODULE__|__CONTROLLER__|__ACTION__|MODULE|CONTROLLER|ACTION|__ROOT__|__APP__|__WEB__)($1)/' => '<?php echo $2; ?>',
	    '/\{(__MODULE__|__CONTROLLER__|__ACTION__|MODULE|CONTROLLER|ACTION|__ROOT__|__APP__|__WEB__)\}/' => '<?php echo $1; ?>',
	     */
	    '/(__MODULE__|__CONTROLLER__|__ACTION__|__ROOT__|__WEB__|__URL__)/' => '<?php echo $1; ?>',
	     
	);
	
	//变量初始化
	function __construct() {
	}


    
    
    //模板编译
    protected function _tCompile()
    {
    	$data = file_get_contents($this->tpl);
        $data = $this->_tParse($data);
		$dir = dirname($this->compile);
		if(!is_dir($dir)){
			@mkdir($dir,0777,true);
		}
    	if(false === @file_put_contents($this->compile, $data)) exit("$this->compile file is not writable");
    	@chmod($this->compile, 0774);
    	return true;
    }
    protected function view_replace_str(){
        if (C('view_replace_str')) {
            $view_replace_str = C('view_replace_str');
            foreach ($view_replace_str as $key => $value) {
                $this->rules['/'.$key.'/'] = $value;
            }
        }
    }
    //标签解析
   	protected function _tParse($string)
	{
	    $this->view_replace_str();
		$string = $this->_tBefore($string);
		$string = preg_replace(array_keys($this->rules), $this->rules, $string);
		return $this->_tAfter($string);
	}
    
	protected function _tBefore($string){
		return $string;
	}
	
	protected function _tAfter($string){
		/* if(!defined("ADMIN") || !empty($GLOBALS['is_user_tpl'])){
			$string = preg_replace('/\"[\w\.\/]+Public/i','"'.__ROOT__.'/Public',$string);
			//$string = str_replace('../static/',$GLOBALS['sitepath'].'/static/',$string);
		} */
		return $string;	
	}
	
    public function loaded_tpl(){
        return self::$tpls;
    }
    
    //模板解析
    public function _tView()
    {
        if(!is_file($this->tpl)) exit("Template not exists<br>$this->tpl");
        self::$tpls[] = $this->tpl;
        if (DEBUG || !file_exists($this->compile) || @filemtime($this->tpl) > @filemtime($this->compile))
        {
            $this->_tCompile();
        }
        return $this->compile;
    }
    
    //查找引用模板文件
    function _tTpl($tpl=null) {
        if (strpos($tpl,':')!==false) {
            $args = explode(':', trim($tpl,':'));
            $module = ucfirst($args[0]);
            /* $tpl = ucfirst($args[0]).DS.C('TPL_PATH').DS.$args[1]. C('TPL_FIX');
            $this->tpl = APP_PATH.$tpl;
            $this->compile = RUNTIME_PATH.$tpl; */;
        }else{
            //如果模板文件名为空，则自动设置为当前操作名为模板名
            if (is_null($tpl)) {
                $tpl=CONTROLLER.DS.ACTION;
            }
            $module = MODULE;
            $module_path = MODULE_PATH;
        }
        
        $tpl .= C('TPL_FIX');
        $this->tpl = $module_path.C('TPL_PATH').DS.$tpl;
        $this->compile = RUNTIME_PATH.C('TPL_PATH').DS.$module.DS.$tpl;
        return $this->_tView();
    }
    
    
    //模板变量赋值
    public function assign($name,$value=''){
        if(is_array($name)) {
            $this->tVar   =  array_merge($this->tVar,$name);
        }elseif(is_object($name)){
            foreach($name as $key =>$val)
                $this->tVar[$key] = $val;
        }else {
            $this->tVar[$name] = $value;
        }
    }
    
    //显示模板
    function display($tpl=null){
        
        if (!empty($this->tVar))extract($this->tVar,EXTR_SKIP);
        require $this->_tTpl($tpl);
        
        //require $this->_tView();
    }
    
    /* function redirect($url, $time = 0, $msg = ''){
        redirect($url,$time,$msg);
    } */
    
    /**
     * 错误页面
     * @param string $message 提示内容
     * @param null $url 跳转URL
     * @param int $time 跳转时间
     * @param null $tpl 模板文件
     */
    protected function error($message = '出错了', $url = NULL, $time = 2, $tpl = null)
    {
        if (isAjax()) {
            $this->ajax(array('status' => 0, 'info' => $message,'url'=>$url));
        } else {
            
            //$url = $url ? "window.location.href='" . U($url) . "'" : "window.location.href='" . __HISTORY__ . "'";
            $url = $url ? U($url) : __HISTORY__;
            
            $tpl = $tpl ? $tpl : C("TPL_ERROR");
            $this->assign(array("message" => $message, 'url' => $url, 'time' => $time));
            $this->display($tpl);
        }
        exit;
    }
    
    /**
     * 成功页面
     * @param string $message 提示内容
     * @param null $url 跳转URL
     * @param int $time 跳转时间
     * @param null $tpl 模板文件
     */
    protected function success($message = '操作成功', $url = NULL, $time = 2, $tpl = null)
    {
        if (isAjax()) {
            $this->ajax(array('status' => 1, 'info' => $message,'url'=>$url));
        } else {
            $url = $url ? U($url) : __HISTORY__;
            $tpl = $tpl ? $tpl : C("TPL_SUCCESS");
            $this->assign(array("message" => $message, 'url' => $url, 'time' => $time));
            $this->display($tpl);
        }
        exit;
    }
    
    /**
     * Ajax输出
     * @param $data 数据
     * @param string $type 数据类型 text html xml json
     */
    protected function ajax($data, $type = "JSON")
    {
        $type = strtoupper($type);
        switch ($type) {
            case "HTML" :
            case "TEXT" :
                $_data = $data;
                break;
            case "XML" :
                //XML处理
                $_data = \Jfunit\Lib\Tool\Xml::create($data, "root", "UTF-8");
                break;
            default :
                //JSON处理
                $_data = json_encode($data);
        }
        echo $_data;
        exit;
    }
}

?>