<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Login extends MY_Controller {
    public $_data = array();
    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('filter'));
    }
    
    public function index()
    {
        $this->template->title('Login to administrator');
        $this->template->set_theme('login_theme');
        $this->template->set_layout('default');                
        //$this->template->build('login/file');
    }
}
?>