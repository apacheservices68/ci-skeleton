<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Template_theme extends MY_Controller {
    public $_data = array();
    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('filter'));
    }
    public function index()
    {
        #$this->template->title('Default page');
        $this->template->set_theme('default_theme');
        $this->template->set_layout('default');        
        $this->template->set_partial('header','template_theme/blocks/header',$this->_data);
        $this->template->set_partial('menu','template_theme/blocks/menu',$this->_data);
        $this->template->set_partial('slide','template_theme/blocks/slide',$this->_data);
        $this->template->set_partial('explode','template_theme/blocks/explode',$this->_data);
        #$this->template->set_partial('content','template_theme/blocks/content',$this->_data);
        $this->template->set_partial('footer','template_theme/blocks/footer',$this->_data);
    }
    
    public function blank()
    {
        $this->template->title('Default page');
        $this->template->set_theme('default_theme');
        $this->template->set_layout('default');
        
        $this->template->set_partial('header','template_theme/blocks/header',$this->_data);
        $this->template->set_partial('menu','template_theme/blocks/menu',$this->_data);
        $this->template->set_partial('slide','template_theme/blocks/slide',$this->_data);
        $this->template->set_partial('explode','template_theme/blocks/explode',$this->_data);
        #$this->template->set_partial('content','template_theme/blocks/content',$this->_data);
        $this->template->set_partial('footer','template_theme/blocks/footer',$this->_data);
        $this->template->build('template_theme/blocks/test');
    }
    
    public function blank1()
    {
        $group = 'writer';
		if (!$this->ion_auth->in_group($group))
		{
			$this->session->set_flashdata('message', 'You must be a writer to view this page');
			redirect('auth/login');
		}
        $this->template->title('Bootstrap page');
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        
        $this->template->set_partial('header','admin_theme/boostrap/header',$this->_data);
        $this->template->build('admin_theme/bs_content/test_blank');
    }
        
    public function handle_404()
    {
        $this->template->title('404 PAGE');
        $this->template->set_theme('default_theme');
        $this->template->set_layout('default');
        # Get category
        
        $this->_data['menu']                = '';        
        $this->_data['menu_top']            = '';
        $this->template->set_partial('header','sites/blocks/header',$this->_data);
        $this->template->set_partial('left_menu','sites/blocks/left_menu',$this->_data);
        $this->template->set_partial('new_letter','sites/blocks/new_letter',$this->_data);
        $this->template->set_partial('footer','sites/blocks/footer',$this->_data);
        $this->template->build('sites/404_pages');
    }
    
    
   
}
?>