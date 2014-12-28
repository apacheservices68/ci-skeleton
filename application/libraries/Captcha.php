<?php
if(!defined('BASEPATH'))exit('No direct script access allowed');
/**
 *Class Captcha:
 *	function code($length = 6, $mode = '0'): Tao ma captcha
 *	function create($str, $pathImage = './', $fontSize = 12, $fontName = null, $background = null): Tao captcha
**/
class Captcha
{
    function create($str, $pathImage = 'templates/captcha/captcha.jpg', $fontSize = 15, $fontName = 'public/media/font/ChaparralPro-BoldIt.otf', $background = 'public/media/common/2.png')
	{
		if($fontName && !file_exists($fontName))
		{
            $fontName = null;
		}
		if(!$fontSize)
		{
			$fontSize = 12;
		}
		if($background && !file_exists($background))
		{
			$background = null;
		}
		$strlen = strlen($str);
		try{
			if(!$str)
			{
                throw new Exception("No Captcha Code!");
			}
			if($fontName)
			{
				if(!$box = @imagettfbbox($fontSize, 45, $fontName, 'W'))
				{
                    throw new Exception("$fontName Not Found!");
				}
				$d = max(abs($box[4] - $box[0]), abs($box[2] - $box[6]), abs($box[5] - $box[1]), abs($box[7] - $box[3]));
			}
			else
			{
				$d = 3 * max(imagefontwidth(5), imagefontheight(5)) / 2;
			}
			if($background)
			{
				$size=@getimagesize($background);
				try{
					switch($size[2])
					{
						case 1:
							$bg = @imagecreatefromgif($background);
							break;
						case 2:
							$bg = @imagecreatefromjpeg($background);
							break;
						case 3:
							$bg = @imagecreatefrompng($background);
							break;
						case 4:
							$bg = @imagecreatefromwbmp($background);
							break;
					}
				}
				catch(exception $e){
					 throw new Exception("Error On Creating $background!");
				}
			}
			else
			{
				if(!$bg = @imagecreatetruecolor(($strlen + 2) * $d, 2 * $d))
				{
					throw new Exception("Error On Creating Image!");
				}
				$color = imagecolorallocate($bg, rand(64, 255), rand(64, 255), rand(64, 255));
				imagefilledrectangle($bg, 0, 0, ($strlen + 2) * $d, 2 * $d, $color);
				for($i = 0; $i < $strlen; $i++)
				{
					@imagearc($bg, rand(0, $strlen * $d), rand(0, 2 * $d), rand(0, 10 * $d), rand($d, 4 * $d), 0, rand(0, 360), rand(0, 255) << 16 + rand(0, 255) << 8 + rand(0, 255));
				}
			}
			$orgY = rand($d, imagesy($bg) - $d / 2);
			$orgX = $x = rand($d, imagesx($bg) - $strlen * $d);
			for($i = 0; $i < $strlen; $i++)
			{
				$angle = rand(0, 70) - 35;
				$color = @imagecolorat($bg, $orgX, $orgY);
				if($fontName)
				{
					@imagettftext($bg, $fontSize, $angle, $orgX - 1, $orgY - 1, ($color < 0x808080) ? 0 : 0xFFFFFF, $fontName, $str{$i});
					$box = @imagettftext($bg, $fontSize, $angle, $orgX, $orgY, 0xFFFFFF - $color, $fontName, $str{$i});
				}
				else
				{
					imagestring($bg, 5, $orgX, $orgY - rand($d / 2, 5 * $d / 6), $str{$i}, 0xFFFFFF - $color);
				}
				$orgX += $d;
			}
			$w = $orgX - $x;
			$h = $d;
			if(!$imgSecure = @imagecreatetruecolor($w, $h))
			{
				throw new Exception("$w-$h-Error On Creating Image!");
			}
			@imagecopy($imgSecure, $bg, 0, 0, $x - $d / 3, $orgY - ($fontSize + $d) / 2, $w, $h);
			@imagejpeg($imgSecure, $pathImage);
		}
		catch(exception $e){
			$w = imagefontwidth(5);
			$h = imagefontheight(5);
			$msg = $e->getMessage();
			$err = imagecreate(10 + $w * strlen($msg), 10 + $h);
			imagecolorallocate($err, 255, 64, 64);
			$white = imagecolorallocate($err, 255, 255, 255);
			imagestring($err, 5, 5, 5, $msg, $white);
			imagejpeg($err, $pathImage);
		}
	}
	
	function code($length = 6, $mode = '3')
	{
		$numbers = '0123456789';
		$lowers = 'abcdefghijklmnopqrstuvwxyz';
		$uppers = strtoupper($lowers);
		$code = '';
		switch(trim((string)$mode))
		{
			case '1':
			    $code = $numbers;
			    break;
			case '2':
			    $code = $lowers;
			    break;
			case '3':
			    $code = $uppers;
			    break;
			case '4':
			    $code = $numbers.$lowers;
			    break;
			case '5':
			    $code = $numbers.$uppers;
			    break;
			case '6':
			    $code = $lowers.$uppers;
			    break;
			default:
			    $code = $numbers.$lowers.$uppers;
		}
		$code = preg_replace("[^a-zA-Z0-9]","",(string)$code);
		$codeCreated = '';
		for($i = 0; $i < $length; $i++)
		{
			$codeCreated .= substr($code, rand(0,strlen($code)-1), 1);
		}
		return preg_replace("[^a-zA-Z0-9]","",(string)$codeCreated);
	}
}