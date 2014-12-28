<?php
#****************************************#
# * @Author: lehieu008                   #
# * @Email: lehieu008@gmail.com          #
# * @Website: http://www.iscvietnam.net  #
# * @Copyright: 2008 - 2009              #
#****************************************#
if(!defined('BASEPATH'))exit('No direct script access allowed');
/**
 *Class Filter:
 *  function injection($str): Filter SQL Injection (~`#%'"\--ar( )
 *  function html($str): Filter ma HTML
 *  function injection_html($str): Filter ma HTML va SQL Injection
 *  function link($link): Load bo tien to http:// ra khoi $link
 *  function clear($str): Loai bo tat ca cac ky tu dac biet (~`#%&'"\/<>) trong $str
**/
class Filter
{
    public $get = array();
	function injection($str)
	{
		$str = str_replace("~", "&tilde;", $str);
		$str = str_replace("`", "&lsquo;", $str);
		$str = str_replace("#", "&curren;", $str);
		$str = str_replace("%", "&permil;", $str);
		$str = str_replace("'", "&rsquo;", $str);
		$str = str_replace("\"", "&quot;", $str);
		$str = str_replace("\\", "&frasl;", $str);
		$str = str_replace("--", "&ndash;&ndash;", $str);
		$str =  str_replace("ar(", "ar&Ccedil;", $str);
		$str =  str_replace("Ar(", "Ar&Ccedil;", $str);
		$str =  str_replace("aR(", "aR&Ccedil;", $str);
		$str =  str_replace("AR(", "AR&Ccedil;", $str);
		return $str;
	}
	
	function html($str)
	{
		return htmlspecialchars($str);
	}
	
	function injection_html($str)
	{
		return $this->injection($this->html($str));
	}
	
	function link($link)
	{
		$link = str_replace("http://", "", strtolower((string)$link));
		$link = ereg_replace("[^a-zA-Z0-9./_-]", "", strtolower((string)$link));
		return $link;
	}
    
    
	
	function clear($str)
	{
        $str = str_replace("~", "", $str);
		$str = str_replace("`", "", $str);
		$str = str_replace("#", "", $str);
		$str = str_replace("%", "", $str);
		$str = str_replace("&", "", $str);
		$str = str_replace("'", "", $str);
		$str = str_replace("\"", "", $str);
		$str = str_replace("\\", "", $str);
		$str = str_replace("/", "", $str);
		$str = str_replace("<", "", $str);
		$str = str_replace(">", "", $str);
		return $str;
	}
    
    function get_domain($url)
    {
      $pieces = parse_url($url);
      $domain = isset($pieces['host']) ? $pieces['host'] : '';
      if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
      }
      return false;
    }
    
    function set_data($data){
        return $this->get = $data;
    }
    
    function recursive($item = 0,$order, $h = '')
    { 
        $get_data = '';     
        
        $url = base_url().uri_string();
        
        $href = '';
        
        if($order!=3)
        {
            $get_data .= '<ul class="nav nav-list categories" >';
        }       
        foreach($this->get as $k=>$v)
        {            
            if($v->category_parent==$item)
            {
                
                $get_data .= '<li><a href="';                
                                         
                if($h!='')
                {                    
                    $href = $h.'/'.strtolower($v->category_slug);         
                }
                else
                {                    
                    $href = strtolower($v->category_slug);
                }                
                
                $get_data .= site_url($href) .'"';
                
                if($v->category_order!=3)
                {
                    $get_data .= ' class="'; 
                    
                    $get_data .= 'is_child ';
                    
                    if(!strcasecmp($url,$href))
                    {
                        $get_data .= 'active';
                    }
                    $get_data .= '"';
                }
               
                $get_data .= '>'.$v->category_name.'</a>';
                                
                $get_data .= $this->recursive($v->category_id,$v->category_order,$href);    
                                
                $get_data .= '</li>';                               
            }            
        }            
        if($order!=3)
        {
            $get_data .= '</ul>';
        }        
        return $get_data;
    }

    function recursive1($item = 0,$order, $h = '')
    { 
        $get_data = '';      
        
        $href = '';
        
        if($order>1 && $order<=3)
        {
            $get_data .= '<div><ul>';
        }        
        foreach($this->get as $k=>$v)
        {            
            if($v->category_parent==$item)
            {                                             
                if($h!='')
                {                    
                    $href = $h.'/'.strtolower($v->category_slug);         
                }
                else
                {                    
                    $href = strtolower($v->category_slug);
                }
                $get_data .= '<li><a';                
                
                $get_data .= ' href="'.site_url($href).'"';    
                
                if($order==1)
                {
                    $get_data .= ' class="font_thin"';    
                }
                                                                     
                $get_data .= '>';
                
                if($order==1 && $v->category_parent==0)
                {                    
                    $get_data .= ' <i class="'.$v->category_icon.' font18 icon-white icon-margin5"></i>';
                }               
                $get_data .= $v->category_name.'</a>';
                                
                $get_data .= $this->recursive1($v->category_id,$order+1,$href);    
                                
                $get_data .= '</li>';                               
            }            
        }            
        if($order>1 && $order<=3)
        {
            $get_data .= '</ul></div>';
        }        
        return $get_data;
    }
    
    function get_string_date($thisTime = 0,$currentTime,$suffixString = ""){
        
        $stringDate = "";
        
        if ( ! is_numeric($thisTime) || $thisTime==0)
        {
            $thisTime = mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('Y'));
        }
        
        if ( ! is_numeric($currentTime) || $currentTime == '')
        {
            $currentTime = 0;
        }
        
        $betweenDate = $thisTime - $currentTime;
        
        switch($betweenDate){
            
            case $betweenDate>86400 :
            $temp = floor($betweenDate/86400);
            $stringDate = $temp . " ngày " .$suffixString;
                break;
            
            case $betweenDate<86400 && $betweenDate>3600 :
            $temp = floor($betweenDate/3600);
            $temp1 = floor(($betweenDate%3600)/60);
            $stringDate = $temp . " giờ " .$temp1. " phút " .$suffixString;
                break;
                
            case $betweenDate<3600 :
            $temp = floor($betweenDate/60);
            $stringDate = $temp . " phút " .$suffixString;
                break;
        }
        
        return $stringDate;
    
    }
    
    function return_id_from_url($str = ""){
        $re =  0;
        if($str!=''){
            $temp  = $this->injection(trim($str));
            $cat_chuoi = explode("-",$temp);
            $dem = intval((int)$cat_chuoi[0]);
            if($dem<=0){
                $re = 0;
            }
            $re = $dem;                     
        }        
        return $re ; 
    }
    
    function return_slug_from_url($str = ""){
        $re =  "";
        $temp = "";
        if($str!=''){
            $temp  = $this->injection_html(trim($str));                        
            $re = $temp;                     
        }        
        return $re ; 
    }
}