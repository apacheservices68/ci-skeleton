<?php
#****************************************#
# * @Author: lehieu008                   #
# * @Email: lehieu008@gmail.com          #
# * @Website: http://www.iscvietnam.net  #
# * @Copyright: 2008 - 2009              #
#****************************************#
function unlink_captcha($pathCaptcha = '')
{
    if(trim($pathCaptcha) != '' && file_exists($pathCaptcha))
    {
        @unlink($pathCaptcha);
    }
}