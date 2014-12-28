<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Template extends MY_Controller 
{
    public $data ;
    
    public function __construct()
    {
        $this->data = array();
        
        parent::__construct();
    }    
    
    public function index()
    {
        $this->template->title('Default page');
        $this->template->set_theme('default_theme');
        $this->template->set_layout('default');        
        $this->template->set_partial('header','template/blocks/header',$this->_data);      
    }    
}