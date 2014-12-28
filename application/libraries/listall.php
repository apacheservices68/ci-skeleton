<?php if(!defined('BASEPATH'))exit('No direct script access allowed');
 
class Listall {     
    public function language($dir ,$recursive ,$level  =0 ){
        if(!is_dir($dir)){
            return false;
        }
        $level++;        
        try{
            $resource = opendir($dir);
            $return = array();
            while(($read = readdir($resource))!== false){
                if(preg_match("/^([^\w\-\_]+)|(index\.html)$/",$read)){
                    continue;
                } 
                if(is_dir($dir.$read) && $recursive == true){
                    $return[] = $this->language($dir.$read.'/',true,$level);
                }else{
                    $path = pathinfo($dir.$read,PATHINFO_EXTENSION);
                    $add = array();
                    if(strtolower($path) == 'php'){
                            $return[$read] = $dir.$read;                            
                        
                    }
                }
            }
        }catch(exception $e){
            return false;
        }
        return $return;
    }
    
    public function merge_array($arr = array()){
        $return = array();
        foreach($arr as $k=>$v){
            if(is_array($v)){
                $return = array_merge($return,$this->merge_array($v));                  
            }   else{
                $return[$v] = $v;   
            }            
        }
        return $return;
    }
    
    public function read($dir , $recursive = true , $level= 0){
        if( ! is_dir($dir)){
            return false;            
        }
        $level++;        
        try{
            $resource = opendir($dir);            
            $find = array();            
            while(($file = readdir($resource)) !== FALSE){                
                if(preg_match("/^([^\w\-\_]+)|(index\.html)$/",$file)){
                    continue;
                }                
                if(is_dir($dir.$file) && $recursive == true){
                    $directory_name = $dir.$file.DIRECTORY_SEPARATOR;                    
                    if($level == 2 && $file!='controllers'){
                        continue;
                    }
                    if($file == 'controllers'){
                        $find = $this->read($directory_name,true,$level);
                    }else{
                        $find[$file] = $this->read($directory_name,true,$level);
                    }
                }else{
                    $filename = $dir.$file;
                    $read = pathinfo($filename, PATHINFO_EXTENSION);
                    $add =array();
                    if($read == 'php'){
                        $classname = ucfirst(str_replace(".php","",$file));
                        include_once($filename);
                        
                        $inflector = new ReflectionClass($classname);
                        $classname = strtolower($classname);
                        foreach($inflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method){
                            if(strtolower($method->class) == $classname){
                                if($method->name != '__construct'){
                                    $add[] = $method->name;
                                }
                            }
                        }   
                        $find[$classname] = $add;
                    }
                }
            }
        }
        catch(Exception $e)
        {
            return false;
        }
        return $find;
    }
    public function readDirectory($Directory,$Recursive = true,$level=0)
    {
        if(is_dir($Directory) === false)
        {
            return false;
        }
        $level++;
        
        try
        {
            $Resource = opendir($Directory);
            
            $Found = array();
            
            while(false !== ($Item = readdir($Resource)))
            {
                if($Item == "." || $Item == ".." || $Item == ".DS_Store")
                {
                    continue;
                }
                if($Recursive === true && is_dir($Directory . $Item))
                {
                    $Directoryname  = $Directory . $Item .'/';
                    
                    if($level == 2 && $Item != "controllers")
                    { 
                        continue;
                    }
                    if($Item == 'controllers')
                    {
                        $Found = $this->readDirectory($Directoryname,true,$level);
                    }
                    else
                    {
                        $Found[$Item] = $this->readDirectory($Directoryname,true,$level);
                    }
                }
                else
                {
                    $filename = $Directory . $Item;
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    if($ext == "php")
                    {
                        $classname = ucfirst(str_replace('.php','',$Item));
                        require_once($filename);
                        $reflector = new ReflectionClass($classname);
                        $methodNames = array();
                        $lowerClassName = strtolower($classname);
                        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                            if (strtolower($method->class) == $lowerClassName) {
                                if($method->name != "__construct")
                                $methodNames[] = $method->name;
                            }
                        }
                        $Found[$classname] =  $methodNames;
                        
                    }
                }
            }
        }catch(Exception $e)
        {
            return false;
        }
    //pre($Found,true);
        return $Found;
    }    
}
?>