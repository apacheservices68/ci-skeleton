<?php
#****************************************#
# * @Author: lehieu008                   #
# * @Email: lehieu008@gmail.com          #
# * @Website: http://www.iscvietnam.net  #
# * @Copyright: 2008 - 2009              #
#****************************************#
if(!defined('BASEPATH'))exit('No direct script access allowed');
/**
 *Class File:
 *  function load($folder = './', $reject = '', $customer = '*'): Load toan bo cac file co trong thu muc $folder
 *  function count($folder = './', $reject = '', $customer = '*'): Tinh tong so file co trong thu muc $folder
**/
class File
{
    function load($folder = './', $reject = '', $customer = '*')
	{
		if(!is_array($reject))
		{
			$reject = explode(',',$reject);
		}
		$list_file = array();
		foreach(glob($folder.DIRECTORY_SEPARATOR.$customer) as $currentItem)
		{
			if(is_file($currentItem))
			{
	            $exist = false;
	            foreach($reject as $reject_file)
	            {
					if($reject_file == basename($currentItem))
					{
						$exist = true;
						break;
					}
	            }
				if($exist == false)
				{
					$list_file[] = basename($currentItem);
				}
			}
		}
		return $list_file;
	}

	function count($folder = './', $reject = '', $customer = '*')
	{
		$count = $this->load($folder, $reject, $customer);
		return count($count);
	}
}