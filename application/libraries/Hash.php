<?php
#****************************************#
# * @Author: lehieu008                   #
# * @Email: lehieu008@gmail.com          #
# * @Website: http://www.iscvietnam.net  #
# * @Copyright: 2008 - 2009              #
#****************************************#
if(!defined('BASEPATH'))exit('No direct script access allowed');
/**
 *Class Hash:
 *  function create($hash = 'md5sha256', $data, $key = null): Tao chuoi hash
 *  function key($length = 1): Tao key cho chuoi hash
 *Co 4 kieu hash:
 *  md5sha256
 *  md5sha512
 *  sha256md5
 *  sha512md5
**/
class Hash
{
    function create($data, $key = null, $hash = 'md5sha256')
	{
		$hash = strtolower(trim($hash));
		if($key == null)
		{
			switch($hash)
			{
				case 'md5sha256':
				    $data = md5($data);
                    $data1 = substr($data, 0, strlen($data)/2);
					$data2 = substr($data, strlen($data)/2, strlen($data)/2);
					$data = md5(hash('sha256', $data2.$data1));
				    break;
				case 'md5sha512':
				    $data = md5($data);
				    $data1 = substr($data, 0, strlen($data)/2);
					$data2 = substr($data, strlen($data)/2, strlen($data)/2);
				    $data = md5(hash('sha512', $data2.$data1));
				    break;
				case 'sha256md5':
				    $data = hash('sha256', $data);
				    $data1 = substr($data, 0, strlen($data)/2);
					$data2 = substr($data, strlen($data)/2, strlen($data)/2);
				    $data = hash('sha256', md5($data2.$data1));
				    break;
				case 'sha512md5':
				    $data = hash('sha512', $data);
				    $data1 = substr($data, 0, strlen($data)/2);
					$data2 = substr($data, strlen($data)/2, strlen($data)/2);
				    $data = hash('sha512', md5($data2.$data1));
				    break;
			}
		}
		else
		{
			switch($hash)
			{
				case 'md5sha256':
					$key = md5(hash('sha256', $key));
					$data = md5($data);
					$data1 = substr($data, 0, strlen($data)/2);
					$data2 = substr($data, strlen($data)/2, strlen($data)/2);
					$data = md5($data2.$key.$data1);
				    break;
				case 'md5sha512':
				    $key = md5(hash('sha512', $key));
					$data = md5($data);
					$data1 = substr($data, 0, strlen($data)/2);
					$data2 = substr($data, strlen($data)/2, strlen($data)/2);
					$data = md5($data2.$key.$data1);
				    break;
				case 'sha256md5':
				    $key = hash('sha256', md5($key));
				    $data = hash('sha256', $data);
				    $data1 = substr($data, 0, strlen($data)/2);
				    $data2 = substr($data, strlen($data)/2, strlen($data)/2);
				    $data = hash('sha256', $data2.$key.$data1);
				    break;
				case 'sha512md5':
				    $key = hash('sha512', md5($key));
				    $data = hash('sha512', $data);
				    $data1 = substr($data, 0, strlen($data)/2);
				    $data2 = substr($data, strlen($data)/2, strlen($data)/2);
				    $data = hash('sha512', $data2.$key.$data1);
				    break;
			}
		}
		return $data;
	}
	
	function key($leng = 1)
	{
		$numbers = '0123456789';
		$lowers = 'abcdefghijklmnopqrstuvwxyz';
		$specials = '_-.';
		$chars = $numbers.$lowers.strtoupper($lowers).$specials;
		$key = '';
		for($i = 0; $i < $leng; $i++)
        {
			$key = $key.substr($chars, rand(0, strlen($chars)-1), 1);
		}
		return $key;
	}
    function rand_str($length = 4) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
    
    function generator_number($length = 4) {
        $characters = '123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}