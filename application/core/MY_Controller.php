<?php (defined('BASEPATH')) OR exit('No direct script access allowed');
require_once (APPPATH.'config/setting.php');
class MY_Controller extends MX_Controller
{
    public $data  =array();
    public function __construct(){
        parent::__construct();
        ini_set('output_buffering', 1);
    }
}
?>