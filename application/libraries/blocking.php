<?php 
#****************************************#
# * @Author: vinh banh                   #
# * @Email: apcheservices68@gmail.com    #
# * @Website: http://www.techzmarket.net #
# * @Copyright: 2014 - 2015              #
#****************************************#
if(!defined('BASEPATH'))exit('No direct script access allowed');
class Blocking{
    private $list = array();
    
    public function __construct(){
        $this->listing();
    }
    /**
     * Blocking ip address     
     * @param array
     * @return boolean
     */
    function block($ipAddresses) {
        $userOctets = explode('.', $_SERVER['REMOTE_ADDR']); // get the client's IP address and split it by the period character
        $userOctetsCount = count($userOctets);  // Number of octets we found, should always be four
    
        $block = false; // boolean that says whether or not we should block this user
    
        foreach($ipAddresses as $ipAddress) { // iterate through the list of IP addresses
            $octets = explode('.', $ipAddress);
            if(count($octets) != $userOctetsCount) {
                continue;
            }
            
            for($i = 0; $i < $userOctetsCount; $i++) {
                if($userOctets[$i] == $octets[$i] || $octets[$i] == '*') {
                    continue;
                } else {
                    break;
                }
            }
            
            if($i == $userOctetsCount) { // if we looked at every single octet and there is a match, we should block the user
                $block = true;
                break;
            }
        }        
        return $block;
    }
    
    private function create($path){
        $temp = '';
        $fp = fopen($path,"wb");
        fwrite($fp,$temp);
        fclose($fp);  
        return ;
    }
    
    public function listing(){
        $path = APPPATH."counters/";
        $file = 'block_list.txt';        
        if(!is_file($path.$file)){
            $this->create($path.$file);
        }
        $content = file_get_contents($path.$file);
        $array = explode(",",$content);
        if(!is_null($array)){
            $this->list = $array;
        }       
        return ;
    }
    
    public function get_list(){
        return $this->list;
    }
    
    private function valid_ip($ip){
        return (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $ip)) ? TRUE : FALSE;
    }
    
    public function add($ip){
        $flag = false;
        if($this->valid_ip($ip) === FALSE){
            return $flag;
        }
        $path = APPPATH."counters/";
        $file = 'block_list.txt';
        $add  = '';        
        if(!is_file($path.$file)){
            if(!is_file($path.$file)){
                $this->create($path.$file);
            }
        }
        $content = file_get_contents($path.$file);
        if($content != ''){
            $add .= ',';
        }
        $content .= $add.$ip;
        file_put_contents($path.$file,$content);
        return ($content== '') ? $flag : true;
    }
    
    public function remove($list = array()){
        $flag = false;
        $path = APPPATH."counters/";
        $file = 'block_list.txt';
        $add  = '';
        $content = file_get_contents($path.$file);
        if(!is_null($content) && !is_null($list))
        {
            $temp = explode(",",$content);
            $new_array = array();
            foreach($temp as $k=>$v){
                if(!in_array($v,$list)){
                    $new_array[] = $v;
                }
            }
            $content = implode(",",$new_array);
        }
        file_put_contents($path.$file,$content);
        return ($content== '') ? $flag : true;
    }

    public function blocks(){
    	$ip = $_SERVER['REMOTE_ADDR'];
    	if(!is_null($this->list)){
    	   if(in_array($ip, $this->list)){
               die("Your IP(" . $ip . ") has been blocked !");
           }
    	}
    }
}
?>