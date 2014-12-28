<?php
#****************************************#
# * @Author: lehieu008                   #
# * @Email: lehieu008@gmail.com          #
# * @Website: http://www.iscvietnam.net  #
# * @Copyright: 2008 - 2009              #
#****************************************#
if(!defined('BASEPATH'))exit('No direct script access allowed');
/**
 *Class Folder:
 *  function load($folder = './', $reject = ''): Load folder trong thu muc $folder
 *  function count($folder = './', $reject = ''): Tinh tong so folder trong thu muc $folder
 *  function size($path = './'): Tinh kich thuoc cua $path
 *  function format($size = 0): Dinh dang Bytes, KB, MB, GB
**/
class Folder
{
    function load($folder = './', $reject = '')
	{
		if(!is_array($reject))
		{
			$reject = explode(',',$reject);
		}
		$list_folder = array();
		foreach(glob($folder.DIRECTORY_SEPARATOR.'*') as $currentItem)
		{
			if(is_dir($currentItem))
			{
	            $exist = false;
	            foreach($reject as $reject_folder)
	            {
					if($reject_folder == basename($currentItem))
					{
						$exist = true;
						break;
					}
	            }
				if($exist == false)
				{
					$list_folder[] = basename($currentItem);
				}
			}
		}
		return $list_folder;
	}

	function count($folder = './', $reject = '')
	{
		$count = $this->load($folder, $reject);
		return count($count);
	}

	function size($path = './')
	{
	  	$totalsize = 0;
	  	$totalcount = 0;
	  	$dircount = 0;
	  	if($handle = opendir($path))
	  	{
	    	while(false !== ($file = readdir($handle)))
	    	{
	      		$nextpath = $path.DIRECTORY_SEPARATOR.$file;
	      		if($file != '.' && $file != '..' && !is_link($nextpath))
	      		{
	        		if(is_dir($nextpath))
	        		{
	          			$dircount++;
	          			$result = self::size($nextpath);
	          			$totalsize += $result['size'];
	          			$totalcount += $result['count'];
	          			$dircount += $result['dircount'];
	        		}
	        		elseif(is_file($nextpath))
	        		{
	          			$totalsize += filesize($nextpath);
	          			$totalcount++;
	        		}
	      		}
    		}
	  	}
	  	closedir($handle);
	  	$total['size'] = $totalsize;
	  	$total['count'] = $totalcount;
	  	$total['dircount'] = $dircount;
	  	$total['filecount'] = $totalcount - $dircount;
	  	return $total;
	}

	function format($size = 0)
	{
	    if($size<1024)
	    {
	        return $size." Bytes";
	    }
	    elseif($size<(1024*1024))
	    {
	        $size=round($size/1024,1);
	        return $size." KB";
	    }
	    elseif($size<(1024*1024*1024))
	    {
	        $size=round($size/(1024*1024),1);
	        return $size." MB";
	    }
	    else
	    {
	        $size=round($size/(1024*1024*1024),1);
	        return $size." GB";
	    }
	}
}