<?php
#****************************************#
# * @Author: lehieu008                   #
# * @Email: lehieu008@gmail.com          #
# * @Website: http://www.iscvietnam.net  #
# * @Copyright: 2008 - 2009              #
#****************************************#
if(!defined('BASEPATH'))exit('No direct script access allowed');
/**
 *Class Check:
 *  function is_logined($user, $group, $type = 'admin'): Kiem tra da duoc dang nhap chua
 *  function is_allowed($permission = 'none', $action = ''): Kiem tra quyen truy cap
 *  function is_more($first, $second): Neu so $first > $second thi tra ve true. Nguoc lai tra ve false
 *  function is_same($firstStr, $secondStr): Neu $firstStr va $secondStr la giong nhau thi tra ve true. Nguoc lai tra ve false
 *  function is_phone($phone): Neu dung la so dien thoai theo qui dinh thi tra ve true, nguoc lai tra ve false
 *  function is_id($id): Kiem tra xem $id co phai la ID
**/

/**
 * Kiểm tra giá trị của biến tên người đăng có dài hơn 25 ký tự hay không , nếu có liệt vào hạng cần xóa
 * Kiểm tra giá trị của biến $groupid xem nếu giá trị này thuộc 3 hay null liệt vào hạng cần xóa 
 * Kiểm tra xem có tồn tại id của người đăng hay không nếu không củng liệt vào hạng cần xóa
 * kiểm tra xem nội dung đăng :
 *  1 | Đếm số ký tự qua dấu cách , nếu số ký tự lớn hơn 10 củng liệt vào hạng cần xóa
 *  2 | Duyệt vòng lặp kiểm tra trong chuổi nếu lớn hơn 10 ký tự thì kiểm tra xem trong chuỗi này có tồn tại ký tự nào lớn hơn 15 hay không 
 *      Nếu có củng liệt vào hạng cần xóa
 *  
 */
class Check
{
	function is_logined($user, $group, $type = 'admin')
	{
		switch(strtolower($type))
		{
			case 'admin':
			    if($user && trim($user) != '' && (int)$user > 0 && $group && trim($group) != '' && (int)$group >0 && (int)$group<3 )
			    {
					return true;
			    }
			    break;
			case 'home':
			    if($user && trim($user) != '' && (int)$user > 0 && $group && trim($group) != ''  && (int)$group >2)
			    {
					return true;
			    }
			    break;
		}
		return false;
	}
    
    function check_for_delete_row_detail_comment($userid="",$groupid="",$name="",$detail="",$ip="")
    {        
        $flag = false;
        if($name!='' && $userid!='' && $detail!='' && $ip!=''){ 
             if(strlen($name)<=25 && ($groupid!=null || $groupid!=3) && $userid!='' && $userid>0 && count(explode(".",$ip))==4 && $this->check_asc($detail)){
                $trim = explode(" ",$detail);
                if(count($trim)>=10){
                    $flag =true;
                    
                 }else{
                    for($i = 0 ; $i<count($trim);$i++){
                        if(strlen($trim[$i])<=10){
                            $flag =true;
                        }
                    }  
                 }    
             } 
         }    
         else{
            $flag = false;
         }         
         
         return $flag;   
    }
    
    function check_asc($str){
        if(preg_match('/[^a-zA-Z0-9[:space:]]/',$str)) {
            return true;
        }
        return false;
    }
    
    function is_logined_extend($user, $group, $type = 'admin',$cookie)
	{
		switch(strtolower($type))
		{
			case 'admin':
			    if(($user && trim($user) != '' && (int)$user > 0 && $group && trim($group) != '' && (int)$group == 1) || ($cookie && trim($cookie)!='' && $this->is_email($cookie)))
			    {
					return true;
			    }
			    break;
			case 'home':
			    if(($user && trim($user) != '' && (int)$user > 0 && $group && trim($group) != ''  && (int)$group >1) || ($cookie && trim($cookie)!='' && $this->is_email($cookie)))
			    {
					return true;
			    }
			    break;
		}
		return false;
	}
	
	function is_allowed($permission = 'none', $action = '')
	{
		if($permission && trim($permission) != '' && strtolower($permission) == 'all')
		{
			return true;
		}        
		elseif($permission && trim($permission) != '' && strtolower($permission) != 'none' && $action && trim($action) != '' && stristr($permission, $action))
		{
			return true;
		}
		return false;
	}

	function is_more($first, $second, $equal = true)
	{
		if($equal == true)
		{
            if((float)$first >= (float)$second)
			{
				return true;
			}
		}
		else
		{
            if((float)$first > (float)$second)
			{
				return true;
			}
		}
		return false;
	}
	
	function is_same($firstStr, $secondStr)
	{
		if((string)$firstStr === (string)$secondStr)
		{
			return true;
		}
		return false;
	}
    
    function is_email($str){
        if(preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)){
            return true;
        }
        return false;
    }
    
    function is_phone_extend($phone)
    {
        if(preg_match('/^((09[0-9]{8})|(01[0-9]{9}))$/i',$phone))
        {
            return true;
        }
        return false;
    }
	
	function is_phone($phone)
	{     
		if(preg_match('/[^0-9().]/i', $phone))
		{
			return false;
		}
		return true;
	}
	
	function is_id($id)
	{
        if(preg_match('/[^0-9]/i', $id) || (int)$id <= 0)
		{
			return false;
		}
		return true;
	}
}