<?php
namespace Jfunit\Lib\Tool;

class Upload{
    
    public $dir;
    public $config='';
    
    public function __construct($config){
    	if (!empty($config)) {
    		foreach ($config as $key => $value) {
    			$this->config[$key]=$value;
    		}
    	}
        $this->dir = SITE_PATH.'/Uploads'.DS.date('Ymd');
        if(!is_dir($this->dir)) mkdir($this->dir,0777,true);
    }
    
    private function single($name){
        $upfile=$_FILES[$name];
        $upfilename = $upfile['name'];
        $fileInfo=pathinfo($upfilename);
        $extension= strtolower($fileInfo['extension']);
        
        if(in_array($extension,array('php','asp','aspx'))) {
            return array("state"=>-1,'msg'=>'上传文件类型不附');
        }
        
        if ($this->config['exts']) {
            if(!in_array($extension,$this->config['exts'])) {
                return array("state"=>-2,'msg'=>'上传文件类型不附');
            }
        }
        
        if ($this->config['limit'] && ($upfile['size']/1024)>($this->config['limit']*1024)) {
            return array("state"=>-3,'msg'=>'文件大于'.$this->config['limit'].'M');
        }
        
        
        
        $relname = date("YmdHis").mt_rand(1000,9999).'.'.$extension;
        $relfile = $this->dir.DS.$relname;
         
        if (move_uploaded_file($upfile['tmp_name'],$relfile)) {
            @chmod($relfile, 0744); // 上传的文件，只有只读权限
            $pic = str_replace(DS,'/',str_replace(SITE_PATH,'',$relfile));
            $result = array(
                //'file_key'  =>$name,
                'state'		=> 1,
                'pic'       => $pic,
                'url'		=> __ROOT__.$pic,
                'title'		=> $relname,
                'original'	=> $upfile['name'],
                'type'		=> ".".$extension,
                'size'		=> $upfile['size'],
            );
            return $result;
        } else {
            return array("state"=>0,'msg'=>'上传失败');
        }
    }
    
    private function multiple($upfile,$index=0){
        
        $upfilename = $upfile['name'][$index];
        $fileInfo=pathinfo($upfilename);
        $extension= strtolower($fileInfo['extension']);
    
        if(in_array($extension,array('php','asp','aspx'))) {
            return array("state"=>-1,'msg'=>'上传文件类型不附');
        }
    
        if ($this->config['exts']) {
            if(!in_array($extension,$this->config['exts'])) {
                return array("state"=>-2,'msg'=>'上传文件类型不附');
            }
        }
    
        if ($this->config['limit'] && ($upfile['size'][$index]/1024)>($this->config['limit']*1024)) {
            return array("state"=>-3,'msg'=>'文件大于'.$this->config['limit'].'M');
        }
    
    
    
        $relname = date("YmdHis").mt_rand(1000,9999).'.'.$extension;
        $relfile = $this->dir.DS.$relname;
         
        if (move_uploaded_file($upfile['tmp_name'][$index],$relfile)) {
            @chmod($relfile, 0744); // 上传的文件，只有只读权限
            $pic = str_replace(DS,'/',str_replace(SITE_PATH,'',$relfile));
            $result = array(
                //'file_key'  =>$name,
                'state'		=> 1,
                'pic'       => $pic,
                'url'		=> __ROOT__.$pic,
                'title'		=> $relname,
                'original'	=> $upfile['name'][$index],
                'type'		=> ".".$extension,
                'size'		=> $upfile['size'][$index],
            );
            return $result;
        } else {
            return array("state"=>0,'msg'=>'上传失败');
        }
    }
    
    public function upload($name){
       
        $result=null;
        if (is_array($_FILES[$name]['name'])) {
            $upfile=$_FILES[$name];
            $count = count($upfile['name']);
            for ($i = 0; $i < $count; $i++) {
                $result[] = $this->multiple($upfile,$i);
            }
        }else{
            $result=$this->single($name);
        }
        
        return $result;
        
    }
    
    public function autoupload(){
        $rs = array();
        foreach($_FILES as $k => $r){
            
            //dump($r);
            if(!empty($r['name'])){
                $rs[$k] = $this->upload($k);
            }
        }
        
        return $rs;
    }
    
}

?>