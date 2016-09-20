<?php
namespace Jfunit\Lib\Core;

if(!defined('IN_SYS')) exit('Access Denied');

class Tag{
	
	public static function sql_select($arr){
		foreach($arr as $k=>$r){
			if(!isset($r['id'])) return $arr;
			if(!empty($r['fields'])){
				$data = @unserialize($r['fields']);
				foreach((array)$data as $_k=>$_r){
					$arr[$k][$_k] = isset($arr[$k][$_k])?$arr[$k][$_k]:$_r;
				}
			}
			if(!isset($arr[$k]['url'])){
				$arr[$k]['url'] = convert_aid_to_url($r['id']);
			}
		}
		return $arr;
	}

	public static function var_protect($type="IN"){
		static $keepvar,$index;
		if($type=="IN"){
			$syskey = array('GLOBALS','_ENV','HTTP_ENV_VARS','_POST','HTTP_POST_VARS','_GET','HTTP_GET_VARS','_COOKIE','HTTP_COOKIE_VARS',
				'_SERVER','HTTP_SERVER_VARS','_FILES','HTTP_POST_FILES','_REQUEST','HTTP_SESSION_VARS','_SESSION');
			$index = isset($index)?$index+1:0;
			$keepvar[$index] = array();
			foreach($GLOBALS as $k => $r){
				if(!in_array($k,$syskey)){
					$keepvar[$index][$k] = $r;
				}
			}
		}else{
			foreach($keepvar[$index] as $k => $r){
				$GLOBALS[$k] = $r;
			}
			$index--;
		}
	}

	public static function _parse_echo($str){
		$str = substr($str,1,-1);
		return '<?php echo '.self::_parse_var($str).'; ?>';
	}

	public static function _parse_var($str){
		if(preg_match('/^@?\$[a-z0-9\.\_\$]+[a-z0-9]$/i',$str)){
			$arr = explode('.',$str);
			foreach($arr as $k => $r){
				if($k==0){
					$str = $r;
				}else{
					if(strpos($r,'$')===false){
						$str .= "['$r']";	
					}else{
						$str .= "[$r]";	
					}
				}
			}
		}
		return $str;
	}
	
	public static function _parse_tag($str){
		return preg_replace('/\$[a-z0-9\.\_]*/sie',"self::_parse_var('$0')",stripslashes($str));
	}
	
	public static function _parse_function($str){
		$stripstr = stripslashes($str);
		$funcstr = self::_parse_tag($str);
		$funcname = preg_replace('/\(.*?$/','',$funcstr);
		if(function_exists($funcname)){
			return '<?php echo '.$funcstr.'; ?>';
		}
		return $stripstr;
	}
	
	public static function _parse_if($str){
	    //eq neq gt egt lt elt == > >= < <= or and || &&
	    $rules=array(
	        'eq'=>'==',
	        'neq'=>'!=',
	        'gt'=>'>',
	        'egt'=>'>=',
	        'lt'=>'<',
	        'elt'=>'<=',
	        'or'=>'||',
	        'and'=>'&&',
	    );
	    $string = preg_replace(array_keys($rules), $rules, $str);
	    return $string;
	    /*return '<?php if('.$string.'){ ?>';*/
	}
	
	
	

}

?>