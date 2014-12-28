<?php
#****************************************#
# * @Author: lehieu008                   #
# * @Email: lehieu008@gmail.com          #
# * @Website: http://www.iscvietnam.net  #
# * @Copyright: 2008 - 2009              #
#****************************************#
function size_thumbnail($pathImage = '', $maxWidth = 125, $maxHeight = 90)
{
    if(file_exists($pathImage))
    {
        $infoImage = @getimagesize($pathImage);
        $width = $infoImage[0];
        $height = $infoImage[1];
        $percent = $width/$height;
        $width = min($width, $maxWidth);
        $height = min($width/$percent, $maxHeight);
        $width = $height*$percent;
        return array('width'=>$width, 'height'=>$height);
    }
    else
    {
        return array('width'=>$maxWidth, 'height'=>$maxHeight);
    }
}

function show_thumbnail($path = '', $image = '', $thumb = 1, $type = 'products')
{
    $image = explode(',', $image);
    $pathThumbnail = 'public/media/'.$type.'/'.$path.'thumbnail_'.$thumb.'_'.$image[0];
    if(file_exists($pathThumbnail))
    {
        return 'thumbnail_'.$thumb.'_'.$image[0];
    }
    else
    {
        return $image[0];
    }
}

function show_image($image = '')
{
    $image = explode(',', $image);
    return $image[0];
}