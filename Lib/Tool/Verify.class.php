<?php
namespace Jfunit\Lib\Tool;

class Verify{

	private $width  = 70;
	private $height = 28;
    private $length = 4;
    private $fontSize = 14;
    private $codeSet = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY'; // 验证码字符集合
    

    public function __construct($config=NULL){
		!empty($config['imageW']) && $this->width=$config['imageW'];
		!empty($config['imageH']) && $this->height=$config['imageH'];
		!empty($config['length']) && $this->length=$config['length'];
		!empty($config['fontSize']) && $this->fontSize=$config['fontSize'];
    }
    
	public function entry($session_name='verify') {
		$im = imagecreate($this->width, $this->height);

		$gray = imagecolorallocate($im, 238,238,238); 
		$randcolor = imagecolorallocate($im, rand(0,150),rand(0,150),rand(0,150)); 
		
		imagefill($im,0,0,$gray);
		
		for ($i = 0; $i < $this->length; $i++) {
		    $code[$i] = $this->codeSet[mt_rand(0, strlen($this->codeSet) - 1)];
		    $codeNX += mt_rand($this->fontSize * 1.2, $this->fontSize * 1.6);
		    imagettftext($im, $this->fontSize, mt_rand(-40, 40), $codeNX, $this->fontSize * 1.6, $randcolor, JFUNIT_PATH.'Config'.DS.'font'.DS.'elephant.ttf', $code[$i]);
		}
		
		$randstr = strtoupper(implode('', $code));
		session($session_name,$randstr);
		    
		for($i=0; $i<200; $i++) { 
     		$randcolor = imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
     		imagesetpixel($im, rand()%$this->width , rand()%$this->height , $randcolor); 
		}
		
		header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		header("content-type: image/png");
		
		$a = imagepng($im); 
		imagedestroy($im);
		//return $a;
	}
	
}

?>