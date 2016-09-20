<?php
namespace Jfunit\Lib\Tool;
/** ******************************************************************************
 * 调试模式类，用于在开发阶段调试程序使用。                           *                                               * 
 * ******************************************************************************/
class Debug {
		static $includefile=array();
		static $info=array();
		static $sqls=array();
		static $startTime;                //保存脚本开始执行时的时间（以微秒的形式保存）
		static $stopTime;                //保存脚本结束执行时的时间（以微秒的形式保存）
		
		static $msg = array(
       			 E_WARNING=>'运行时警告',
       			 E_NOTICE=>'运行时提醒',
        		 E_STRICT=>'编码标准化警告',
        		 E_USER_ERROR=>'自定义错误',
        		 E_USER_WARNING=>'自定义警告',
        		 E_USER_NOTICE=>'自定义提醒',
        		 'Unkown'=>'未知错误'
		 );

		/**
		 * 在脚本开始处调用获取脚本开始时间的微秒值
		 */
		static function start(){                       
			//self::$startTime = microtime(true);   //将获取的时间赋给成员属性$startTime
			//self::$startTime = DEBUG_STARTTIME;
		}
		/**
		 *在脚本结束处调用获取脚本结束时间的微秒值
		 */
		static function stop(){
			self::$stopTime= microtime(true);   //将获取的时间赋给成员属性$stopTime
		}

		/**
		 *返回同一脚本中两次获取时间的差值
		 */
		static function spent(){
			//return round((self::$stopTime - self::$startTime) , 4);  //计算后以4舍5入保留4位返回
			return round((self::$stopTime - DEBUG_STARTTIME) , 4);  //计算后以4舍5入保留4位返回
		}

    		/*错误 handler*/
   		static function Catcher($errno, $errstr='', $errfile='', $errline=''){
	   		if(!isset(self::$msg[$errno])){
	   		    $errno='Unkown';
	   		}
	   		
			if($errno==E_NOTICE || $errno==E_USER_NOTICE)
				$color="#000088";
			else
				$color="red";

	   		$mess='<font color='.$color.'>';
	   		$mess.='<b>'.self::$msg[$errno]."</b>[在文件 {$errfile} 中,第 $errline 行]:";
	   		$mess.=$errstr;
	   		$mess.='</font>'; 		
	  		self::addMsg($mess);
		}
		/**
		 * 添加调试消息
		 * @param	string	$msg	调试消息字符串
		 * @param	int	$type	消息的类型
		 */
		static function addmsg($msg,$type=0) {
			if(DEBUG){
				switch($type){
					case 0:
						self::$info[]=$msg;
						break;
					case 1:
						self::$includefile[]=$msg;
						break;
					case 2:
						self::$sqls[]=$msg;
						break;
				}
			}
		}
		/**
		 * 输出调试消息
		 */
		static function message(){
			echo '<span id="debug_open" style=" position:fixed; right:5px; bottom:5px; display:inline;cursor:pointer;float:right;width:35px;background:#500;border:1px solid #555;color:white" onclick="debug_open()">打开</span>';
			echo '<div id="debug" style="position:fixed;bottom:0;right:0;display:none;float:left;clear:both;text-align:left;font-size:11px;color:#888;width:95%;margin:10px;padding:10px;background:#F5F5F5;border:1px dotted #778855;z-index:10000">';
			echo '<div style="float:left;width:100%;"><span style="float:left;width:200px;"><b>运行信息</b>( <font color="red">'.self::spent().' </font>秒):</span><span onclick="debug_close()" style="cursor:pointer;float:right;width:35px;background:#500;border:1px solid #555;color:white">关闭X</span></div><br>';
			echo '<ul style="max-height: 400px;overflow-y: scroll;margin:0px;padding:0 10px 0 10px;list-style:none;word-wrap: break-word; ">';
			if(count(self::$includefile) > 0){
				echo '［自动包含］';
				foreach(self::$includefile as $file){
					echo '<li>&nbsp;&nbsp;&nbsp;&nbsp;'.$file.'</li>';
				}		
			}
			if(count(self::$info) > 0 ){
				echo '<br>［系统信息］';
				foreach(self::$info as $info){
					echo '<li>&nbsp;&nbsp;&nbsp;&nbsp;'.$info.'</li>';
				}
			}

			if(count(self::$sqls) > 0) {
				echo '<br>［SQL语句］';
				foreach(self::$sqls as $sql){
					echo '<li>&nbsp;&nbsp;&nbsp;&nbsp;'.$sql.'</li>';
				}
			}
			echo '</ul>';
			echo '</div>
				<script>
				function debug_close(){
					document.getElementById(\'debug\').style.display="none";
					document.getElementById(\'debug_open\').style.display="inline";
				}
				function debug_open(){
					document.getElementById(\'debug\').style.display="block";
					document.getElementById(\'debug_open\').style.display="none";
				}
				</script>
			';	
		}
	}
