<?php
#****************************************#
# * @Author: vinh banh                   #
# * @Email: apcheservices68@gmail.com    #
# * @Website: http://www.techzmarket.net #
# * @Copyright: 2014 - 2015              #
#****************************************#
if(!defined('BASEPATH'))exit('No direct script access allowed');
class Arr_obj
{
    public function arrayToObject($d) {
		if (is_array($d)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return (object) array_map(array($this,__FUNCTION__), $d);
		}
		else {
			// Return object
			return $d;
		}
	}
    
    public function objectToArray($d) {
		if (is_object($d)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}
 
		if (is_array($d)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map(array($this,__FUNCTION__), $d);
		}
		else {
			// Return array
			return $d;
		}
	}
}
?>