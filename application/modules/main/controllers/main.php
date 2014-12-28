<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends MY_Controller 
{
    public $data ;
    
    public function __construct()
    {
        $this->data = array();        
        parent::__construct();
    }
    
    public function test()
    {
        $this->_output();
        $this->template->title("cd");
        $this->template->build('main/file',$this->data);
    }
    
    protected function _output($output = null)
	{
	    $this->data['output']              = $output;
	    #$this->template->title("Coffee house");
        $this->template->set_theme('admin_theme');
        $this->template->set_layout('default');        
        $this->template->set_partial('headers','template/blocks/header',$this->data);      
	}
}   