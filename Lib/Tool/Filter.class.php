<?php
namespace Jfunit\Lib\Tool;

class Filter
{
	private static $_allowtags = 'p|br|b|strong|hr|a|img|object|param|form|input|label|dl|dt|dd|div|font|blockquote|span|embed',
	               $_allowattrs = 'id|class|align|valign|src|border|href|target|width|height|title|alt|name|action|method|value|type|style|pluginspage|play|loop|menu|allowfullscreen|allowscriptaccess',
	               $_disallowattrvals = 'expression|javascript:|behaviour:|vbscript:|mocha:|livescript:';
	
	function __construct($allowtags = null, $allowattrs = null, $disallowattrvals = null)
	{
		if ($allowtags) self::$_allowtags = $allowtags;
		if ($allowattrs) self::$_allowattrs = $allowattrs;
		if ($disallowattrvals) self::$_disallowattrvals = $disallowattrvals;
	}
	
	static function gpc_addslashes($string){
	    if(!is_array($string)) return addslashes($string);
	    foreach($string as $key => $val) $string[$key] = self::gpc_addslashes($val);
	    return $string;
	}
	
	static function gpc_stripslashes($string) {
	    if(!is_array($string)) return stripslashes($string);
	    foreach($string as $key => $val) $string[$key] = self::gpc_stripslashes($val);
	    return $string;
	}
	
	static function input($cleanxss = 1)
	{
        /*if (!get_magic_quotes_gpc())
        {
           $_POST = gpc_addslashes($_POST);
           $_GET = gpc_addslashes($_GET);
           $_COOKIE = gpc_addslashes($_COOKIE);
           $_REQUEST = gpc_addslashes($_REQUEST);
        }*/
        if (/*!defined('ADMIN') && */$cleanxss)
        {
        	$_POST = self::xss($_POST);
        	$_GET = self::xss($_GET);
        	$_COOKIE = self::xss($_COOKIE);
        	$_REQUEST = self::xss($_REQUEST);
        }
		//check_disallow_char($_REQUEST);
	}
	
	static function xss($string)
	{
		if (is_array($string))
		{
			$string = array_map(array('self', 'xss'), $string);
		}
		else 
		{
			if (strlen($string) > 20)
			{
                if (get_magic_quotes_gpc()){
                    $string = self::gpc_addslashes( self::_strip_tags(self::gpc_stripslashes($string)));
                }else{
                    $string = self::_strip_tags( $string );
                }
			}
		}
		return $string;
	}
	
	static function _strip_tags($string)
	{
		
		return preg_replace_callback("|(<)(/?)(\w+)([^>]*)(>)|", array('self', '_strip_attrs'), $string);
	}
	
	static function _strip_attrs($matches)
	{
		//dump($matches);
		if (preg_match("/^(".self::$_allowtags.")$/", $matches[3]))
		{
			if ($matches[4])
			{
				preg_match_all("/\s(".self::$_allowattrs.")\s*=\s*(['\"]?)(.*?)\\2/i", $matches[4], $m, PREG_SET_ORDER);
				$matches[4] = '';
				foreach ($m as $k=>$v)
				{
					if (!preg_match("/(".self::$_disallowattrvals.")/", $v[3]))
					{
						$matches[4] .= $v[0];
					}
				}
			}
		}
		else 
		{
			$matches[1] = '&lt;';
			$matches[5] = '&gt;';
		}
		unset($matches[0]);
		return implode('', $matches);
	}
}