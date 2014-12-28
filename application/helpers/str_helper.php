<?php
#****************************************#
# * @Author: lehieu008                   #
# * @Email: lehieu008@gmail.com          #
# * @Website: http://www.iscvietnam.net  #
# * @Copyright: 2008 - 2009              #
#****************************************#
if(!defined('BASEPATH'))exit('No direct script access allowed');
/**
 *Helper Str:
 *  function sub($s, $count, $from = 0, $a3p = true): Cat chuoi $s voi so ky tu $count
 *  function jstr($str)
 *  function clear_symbol($s): Xoa bo ky tu dac biet
**/
function sub($s, $count, $from = 0, $a3p = true)
{
	$INT_MAX = 2147483647;
	if($count+$from >= strlen($s))
	{
		return substr($s, $from);
	}
	$sp = strpos($s, ' ', $from+$count);
	$tp = strpos($s, '\t', $from+$count);
	$np = strpos($s, '\n', $from+$count);
	if($sp === false)
	{
		$sp= $INT_MAX;
	}
	if($tp === false)
	{
		$tp= $INT_MAX;
	}
	if($np === false)
	{
		$np= $INT_MAX;
	}
	$count = min($sp,$tp,$np);
	if($count >= strlen($s))
	{
		return substr($s, $from);
	}
	return substr($s, $from, $count-$from).($a3p?' ...':'');
}

function jstr($str)
{
	return str_replace("\n",'\n',str_replace("\r\n",'\n',str_replace('"','\"',$str)));
}

function clear_symbol($s)
{
	return preg_replace('/\W/', ' ', $s);
}