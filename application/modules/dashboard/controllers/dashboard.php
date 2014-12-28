<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
class Dashboard extends MY_Controller {
    public $_data = array();        
    public $obj ;        
    public $setting ;    
    public $lang_index;
    public function __construct(){
        parent::__construct();
        $this->load->library(array('administrator/ion_auth','form_validation','grocery_CRUD'));
        $this->load->model(array('abouts','assets','socials','images','counters','plugins','language','product','category','product_meta','category_meta','newz','new_meta','setting'));
        if(!$this->session->userdata('cf24_admin_session_lang_name')){
            $this->session->set_userdata('cf24_admin_session_lang_name','vietnam');
        }
        $this->lang->load('private/admin_common',$this->session->userdata('cf24_admin_session_lang_name'));
        $this->load->helper('text');
        $this->config->load('dropdown');
        $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
        #
        if ( ! $this->ion_auth->logged_in()){
			return redirect('auth/login');
		}else{
            # Set quyen upload hinh va extend_delete hinh CKFINDER
            if(! $this->session->userdata('access')){
                if ($this->ion_auth->is_admin()){
        			$this->session->set_userdata('access','admin');
        		}else{
                    $this->session->set_userdata('access','writer');
                }
            }
        }
        #
        $this->handle_language();
        $this->setting = new stdClass();
        # Khai bao de insert vao trong bang ket giua hai bang chinh
        $this->obj = new StdClass();        
        $this->obj->prefix = '';
        $this->obj->table = '';
        # Kiem fra session last page
        if( !$this->input->is_ajax_request() && !$this->input->is_cli_request()){
            $this->session->set_userdata('ad_last_page',current_url());
        }
        # Kiem tra folder thang da co hay chua neu chua co thi tao folder thang
        #$this->move_recycle();
        # Xoa nhung gi khong phai la hinh
        //$dir = 'assets/uploads/contents/images/'.date('Y')."/".date('m').'/';
//        if(is_dir($dir))
//        {
//            if(Modules::run('upload/remove_non_image',$dir) === false)
//            {
//                log_message('error',"Khong xoa duoc hinh anh");
//            }    
//        }
        # Fetch setting object for all site        
        $setting = new Setting();
        foreach($setting->get()->all as $item){
            $name = $item->name;
            $this->setting->$name = $item->value;
        }
        $this->cr_dir();
    }
    
    public function index()
    {
        $this->template->title('Coffee house 24/7 Administrator');
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['is_index_admin']      = true;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->output->enable_profiler();
        $this->template->build('dashboard/content/default');
    }
    
    public function _example_output($output = null)
	{
	    $this->_data['output']              = $output;  
        $this->_data['is_index_admin']      = false;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
	}        
    
    public function lang_file(){
        if(!$this->session->userdata('cf24_admin_full_path_lang') || !is_file($this->session->userdata('cf24_admin_full_path_lang'))){
            return $this->handle_404();
        }
        $dir = $this->session->userdata('cf24_admin_full_path_lang');
        $temp = explode(".",end(explode("/",$dir)));
        $key = $temp[0];
        $text = file_get_contents($dir);        
        preg_match_all("/lang\[\'(.*)\'\]\s+\=\s+(\"|\')(.*)(\"|\')\;/",$text,$data);
        $list_key = $data[1];
        $list_value = $data[3];
        $val = array();
        foreach($list_key as $k=>$item){
            $val[$item] = $list_value[$k];
        }
        $items = '';
        foreach($val as $k=>$v){
            $this->form_validation->set_rules($k,$k,'required|callback_check_quote');
        }
        if($this->form_validation->run($this) !== FALSE){
            #$pattern = "\\\'";
            foreach($val as $k=>$v){
                $post = $this->input->post($k);               
                $post = preg_replace("/(\'|\")/", "\\'", $post);
                $text = str_replace($v,$post,$text);
            }
            $f = fopen($dir,'w');
            fwrite($f,$text);
            fclose($f);
            $this->session->set_flashdata('cf24_admin_success_edit_lang','Sửa thành công file '.$key.'.php với đường dẫn'.$dir);
            return redirect('dashboard/get_language','refresh');
        }
        $att = array('role'=>'form','id'=>$key,'name'=>$key);
        $items .= "<h4>File bạn chọn <i class='text-primary'>".$key.".php</i> Có đường dẫn <i class='text text-primary'>".$dir."</i></h4>";
        $items .= form_open_multipart('dashboard/lang_file',$att);
        $items .= "<button class='btn btn-default' type='submit' value='save' name='save' >Lưu lại</button>";        
        foreach($val as $k=>$v){
            $value = stripslashes($v);            
            $items .= '<div class="form-group">
            <label>'.$k.'</label>
            '.form_input(array('name'=>$k,'id'=>$k,'class'=>'form-control','value'=>$value,'max-length'=>'255')).'
            <p class="help-block">Kiểu text.</p>
            <p class="label label-danger">'.form_error($k).'</p>
            </div>';
        }
        $output = new stdClass;
        $items .= form_close();
        $output->output = ($items);
        $this->_data['output']              = $output;#var_dump(call_user_func(array($this,'preg_match_image'),$string)).var_dump(filter_list());  
        $this->_data['is_index_admin']      = true;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    
    
    public function aj_lang_file(){
        $this->load->library('listall');
        $dir = APPPATH.'language/'.$this->input->post('language').'/';
        if(!is_dir($dir)){
            return ;
        }
        $file = $this->listall->merge_array($this->listall->language($dir,true,0));
        $return = '';
        foreach($file as $k=>$v){
            $return .= '<option value="'.$k.'">'.$v.'</option>'."\n";
        }
        $this->output
        ->set_content_type('text/html')
        ->set_output(json_encode(array('join'=>($return))));        
    }
    
    public function get_language(){
        $output = new stdClass();
        
        $lang = new Language();
        $list_lang = array();
        foreach($lang->get()->all as $item){
            $list_lang[$item->name] = $item->name;
        }
        unset($lang);
        $this->form_validation->set_rules('language_name','ngôn ngữ','required'); 
        $this->form_validation->set_rules('language_file','file','required');
        
        $this->session->unset_userdata('cf24_admin_full_path_lang');
        
        if($this->form_validation->run($this) !== FALSE){
            $this->session->set_userdata('cf24_admin_full_path_lang',$this->input->post('language_file'));
            return redirect('dashboard/lang_file','refresh');
        }
        $item = '';
        $attribute = array('id'=>'frm_get_language','role'=>'form','class'=>'form-horizontal');
        
        $item .= form_open_multipart('dashboard/get_language',$attribute);
        
        $item .= ($this->session->flashdata('cf24_admin_success_edit_lang')) ? "<div class=\"form-group\"><div class=\"alert alert-success\" role=\"alert\">".$this->session->flashdata('cf24_admin_success_edit_lang')."</div></div>":'';
        $item .= "<div class=\"form-group\"><button class='btn btn-default' type='submit' value='Confirm edit language' name='save' >Confirm edit language</button></div>";
        $item .= '<div class="form-group">
                    <label>Chọn ngôn ngữ để hiển thị</label>
                    '.form_dropdown('language_name',$list_lang,'vietnam',' class ="form-control" id="language_name" onchange="fetch_lang_file()" ').'
                    <p class="label label-danger">'.form_error('language_name').'</p>
                </div>';
        $item .= '<div class="form-group">
                    <label>Chọn file</label>
                    '.form_dropdown('language_file',array(),'',' id="language_file" class ="form-control" ').'
                    <p class="label label-danger">'.form_error('language_file').'</p>
                </div>';
        
        $item .= form_close();
        $output->output = ($item);
        $this->_data['output']              = $output;#var_dump(call_user_func(array($this,'preg_match_image'),$string)).var_dump(filter_list());  
        $this->_data['is_index_admin']      = true;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    public function logs(){
        $path = APPPATH.'logs/';
        $output = new stdClass;
        $this->load->library('listall');
        $data = $this->listall->merge_array($this->listall->language($path,true,0));
        $return = $array = $test = $content = $fetch =  array(); $i = 0; $small = '';
        foreach($data as $k=>$v){
            $temp = filemtime($v);
            $return[$v] = $temp;
            $test[$temp] = $v;            
        }
        $sort = $this->quicksort(array_values($return));   
        $flip = array_flip($sort);     
        foreach($flip as $k=>$v){
            if($v == 7){
                break;
            }
            $small .= "<h3>".$test[$k]."</h3>";
            $f = fopen($test[$k],'r');
            while(!feof($f))   {                
                $small  .= "<p>{$i} <code>".fgets($f)."</code></p>\n";
                $i+=1;                            
            }
            fclose($f);
            unset($f);            
        }
        # Chi lay 7 file moi nhat nen se xoa het nhung file da cu
        if(count($flip)>7){
            foreach($flip as $k=>$v){
                if($v<7){
                    continue;
                }
                if(is_file($test[$k])){
                    unlink($test[$k]);
                }
            }
        }
        $output->output = $small;
        $this->_data['output']              = $output;#var_dump(call_user_func(array($this,'callback_upload'),$post_array));  
        $this->_data['is_index_admin']      = true;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    public function view()
    {
        $this->template->title('Coffee house 24/7 Administrator');
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        //$this->template->build('dashboard/content/default');
    }
    
    public function handle_language(){
        if(!$this->session->userdata('cf24_admin_session_lang')){
            $this->session->set_userdata('cf24_admin_session_lang',0);
        }        
        $this->lang_index = $this->session->userdata('cf24_admin_session_lang');
        return ;
    }    
   
    public function handle_404()
    {
        $this->template->title('Coffee house 404 not found');
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['is_index_admin']      = true;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('admin_theme/404_pages');
    }
    
    public function visiter(){
        $path = APPPATH.'counters/';
        $output = new stdClass; $err = true; $file = null;
        $i = 0; $small = '';
        if(!is_dir($path)){
            return $this->handle_404();
        }
        $dir = opendir($path);
        while(($line = readdir($dir))!== FALSE){
            if(preg_match("/ip\-.*/",$line)){
                $_temp = substr($line,10,-4);
                if(strcasecmp($_temp,date('m-d-Y',time()) === 0)){
                    $err = false;   
                    $file = $line;                                  
                }
            }
        }
        closedir($dir);
        if($err){
            return $this->handle_404();
        }
        if($file!== null && is_file($path.$file)){
            $small .= "<h3>".$file."</h3>";
            $f = fopen($path.$file,'r');
            $small .= "<table class='table table-striped'>";
            while(!feof($f)){
                $small  .= "<tr><td>";
                $small  .= "<p>{$i} <small class='text-primary'>".fgets($f)."</small></p>\n";
                $small  .= "</tr></td>";
                $i+=1;                            
            }
            $small .= "<table>";
            fclose($f);
        }
        $output->output = $small;
        $this->_data['output']              = $output;#var_dump(call_user_func(array($this,'callback_upload'),$post_array));  
        $this->_data['is_index_admin']      = true;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }   
    
    
    public function quicksort($data = array()){
        $length = count($data);
        if($length <= 1){
            return $data;
        }else{
            $moc = $data[0];
            $left = $right = array();
            # First with 1 because the pivot is 0
            for($i = 1;$i<count($data) ; $i++){
                if($data[$i] > $moc){
                    $left[] = $data[$i];
                }else{
                    $right[] = $data[$i];
                }
            }
        }
        return array_merge($this->quicksort($left),array($moc),$this->quicksort($right));
    }
    
    public function ip_remove(){
        $add = '';
        $this->load->library('blocking');
        $this->form_validation->set_rules('remove',"OHO",'required');
        if($this->form_validation->run($this) !== FALSE){
            $drop = $this->input->post('remove_list');
            if(!is_null($drop) && count($drop)>0){
                if(!$this->blocking->remove($drop)){
                    log_message("error","Gở không thành công tại ".__METHOD__);   
                }else{
                    $_list = implode(",",$drop);
                    $this->session->set_flashdata('cf24_flash_request_ip',"<p><div class=\"alert alert-success\" role=\"alert\">Gở thành công: {$_list}</div></p>");    
                }
            }
            redirect('dashboard/ip_remove','refresh');
        }
        $list = $this->blocking->get_list();        
        $add .= form_open_multipart('dashboard/ip_remove',array('role'=>'form','id'=>'ip_remove'));
        $add .= '<input type="hidden" name="remove" id="remove" value="remove" />';      
        if(!is_null($list) && count($list)>0){
            foreach($list as $item){
                $add .= '<div class="checkbox">
                            <label>
                                <input type="checkbox" name="remove_list[]" value="'.$item.'">'.$item.'
                            </label>
                        </div>';    
            }
        }
        $add .= form_submit(array('name'=>'check','value'=>"Gở ra","class"=>'btn btn-default'));      
        $add .= form_close();
        $add .= ($this->session->flashdata('cf24_flash_request_ip')) ? $this->session->flashdata('cf24_flash_request_ip') : '';
        $output = new stdClass();
        $output->output = $add;
        $this->_data['output']              = $output;  
        $this->_data['is_index_admin']      = true;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    public function ip_address(){
        $add = '';
        $this->load->library('blocking');
        $this->form_validation->set_rules('txt_ip',"Địa chỉ ip",'trim|required|valid_ip');
        if($this->form_validation->run($this) !== FALSE){
            $ip =  $this->input->post('txt_ip');
            if(!$this->blocking->add($ip)){
                log_message('error','Cant not add ip address '.$ip." at ".__METHOD__);
            }
            else{
                $this->session->set_flashdata('cf24_flash_request_ip',"<p><div class=\"alert alert-success\" role=\"alert\">Thêm thành công</div></p>");
            }
            redirect('dashboard/ip_address','refresh');
        }
        $list = $this->blocking->get_list();        
        $add .= form_open_multipart('dashboard/ip_address',array('role'=>'form','id'=>'ip_address'));
        $add .= '<div class="form-group"><label>Thêm địa chỉ chặn</label>
                    '.form_input(array('name'=>'txt_ip','id'=>'txt_ip','class'=>'form-control','value'=>'','max-length'=>'15')).'
                    <p class="help-block">Kiểu địa chỉ ip.</p>
                    <p class="label label-danger">'.form_error('txt_ip').'</p>
                </div>';      
        $add .= form_submit(array('name'=>'check','value'=>"Chặn","class"=>'btn btn-default'));      
        $add .= form_close();
        $add .= ($this->session->flashdata('cf24_flash_request_ip')) ? $this->session->flashdata('cf24_flash_request_ip') : '';
        if(!is_null($list)){
            $add .= "<p>".implode("</a> , <a href='javascript:;'>",$list)."</p>";    
        }
        $output = new stdClass();
        $output->output = $add;
        $this->_data['output']              = $output;  
        $this->_data['is_index_admin']      = true;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    public function check_ip(){
        $add = '';
        $this->form_validation->set_rules('txt_ip',"Địa chỉ ip",'trim|required|valid_ip');
        if($this->form_validation->run()!== FALSE){
            $ip =  $this->input->post('txt_ip');
            $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip={$ip}"));
            $return = '';            
            if(is_array($geo) || !is_null($geo)){
                $lat = $geo['geoplugin_latitude'];
                $long = $geo['geoplugin_longitude'];                
                $return .= '<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>';
                $return .= $this->add_maps($lat,$long);
                $return .= '<div id="map-canvas" style="width: 1200px;height: 300px;"></div>';
                $return .= "<table class='table table-responsive'>";
                $return .= '<tr><th colspan="2">From '.$ip.'</th></tr>';
                foreach($geo as $k=>$v){                        
                    $return .= "<tr>";
                    $return .= "<td>".$k."</td>";
                    $return .= "<td>".$v."</td>";
                    $return .= "</tr>";
                }
                $return .= "</table>";                
                
                
            }else{
                $return .= "<h4 class=\"text-danger\">Không có dữ liệu</h4>";
            }
            $this->session->set_flashdata('cf24_flash_request_ip',$return);
            redirect('dashboard/check_ip','refresh');
        }
        $add .= form_open_multipart('dashboard/check_ip',array('role'=>'form','id'=>'check_ip'));
        $add .= '<div class="form-group"><label>Kiểm tra địa chỉ ip đến từ đâu</label>
                    '.form_input(array('name'=>'txt_ip','id'=>'txt_ip','class'=>'form-control','value'=>'','max-length'=>'15')).'
                    <p class="help-block">Kiểu địa chỉ ip.</p>
                    <p class="label label-danger">'.form_error('txt_ip').'</p>
                </div>';      
        $add .= form_submit(array('name'=>'check','value'=>"Kiểm tra","class"=>'btn btn-default'));      
        $add .= form_close();
        $add .= "<p>&nbsp;</p>";
        $add .= ($this->session->flashdata('cf24_flash_request_ip'))? $this->session->flashdata('cf24_flash_request_ip') : '';
        $output = new stdClass();
        $output->output = $add;
        $this->_data['output']              = $output;  
        $this->_data['is_index_admin']      = true;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    
    public function move_recycle()
    {
        $flag = false;
        $path = 'assets/uploads/contents/images/temp/';
        $dir = opendir($path);
        while(($d = readdir($dir)) !== false)
        {
            if(preg_match("/^([\w\-\_]+)((\.(jpg|png|gif)){1})$/",$d))
            {
                if($this->direct_delete($path.$d))
                {
                    $flag = true;
                }
            }
        }
        closedir($dir);
        return $flag;
    }
    
    
    
    public function configs()
    {
        $output = new stdClass();
        $setting = new Setting();
        $all = $setting->get();
        $add = '';
        $this->template->title($this->lang->line('admin_default_title'));
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['output'] = '';
        $method = end(explode("::",__METHOD__));
        $class = strtolower(__CLASS__);
        $obj = new stdClass();
        foreach($all->all as $item)
        {
            $this->form_validation->set_rules($item->name,$item->description,'trim|required');
        }
        if($this->form_validation->run($this) !== false)
        {
            foreach($all->all as $item)
            {
                $setting->get_by_name($item->name);
                $setting->value = $this->input->post($item->name);
                $setting->save();
                $setting->refresh_all();     
            }
            $this->session->set_flashdata('add_success','Lưu thay đổi thành công');
            redirect(current_url(),'location');
        }
        else
        {
            $add .= '<div class="panel panel-default"><div class="panel-heading">'.ucfirst($method).'</div>
                    <div class="panel-body"><p>';
            $add .= ($this->session->flashdata('add_success')) ? '<p><div class="alert alert-success" role="alert">'.$this->session->flashdata('add_success').'</div></p>' : "";
            $attributes = array('role' => 'form', 'id' => strtolower($method));
            $add .= form_open_multipart($class."/".$method,$attributes);
            $add .= '<div class="form-group block col-md-6 col-md-offset-6 text-right">
                        '.form_button(array('name'=>'add','value'=>"Thêm mới","class"=>'btn btn-success','onclick'=>'window.location.href=\''.site_url($class.'/settings').'\''),'Thêm mới').'
                        '.form_submit(array('name'=>'save','value'=>"Lưu lại","class"=>'btn btn-default')).'                            
                        '.form_button(array('name'=>'cancel','value'=>"Hủy","class"=>'btn btn-warning','onclick'=>'window.location.href=\''.site_url($class.'/'.$method).'\''),'Hủy').'
                    </div>';   
            foreach($all->all as $item)
            {
                if($item->type == 'text')
                {
                    $add .= '<div class="form-group">
                            <label>'.ucwords($item->description).'</label>
                            '.form_input(array('name'=>$item->name,'id'=>$item->name,'class'=>'form-control','value'=>$item->value,'max-length'=>'255')).'
                            <p class="help-block">Kiểu text tối thiểu 1 ký tự.</p>
                            <p class="label label-danger">'.ucwords(form_error($item->name)).'</p>
                        </div>';    
                }
                if($item->type == 'textarea')
                {
                    $add .= '<div class="form-group">
                                <label>'.ucwords($item->description).'</label>
                                '.form_textarea(array('name'=>$item->name,'id'=>$item->name,'class'=>'form-control','value'=>$item->value,'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring tối đa 500 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($item->name)).'</p>
                            </div>';
                }
                if($item->type == 'ckeditor_textarea')
                {
                    $add .= '<div class="form-group">
                                <label>'.ucwords($item->description).'</label>
                                '.form_textarea(array('name'=>$item->name,'id'=>$item->name,'class'=>'form-control ckeditor','value'=>$item->value,'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring tối đa 500 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($item->name)).'</p>
                            </div>';
                }            
                elseif($item->type == 'radio')
                {
                    $add .= '<div class="form-group">
                            <label>'.ucwords($item->description).'</label>';
                    if($item->value == 1){
                        $add .= '<div class="radio"><label>';
                        $add .= form_radio(array('name'=>$item->name,'value'=>1,'checked'=>TRUE));
                        $add .= 'Kích hoạt</label></div>'; $add .= '<div class="radio"><label>';
                        $add .= form_radio(array('name'=>$item->name,'value'=>0));
                        $add .= 'Ngưng kích hoạt</label></div>';
                        
                    }else{
                        $add .= '<div class="radio"><label>';
                        $add .= form_radio(array('name'=>$item->name,'value'=>1));
                        $add .= 'Kích hoạt</label></div>'; $add .= '<div class="radio"><label>';
                        $add .= form_radio(array('name'=>$item->name,'value'=>0,'checked'=>TRUE));
                        $add .= 'Ngưng kích hoạt</label></div>';
                    }
                    $add     .= '<p class="help-block">Kích hoạt hoặc không kích hoạt.</p>
                                </div>';    
                }
            }
            $add .= form_close();
            $add .= '</p></div><div class="panel-footer">End '.ucfirst($method).'</div></div>';
            $output->output = $add;
            $output->css_files = null;
            $output->js_files = array(base_url('assets/editor/ckeditor/ckeditor.js'));
            $this->_data['output'] = $output;
        }
        # Concat string 
        $this->_data['is_index_admin']      = false;
        $js = '';        
        $this->_data['js_custom'] = $js;
        
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    public function categories($action,$id = null,$sort = null,$page = 1)
    {        
        if( ! preg_match("/^[\w\_]+$/",$action))
        {
            return Modules::run('dashboard/handle_404');
        }      
        # Phan khoi tao dau ham .
        $this->load->library('form_validation');
        $this->template->title($this->lang->line('admin_default_title'));
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['output'] = '';
        $method = end(explode("::",__METHOD__));
        $class = strtolower(__CLASS__);
        if($action == 'add')
        {
            $cat    = new Category();
            $lang   = new Language();
            $output = new stdClass();
            $list_lang = $list_id_lang = $list_lang_keyword = $list_lang_title = $list_lang_description = $list_slug_title = $list_id_parent_id = $arr_meta = array();             
            $meta_type_arr = array('seo','google','facebook','twitter');            
            $add = '';
            foreach($lang->get() as $item)
            {
                $list_id_lang[] = $item->id;
                $list_lang[] = $item->name;
            }
            foreach($list_lang as $item){
                $this->form_validation->set_rules(substr($item,0,2).'_title','Tiêu đề '.substr($item,0,2),'trim|required|min_length[6]|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_description','Mô tả '.substr($item,0,2),'trim|required|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_keyword','Keyword '.substr($item,0,2),'trim|required|min_length[10]|max_length[255]|callback_check_keyword');              
            }              
            $this->form_validation->set_rules('status','Tiêu đề','required');
            $this->form_validation->set_rules('parent_id','Nguồn gốc','required');
            $this->form_validation->set_rules('type','Kiểu menu','trim|required');
            if (!empty($_FILES['img_avatar']['name']))
            {
                    
            }
            else
            {
                $this->form_validation->set_rules('img_name','Hình đại diện','required');   
            }
            if($this->form_validation->run($this) !== FALSE)
            {
                $this->load->library(array('upload','image_lib'));                
                $this->load->helper('thumbnail');
                $author      = 'Vinh Banh';
                $ggplus      = settings::google_plus_author;
                $title = $this->input->post('vi_title');
                $description = $this->input->post('vi_description');                
                $slug = url_title(convert_accented_characters($this->input->post('vi_title')));
                
                foreach($list_lang as $item)
                {
                    $substr = substr($item,0,2);
                    $list_lang_title[] = $this->input->post($substr.'_title');
                    $list_slug_title[] = url_title(convert_accented_characters($this->input->post($substr.'_title')));
                    $list_lang_description[] = $this->input->post($substr.'_description');
                    $list_lang_keyword[] = $this->input->post($substr.'_keyword');
                    //$list_slug_title[] = 
                }
                $status      = $cat->status    = $this->input->post('status');
                $type        = $cat->type      = $this->input->post('type');
                $parent_id   = $cat->parent_id = $this->input->post('parent_id');
                $keyword     = $this->input->post('vi_keyword');
                # Phan xu ly hinh anh 
                $cat->img_avatar = "no_picture.gif";
                $path = 'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';                
                # allowed types - max size - name - path - content_type - slug
                $post_data = array(
                                    'allowed_types'=>'jpg|png|gif',
                                    'max_size'=>'2000',
                                    'name'=>'img_avatar',
                                    'slug'=> strtolower($slug),
                                    'path'=>$path,
                                    'content_type' => false,
                                    'type' => 'categories'
                                );
                Modules::run('upload/handler',$post_data);                
                $image = $cat->img_avatar = Modules::run('upload/get_image');
                #                
    			if($cat->save_languages($lang->all))
                {
                    # Phan ngon ngu                                        
                    $_id = $cat->id;
                    foreach($list_id_lang as $k => $v)
                    {
                        $lang_id      = $list_id_lang[$k];
                        $this->obj->id = $lang_id;
                        $cat->set_join_field('languages','title',$list_lang_title[$k],$this->obj);
                        $cat->set_join_field('languages','description',$list_lang_description[$k],$this->obj);
                        $cat->set_join_field('languages','keyword',$list_lang_keyword[$k],$this->obj);
                        $cat->set_join_field('languages','slug_title',$list_slug_title[$k],$this->obj);
                    }
                    $cat->refresh_all();
                    $vars = array('type'=>'category','meta_type_arr'=>$meta_type_arr,'description'=>$description,'keyword'=>$keyword,'title'=>$title,'slug'=>$slug,'image'=>$image,'_id'=>$_id);
                    $this->handle_meta($vars);
                    # Phan the meta
                    $this->session->set_flashdata('add_success',$this->lang->line('admin_success_add'));
                    redirect(current_url(),'refresh');
                }
                # Code here
            }
            else
            {
                $dir = opendir('assets/uploads/temp/');
                if($dir)
                {
                    while(($file = readdir($dir)) !== FALSE)
                    {
                        if(preg_match("/^([\w\-\_]+)((\.(jpg|png|gif)){1})$/",$file))
                        {
                            @unlink('assets/uploads/temp/'.$file);
                        }
                    }
                }
                $list_id_parent_id[0] = ' -- Cha -- ';
                $metadata = new stdClass();
                $metadata->all = $cat->get()->all;
                foreach($metadata->all as $v)
                {
                    $cat->get_by_id($v->id);
                    $list_id_parent_id[$v->id] = $v->id." | ".character_limiter($cat->language->include_join_fields()->get()->all[$this->lang_index]->join_title,100);
                }
                
                $add .= '<div class="panel panel-default"><div class="panel-heading">'.ucfirst($method).'</div>
                        <div class="panel-body"><p>';
                $add .= ($this->session->flashdata('add_success')) ? '<p><div class="alert alert-success" role="alert">'.$this->session->flashdata('add_success').'</div></p>' : "";
                $attributes = array('role' => 'form', 'id' => strtolower($method));
                $add .= form_open_multipart(strtolower(__CLASS__)."/".$method."/".$action,$attributes);
                $add .= '<div class="form-group block col-md-6 col-md-offset-6 text-right">
                            '.form_submit(array('name'=>'add','value'=>"Thêm mới","class"=>'btn btn-default')).'                            
                            '.form_button(array('name'=>'cancel','value'=>"Hủy","class"=>'btn btn-warning','onclick'=>'window.location.href=\''.site_url($class.'/'.$method).'\''),'Hủy').'
                        </div>';
                #$add .= '<script src="'.base_url('assets/editor/ckeditor/ckeditor.js').'"></script>';
                # There here is place the code if else language
                
                foreach($list_lang as $item)
                { 
                    $add .= '<p ><h2 class="text-primary">'.ucfirst($item).'</h2></p>';
                    $segment = substr($item,0,2);
                    $add .= '<div class="form-group">
                                <label>Tiêu đề</label>
                                '.form_input(array('name'=>$segment.'_title','id'=>$segment.'_title','class'=>'form-control','value'=>set_value($segment.'_title'),'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_title')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Mô tả thể loại</label>
                                '.form_textarea(array('name'=>$segment.'_description','id'=>$segment.'_description','class'=>'form-control ckeditor','value'=>set_value($segment.'_description'),'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_description')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Keyword</label>
                                '.form_input(array('name'=>$segment.'_keyword','id'=>$segment.'_keyword','class'=>'form-control','value'=>set_value($segment.'_keyword'),'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài cách nhau dấu phẩy.</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_keyword')).'</p>
                            </div>';
                }
                # There here is place the code if else language
                $add .= '<div class="form-group">
                            <label>Hiển thị</label>
                            '.form_dropdown('status',array('active'=>'active','deactive'=>'deactive'),'active',' class ="form-control" ').'
                        </div>';
                $add .= '<div class="form-group">
                            <label>Cha</label>
                            '.form_dropdown('parent_id',$list_id_parent_id,'0',' class ="form-control" ').'
                            <p class="help-block">Xác định row này có nhánh hay không.</p>
                            <p class="label label-danger">'.ucwords(form_error('parent_id')).'</p>
                        </div>';
                $add .= '<div class="form-group">
                            <label>Kiểu menu</label>
                            '.form_dropdown('type',array('news'=>'Tin tức','products'=>'Sản phẩm'),'news',' class ="form-control" ').'
                        </div>';
                $add .= '<div class="form-group">
                            <label>Hình đại diện</label>
                            '.form_upload(array('name'=>'img_avatar','id'=>'img_avatar','value'=>'','class'=>'form-control')).'
                            '.form_hidden(array('name'=>'img_name','id'=>'img_name','value'=>'','style'=>'display:none;')).'
                            <p class="help-block">Hình ảnh cho row rất quan trọng.</p>
                            <p class="label label-danger">'.ucwords(form_error('img_name')).'</p>
                        </div>';
                                              
                $add .= form_close();
                $add .= '</p></div><div class="panel-footer">End Categories</div></div>';
                
                $output->output = $add;
                $output->css_files = null;
                $output->js_files = array(base_url('assets/editor/ckeditor/ckeditor.js'));
                $this->_data['output'] = $output;
            }
        }
        elseif($action == 'view' || $action == '')
        {
            # Initialize
            $this->load->library(array('table','Arr_obj'));
            $this->load->helper('photo');
            $cat = new Categories();
            $output = new stdClass();
            $arr = new Arr_obj;            
            $data = $list_key = $datalist = array(); $add = $pagination = $actions = ''; $i  = 1; $dem = 0; $paging = 2; $default_status = 'active';
            $href = ''; $return_null = false;
            $dropdown = $cat->get();
            $cat->refresh_all();
            # Phan extend_delete va update active or deactive
            
		    if($this->input->post('drop') && is_array($this->input->post('drop')) && count($this->input->post('drop'))>0)
            {
                if (!$this->ion_auth->is_admin())
        		{
        			$this->set_flashdata('status_message',settings::status_message_non_permission);
                    return redirect(site_url($class.'/'.$method),'location');
        		}
                $delete_type = ($this->input->post('delete_type')) ? $this->input->post('delete_type') : 'categories';
                $_id  = $this->input->post('drop');
                if($this->input->post('action_status') && $this->input->post('action_status') != ''){
                    $actions = ($this->input->post('action_status')) ? $this->input->post('action_status') : $default_status;
                    foreach($_id as $item){
                        if($this->active_or_deactive($item,$delete_type,$actions)){
                            $dem +=1;
                        }
                    }
                }else{                    
                    foreach($_id as $item){
                        if($this->extend_delete($item,$delete_type)){
                            $dem +=1;
                        }
                    }
                }
                if(($dem > 0) && $dem == count($_id)){
                    if($actions != ''){
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_update').implode(",",$_id));    
                    }
                    else{
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_delete').implode(",",$_id));
                    }
                    return redirect(site_url($class.'/'.$method.'/view'),'location');
                }
                $dem = 0; # Lam xong reset bien de danh sai lai
            }
            # Phan search
            if($this->input->post($method.'_search') && $this->input->post($method.'_search') != '' && strlen($this->input->post($method.'_search'))>=2)
            {
                $_search_type = $this->input->post('search_type'); 
                $search  =  trim($this->input->post($method.'_search'));
                $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_search').' '.$search);
                redirect(site_url($class.'/'.$method.'/'.$action.'/'.$_search_type.':'.$this->url_decode($search).'/asc/1'),'location');
            }
            # Phan active and deactive            
            
            # Phan set session cho so trang hien thi
            
            if(!$this->session->userdata('num_of_page'))
            {
                $this->session->set_userdata('num_of_page',settings::default_paging);
            }
            if($this->input->post('num_of_page') && $this->input->post('num_of_page')!=$this->session->userdata('num_of_page'))
            {
                $this->session->set_userdata('num_of_page',(int)$this->input->post('num_of_page'));
            }
            $paging = $this->session->userdata('num_of_page');
            
            # Lay list key add vao dropdown list
            
            $obj = array_keys($arr->objectToArray($dropdown->stored));
                 
            unset($obj[2]);
            foreach($obj as $key=>$val)
            {
                $list_key[strtolower($val)] = ucfirst($val);
            }
            # Phan Create Form 
            $naming = strtolower(substr($method,0,2))."_".strtolower($method);
            $attributes = array('role' => 'form', 'id' => $naming,'name'=>$naming);
            $add .= form_open_multipart(strtolower(__CLASS__)."/".$method."/".$action,$attributes);
            $add .= form_hidden('delete_type', $method);
            $add .= ($this->session->flashdata('status_message')) ? '<div class="alert alert-warning" role="alert">'.$this->session->flashdata('status_message')."</div>" : ''; 
            $add .= '<div class="row">';
            $add .= '<div class="form-group col-md-1"><a class="btn btn-default" href="'.site_url($class.'/'.$method).'"><i class="fa fa-arrow-left"></i> List</a>                        
                    </div> ';
            $add .= '<div class="form-group col-md-1"><a class="btn btn-primary" href="'.site_url($class.'/'.$method.'/add').'"><i class="fa fa-plus"></i> Thêm mới</a>                        
                    </div>';
            $add .= '<div class="form-group col-md-1"><a class="btn btn-success" href="'.site_url($class.'/save_excel_file').'"><i class="fa fa-file-excel-o"></i> Xuất excel</a>                        
                    </div> ';
            $add .= '<div class="form-group col-md-1">&nbsp;<input class="btn btn-warning" type="submit" name="delete" id="delete" value="delete" onclick="return check_remove(\''.$naming.'\')"/></div>';            
            $add .= "</div>";
            
            
            $add .= '<div class="row">';
            $add .= '<div class="form-group col-md-2">
                        '.form_input(array('name'=>$method.'_search','id'=>$method.'_search','class'=>'inline form-control __search','value'=>set_value($method.'_search'),'placeholder'=>'Type here and select next','max-length'=>'255','onsubmit'=>'$(\''.$naming.'\').submit()')).'
                    </div>';
            $add .= '<div class="form-group col-md-2">
                        '.form_dropdown('search_type',$list_key,'','onchange="search_form_data_list($(this))" class =" inline form-control" ').'
                    </div> ';            
            $add .= '<div class="form-group col-md-2 pull-right">
                        '.form_dropdown('action_status',array(''=>'Chọn action','deactive'=>'Ngưng kích hoạt','active'=>'Kích hoạt'),'','class ="form-control" onchange="fn_active_or_deactive(\''.$naming.'\',$(this))" ').'
                    </div>';                        
            $add .= '</div>';
            $pagination .= '<ul class="pagination">';
            if(!is_null($id))
            {
                $_sort = 'asc';
                if(isset($sort))
                {
                   $_sort = $sort; 
                }
                # Phan nay search 
                if( ! array_key_exists($id,$list_key))
                {
                    $handle_id = explode(":",$id);
                    $handle_search = (count($handle_id)==2) ? $this->url_decode(end($handle_id)) : null;                   
                    $search_type = ($handle_search !== null) ? $handle_id[0] : 'id';
                    if($search_type == 'id')
                    {
                        if(!preg_match("/^[^0][0-9]{1,11}$/",$handle_search))
                        {
                            $return_null = true;
                        }
                    }
                    else
                    {
                        if(!preg_match("/^([\w]+)([\s]?)/",$handle_search))
                        {
                            $return_null = true;
                        }
                    }
                    if($return_null)
                    {
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_failed_search'));
                        return redirect(site_url($class.'/'.$method),'location');
                    }   
                    else
                    {
                        $test_search_has_array  = explode(" ",$handle_search);
                        if(count($test_search_has_array)>1)
                        {
                            $i = 1;
                            foreach($test_search_has_array as $item)
                            {
                                if($i == 1){
                                    $cat->like($search_type,$item, 'both');
                                }                                
                                elseif($i == count($test_search_has_array))
                                {
                                    $cat->or_like($search_type,$handle_search, 'both');
                                }
                                else{
                                    $cat->or_like($search_type,$item, 'both');
                                }
                                $i+=1;
                            }
                        }
                        else
                        {
                            $cat->like($search_type,$handle_search, 'both');  
                              
                        }
                        $i = 0 ;
                        $id = $search_type.':'.$this->url_encode($handle_search);
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_search').' '.$handle_search);
                        #redirect(site_url($class.'/'.$method.'/'.$action.'/'.$id),'location');
                    }
                }
                else
                {
                    $cat->order_by($id,$_sort);    
                }
            }
            else
            {
                $method  = $method; $action = $action; $id = 'id'; $sort = 'asc';    
            }
            if(isset($page) && $page != 0)
            {
                $cdata = new stdClass();
                $cat->get_paged($page,$paging);
                $cdata->all = $cat->all;
                $j = 0 ;
                foreach($cdata->all as $item)
                {
                    $cat->get_by_id($item->id);
                    $cdata->all[$j]->join_title = $cat->language->include_join_fields()->get()->all[$this->lang_index]->join_title;
                    $j+=1;
                }
                #$c = $cat->get_paged($page,$paging);
                #$c->language->include_join_fields()->get()->all[0]->join_title;
                if($cat->paged->has_previous)
                {
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.'1').'"><i class="fa fa-angle-double-left"></i> First</a></li>';
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.$cat->paged->previous_page).'"><i class="fa fa-angle-left"></i> Prev</a></li>';
                }
                if($cat->paged->has_next)
                {
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.$cat->paged->next_page).'">Next <i class="fa fa-angle-right"></i></a></li>';
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.$cat->paged->total_pages).'">Last <i class="fa fa-angle-double-right"></i></a></li>';
                }
            }
            $pagination .= '</ul>';
            $list_heading = array("","Identity","Image","Status","Parent","Type","Functions");
            $data[0] = "<input type='checkbox' class='sr check_all' name='check_all' id='check_all' />";
            $value = $this->url_decode($id); 
            foreach($obj as $item=>$val)
            {
                if(strcasecmp($val,$id) !== false)
                {
                    $value = $val;
                }
                $data[] = '<a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$value.'/asc/'.$page).'" class="label label-primary" title="ASC"><i class="fa fa-caret-up"></i></a> '.$list_heading[$i].' <a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$value.'/desc'.'/'.$page).'" class="label label-success" title="DESC"><i class="fa fa-caret-down"></i></a>';
                $i += 1;    
            }
            $data[count($list_heading)] = "Functions";
            $this->table->set_heading($data);
            $tmpl = array ( 'table_open'  => '<table class="table">' );
            $this->table->set_template($tmpl);
            $i = 0;
            foreach($cdata->all as $item)
            {
                $datalist[] = array($item->id,$item->img_avatar,$item->status,$item->parent_id,$item->type);
                $photo = showPhoto(base_url($item->img_avatar),array('width'=>100,'class'=>'img-responsive'));
                $function = '<a href="'.site_url($class.'/'.$method.'/'.'edit'.'/'.$item->id).'" class="label label-info" data-toggle="tooltip" data-placement="top" title="Edit '.$item->id.'"><i class="fa fa-pencil"></i></a>';
                $this->table->add_row(array('<input type="checkbox" class="sr" name="drop[]" value="'.$item->id.'" />',$item->id." | ".character_limiter($item->join_title,100),$photo,$item->status,$item->parent_id,$item->type,$function));
                $i++;                
            }
            $this->session->set_userdata('datalist',$datalist);
            $this->session->set_userdata('export_type',$method);
            $this->session->set_userdata('export_stored',$obj);
            $add .= $this->table->generate();
            $add .= '<div class="row">';
            $add .= $pagination;
            $add .= '<div class=" form-group col-md-2 pull-right ">                        
                        '.form_dropdown('num_of_page',array('10'=>'10 rows','25'=>'25 rows','50'=>'50 rows','100'=>'100 rows'),$paging,'class ="form-control inline" onchange="set_num_of_page(\''.$naming.'\',$(this))"').'
                    </div></div>'; 
            
            $add .= '<script>var update_status_message = \''.$this->lang->line('update_status_message').'\';var remove_message = \''.$this->lang->line('remove_message').'\';var failed_confirm = \''.$this->lang->line('checkin').'\'</script>';
            $add .= form_close();
            # End create form .
            $output->output = $add;
            $output->css_files = null;
            $output->js_files = array(base_url('assets/editor/ckeditor/ckeditor.js'));
            $this->_data['output'] = $output;
        }
        elseif($action == 'edit')
        {
            # Xu ly du lieu dau vao
            if($id == null && !preg_match("/^[^0][\d]+$/",$id))
            {
                return modules::run('dashboard/handle_404');
            }            
            $cat = new Category($id);
            if(count($cat->all) == 0)
            {
                return modules::run('dashboard/handle_404'); 
            }
            #
            $this->load->helper(array('thumbnail','photo'));
            $lang   = new Language();
            $output = new stdClass();
            $i = 0;
            $photo = showPhoto(base_url($cat->img_avatar),array('width'=>100,'class'=>'img-responsive'));
            
            $list_lang = $list_id_lang = $list_join_field = $list_lang_keyword = $list_lang_title = $list_lang_description = $list_slug_title = $arr_meta = array();             
            $meta_type_arr = array('seo','google','facebook','twitter'); $add = ''; $preg_img = "/^([\w\-\_]+)([\.]{1}(jpg|png|gif))$/";
            foreach($lang->get() as $item)
            {
                $list_id_lang[] = $item->id;
                $list_lang[] = $item->name;
            }
            foreach($list_lang as $item){
                $this->form_validation->set_rules(substr($item,0,2).'_title','Tiêu đề '.substr($item,0,2),'trim|required|min_length[6]|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_description','Mô tả '.substr($item,0,2),'trim|required|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_keyword','Keyword '.substr($item,0,2),'trim|required|min_length[10]|max_length[255]|callback_check_keyword');              
            }              
            $this->form_validation->set_rules('status','Tiêu đề','required');
            $this->form_validation->set_rules('type','Kiểu menu','trim|required');
            
            if($this->form_validation->run($this) !== FALSE)
            {
                # Phan kiem tra co hinh anh duoc upload hay khong
                $image_file = null;
                if($_FILES)
                {
                    $files = $_FILES['img_avatar']['name'];
                    $type  = $_FILES['img_avatar']['type'];
                    if (!empty($files))
                    {
                        if(preg_match($preg_img,$files) && $this->is_image($type))
                        {
                            $this->delete_image_category($cat->img_avatar,'products');
                            $image_file = $files;
                        }
                        else
                        {
                            $this->session->set_flashdata('status_message','If you choose an image upload you need the file is sure an image type .');
                            redirect(site_url($class.'/'.$method.'/'.$action.'/'.$id),'location');    
                        }
                    }
                }
                # Phan lay du lieu cho table meta
                $title = $this->input->post('vi_title');
                $description = $this->input->post('vi_description');
                $keyword = $this->input->post('vi_keyword');
                $slug = url_title(convert_accented_characters($this->input->post('vi_title')));
                $type        = $cat->type      = $this->input->post('type');
                $status      = $cat->status    = $this->input->post('status');
                $this->active_or_deactive($id,strtolower($method),$status);            
                
                foreach($list_lang as $item)
                {
                    $substr = substr($item,0,2);
                    $list_lang_title[] = strtolower($this->input->post($substr.'_title'));
                    $list_slug_title[] = url_title(convert_accented_characters(strtolower($this->input->post($substr.'_title'))));
                    $list_lang_description[] = strtolower($this->input->post($substr.'_description'));
                    $list_lang_keyword[] = strtolower($this->input->post($substr.'_keyword'));
                    //$list_slug_title[] = 
                }
                
                $ext         = null;
                $pathImage = "assets/uploads/temp/";                            
                #$cat->img_avatar = $image_file;
                $imageTemp = "";
                $image = $cat->img_avatar;
                # Image           
                			
                # Initialize Config Image
                if($image_file !== null)
                {
                    $path = 'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';                
                    # allowed types - max size - name - path - content_type - slug
                    $post_data = array(
                                        'allowed_types'=>'jpg|png|gif',
                                        'max_size'=>'2000',
                                        'name'=>'img_avatar',
                                        'slug'=> strtolower($slug),
                                        'path'=>$path,
                                        'content_type' => false,
                                        'type' => 'categories'
                                    );
                    Modules::run('upload/handler',$post_data);                
                    $image = $cat->img_avatar = Modules::run('upload/get_image');     
                }
                $cat->save(); 
                # Phan ngon ngu                                        
                $_id = $id;
                foreach($list_id_lang as $k => $v)
                {
                    $lang_id      = $list_id_lang[$k];
                    $this->obj->id = $lang_id;
                    $cat->set_join_field('languages','title',$list_lang_title[$k],$this->obj);
                    $cat->set_join_field('languages','description',$list_lang_description[$k],$this->obj);
                    $cat->set_join_field('languages','keyword',$list_lang_keyword[$k],$this->obj);
                    $cat->set_join_field('languages','slug_title',$list_slug_title[$k],$this->obj);
                }
                $cat->refresh_all();
                # Phan the meta
                $vars = array('type'=>'category','edit'=>true,'meta_type_arr'=>$meta_type_arr,'description'=>$description,'keyword'=>$keyword,'title'=>$title,'slug'=>$slug,'image'=>$image,'_id'=>$_id);
                $this->handle_meta($vars);
                #
                $this->session->set_flashdata('add_success',$this->lang->line('admin_success_update').' '. $id);
                redirect(site_url($class.'/'.$method.'/'.$action.'/'.$id),'refresh');
                # Code here
            }
            else
            {
                # Xoa hinh chua upload thanh cong
                $dir = opendir('assets/uploads/temp/');
                if($dir)
                {
                    while(($file = readdir($dir)) !== FALSE)
                    {
                        if(preg_match("/^([\w\-\_]+)((\.(jpg|png|gif)){1})$/",$file))
                        {
                            @unlink('assets/uploads/temp/'.$file);
                        }
                    }
                }
                # Lay thong tin ngon ngu trong bang join field
                $join = new Category($id);
                foreach($join->language->include_join_fields()->get()->all as $k=>$item)
                {
                    $list_join_field[$k]['title'] = $item->join_title;
                    $list_join_field[$k]['keyword'] = $item->join_keyword;
                    $list_join_field[$k]['description'] = $item->join_description;
                }
                # Bat dau tao form 
                $add .= '<div class="panel panel-default"><div class="panel-heading">'.ucfirst($method).'</div>
                         <div class="panel-body"><p>';
                $add .= ($this->session->flashdata('add_success')) ? '<p><div class="alert alert-success" role="alert">'.$this->session->flashdata('add_success').'</div></p>' : "";
                $attributes = array('role' => 'form', 'id' => strtolower($method));
                $add .= form_open_multipart(strtolower(__CLASS__)."/".$method."/".$action.'/'.$id,$attributes);
                $add .= '<div class="form-group block col-md-6 col-md-offset-6 text-right">
                            '.form_submit(array('name'=>'add','value'=>"Lưu lại","class"=>'btn btn-default')).'
                            '.form_button(array('name'=>'back','value'=>"","class"=>'btn btn-primary'),'Thêm và return lại list').'
                            '.form_button(array('name'=>'cancel','value'=>"Hủy","class"=>'btn btn-warning','onclick'=>'window.location.href=\''.site_url($class.'/'.$method).'\''),'Hủy').'
                         </div>';
                #$add .= '<script src="'.base_url('assets/editor/ckeditor/ckeditor.js').'"></script>';
                # There here is place the code if else language
                foreach($list_lang as $k=>$item)
                { 
                    $add .= '<p ><h2 class="text-primary">'.ucfirst($item).'</h2></p>';
                    $segment = substr($item,0,2);
                    $add .= '<div class="form-group">
                                <label>Tiêu đề</label>
                                '.form_input(array('name'=>$segment.'_title','id'=>$segment.'_title','class'=>'form-control','value'=>$list_join_field[$k]['title'],'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_title')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Mô tả thể loại</label>
                                '.form_textarea(array('name'=>$segment.'_description','id'=>$segment.'_description','class'=>'form-control ckeditor','value'=>$list_join_field[$k]['description'],'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_description')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Keyword</label>
                                '.form_input(array('name'=>$segment.'_keyword','id'=>$segment.'_keyword','class'=>'form-control','value'=>$list_join_field[$k]['keyword'],'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài cách nhau dấu phẩy.</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_keyword')).'</p>
                            </div>';
                    $i++;
                }
                $i = 0;
                # There here is place the code if else language
                $add .= '<div class="form-group">
                            <label>Hiển thị</label>
                            '.form_dropdown('status',array('active'=>'active','deactive'=>'deactive'),$cat->status,' class ="form-control" ').'
                        </div>';
              
                $add .= '<div class="form-group">
                            <label>Kiểu menu</label>
                            '.form_dropdown('type',array('news'=>'Tin tức','products'=>'Sản phẩm'),$cat->type,' class ="form-control" ').'
                        </div>';
                
                $add .= '<div class="form-group">
                            <label>Hình đại diện</label>
                            '.form_upload(array('name'=>'img_avatar','id'=>'img_avatar','value'=>'','class'=>'form-control')).'
                            '.form_hidden(array('name'=>'img_name','id'=>'img_name','value'=>'','style'=>'display:none;')).'
                            <p class="help-block">'.$photo.'</p>
                            <p class="label label-danger">'.ucwords(form_error('img_name')).'</p>
                        </div>';
                                              
                $add .= form_close();
                $add .= '</p></div><div class="panel-footer">End '.$method.'</div></div>';
                $output->output = $add;
                $output->css_files = null;
                $output->js_files = array(base_url('assets/editor/ckeditor/ckeditor.js'));
                $this->_data['output'] = $output;
            }
        }
        # Concat string 
        $this->_data['is_index_admin']      = false;
        $js = '';        
        $this->_data['js_custom'] = $js;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    public function products($action,$id = null,$sort = null,$page = 1)
    {        
        if( ! preg_match("/^[\w\_]+$/",$action))
        {
            return Modules::run('dashboard/handle_404');
        }      
        # Phan khoi tao dau ham $cat.
        $this->load->library('form_validation');
        $this->template->title($this->lang->line('admin_default_title'));
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['output'] = '';
        $method = end(explode("::",__METHOD__));
        $class = strtolower(__CLASS__);
        $full_path = $class.'/'.$method.'/';
        if($action == 'add')
        {
            $pro    = new Product();
            $lang   = new Language();
            $cat    = new Category();
            $output = new stdClass();
            $list_lang = $list_id_lang = $_list_content = $list_lang_keyword = $list_ingredient = $list_content = $list_price = $list_lang_title = $list_lang_description = $list_slug_title = $list_id_parent_id = $arr_meta = array();             
            $meta_type_arr = array('seo','google','facebook','twitter');            
            $add = '';
            foreach($lang->get() as $item)
            {
                $list_id_lang[] = $item->id;
                $list_lang[] = $item->name;
            }
            foreach($list_lang as $item){
                $this->form_validation->set_rules(substr($item,0,2).'_title','Tiêu đề '.substr($item,0,2),'trim|required|min_length[6]|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_description','Mô tả '.substr($item,0,2),'trim|required|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_keyword','Keyword '.substr($item,0,2),'trim|required|min_length[10]|max_length[255]|callback_check_keyword');              
                $this->form_validation->set_rules(substr($item,0,2).'_ingredient','Thành phần '.substr($item,0,2),'trim|required');
                $this->form_validation->set_rules(substr($item,0,2).'_content','Nội dung '.substr($item,0,2),'trim|required|min_length[10]');
                $this->form_validation->set_rules(substr($item,0,2).'_price','Giá tiền '.substr($item,0,2),'trim|required|is_numeric');
            }              
            $this->form_validation->set_rules('status','Tiêu đề','required');
            $this->form_validation->set_rules('category_id','Thuộc về','required');
            if (!empty($_FILES['image']['name']))
            {
                    
            }
            else
            {
                $this->form_validation->set_rules('img_name','Hình đại diện','required');   
            }
            if($this->form_validation->run($this) !== FALSE)
            {
                $this->load->library(array('upload','image_lib'));                
                $this->load->helper('thumbnail');
                
                $author      = 'Vinh Banh';
                $ggplus      = settings::google_plus_author;
                
                $title       = $this->input->post('vi_title');
                $description = $this->input->post('vi_description');
                $keyword     = $this->input->post('vi_keyword');                
                $slug        = strtolower(url_title(convert_accented_characters($this->input->post('vi_title'))));
                
                $i = 0 ;
                $_content = $replace = array();
                $path = 'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';
                foreach($list_lang as $item)
                {
                    $substr = substr($item,0,2);
                    $list_lang_title[] = $this->input->post($substr.'_title');
                    $list_slug_title[] = url_title(convert_accented_characters($this->input->post($substr.'_title')));
                    $list_lang_description[] = $this->input->post($substr.'_description');
                    $list_lang_keyword[] = $this->input->post($substr.'_keyword');
                    /**
                     * Giai thich cho nay 
                     * 1 . Lay mang ten hinh voi attribute src = preg_match output array
                     * 2 . Truyen mang vao de lay ve hinh da download va resize (dong dau)
                     * 3 . Replace lai chuoi va vi day la da ngon ngu nen chi thuc hien mot lan 
                     * cac lan sau lay mang da downloaded o Muc 2 replace lai Doi voi cac ngon ngu thu 2
                     */
                    $data = $this->input->post($substr.'_content');
                    $content = $this->preg_match_image($data);                                        
                    if( $content !== false && count($content)>0){
                        $_content = array('upload_content'=>$content,'slug'=>$slug,'path'=>$path);
                        if($i == 0){
                            foreach(Modules::run('upload/curl_upload',$_content) as $k=>$v){
                                $replace[] = $v;
                                $this->direct_delete(str_replace(base_url(),"",$content[$k]));
                                $data = str_replace($content[$k],$v,$data);
                            }  
                        }
                        else{
                            foreach($replace as $k=>$v){
                                $data = str_replace($content[$k],$v,$data);
                            }
                        }
                    }
                    $list_content[] = $data;                  
                    $list_ingredient[] = $this->input->post($substr.'_ingredient');
                    $list_price[] = $this->input->post($substr.'_price');
                    //$list_slug_title[] = 
                    $i+=1;
                }
                $status      = $pro->status      = $this->input->post('status');                
                $create      = $pro->create      = $pro->last_update = time();
                $category_id = $pro->category_id = $this->input->post('category_id');
                # Phan upload hinh
                $pathImage   = "assets/uploads/temp/";
                $pro->image  = "no_picture.gif";
                $path = 'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';                
                # allowed types - max size - name - path - content_type - slug
                $post_data = array(
                                    'allowed_types'=>'jpg|png|gif',
                                    'max_size'=>'2000',
                                    'name'=>'image',
                                    'slug'=> strtolower($slug),
                                    'path'=>$path,
                                    'content_type' => false,
                                    'type' => 'products'
                                );
                Modules::run('upload/handler',$post_data);                
                $image = $pro->image = Modules::run('upload/get_image');
                    
                
    			if($pro->save_languages($lang->all))
                {
                    # Phan ngon ngu                                        
                    $_id = $pro->id;
                    foreach($list_id_lang as $k => $v)
                    {
                        $lang_id      = $list_id_lang[$k];
                        $this->obj->id = $lang_id;
                        $pro->set_join_field('languages','title',$list_lang_title[$k],$this->obj);
                        $pro->set_join_field('languages','description',$list_lang_description[$k],$this->obj);
                        $pro->set_join_field('languages','keyword',$list_lang_keyword[$k],$this->obj);
                        $pro->set_join_field('languages','slug_title',$list_slug_title[$k],$this->obj);
                        $pro->set_join_field('languages','content',$list_content[$k],$this->obj);
                        $pro->set_join_field('languages','ingredient',$list_ingredient[$k],$this->obj);
                        $pro->set_join_field('languages','price',$list_price[$k],$this->obj);
                    }
                    $pro->refresh_all();
                    # Phan the meta
                    $vars = array('type'=>'product','meta_type_arr'=>$meta_type_arr,'description'=>$description,'keyword'=>$keyword,'title'=>$title,'slug'=>$slug,'image'=>$image,'_id'=>$_id);
                    $this->handle_meta($vars);
                    #
                    # Update category_id
                    $this->move_recycle();
                    $this->session->set_flashdata('add_success',$this->lang->line('admin_success_add'));
                    redirect(current_url(),'refresh');
                }
                # Code here
            }
            else
            {
                $dir = opendir('assets/uploads/temp/');
                if($dir)
                {
                    while(($file = readdir($dir)) !== FALSE)
                    {
                        if(preg_match("/^([\w\-\_]+)((\.(jpg|png|gif)){1})$/",$file))
                        {
                            @unlink('assets/uploads/temp/'.$file);
                        }
                    }
                }
                $list_id_parent_id[0] = ' -- Thuộc về -- ';
                $metadata = new stdClass();
                $metadata->all = $cat->where('type','products')->get()->all;
                foreach($metadata->all as $v)
                {
                    $cat->get_by_id($v->id);
                    $list_id_parent_id[$v->id] = $v->id." | ".character_limiter($cat->language->include_join_fields()->get()->all[$this->lang_index]->join_title,100);
                }
                $add .= '<div class="panel panel-default"><div class="panel-heading">'.ucfirst($method).'</div>
                        <div class="panel-body"><p>';
                $add .= ($this->session->flashdata('add_success')) ? '<p><div class="alert alert-success" role="alert">'.$this->session->flashdata('add_success').'</div></p>' : "";
                $attributes = array('role' => 'form', 'id' => strtolower($method));
                $add .= form_open_multipart($full_path.$action,$attributes);
                $add .= '<div class="form-group block col-md-6 col-md-offset-6 text-right">
                            '.form_submit(array('name'=>'add','value'=>"Thêm mới","class"=>'btn btn-default')).'
                            '.form_button(array('name'=>'cancel','value'=>"Hủy","class"=>'btn btn-warning','onclick'=>'window.location.href="'.site_url($class.'/'.$method.'/').'"'),'Hủy').'
                            <a href="'.site_url($full_path).'" class="btn btn-warning">Return</a>
                        </div>';
                #$add .= '<script src="'.base_url('assets/editor/ckeditor/ckeditor.js').'"></script>';
                # There here is place the code if else language
                
                foreach($list_lang as $item)
                { 
                    $add .= '<p ><h2 class="text-primary">'.ucfirst($item).'</h2></p>';
                    $segment = substr($item,0,2);
                    $add .= '<div class="form-group">
                                <label>Tiêu đề</label>
                                '.form_input(array('name'=>$segment.'_title','id'=>$segment.'_title','class'=>'form-control','value'=>set_value($segment.'_title'),'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_title')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Mô tả thể loại</label>
                                '.form_textarea(array('name'=>$segment.'_description','id'=>$segment.'_description','class'=>'form-control ckeditor','value'=>set_value($segment.'_description'),'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_description')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Keyword</label>
                                '.form_input(array('name'=>$segment.'_keyword','id'=>$segment.'_keyword','class'=>'form-control','value'=>set_value($segment.'_keyword'),'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài cách nhau dấu phẩy.</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_keyword')).'</p>
                            </div>';
                            
                    $add .= '<div class="form-group">
                                <label>Thành phần</label>
                                '.form_textarea(array('name'=>$segment.'_ingredient','id'=>$segment.'_ingredient','class'=>'form-control ckeditor','value'=>set_value($segment.'_ingredient'),'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring dạng ul li .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_ingredient')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Nội dung</label>
                                '.form_textarea(array('name'=>$segment.'_content','id'=>$segment.'_content','class'=>'form-control ckeditor','value'=>set_value($segment.'_content'),'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu string .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_content')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Giá</label>
                                '.form_input(array('name'=>$segment.'_price','id'=>$segment.'_price','class'=>'form-control','value'=>set_value($segment.'_price'),'max-length'=>'255')).'
                                <p class="help-block">Kiểu số nguyên nếu = không là giá thay đổi theo thời gian .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_price')).'</p>
                            </div>';
                }
                # There here is place the code if else language
                $add .= '<div class="form-group">
                            <label>Hiển thị</label>
                            '.form_dropdown('status',array('active'=>'active','deactive'=>'deactive'),'active',' class ="form-control" ').'
                        </div>';
                $add .= '<div class="form-group">
                            <label>Thuộc về </label>
                            '.form_dropdown('category_id',$list_id_parent_id,'0',' class ="form-control" ').'
                            <p class="help-block">Xác định row này thuộc về menu hoặc nhánh menu nào.</p>
                            <p class="label label-danger">'.ucwords(form_error('category_id')).'</p>
                        </div>';
                $add .= '<div class="form-group">
                            <label>Hình đại diện</label>
                            '.form_upload(array('name'=>'image','id'=>'image','value'=>'','class'=>'form-control')).'
                            '.form_hidden(array('name'=>'img_name','id'=>'img_name','value'=>'','style'=>'display:none;')).'
                            <p class="help-block">Hình ảnh cho row không được rổng và phải là kiểu jpg png hoặc gif.</p>
                            <p class="label label-danger">'.ucwords(form_error('img_name')).'</p>
                        </div>';
                                              
                $add .= form_close();
                $add .= '</p></div><div class="panel-footer">End '.$method.'</div></div>';
                
                $output->output = $add;
                $output->css_files = null;
                $output->js_files = array(base_url('assets/editor/ckeditor/ckeditor.js'));
                $this->_data['output'] = $output;
            }
        }
        elseif($action == 'view' || $action == '')
        {
            # Initialize
            $this->load->library(array('table','Arr_obj'));
            $this->load->helper('photo');
            $cat = new Categories();
            $pro  =new Product();
            $output = new stdClass();
            $arr = new Arr_obj;            
            $data = $list_key = $datalist = array(); $add = $pagination = $actions = ''; $i  = 1; $dem = 0; $paging = 2; $default_status = 'active';
            $href = ''; $return_null = false;
            $dropdown = $pro->get();
            $pro->refresh_all();
            # Phan extend_delete va update active or deactive
            
		    if($this->input->post('drop') && is_array($this->input->post('drop')) && count($this->input->post('drop'))>0)
            {
                # Kiem tra neu day khong phai la admin thi khong cho delete
                if (!$this->ion_auth->is_admin())
        		{
        			$this->set_flashdata('status_message',settings::status_message_non_permission);
                    return redirect(site_url($class.'/'.$method),'location');
        		}
                #
                # Dong nay kiem tra ten cua bang de thuc hien tac vu delete va update
                $delete_type = ($this->input->post('delete_type')) ? $this->input->post('delete_type') : 'products';
                $_id  = $this->input->post('drop');
                if($this->input->post('action_status') && $this->input->post('action_status') != ''){
                    $actions = ($this->input->post('action_status')) ? $this->input->post('action_status') : $default_status;
                    foreach($_id as $item){
                        if($this->active_or_deactive($item,$delete_type,$actions)){
                            $dem +=1;
                        }
                    }
                }else{                    
                    foreach($_id as $item){
                        if($this->extend_delete($item,$delete_type)){
                            $dem +=1;
                        }
                    }
                }
                if(($dem > 0) && $dem == count($_id)){
                    if($actions != ''){
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_update').implode(",",$_id));    
                    }
                    else{
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_delete').implode(",",$_id));
                    }
                    return redirect(site_url($class.'/'.$method.'/view'),'location');
                }
                $dem = 0; # Lam xong reset bien de danh sai lai
            }
            # Phan search
            if($this->input->post($method.'_search') && $this->input->post($method.'_search') != '' && strlen($this->input->post($method.'_search'))>=2)
            {
                $_search_type = $this->input->post('search_type'); 
                $search  =  trim($this->input->post($method.'_search'));
                $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_search').' '.$search);
                redirect(site_url($class.'/'.$method.'/'.$action.'/'.$_search_type.':'.$this->url_decode($search).'/asc/1'),'location');
            }
            # Phan active and deactive            
            
            # Phan set session cho so trang hien thi
            
            if(!$this->session->userdata('num_of_page'))
            {
                $this->session->set_userdata('num_of_page',settings::default_paging);
            }
            if($this->input->post('num_of_page') && $this->input->post('num_of_page')!=$this->session->userdata('num_of_page'))
            {
                $this->session->set_userdata('num_of_page',(int)$this->input->post('num_of_page'));
            }
            $paging = $this->session->userdata('num_of_page');
            
            # Lay list key add vao dropdown list
            
            $obj = array_keys($arr->objectToArray($dropdown->stored));
            
            foreach($obj as $key=>$val)
            {
                $list_key[strtolower($val)] = ucfirst($val);
            }
            # Phan Create Form 
            $naming = strtolower(substr($method,0,2))."_".strtolower($method);
            $attributes = array('role' => 'form', 'id' => $naming,'name'=>$naming);
            $add .= form_open_multipart(strtolower(__CLASS__)."/".$method."/".$action,$attributes);
            $add .= form_hidden('delete_type', $method);
            $add .= ($this->session->flashdata('status_message')) ? '<div class="alert alert-warning" role="alert">'.$this->session->flashdata('status_message')."</div>" : ''; 
            $add .= '<div class="row">';
            $add .= '<div class="form-group col-md-1"><a class="btn btn-default" href="'.site_url($class.'/'.$method).'"><i class="fa fa-arrow-left"></i> List</a>                        
                    </div> ';
            $add .= '<div class="form-group col-md-1"><a class="btn btn-primary" href="'.site_url($class.'/'.$method.'/add').'"><i class="fa fa-plus"></i> Thêm mới</a>                        
                    </div>';
            $add .= '<div class="form-group col-md-1"><a class="btn btn-success" href="'.site_url($class.'/save_excel_file').'"><i class="fa fa-file-excel-o"></i> Xuất excel</a>                        
                    </div> ';
            $add .= '<div class="form-group col-md-1">&nbsp;<input class="btn btn-warning" type="submit" name="delete" id="delete" value="delete" onclick="return check_remove(\''.$naming.'\')"/></div>';            
            $add .= "</div>";
            
            
            $add .= '<div class="row">';
            $add .= '<div class="form-group col-md-2">
                        '.form_input(array('name'=>$method.'_search','id'=>$method.'_search','class'=>'inline form-control __search','value'=>set_value($method.'_search'),'placeholder'=>'Type here and select next','max-length'=>'255','onsubmit'=>'$(\''.$naming.'\').submit()')).'
                    </div>';
            $add .= '<div class="form-group col-md-2">
                        '.form_dropdown('search_type',$list_key,'','onchange="search_form_data_list($(this))" class =" inline form-control" ').'
                    </div> ';            
            $add .= '<div class="form-group col-md-2 pull-right">
                        '.form_dropdown('action_status',array(''=>'Chọn action','deactive'=>'Ngưng kích hoạt','active'=>'Kích hoạt'),'','class ="form-control" onchange="fn_active_or_deactive(\''.$naming.'\',$(this))" ').'
                    </div>';                        
            $add .= '</div>';
            $pagination .= '<ul class="pagination">';
            if(!is_null($id))
            {
                $_sort = 'asc';
                if(isset($sort))
                {
                   $_sort = $sort; 
                }
                # Phan nay search 
                if( ! array_key_exists($id,$list_key))
                {
                    $handle_id = explode(":",$id);
                    $handle_search = (count($handle_id)==2) ? $this->url_decode(end($handle_id)) : null;                   
                    $search_type = ($handle_search !== null) ? $handle_id[0] : 'id';
                    if($search_type == 'id')
                    {
                        if(!preg_match("/^[^0][0-9]{1,11}$/",$handle_search))
                        {
                            $return_null = true;
                        }
                    }
                    else
                    {
                        if(!preg_match("/^([\w]+)([\s]?)/",$handle_search))
                        {
                            $return_null = true;
                        }
                    }
                    if($return_null)
                    {
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_failed_search'));
                        return redirect(site_url($class.'/'.$method),'location');
                    }   
                    else
                    {
                        $test_search_has_array  = explode(" ",$handle_search);
                        if(count($test_search_has_array)>1)
                        {
                            $i = 1;
                            foreach($test_search_has_array as $item)
                            {
                                if($i == 1){
                                    $pro->like($search_type,$item, 'both');
                                }                                
                                elseif($i == count($test_search_has_array))
                                {
                                    $pro->or_like($search_type,$handle_search, 'both');
                                }
                                else{
                                    $pro->or_like($search_type,$item, 'both');
                                }
                                $i+=1;
                            }
                        }
                        else
                        {
                            $pro->like($search_type,$handle_search, 'both');  
                              
                        }
                        $i = 0 ;
                        $id = $search_type.':'.$this->url_encode($handle_search);
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_search').' '.$handle_search);
                        #redirect(site_url($class.'/'.$method.'/'.$action.'/'.$id),'location');
                    }
                }
                else
                {
                    $pro->order_by($id,$_sort);    
                }
            }
            else
            {
                $method  = $method; $action = $action; $id = 'id'; $sort = 'asc';    
            }
            if(isset($page) && $page != 0)
            {
                $c = $pro->get_paged($page,$paging);
                $_data = new stdClass();
                $_data->all = $c->all;
                $j = 0 ;
                foreach($_data->all as $v)
                {
                    $pro->get_by_id($v->id);
                    $cat->get_by_id($v->category_id);
                    $_data->all[$j]->join_title = $pro->language->include_join_fields()->get()->all[$this->lang_index]->join_title;
                    $_data->all[$j]->join_category = $cat->language->include_join_fields()->get()->all[$this->lang_index]->join_title;
                    $j+=1;
                }
                if($pro->paged->has_previous)
                {
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.'1').'"><i class="fa fa-angle-double-left"></i> First</a></li>';
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.$pro->paged->previous_page).'"><i class="fa fa-angle-left"></i> Prev</a></li>';
                }
                if($pro->paged->has_next)
                {
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.$pro->paged->next_page).'">Next <i class="fa fa-angle-right"></i></a></li>';
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.$pro->paged->total_pages).'">Last <i class="fa fa-angle-double-right"></i></a></li>';
                }
            }
            $pagination .= '</ul>';
            $list_heading = array("","Identity","Create date","Image","Status","Category_id","Last_update","Functions");
            $data[0] = "<input type='checkbox' class='sr check_all' name='check_all' id='check_all' />";
            $value = $this->url_decode($id); 
            foreach($obj as $item=>$val)
            {
                if(strcasecmp($val,$id) !== false)
                {
                    $value = $val;
                }
                $data[] = '<a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$value.'/asc/'.$page).'" class="label label-primary" title="ASC"><i class="fa fa-caret-up"></i></a> '.$list_heading[$i].' <a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$value.'/desc'.'/'.$page).'" class="label label-success" title="DESC"><i class="fa fa-caret-down"></i></a>';
                $i += 1;    
            }
            $data[count($list_heading)] = "Functions";
            $this->table->set_heading($data);
            $tmpl = array ( 'table_open'  => '<table class="table">' );
            $this->table->set_template($tmpl);
            $i = 0;
            foreach($_data->all as $item)
            {
                $create = $this->timestamps($item->create);
                $last_update = $this->timestamps($item->last_update);
                $datalist[] = array($item->id.' | '.character_limiter($item->join_title,10),$create,$item->image,$item->status,$item->category_id.' | '.$item->join_category,$last_update);
                $photo = showPhoto(base_url($item->image),array('width'=>80,'class'=>'img-responsive'));
                $function = '<a href="'.site_url($class.'/'.$method.'/'.'edit'.'/'.$item->id).'" class="label label-info" data-toggle="tooltip" data-placement="top" title="Edit '.$item->id.'"><i class="fa fa-pencil"></i></a>';
                $category_link = '<a href="'.site_url($class.'/categories/'.'edit'.'/'.$item->category_id).'" title="Edit '.$item->id.'">'.$item->category_id.'</a>';
                $this->table->add_row(array('<input type="checkbox" class="sr" name="drop[]" value="'.$item->id.'" />',$item->id." | ".character_limiter($item->join_title,100),$create,$photo,$item->status,$category_link." | ".$item->join_category,$last_update,$function));
                $i++;                
            }
            $this->session->set_userdata('datalist',$datalist);
            $this->session->set_userdata('export_type',$method);
            $this->session->set_userdata('export_stored',$obj);
            $add .= $this->table->generate();
            $add .= '<div class="row">';
            $add .= $pagination;
            $add .= '<div class=" form-group col-md-2 pull-right ">                        
                        '.form_dropdown('num_of_page',array('10'=>'10 rows','25'=>'25 rows','50'=>'50 rows','100'=>'100 rows'),$paging,'class ="form-control inline" onchange="set_num_of_page(\''.$naming.'\',$(this))"').'
                    </div></div>'; 
            
            $add .= '<script>var update_status_message = \''.$this->lang->line('update_status_message').'\';var remove_message = \''.$this->lang->line('remove_message').'\';var failed_confirm = \''.$this->lang->line('checkin').'\'</script>';
            $add .= form_close();
            # End create form .
            $output->output = $add;
            $output->css_files = null;
            $output->js_files = array(base_url('assets/editor/ckeditor/ckeditor.js'));
            $this->_data['output'] = $output;
        }
        elseif($action == 'edit')
        {
            # Xu ly du lieu dau vao
            if($id == null && !preg_match("/^[^0][\d]+$/",$id))
            {
                return modules::run('dashboard/handle_404');
            }            
            $pro = new Product($id);
            if(count($pro->all) == 0)
            {
                return modules::run('dashboard/handle_404'); 
            }
            #
            $this->load->helper(array('thumbnail','photo'));
            $lang   = new Language();
            $output = new stdClass();
            $i = 0;
            $photo = showPhoto(base_url($pro->image),array('width'=>100,'class'=>'img-responsive'));
            
            $list_lang = $list_id_lang = $list_join_field = $list_lang_ingredient = $list_lang_content = $list_lang_price = $list_lang_keyword = $list_lang_title = $list_lang_description = $list_slug_title = $arr_meta = array();             
            $meta_type_arr = array('seo','google','facebook','twitter'); $add = ''; $preg_img = "/^([\w\-\_]+)([\.]{1}(jpg|png|gif))$/";
            foreach($lang->get() as $item)
            {
                $list_id_lang[] = $item->id;
                $list_lang[] = $item->name;
            }
            foreach($list_lang as $item){
                $this->form_validation->set_rules(substr($item,0,2).'_title','Tiêu đề '.substr($item,0,2),'trim|required|min_length[6]|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_description','Mô tả '.substr($item,0,2),'trim|required|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_keyword','Keyword '.substr($item,0,2),'trim|required|min_length[10]|max_length[255]|callback_check_keyword');
                $this->form_validation->set_rules(substr($item,0,2).'_ingredient','Thành phần '.substr($item,0,2),'trim|required');
                $this->form_validation->set_rules(substr($item,0,2).'_content','Nội dung '.substr($item,0,2),'trim|required');
                $this->form_validation->set_rules(substr($item,0,2).'_price','Giá '.substr($item,0,2),'trim|required');              
            }              
            $this->form_validation->set_rules('status','Tiêu đề','required');
            $this->form_validation->set_rules('category_id','thuộc về','trim|required');
            
            if($this->form_validation->run($this) !== FALSE)
            {
                # Phan kiem tra co hinh anh duoc upload hay khong
                $image_file = null;
                if($_FILES)
                {
                    $files = $_FILES['image']['name'];
                    $type  = $_FILES['image']['type'];
                    if (!empty($files))
                    {
                        if(preg_match($preg_img,$files) && $this->is_image($type))
                        {
                            $this->delete_image_category($pro->image,'products');
                            $image_file = $files;
                        }
                        else
                        {
                            $this->session->set_flashdata('status_message','If you choose an image upload you need the file is sure an image type .');
                            redirect(site_url($class.'/'.$method.'/'.$action.'/'.$id),'location');    
                        }
                    }
                }
                # Phan lay du lieu cho table meta
                $title = $this->input->post('vi_title');
                $description = $this->input->post('vi_description');
                $keyword = $this->input->post('vi_keyword');
                $slug = strtolower(url_title(convert_accented_characters($this->input->post('vi_title'))));
                $category_id        = $pro->category_id = $this->input->post('category_id');
                $status      = $pro->status    = $this->input->post('status');
                # Check hinh 
                
                $this->active_or_deactive($id,strtolower($method),$status);            
                $i = 0 ; $replace = array();
                $path = 'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';
                foreach($list_lang as $item)
                {
                    $substr = substr($item,0,2);
                    $list_lang_title[] = strtolower($this->input->post($substr.'_title'));
                    $list_slug_title[] = url_title(convert_accented_characters(strtolower($this->input->post($substr.'_title'))));
                    $list_lang_description[] = strtolower($this->input->post($substr.'_description'));
                    $list_lang_keyword[] = strtolower($this->input->post($substr.'_keyword'));
                    $list_lang_ingredient[] = strtolower($this->input->post($substr.'_ingredient'));
                    
                    # Check hinh
                    # Kiem tra dieu kien chi xu ly hinh khi i = 0 ;
                    
                    # Khai bao bien de lay mang ton tai va mang khong ton tai
                    $arr_exists = $arr_non_exists = array();
                    # Dong duoi nay lay noi dung cua row
                    $row_content = $pro->language->include_join_fields()->get()->all[0]->join_content;
                    # Dong duoi nay lay so phan tu la image trong noi dung cua row
                    $arr_row_content = $this->preg_match_image($row_content);
                    # Dong duoi nay lay noi dung cua bien post 
                    $data = $this->replace_content($this->input->post($substr.'_content'));
                    # Dong duoi nay lay so phan tu la image cua noi dung bien post
                    $arr_post_content = $this->preg_match_image($data);
                    # Dong duoi nay dem so phan tu cua bien count va bien post
                    $count_row = count($arr_row_content); $count_post = count($arr_post_content);
                    # Truong hop ca hai deu co hinh : 
                    if($arr_row_content !== false && $count_row > 0 && $arr_post_content !== false &&  $count_post > 0 ){
                        foreach($arr_row_content as $k=>$v){
                            if(!in_array($v,$arr_post_content))
                            {
                                $arr_exists[$k] = $v;   
                            }
                        }
                        foreach($arr_post_content as $k=>$v){
                            if(!in_array($v,$arr_row_content))
                            {
                                $arr_non_exists[$k] = $v;   
                            }
                        }
                        # Neu khong tim thay thuoc mang row co trong mang post thi xoa phan tu do di.
                        if(!is_null($arr_exists))
                        {
                            if($i == 0){
                                foreach($arr_exists as $item){
                                    $name = str_replace(base_url(),"",$item);
                                    $this->direct_delete($name);
                                }
                            }
                        }
                        # Neu khong tim thay phan tu thuoc mang post co trong mang row thi lay cac phan tu do upload vao
                        if(!is_null($arr_non_exists))
                        {
                            if($i == 0){
                                $_content = array('upload_content'=>$arr_non_exists,'slug'=>$slug,'path'=>$path);
                                foreach(Modules::run('upload/curl_upload',$_content) as $k=>$v){
                                    $replace[$k] = $v;
                                    $data = str_replace($arr_post_content[$k],$v,$data);
                                }     
                            }
                            else{
                                foreach($replace as $k=>$v)
                                {
                                    $data = str_replace($arr_post_content[$k],$v,$data);
                                }
                            }
                        }
                    }
                    # Truong hop chi co row co hinh
                    elseif($arr_row_content !== false && $count_row > 0 && $arr_post_content === false){
                        # Duyet vong lap foreach va xoa het hinh di
                        if($i == 0){ 
                            foreach($arr_row_content as $item){
                                $name = str_replace(base_url(),"",$item);
                                $this->direct_delete($name);
                            }
                        }
                    }
                    # Truong hop chi co post co hinh 
                    elseif($arr_row_content === false && $arr_post_content !== false && $count_post > 0){
                        if($i == 0){
                            $_content = array('upload_content'=>$arr_post_content,'slug'=>$slug,'path'=>$path);
                            foreach(Modules::run('upload/curl_upload',$_content) as $k=>$v){
                                $replace[$k] = $v;
                                $data = str_replace($arr_post_content[$k],$v,$data);
                            } 
                        }
                        else{
                            foreach($replace as $k=>$v)
                            {
                                $data = str_replace($arr_post_content[$k],$v,$data);
                            }
                        }
                    }                    
                    # Kiem tra link hinh khong ton tai va xoa Doi voi I > 0 de Dong Bo Hoa du lieu noi dung 
                    if($i > 0){
                        $sycn_content = $this->preg_match_image($data);
                        if($sycn_content !== FALSE){
                            foreach($sycn_content as $k=>$v){
                                if(!file_exists(str_replace(base_url(),"",$v))){
                                    $data = str_replace($v,"",$data);
                                }
                            }
                        }
                    }
                    # Cac truong hop con lai cu the la ca hai deu bang khong
                    # Hoan tat cac thao tac va gan bien 
                    $list_lang_content[] = $data;
                    #$list_lang_content[] = strtolower($this->input->post($substr.'_content'));
                    $list_lang_price[] = strtolower($this->input->post($substr.'_price'));
                    //$list_slug_title[] = 
                    $i+=1;
                }
                $i = 0 ;
                $ext         = null;
                $pathImage = "assets/uploads/temp/";                            
                #$cat->img_avatar = $image_file;
                $imageTemp = "";
                $image = $pro->image;
                # Image           
                			
                # Initialize Config Image
                if($image_file !== null)
                {                    
                    $path = 'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';                
                    # allowed types - max size - name - path - content_type - slug
                    $post_data = array(
                                        'allowed_types'=>'jpg|png|gif',
                                        'max_size'=>'2000',
                                        'name'=>'image',
                                        'slug'=> $slug,
                                        'path'=>$path,
                                        'content_type' => false,
                                        'type' => 'products'
                                    );
                    Modules::run('upload/handler',$post_data);                
                    $image = $pro->image = Modules::run('upload/get_image');   
                }
                $pro->save(); 
                # Phan ngon ngu                                        
                $_id = $id;
                foreach($list_id_lang as $k => $v)
                {
                    $lang_id      = $list_id_lang[$k];
                    $this->obj->id = $lang_id;
                    $pro->set_join_field('languages','title',$list_lang_title[$k],$this->obj);
                    $pro->set_join_field('languages','description',$list_lang_description[$k],$this->obj);
                    $pro->set_join_field('languages','keyword',$list_lang_keyword[$k],$this->obj);
                    $pro->set_join_field('languages','ingredient',$list_lang_ingredient[$k],$this->obj);
                    $pro->set_join_field('languages','content',$list_lang_content[$k],$this->obj);
                    $pro->set_join_field('languages','price',$list_lang_price[$k],$this->obj);
                    $pro->set_join_field('languages','slug_title',$list_slug_title[$k],$this->obj);
                }
                $pro->refresh_all();
                # Phan the meta
                $vars = array('type'=>'product','edit'=>true,'meta_type_arr'=>$meta_type_arr,'description'=>$description,'keyword'=>$keyword,'title'=>$title,'slug'=>$slug,'image'=>$image,'_id'=>$_id);
                $this->handle_meta($vars);
                $this->move_recycle();
                $this->session->set_flashdata('add_success',$this->lang->line('admin_success_update').' '. $id);
                redirect(site_url($class.'/'.$method.'/'.$action.'/'.$id),'refresh');
                # Code here
            }
            else
            {
                # Xoa hinh chua upload thanh cong
                $dir = opendir('assets/uploads/temp/');
                if($dir)
                {
                    while(($file = readdir($dir)) !== FALSE)
                    {
                        if(preg_match("/^([\w\-\_]+)((\.(jpg|png|gif)){1})$/",$file))
                        {
                            @unlink('assets/uploads/temp/'.$file);
                        }
                    }
                }
                # Lay thong tin ngon ngu trong bang join field
                $join = new Product($id);
                $category = new Category();
                $list_id = array();
                $list_id[0] = ' -- Thuộc về -- ';
                $metadata = new stdClass();
                $metadata->all = $category->where('type','products')->get()->all;
                foreach($metadata->all as $v)
                {
                    $category->get_by_id($v->id);
                    $list_id[$v->id] = $v->id." | ".character_limiter($category->language->include_join_fields()->get()->all[$this->lang_index]->join_title,100);
                }
                
                foreach($join->language->include_join_fields()->get()->all as $k=>$item)
                {
                    $list_join_field[$k]['title'] = $item->join_title;
                    $list_join_field[$k]['keyword'] = $item->join_keyword;
                    $list_join_field[$k]['description'] = $item->join_description;
                    $list_join_field[$k]['ingredient'] = $item->join_ingredient;
                    $list_join_field[$k]['content'] = $this->detect_content($item->join_content);
                    $list_join_field[$k]['price'] = $item->join_price;
                }
                # Bat dau tao form 
                $add .= '<div class="panel panel-default"><div class="panel-heading">'.ucfirst($method).'</div>
                         <div class="panel-body"><p>';
                $add .= ($this->session->flashdata('add_success')) ? '<p><div class="alert alert-success" role="alert">'.$this->session->flashdata('add_success').'</div></p>' : "";
                $attributes = array('role' => 'form', 'id' => strtolower($method));
                $add .= form_open_multipart(strtolower(__CLASS__)."/".$method."/".$action.'/'.$id,$attributes);
                $add .= '<div class="form-group block col-md-6 col-md-offset-6 text-right">
                            '.form_submit(array('name'=>'add','value'=>"Lưu lại","class"=>'btn btn-default')).'
                            '.form_button(array('name'=>'cancel','value'=>"Hủy","class"=>'btn btn-warning','onclick'=>'window.location.href=\''.site_url($class.'/'.$method).'\''),'Hủy').'
                         </div>';
                $add .= '<p class="label label-danger">'.$this->lang->line('content_edit_rule').'</p>';
                #$add .= '<script src="'.base_url('assets/editor/ckeditor/ckeditor.js').'"></script>';
                # There here is place the code if else language
                foreach($list_lang as $k=>$item)
                { 
                    $add .= '<p ><h2 class="text-primary">'.ucfirst($item).'</h2></p>';
                    $segment = substr($item,0,2);
                    $add .= '<div class="form-group">
                                <label>Tiêu đề</label>
                                '.form_input(array('name'=>$segment.'_title','id'=>$segment.'_title','class'=>'form-control','value'=>$list_join_field[$k]['title'],'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_title')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Mô tả thể loại</label>
                                '.form_textarea(array('name'=>$segment.'_description','id'=>$segment.'_description','class'=>'form-control ckeditor','value'=>$list_join_field[$k]['description'],'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_description')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Keyword</label>
                                '.form_input(array('name'=>$segment.'_keyword','id'=>$segment.'_keyword','class'=>'form-control','value'=>$list_join_field[$k]['keyword'],'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài cách nhau dấu phẩy.</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_keyword')).'</p>
                            </div>';
                    
                    $add .= '<div class="form-group">
                                <label>Thành phần</label>
                                '.form_textarea(array('name'=>$segment.'_ingredient','id'=>$segment.'_ingredient','class'=>'form-control ckeditor','value'=>$list_join_field[$k]['ingredient'],'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_ingredient')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Nội dung</label>
                                '.form_textarea(array('name'=>$segment.'_content','id'=>$segment.'_content','class'=>'form-control ckeditor','value'=>$list_join_field[$k]['content'],'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_content')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Giá tiền </label>
                                '.form_input(array('name'=>$segment.'_price','id'=>$segment.'_price','class'=>'form-control','value'=>$list_join_field[$k]['price'],'max-length'=>'255')).'
                                <p class="help-block">Kiểu số nguyên .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_price')).'</p>
                            </div>';
                            
                    $i++;
                }
                $i = 0;
                # There here is place the code if else language
                $add .= '<div class="form-group">
                            <label>Hiển thị</label>
                            '.form_dropdown('status',array('active'=>'active','deactive'=>'deactive'),$pro->status,' class ="form-control" ').'
                        </div>';
              
                $add .= '<div class="form-group">
                            <label>Thuộc về menu</label>
                            '.form_dropdown('category_id',$list_id,$pro->category_id,' class ="form-control" ').'
                        </div>';
                
                $add .= '<div class="form-group">
                            <label>Hình đại diện</label>
                            '.form_upload(array('name'=>'image','id'=>'image','value'=>'','class'=>'form-control')).'
                            '.form_hidden(array('name'=>'img_name','id'=>'img_name','value'=>'','style'=>'display:none;')).'
                            <p class="help-block">'.$photo.'</p>
                            <p class="label label-danger">'.ucwords(form_error('img_name')).'</p>
                        </div>';
                                              
                $add .= form_close();
                $add .= '</p></div><div class="panel-footer">End '.$method.'</div></div>';
                $output->output = $add;
                $output->css_files = null;
                $output->js_files = array(base_url('assets/editor/ckeditor/ckeditor.js'));
                $this->_data['output'] = $output;
            }
        }
        # Concat string 
        $this->_data['is_index_admin']      = false;
        $js = '';        
        $this->_data['js_custom'] = $js;
        
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    public function news($action,$id = null,$sort = null,$page = 1)
    {        
        if( ! preg_match("/^[\w\_]+$/",$action))
        {
            return Modules::run('dashboard/handle_404');
        }      
        # Phan khoi tao dau ham $cat.
        $this->load->library('form_validation');
        $this->template->title($this->lang->line('admin_default_title'));
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['output'] = '';
        $method = end(explode("::",__METHOD__));
        $class = strtolower(__CLASS__);
        $full_path = $class.'/'.$method.'/';
        if($action == 'add')
        {
            $pro    = new Newz();
            $lang   = new Language();
            $cat    = new Category();
            $output = new stdClass();
            $list_lang = $list_id_lang = $_list_content = $list_lang_keyword = $list_content = $list_lang_title = $list_lang_description = $list_slug_title = $list_id_parent_id = $arr_meta = array();             
            $meta_type_arr = array('seo','google','facebook','twitter');            
            $add = '';
            foreach($lang->get() as $item)
            {
                $list_id_lang[] = $item->id;
                $list_lang[] = $item->name;
            }
            foreach($list_lang as $item){
                $this->form_validation->set_rules(substr($item,0,2).'_title','Tiêu đề '.substr($item,0,2),'trim|required|min_length[6]|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_description','Mô tả '.substr($item,0,2),'trim|required|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_keyword','Keyword '.substr($item,0,2),'trim|required|min_length[10]|max_length[255]|callback_check_keyword');                
                $this->form_validation->set_rules(substr($item,0,2).'_content','Nội dung '.substr($item,0,2),'trim|required|min_length[10]');                
            }              
            $this->form_validation->set_rules('status','Tiêu đề','required');
            $this->form_validation->set_rules('category_id','Thuộc về','required');
            if (!empty($_FILES['image']['name']))
            {
                    
            }
            else
            {
                $this->form_validation->set_rules('img_name','Hình đại diện','required');   
            }
            if($this->form_validation->run($this) !== FALSE)
            {
                $this->load->library(array('upload','image_lib'));                
                $this->load->helper('thumbnail');
                
                $author      = 'Vinh Banh';
                $ggplus      = settings::google_plus_author;
                
                $title       = $this->input->post('vi_title');
                $description = $this->input->post('vi_description');
                $keyword     = $this->input->post('vi_keyword');                
                $slug        = url_title(convert_accented_characters($this->input->post('vi_title')));
                
                $i = 0 ;
                $_content = $replace = array();
                $path = 'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';
                foreach($list_lang as $item)
                {
                    $substr = substr($item,0,2);
                    $list_lang_title[] = $this->input->post($substr.'_title');
                    $list_slug_title[] = url_title(convert_accented_characters($this->input->post($substr.'_title')));
                    $list_lang_description[] = $this->input->post($substr.'_description');
                    $list_lang_keyword[] = $this->input->post($substr.'_keyword');
                    /**
                     * Giai thich cho nay 
                     * 1 . Lay mang ten hinh voi attribute src = preg_match output array
                     * 2 . Truyen mang vao de lay ve hinh da download va resize (dong dau)
                     * 3 . Replace lai chuoi va vi day la da ngon ngu nen chi thuc hien mot lan 
                     * cac lan sau lay mang da downloaded o Muc 2 replace lai Doi voi cac ngon ngu thu 2
                     */
                    $data = $this->input->post($substr.'_content');
                    $content = $this->preg_match_image($data);                                        
                    if( $content !== false && count($content)>0){
                        $_content = array('upload_content'=>$content,'slug'=>$slug,'path'=>$path);
                        if($i == 0){
                            foreach(Modules::run('upload/curl_upload',$_content) as $k=>$v){
                                $replace[] = $v;
                                $this->direct_delete(str_replace(base_url(),"",$content[$k]));
                                $data = str_replace($content[$k],$v,$data);
                            }  
                        }
                        else{
                            foreach($replace as $k=>$v){
                                $data = str_replace($content[$k],$v,$data);
                            }
                        }
                    }
                    $list_content[] = $data;                    
                    //$list_slug_title[] = 
                    $i+=1;
                }
                $status      = $pro->status      = $this->input->post('status');                
                $create      = $pro->create      = $pro->last_update = time();
                $category_id = $pro->category_id = $this->input->post('category_id');
                $pro->viewer =  $viewer          = 0;
                # Phan upload hinh
                $pathImage   = "assets/uploads/temp/";
                $pro->image  = "no_picture.gif";
                $path = 'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';                
                # allowed types - max size - name - path - content_type - slug
                $post_data = array(
                                    'allowed_types'     => 'jpg|png|gif',
                                    'max_size'          => '5000',
                                    'name'              => 'image',
                                    'slug'              => $slug,
                                    'path'              => $path,
                                    'content_type'      => false,
                                    'type'              => 'news'
                                );
                Modules::run('upload/handler',$post_data);                
                $image = $pro->image = Modules::run('upload/get_image');
                # Save data
    			if($pro->save_language($lang->all))
                {
                    # Phan ngon ngu                                        
                    $_id = $pro->id;
                    foreach($list_id_lang as $k => $v)
                    {
                        $lang_id      = $list_id_lang[$k];
                        $this->obj->id = $lang_id;
                        $pro->set_join_field('language','title',$list_lang_title[$k],$this->obj);
                        $pro->set_join_field('language','description',$list_lang_description[$k],$this->obj);
                        $pro->set_join_field('language','keyword',$list_lang_keyword[$k],$this->obj);
                        $pro->set_join_field('language','slug_title',$list_slug_title[$k],$this->obj);
                        $pro->set_join_field('language','content',$list_content[$k],$this->obj);
                    }
                    $pro->refresh_all();
                    # Phan the meta
                    $vars = array('type'=>'new','meta_type_arr'=>$meta_type_arr,'description'=>$description,'keyword'=>$keyword,'title'=>$title,'slug'=>$slug,'image'=>$image,'_id'=>$_id);
                    $this->handle_meta($vars);
                    # Update category_id
                    $this->move_recycle();
                    $this->session->set_flashdata('add_success',$this->lang->line('admin_success_add'));
                    redirect(current_url(),'refresh');
                }
                # Code here
            }
            else
            {
                $dir = opendir('assets/uploads/temp/');
                if($dir)
                {
                    while(($file = readdir($dir)) !== FALSE)
                    {
                        if(preg_match("/^([\w\-\_]+)((\.(jpg|png|gif)){1})$/",$file))
                        {
                            @unlink('assets/uploads/temp/'.$file);
                        }
                    }
                }
                $list_id_parent_id[0] = ' -- Thuộc về -- ';
                $metadata = new stdClass();
                $metadata->all = $cat->where('type','news')->get()->all;
                foreach($metadata->all as $v)
                {
                    $cat->get_by_id($v->id);
                    $list_id_parent_id[$v->id] = $v->id." | ".character_limiter($cat->language->include_join_fields()->get()->all[$this->lang_index]->join_title,100);
                }
               
                
                $add .= '<div class="panel panel-default"><div class="panel-heading">'.ucfirst($method).'</div>
                        <div class="panel-body"><p>';
                $add .= ($this->session->flashdata('add_success')) ? '<p><div class="alert alert-success" role="alert">'.$this->session->flashdata('add_success').'</div></p>' : "";
                $attributes = array('role' => 'form', 'id' => strtolower($method));
                $add .= form_open_multipart($full_path.$action,$attributes);
                $add .= '<div class="form-group block col-md-6 col-md-offset-6 text-right">
                            '.form_submit(array('name'=>'add','value'=>"Thêm mới","class"=>'btn btn-default')).'
                            '.form_button(array('name'=>'cancel','value'=>"Hủy","class"=>'btn btn-warning','onclick'=>'window.location.href="'.site_url($class.'/'.$method.'/').'"'),'Hủy').'
                            <a href="'.site_url($full_path).'" class="btn btn-warning">Return</a>
                        </div>';
                #$add .= '<script src="'.base_url('assets/editor/ckeditor/ckeditor.js').'"></script>';
                # There here is place the code if else language
                foreach($list_lang as $item)
                { 
                    $add .= '<p ><h2 class="text-primary">'.ucfirst($item).'</h2></p>';
                    $segment = substr($item,0,2);
                    $add .= '<div class="form-group">
                                <label>Tiêu đề</label>
                                '.form_input(array('name'=>$segment.'_title','id'=>$segment.'_title','class'=>'form-control','value'=>set_value($segment.'_title'),'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_title')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Mô tả thể loại</label>
                                '.form_textarea(array('name'=>$segment.'_description','id'=>$segment.'_description','class'=>'form-control ckeditor','value'=>set_value($segment.'_description'),'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_description')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Keyword</label>
                                '.form_input(array('name'=>$segment.'_keyword','id'=>$segment.'_keyword','class'=>'form-control','value'=>set_value($segment.'_keyword'),'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài cách nhau dấu phẩy.</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_keyword')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Nội dung</label>
                                '.form_textarea(array('name'=>$segment.'_content','id'=>$segment.'_content','class'=>'form-control ckeditor','value'=>set_value($segment.'_content'),'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu string .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_content')).'</p>
                            </div>';                   
                }
                # There here is place the code if else language
                $add .= '<div class="form-group">
                            <label>Hiển thị</label>
                            '.form_dropdown('status',array('active'=>'active','deactive'=>'deactive'),'active',' class ="form-control" ').'
                        </div>';
                $add .= '<div class="form-group">
                            <label>Thuộc về </label>
                            '.form_dropdown('category_id',$list_id_parent_id,'0',' class ="form-control" ').'
                            <p class="help-block">Xác định row này thuộc về menu hoặc nhánh menu nào.</p>
                            <p class="label label-danger">'.ucwords(form_error('category_id')).'</p>
                        </div>';
                $add .= '<div class="form-group">
                            <label>Hình đại diện</label>
                            '.form_upload(array('name'=>'image','id'=>'image','value'=>'','class'=>'form-control')).'
                            '.form_hidden(array('name'=>'img_name','id'=>'img_name','value'=>'','style'=>'display:none;')).'
                            <p class="help-block">Hình ảnh cho row không được rổng và phải là kiểu jpg png hoặc gif.</p>
                            <p class="label label-danger">'.ucwords(form_error('img_name')).'</p>
                        </div>';
                $add .= form_close();
                $add .= '</p></div><div class="panel-footer">End '.$method.'</div></div>';
                $output->output = $add;
                $output->css_files = null;
                $output->js_files = array(base_url('assets/editor/ckeditor/ckeditor.js'));
                $this->_data['output'] = $output;
            }
        }
        elseif($action == 'view' || $action == '')
        {
            # Initialize
            $this->load->library(array('table','Arr_obj'));
            $this->load->helper('photo');
            $cat = new Category();
            $pro  =new Newz();
            $output = new stdClass();
            $arr = new Arr_obj;            
            $data = $list_key = $datalist = array(); $add = $pagination = $actions = ''; $i  = 1; $dem = 0; $paging = 2; $default_status = 'active';
            $href = ''; $return_null = false;
            $dropdown = $pro->get();
            $pro->refresh_all();
            # Phan extend_delete va update active or deactive
            
		    if($this->input->post('drop') && is_array($this->input->post('drop')) && count($this->input->post('drop'))>0)
            {
                # Kiem tra neu day khong phai la admin thi khong cho delete
                if (!$this->ion_auth->is_admin())
        		{
        			$this->set_flashdata('status_message',settings::status_message_non_permission);
                    return redirect(site_url($class.'/'.$method),'location');
        		}
                #
                # Dong nay kiem tra ten cua bang de thuc hien tac vu delete va update
                $delete_type = 'news';
                $_id  = $this->input->post('drop');
                if($this->input->post('action_status') && $this->input->post('action_status') != ''){
                    $actions = ($this->input->post('action_status')) ? $this->input->post('action_status') : $default_status;
                    foreach($_id as $item){
                        if($this->active_or_deactive($item,$delete_type,$actions)){
                            $dem +=1;
                        }
                    }
                }else{                    
                    foreach($_id as $item){
                        if($this->extend_delete($item,$delete_type)){
                            $dem +=1;
                        }
                    }
                }
                if(($dem > 0) && $dem == count($_id)){
                    if($actions != ''){
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_update').implode(",",$_id));    
                    }
                    else{
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_delete').implode(",",$_id));
                    }
                    return redirect(site_url($class.'/'.$method.'/view'),'location');
                }
                $dem = 0; # Lam xong reset bien de danh sai lai
            }
            # Phan search
            if($this->input->post($method.'_search') && $this->input->post($method.'_search') != '' && strlen($this->input->post($method.'_search'))>=2)
            {
                $_search_type = $this->input->post('search_type'); 
                $search  =  trim($this->input->post($method.'_search'));
                $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_search').' '.$search);
                redirect(site_url($class.'/'.$method.'/'.$action.'/'.$_search_type.':'.$this->url_decode($search).'/asc/1'),'location');
            }
            # Phan active and deactive            
            
            # Phan set session cho so trang hien thi
            
            if(!$this->session->userdata('num_of_page'))
            {
                $this->session->set_userdata('num_of_page',settings::default_paging);
            }
            if($this->input->post('num_of_page') && $this->input->post('num_of_page')!=$this->session->userdata('num_of_page'))
            {
                $this->session->set_userdata('num_of_page',(int)$this->input->post('num_of_page'));
            }
            $paging = $this->session->userdata('num_of_page');
            
            # Lay list key add vao dropdown list
            
            $obj = array_keys($arr->objectToArray($dropdown->stored));
            
            foreach($obj as $key=>$val)
            {
                $list_key[strtolower($val)] = ucfirst($val);
            }
            # Phan Create Form 
            $naming = strtolower(substr($method,0,2))."_".strtolower($method);
            $attributes = array('role' => 'form', 'id' => $naming,'name'=>$naming);
            $add .= form_open_multipart(strtolower(__CLASS__)."/".$method."/".$action,$attributes);
            $add .= form_hidden('delete_type', $method);
            $add .= ($this->session->flashdata('status_message')) ? '<div class="alert alert-warning" role="alert">'.$this->session->flashdata('status_message')."</div>" : ''; 
            $add .= '<div class="row">';
            $add .= '<div class="form-group col-md-1"><a class="btn btn-default" href="'.site_url($class.'/'.$method).'"><i class="fa fa-arrow-left"></i> List</a>                        
                    </div> ';
            $add .= '<div class="form-group col-md-1"><a class="btn btn-primary" href="'.site_url($class.'/'.$method.'/add').'"><i class="fa fa-plus"></i> Thêm mới</a>                        
                    </div>';
            $add .= '<div class="form-group col-md-1"><a class="btn btn-success" href="'.site_url($class.'/save_excel_file').'"><i class="fa fa-file-excel-o"></i> Xuất excel</a>                        
                    </div> ';
            $add .= '<div class="form-group col-md-1">&nbsp;<input class="btn btn-warning" type="submit" name="delete" id="delete" value="delete" onclick="return check_remove(\''.$naming.'\')"/></div>';            
            $add .= "</div>";
            
            
            $add .= '<div class="row">';
            $add .= '<div class="form-group col-md-2">
                        '.form_input(array('name'=>$method.'_search','id'=>$method.'_search','class'=>'inline form-control __search','value'=>set_value($method.'_search'),'placeholder'=>'Type here and select next','max-length'=>'255','onsubmit'=>'$(\''.$naming.'\').submit()')).'
                    </div>';
            $add .= '<div class="form-group col-md-2">
                        '.form_dropdown('search_type',$list_key,'','onchange="search_form_data_list($(this))" class =" inline form-control" ').'
                    </div> ';            
            $add .= '<div class="form-group col-md-2 pull-right">
                        '.form_dropdown('action_status',array(''=>'Chọn action','deactive'=>'Ngưng kích hoạt','active'=>'Kích hoạt'),'','class ="form-control" onchange="fn_active_or_deactive(\''.$naming.'\',$(this))" ').'
                    </div>';                        
            $add .= '</div>';
            $pagination .= '<ul class="pagination">';
            if(!is_null($id))
            {
                $_sort = 'asc';
                if(isset($sort))
                {
                   $_sort = $sort; 
                }
                # Phan nay search 
                if( ! array_key_exists($id,$list_key))
                {
                    $handle_id = explode(":",$id);
                    $handle_search = (count($handle_id)==2) ? $this->url_decode(end($handle_id)) : null;                   
                    $search_type = ($handle_search !== null) ? $handle_id[0] : 'id';
                    if($search_type == 'id')
                    {
                        if(!preg_match("/^[^0][0-9]{1,11}$/",$handle_search))
                        {
                            $return_null = true;
                        }
                    }
                    else
                    {
                        if(!preg_match("/^([\w]+)([\s]?)/",$handle_search))
                        {
                            $return_null = true;
                        }
                    }
                    if($return_null)
                    {
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_failed_search'));
                        return redirect(site_url($class.'/'.$method),'location');
                    }   
                    else
                    {
                        $test_search_has_array  = explode(" ",$handle_search);
                        if(count($test_search_has_array)>1)
                        {
                            $i = 1;
                            foreach($test_search_has_array as $item)
                            {
                                if($i == 1){
                                    $pro->like($search_type,$item, 'both');
                                }                                
                                elseif($i == count($test_search_has_array))
                                {
                                    $pro->or_like($search_type,$handle_search, 'both');
                                }
                                else{
                                    $pro->or_like($search_type,$item, 'both');
                                }
                                $i+=1;
                            }
                        }
                        else
                        {
                            $pro->like($search_type,$handle_search, 'both');  
                              
                        }
                        $i = 0 ;
                        $id = $search_type.':'.$this->url_encode($handle_search);
                        $this->session->set_flashdata('status_message',$this->lang->line('status_message_success_search').' '.$handle_search);
                        #redirect(site_url($class.'/'.$method.'/'.$action.'/'.$id),'location');
                    }
                }
                else
                {
                    $pro->order_by($id,$_sort);    
                }
            }
            else
            {
                $method  = $method; $action = $action; $id = 'id'; $sort = 'asc';    
            }
            if(isset($page) && $page != 0)
            {
                $c = $pro->get_paged($page,$paging);
                $_data = new stdClass();
                $_data->all = $c->all;
                $j = 0 ;
                foreach($_data->all as $v)
                {
                    $pro->get_by_id($v->id);
                    $cat->get_by_id($v->category_id);
                    $_data->all[$j]->join_title = $pro->language->include_join_fields()->get()->all[$this->lang_index]->join_title;
                    $_data->all[$j]->join_category = $cat->language->include_join_fields()->get()->all[$this->lang_index]->join_title;
                    $j+=1;
                }
                if($pro->paged->has_previous)
                {
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.'1').'"><i class="fa fa-angle-double-left"></i> First</a></li>';
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.$pro->paged->previous_page).'"><i class="fa fa-angle-left"></i> Prev</a></li>';
                }
                if($pro->paged->has_next)
                {
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.$pro->paged->next_page).'">Next <i class="fa fa-angle-right"></i></a></li>';
                    $pagination .= '<li><a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$id.'/'.$sort.'/'.$pro->paged->total_pages).'">Last <i class="fa fa-angle-double-right"></i></a></li>';
                }
            }
            $pagination .= '</ul>';
            $list_heading = array("","Identity","Create date","Image","Status","Category_id","Last_update","Viewer","Functions");
            $data[0] = "<input type='checkbox' class='sr check_all' name='check_all' id='check_all' />";
            $value = $this->url_decode($id); 
            foreach($obj as $item=>$val)
            {
                if(strcasecmp($val,$id) !== false)
                {
                    $value = $val;
                }
                $data[] = '<a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$value.'/asc/'.$page).'" class="label label-primary" title="ASC"><i class="fa fa-caret-up"></i></a> '.$list_heading[$i].' <a href="'.site_url($class.'/'.$method.'/'.$action.'/'.$value.'/desc'.'/'.$page).'" class="label label-success" title="DESC"><i class="fa fa-caret-down"></i></a>';
                $i += 1;    
            }
            $data[count($list_heading)] = "Functions";
            $this->table->set_heading($data);
            $tmpl = array ( 'table_open'  => '<table class="table">' );
            $this->table->set_template($tmpl);
            $i = 0;
            foreach($_data->all as $item)
            {
                $create = $this->timestamps($item->create);
                $last_update = $this->timestamps($item->last_update);
                $datalist[] = array($item->id." | ".character_limiter($item->join_title,10),$create,$last_update,$item->image,$item->status,$item->category_id." | ".$item->join_category,$item->viewer);
                $photo = showPhoto(base_url($item->image),array('width'=>80,'class'=>'img-responsive'));
                $function = '<a href="'.site_url($class.'/'.$method.'/'.'edit'.'/'.$item->id).'" class="label label-info" data-toggle="tooltip" data-placement="top" title="Edit '.$item->id.'"><i class="fa fa-pencil"></i></a>';
                $category_link = '<a href="'.site_url($class.'/categories/'.'edit'.'/'.$item->category_id).'" title="Edit '.$item->id.'">'.$item->category_id.'</a>';
                $this->table->add_row(array('<input type="checkbox" class="sr" name="drop[]" value="'.$item->id.'" />',$item->id." | ".character_limiter($item->join_title,10),$create,$photo,$item->status,$category_link." | ".$item->join_category,$last_update,$item->viewer,$function));
                $i++;                
            }
            $this->session->set_userdata('datalist',$datalist);
            $this->session->set_userdata('export_type',$method);
            $this->session->set_userdata('export_stored',$obj);
            $add .= $this->table->generate();
            $add .= '<div class="row">';
            $add .= $pagination;
            $add .= '<div class=" form-group col-md-2 pull-right ">                        
                        '.form_dropdown('num_of_page',array('10'=>'10 rows','25'=>'25 rows','50'=>'50 rows','100'=>'100 rows'),$paging,'class ="form-control inline" onchange="set_num_of_page(\''.$naming.'\',$(this))"').'
                    </div></div>'; 
            
            $add .= '<script>var update_status_message = \''.$this->lang->line('update_status_message').'\';var remove_message = \''.$this->lang->line('remove_message').'\';var failed_confirm = \''.$this->lang->line('checkin').'\'</script>';
            $add .= form_close();
            # End create form .
            $output->output = $add;
            $output->css_files = null;
            $output->js_files = array(base_url('assets/editor/ckeditor/ckeditor.js'));
            $this->_data['output'] = $output;
        }
        elseif($action == 'edit')
        {
            # Xu ly du lieu dau vao
            if($id == null && !preg_match("/^[^0][\d]+$/",$id))
            {
                return modules::run('dashboard/handle_404');
            }            
            $pro = new Newz($id);
            if(count($pro->all) == 0)
            {
                return modules::run('dashboard/handle_404'); 
            }
            #
            $this->load->helper(array('thumbnail','photo'));
            $lang   = new Language();
            $output = new stdClass();
            $i = 0;
            $photo = showPhoto(base_url($pro->image),array('width'=>100,'class'=>'img-responsive'));
            
            $list_lang = $list_id_lang = $list_join_field = $list_lang_content = $list_lang_keyword = $list_lang_title = $list_lang_description = $list_slug_title = $arr_meta = array();             
            $meta_type_arr = array('seo','google','facebook','twitter'); $add = ''; $preg_img = "/^([\w\-\_]+)([\.]{1}(jpg|png|gif))$/";
            foreach($lang->get() as $item)
            {
                $list_id_lang[] = $item->id;
                $list_lang[] = $item->name;
            }
            foreach($list_lang as $item){
                $this->form_validation->set_rules(substr($item,0,2).'_title','Tiêu đề '.substr($item,0,2),'trim|required|min_length[6]|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_description','Mô tả '.substr($item,0,2),'trim|required|max_length[255]');
                $this->form_validation->set_rules(substr($item,0,2).'_keyword','Keyword '.substr($item,0,2),'trim|required|min_length[10]|max_length[255]|callback_check_keyword');                
                $this->form_validation->set_rules(substr($item,0,2).'_content','Nội dung '.substr($item,0,2),'trim|required');                              
            }              
            $this->form_validation->set_rules('status','Tiêu đề','required');
            $this->form_validation->set_rules('category_id','Thuộc về','trim|required');
            
            if($this->form_validation->run($this) !== FALSE)
            {
                # Phan kiem tra co hinh anh duoc upload hay khong
                $image_file = null;
                if($_FILES)
                {
                    $files = $_FILES['image']['name'];
                    $type  = $_FILES['image']['type'];
                    if (!empty($files))
                    {
                        if(preg_match($preg_img,$files) && $this->is_image($type))
                        {
                            $image_file = $files;
                            $this->delete_image_category($pro->image,'news');
                        }
                        else
                        {
                            $this->session->set_flashdata('status_message','If you choose an image upload you need the file is sure an image type .');
                            redirect(site_url($class.'/'.$method.'/'.$action.'/'.$id),'location');    
                        }
                    }
                }
                # Phan lay du lieu cho table meta
                $title              = $this->input->post('vi_title');
                $description        = $this->input->post('vi_description');
                $keyword            = $this->input->post('vi_keyword');
                $slug               = url_title(convert_accented_characters($this->input->post('vi_title')));
                $category_id        = $pro->category_id     = $this->input->post('category_id');
                $status             = $pro->status          = $this->input->post('status');
                # Check hinh 
                
                $this->active_or_deactive($id,strtolower($method),$status);            
                $i = 0 ; $replace = array();
                $path = 'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';
                foreach($list_lang as $item)
                {
                    $substr = substr($item,0,2);
                    $list_lang_title[] = strtolower($this->input->post($substr.'_title'));
                    $list_slug_title[] = url_title(convert_accented_characters(strtolower($this->input->post($substr.'_title'))));
                    $list_lang_description[] = strtolower($this->input->post($substr.'_description'));
                    $list_lang_keyword[] = strtolower($this->input->post($substr.'_keyword'));                    
                    # Check hinh
                    # Kiem tra dieu kien chi xu ly hinh khi i = 0 ;
                    
                    # Khai bao bien de lay mang ton tai va mang khong ton tai
                    $arr_exists = $arr_non_exists = array();
                    # Dong duoi nay lay noi dung cua row
                    $row_content = $pro->language->include_join_fields()->get()->all[0]->join_content;
                    # Dong duoi nay lay so phan tu la image trong noi dung cua row
                    $arr_row_content = $this->preg_match_image($row_content);
                    # Dong duoi nay lay noi dung cua bien post 
                    $data = $this->replace_content($this->input->post($substr.'_content'));
                    # Dong duoi nay lay so phan tu la image cua noi dung bien post
                    $arr_post_content = $this->preg_match_image($data);
                    # Dong duoi nay dem so phan tu cua bien count va bien post
                    $count_row = count($arr_row_content); $count_post = count($arr_post_content);
                    # Truong hop ca hai deu co hinh : 
                    if($arr_row_content !== false && $count_row > 0 && $arr_post_content !== false &&  $count_post > 0 ){
                        foreach($arr_row_content as $k=>$v){
                            if(!in_array($v,$arr_post_content))
                            {
                                $arr_exists[$k] = $v;   
                            }
                        }
                        foreach($arr_post_content as $k=>$v){
                            if(!in_array($v,$arr_row_content))
                            {
                                $arr_non_exists[$k] = $v;   
                            }
                        }
                        # Neu khong tim thay thuoc mang row co trong mang post thi xoa phan tu do di.
                        if(!is_null($arr_exists))
                        {
                            if($i == 0){
                                foreach($arr_exists as $item){
                                    $name = str_replace(base_url(),"",$item);
                                    $this->direct_delete($name);
                                }
                            }
                        }
                        # Neu khong tim thay phan tu thuoc mang post co trong mang row thi lay cac phan tu do upload vao
                        if(!is_null($arr_non_exists))
                        {
                            if($i == 0){
                                $_content = array('upload_content'=>$arr_non_exists,'slug'=>$slug,'path'=>$path);
                                foreach(Modules::run('upload/curl_upload',$_content) as $k=>$v){
                                    $replace[$k] = $v;
                                    $data = str_replace($arr_post_content[$k],$v,$data);
                                }     
                            }
                            else{
                                foreach($replace as $k=>$v)
                                {
                                    $data = str_replace($arr_post_content[$k],$v,$data);
                                }
                            }
                        }
                    }
                    # Truong hop chi co row co hinh
                    elseif($arr_row_content !== false && $count_row > 0 && $arr_post_content === false){
                        # Duyet vong lap foreach va xoa het hinh di
                        if($i == 0){ 
                            foreach($arr_row_content as $item){
                                $name = str_replace(base_url(),"",$item);
                                $this->direct_delete($name);
                            }
                        }
                    }
                    # Truong hop chi co post co hinh 
                    elseif($arr_row_content === false && $arr_post_content !== false && $count_post > 0){
                        if($i == 0){
                            $_content = array('upload_content'=>$arr_post_content,'slug'=>$slug,'path'=>$path);
                            foreach(Modules::run('upload/curl_upload',$_content) as $k=>$v){
                                $replace[$k] = $v;
                                $data = str_replace($arr_post_content[$k],$v,$data);
                            } 
                        }
                        else{
                            foreach($replace as $k=>$v)
                            {
                                $data = str_replace($arr_post_content[$k],$v,$data);
                            }
                        }
                    }                    
                    # Kiem tra link hinh khong ton tai va xoa Doi voi I > 0 de Dong Bo Hoa du lieu noi dung 
                    if($i > 0){
                        $sycn_content = $this->preg_match_image($data);
                        if($sycn_content !== FALSE){
                            foreach($sycn_content as $k=>$v){
                                if(!file_exists(str_replace(base_url(),"",$v))){
                                    $data = str_replace($v,"",$data);
                                }
                            }
                        }
                        if(count($replace)>0){
                            foreach($replace as $k=>$v)
                            {
                                $data = str_replace($arr_post_content[$k],$v,$data);
                            }
                        }
                    }
                    # Cac truong hop con lai cu the la ca hai deu bang khong
                    # Hoan tat cac thao tac va gan bien 
                    $list_lang_content[] = $data;
                    $i+=1;
                }
                $i = 0 ;
                $ext         = null;
                $pathImage = "assets/uploads/temp/";                            
                #$cat->img_avatar = $image_file;
                $imageTemp = "";
                $image = $pro->image;
                # Image           
                			
                # Initialize Config Image
                if($image_file !== null)
                {                    
                    $path = 'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';                
                    # allowed types - max size - name - path - content_type - slug
                    $post_data = array(
                                        'allowed_types'=>'jpg|png|gif',
                                        'max_size'=>'2000',
                                        'name'=>'image',
                                        'slug'=> $slug,
                                        'path'=>$path,
                                        'content_type' => false,
                                        'type' => 'news'
                                    );
                    Modules::run('upload/handler',$post_data);                
                    $image = $pro->image = Modules::run('upload/get_image');   
                }
                $pro->save(); 
                # Phan ngon ngu                                        
                $_id = $id;
                foreach($list_id_lang as $k => $v)
                {
                    $lang_id      = $list_id_lang[$k];
                    $this->obj->id = $lang_id;
                    $pro->set_join_field('language','title',$list_lang_title[$k],$this->obj);
                    $pro->set_join_field('language','description',$list_lang_description[$k],$this->obj);
                    $pro->set_join_field('language','keyword',$list_lang_keyword[$k],$this->obj);                    
                    $pro->set_join_field('language','content',$list_lang_content[$k],$this->obj);                    
                    $pro->set_join_field('language','slug_title',$list_slug_title[$k],$this->obj);
                }
                $pro->refresh_all();
                # Phan the meta
                $vars = array('type'=>'new','edit'=>true,'meta_type_arr'=>$meta_type_arr,'description'=>$description,'keyword'=>$keyword,'title'=>$title,'slug'=>$slug,'image'=>$image,'_id'=>$_id);
                $this->handle_meta($vars);                
                $this->move_recycle();
                $this->session->set_flashdata('add_success',$this->lang->line('admin_success_update').' '. $id);
                redirect(site_url($class.'/'.$method.'/'.$action.'/'.$id),'refresh');
                # Code here
            }
            else
            {
                # Xoa hinh chua upload thanh cong
                $dir = opendir('assets/uploads/temp/');
                if($dir)
                {
                    while(($file = readdir($dir)) !== FALSE)
                    {
                        if(preg_match("/^([\w\-\_]+)((\.(jpg|png|gif)){1})$/",$file))
                        {
                            @unlink('assets/uploads/temp/'.$file);
                        }
                    }
                }
                # Lay thong tin ngon ngu trong bang join field
                $join = new Newz($id);
                $category = new Category();
                $list_id = array();
                foreach($category->get()->all as $item)
                {
                    $list_id[$item->id] = $item->id;
                }
                foreach($join->language->include_join_fields()->get()->all as $k=>$item)
                {
                    $list_join_field[$k]['title'] = $item->join_title;
                    $list_join_field[$k]['keyword'] = $item->join_keyword;
                    $list_join_field[$k]['description'] = $item->join_description;
                    $list_join_field[$k]['content'] = $this->detect_content($item->join_content);
                }
                # Bat dau tao form 
                $add .= '<div class="panel panel-default"><div class="panel-heading">'.ucfirst($method).'</div>
                         <div class="panel-body"><p>';
                $add .= ($this->session->flashdata('add_success')) ? '<p><div class="alert alert-success" role="alert">'.$this->session->flashdata('add_success').'</div></p>' : "";
                $attributes = array('role' => 'form', 'id' => strtolower($method));
                $add .= form_open_multipart(strtolower(__CLASS__)."/".$method."/".$action.'/'.$id,$attributes);
                $add .= '<div class="form-group block col-md-6 col-md-offset-6 text-right">
                            '.form_submit(array('name'=>'add','value'=>"Lưu lại","class"=>'btn btn-default')).'
                            '.form_button(array('name'=>'cancel','value'=>"Hủy","class"=>'btn btn-warning','onclick'=>'window.location.href=\''.site_url($class.'/'.$method).'\''),'Hủy').'
                         </div>';
                $add .= '<p class="label label-danger">'.$this->lang->line('content_edit_rule').'</p>';
                #$add .= '<script src="'.base_url('assets/editor/ckeditor/ckeditor.js').'"></script>';
                # There here is place the code if else language
                foreach($list_lang as $k=>$item)
                { 
                    $add .= '<p ><h2 class="text-primary">'.ucfirst($item).'</h2></p>';
                    $segment = substr($item,0,2);
                    $add .= '<div class="form-group">
                                <label>Tiêu đề</label>
                                '.form_input(array('name'=>$segment.'_title','id'=>$segment.'_title','class'=>'form-control','value'=>$list_join_field[$k]['title'],'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_title')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Mô tả thể loại</label>
                                '.form_textarea(array('name'=>$segment.'_description','id'=>$segment.'_description','class'=>'form-control ckeditor','value'=>$list_join_field[$k]['description'],'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_description')).'</p>
                            </div>';
                    $add .= '<div class="form-group">
                                <label>Keyword</label>
                                '.form_input(array('name'=>$segment.'_keyword','id'=>$segment.'_keyword','class'=>'form-control','value'=>$list_join_field[$k]['keyword'],'max-length'=>'255')).'
                                <p class="help-block">Kiểu tring tối đa 255 ký tự độ dài cách nhau dấu phẩy.</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_keyword')).'</p>
                            </div>';            
                    $add .= '<div class="form-group">
                                <label>Nội dung</label>
                                '.form_textarea(array('name'=>$segment.'_content','id'=>$segment.'_content','class'=>'form-control ckeditor','value'=>$list_join_field[$k]['content'],'rows'=>5,'cols'=>10)).'
                                <p class="help-block">Kiểu tring .</p>
                                <p class="label label-danger">'.ucwords(form_error($segment.'_content')).'</p>
                            </div>';                    
                    $i++;
                }
                $i = 0;
                # There here is place the code if else language
                $add .= '<div class="form-group">
                            <label>Hiển thị</label>
                            '.form_dropdown('status',array('active'=>'active','deactive'=>'deactive'),$pro->status,' class ="form-control" ').'
                        </div>';
              
                $add .= '<div class="form-group">
                            <label>Thuộc về menu</label>
                            '.form_dropdown('category_id',$list_id,$pro->category_id,' class ="form-control" ').'
                        </div>';
                
                $add .= '<div class="form-group">
                            <label>Hình đại diện</label>
                            '.form_upload(array('name'=>'image','id'=>'image','value'=>'','class'=>'form-control')).'
                            '.form_hidden(array('name'=>'img_name','id'=>'img_name','value'=>'','style'=>'display:none;')).'
                            <p class="help-block">'.$photo.'</p>
                            <p class="label label-danger">'.ucwords(form_error('img_name')).'</p>
                        </div>';
                                              
                $add .= form_close();
                $add .= '</p></div><div class="panel-footer">End '.$method.'</div></div>';
                $output->output = $add;
                $output->css_files = null;
                $output->js_files = array(base_url('assets/editor/ckeditor/ckeditor.js'));
                $this->_data['output'] = $output;
            }
        }
        # Concat string 
        $this->_data['is_index_admin']      = false;
        $js = '';        
        $this->_data['js_custom'] = $js;
        
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    public function save_excel_file()
    {
        $this->load->library('Excel');
        $list = array('A','B','C','D','E','F','G','H','I','J','K','M','N');
        $title = $subject = $description = $class = null;
        $item_list = $this->session->userdata('datalist');
        $type = $this->session->userdata('export_type');
        $obj = $this->session->userdata('export_stored');        
        switch($type)
        {
            case 'categories':
            $title = 'Category';
            $subject = 'Category_list';
            $description = 'Export list category';
            $class = 'category';
            break;
            case 'news':
            $title = 'New';
            $subject = 'New_list';
            $description = 'Export list new';
            $class = 'newz';
            break;
            case 'products':
            $title = 'Product';
            $subject = 'Product_list';
            $description = 'Export list product';
            $class = 'product';
            break;
        }        
        //load PHPExcel library
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Vinh Banh")
        							 ->setLastModifiedBy("Vinh Banh")
        							 ->setTitle($title)
        							 ->setSubject($subject)
        							 ->setDescription($description)
        							 ->setKeywords("Office 2007 openxml php")
        							 ->setCategory("Test result file");
        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $i = 1; $j = 0;
        foreach($obj as $item=>$val)
        {
            $objPHPExcel->getActiveSheet()->setCellValue($list[$j].$i,ucfirst($val));
            $j+=1;
        }
        $i = 2;  $j = 0;      
        $this->load->helper('text');
        foreach($item_list as $k)
        {
            $j= 0;
            foreach($k as $v)
            {
                $objPHPExcel->getActiveSheet()->setCellValue($list[$j].$i, $v);
                $j+=1;
            }
            $i++; 
        }
        // Rename worksheet (worksheet, not filename)
        $objPHPExcel->getActiveSheet()->setTitle($title);
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        // Redirect output to a client’s web browser (Excel2007)
        //clean the output buffer
        ob_end_clean();
        //this is the header given from PHPExcel examples. but the output seems somewhat corrupted in some cases.
        //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //so, we use this header instead.
        $filename = settings::sitename_noslug."-export-".$type."-".date("d-m-Y_H-i-s");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
    
    private function cr_dir(){
        $dir = 'assets/uploads/contents/images/'.date('Y')."/".date('m')."/";
        if(!is_dir($dir)){
            @mkdir($dir);
            @write_file($dir.'index.html', '<p>Directory access is forbidden.</p>');
        }
        return ;
    }
    
    public function leech(){
        $this->load->library('curl_download');        
        $link_curl          = array('http://saigonamthuc.thanhnien.com.vn/pages/danh-sach-rss.aspx'=>'Sài gòn ẩm thực');
        $curl               = 'http://saigonamthuc.thanhnien.com.vn/pages/danh-sach-rss.aspx';
        $l                  = parse_url($curl);
        $content            = $this->curl_download->get($curl,'://',30,0);
        $html               =  new simple_html_dom();
        $menu = new Category();
        $html->load($content);
        $src                = $text = $num = $success = $exists = $menu_list = array();
        $method             = end(explode("::",__METHOD__));
        $first              = null;
        $class              = strtolower(__CLASS__);
        $i                  = 0;
        $output             = new stdClass();
        $message            = null;
        # Lay link leech
        foreach($html->find('div.tr-lv0 a') as $items){
            if($i == 0){
                $first = $items->href;
            }
            if($i == 6){
                break;
            }
            $text[$l['host'].$items->href] = ($items->plaintext);
            $i+=1;
        }        
        #
        # Reset bien i
        $i = 0;
        # Lay list tu 1 toi 20 so mau tin
        for($j = 1; $j<=20;$j++){$num[$j] = $j;}
        #
        # Lay thong tin category
        foreach($menu->where('type','news')->get()->all as $k=>$v)
        {
            $menu->get_by_id($v->id);
            $menu_list[$v->id] = $menu->language->include_join_fields()->get()->all[$this->lang_index]->join_title; 
        }
        #   
        #
        $this->form_validation->set_rules('domain','Tên miền','required');
        $this->form_validation->set_rules('category','Menu','required');
        $this->form_validation->set_rules('menu','Chuyên mục','required');
        $this->form_validation->set_rules('number','Số tin','required');
        
        if($this->form_validation->run($this) !== FALSE)
        {
            $this->load->library('curl_download');
            $string         = 'http://'.rawurldecode($this->input->post('menu'));
            /**
             * Chổ này đặt category để lấy menu insert nha ba 
             */
            $category       = $this->input->post('category');
            $content        = $this->curl_download->get($string,'://',60,0);
            #$content        = file_get_contents($string);
            $xml            = new SimpleXMLElement($content);
            $lang           = new Language();
            $arr            = $test = $full = $replace = $list_lang = $list_id_lang = $_list_content = $list_lang_keyword = $list_content = $list_lang_title = $list_lang_description = $list_slug_title = $list_id_parent_id = $arr_meta = array();             
            $meta_type_arr  = array('seo','google','facebook','twitter');
            $pathImage      = 'assets/uploads/temp/';
            $path           = 'assets/uploads/contents/images/'.date('Y')."/".date('m')."/";
            $link           = preg_replace("/\/$/","",'http://'.$l['host']);
            $i              = 0;
            $limit          = $this->input->post('number');
            # Lay du lieu tu RSS 
            foreach($xml->channel->item as $item){    
                if($i == $limit)
                {
                    break;
                }
                $arr                    = array();
                $meta                   = get_meta_tags($link."/".$item->link);
                $full[$i]['keyword']    = preg_replace("/\;/",",",$meta['keywords']);
                $full[$i]['link']       = $link;
                $full[$i]['title']      = (string)$item->title;
                $full[$i]['slug']       = url_title(convert_accented_characters(strtolower((string)$item->title)));
                
                $full[$i]['description']= preg_replace("/(\<.*\>)|(\&?\#*\w\d*\;)/","",word_limiter((string)$item->header,30))." ..";
                $full[$i]['list']       = $images = $this->preg_match_image((string)$item->description);
                foreach($images as $k=>$v){
                    $arr[] = $link.$v;
                }
                $full[$i]['avatar']     = $arr[0];#(string)$item->enclosure['url'];
                $full[$i]['images']     = $arr;
                $full[$i]['contents']   = ((string)$item->description);
                $i+=1;    
            }
            #
            # Lay danh sach ngon ngu
            foreach($lang->get() as $item)
            {
                $list_id_lang[] = $item->id;
                $list_lang[]    = $item->name;
            }        
            #
            $i = 0;
            # Duyet vong lap foreach de insert du lieu 
            foreach($full as $k=>$v)
            {
                $title          = $full[$i]['title'];
                $description    = $full[$i]['description'];
                $keyword        = $full[$i]['keyword'];
                $image          = $full[$i]['avatar'];
                $slug           = $full[$i]['slug'];
                $arr_list       = $full[$i]['images'];
                $contents       = $full[$i]['contents'];
                $list           = $full[$i]['list'];
                # Kiem tra su ton tai cua du lieu 
                $test           = new New_meta();
                $_data = $test->where('description',$slug)->get()->all;
                # Neu du lieu nay da ton tai
                if(count($_data)>0)
                {
                    $exists[$k] = $title;
                }
                # Neu du lieu nay khong ton tai thi tiep tuc insert trong else
                else{
                    # Du lieu dung de insert
                    # Reset tat ca cac bien khi i tang len  
                    $list_lang_title = $list_lang_description = $list_lang_keyword = $list_slug_title = $list_content = array();
                    unset($_content); 
                    Modules::run('upload/reset_all');
                    $pro            = new Newz();
                    
                    # Reset lan dau khi upload hinh
                    $_content = array('upload_content'=>array($image),'slug'=>$slug,'path'=>$path,'pathImage'=>$pathImage);
                    $dam = Modules::run('upload/curl_upload_link',$_content);
                    $image              = $pro->image       = Modules::run('upload/get_image');
                    #
                    unset($_content);
                    Modules::run('upload/reset_all');
                    #$image              = $pro->image       = 'csdcdsc.jpg';
                    $status             = $pro->status      = 'active';                
                    $create             = $pro->create      = $pro->last_update = time();
                    $category_id        = $pro->category_id = $category;
                    $viewer             = $pro->viewer      = 0;
                    $j = 0;
                    foreach($list_lang as $item)
                    {
                        $list_lang_title[] = $title;
                        $list_slug_title[] = $slug;
                        $list_lang_description[] = $description;
                        $list_lang_keyword[] = $keyword;
                        $data = &$contents;                                                  
                        if( $arr_list !== false && count($arr_list)>0){
                            $_content = array('upload_content'=>$arr_list,'slug'=>$slug,'path'=>$path);
                            if($j == 0)
                            {
                                foreach(Modules::run('upload/curl_upload',$_content) as $k2=>$v2){
                                    #$this->direct_delete(str_replace(base_url(),"",$arr_list[$k]));
                                    #$replace[$k] = $v;
                                    $data = str_replace($list[$k2],$v2,$data);
                                }          
                            }
                        }
                        $list_content[] = $data;
                        $j+=1;
                    }
                    # Reset bien dem $j
                    $j = 0;
                    #
                    # Handle the meta  
                    if($pro->save_language($lang->all))
                    {
                        # Phan ngon ngu                                        
                        $_id = $pro->id;
                        $success[] = $_id;
                        foreach($list_id_lang as $k1 => $v1)
                        {
                            $lang_id      = $list_id_lang[$k1];
                            $this->obj->id = $lang_id;
                            $pro->set_join_field('language','title',$list_lang_title[$k1],$this->obj);
                            $pro->set_join_field('language','description',$list_lang_description[$k1],$this->obj);
                            $pro->set_join_field('language','keyword',$list_lang_keyword[$k1],$this->obj);
                            $pro->set_join_field('language','slug_title',$list_slug_title[$k1],$this->obj);
                            $pro->set_join_field('language','content',$list_content[$k1],$this->obj);
                        }
                        $vars = array('type'=>'new','meta_type_arr'=>$meta_type_arr,'description'=>$description,'keyword'=>$keyword,'title'=>$title,'slug'=>$slug,'image'=>$image,'_id'=>$_id);
                        $this->handle_meta($vars);
                    }
                    #$pro->refresh_all();
                    #
                }
                $i+=1;
            }
            $i = 0;
            
            if(count($exists)>0){
                $message  .= 'Leech các link sau không thành công :'.implode("<br /> - ",$exists)." <br />Lưu ý : Hãy kiểm tra sự tồn tại của các row không thể insert.";    
            }
            $message .= '<br />Leech link thành công với các id sau :'.implode(",",$success);
            $this->session->set_flashdata('add_success',$message);
            redirect(current_url(),'location'); 
        }
        else
        {
            $add = '';    
            $add .= '<div class="panel panel-default"><div class="panel-heading">Leech link</div>
                     <div class="panel-body"><p>';
            $add .= ($this->session->flashdata('add_success')) ? '<p><div class="alert alert-success" role="alert">'.$this->session->flashdata('add_success').'</div></p>' : "";
            $attributes = array('role' => 'form', 'id' => strtolower($method));
            $add .= form_open_multipart($class."/".$method,$attributes);
            $add .= '<div class="form-group block col-md-6 col-md-offset-6 text-right">
                        '.form_submit(array('name'=>'add','value'=>"Lấy dữ liệu","class"=>'btn btn-default')).'
                        '.form_button(array('name'=>'cancel','value'=>"Hủy","class"=>'btn btn-warning','onclick'=>'window.location.href=\''.site_url($class.'/'.$method).'\''),'Hủy').'
                     </div>';            
            # There here is place the code if else language
            $add .= '<div class="form-group">
                        <label>Domain</label>
                        '.form_dropdown('domain',$link_curl,$curl,' class ="form-control" id="change_domain" ').'
                        <label class="text-danger">'.form_error('domain').'</label>
                    </div>';
          
            $add .= '<div class="form-group">
                        <label>Chọn menu</label>
                        '.form_dropdown('menu',$text,$first,' class ="form-control" ').'
                        <label class="text-danger">'.form_error('menu').'</label>
                    </div>';
                    
            $add .= '<div class="form-group">
                        <label>Chọn category để insert</label>
                        '.form_dropdown('category',$menu_list,'',' class ="form-control" ').'
                        <label class="text-danger">'.form_error('category').'</label>
                    </div>';
                    
            $add .= '<div class="form-group">
                        <label>Chọn số tin muốn lấy</label>
                        '.form_dropdown('number',$num,1,' class ="form-control" ').'
                        <label class="text-danger">'.form_error('number').'</label>
                    </div>';
                    
            $add .= form_close();
            $add .= '</p></div><div class="panel-footer">End '.$method.'</div></div>';
            $output->output = $add;
        }
        
        # Save data
        #$output->output = var_dump($pro);
        $this->_data['output']              = $output;        
        $this->_data['is_index_admin']      = true;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    public function handle_meta($vars = array())
    {
        $arr_meta = array();
        $naming = ucfirst($vars['type'])."_meta";
        $naming_id = $vars['type']."_id";
        $i = 0 ;
        foreach($vars['meta_type_arr'] as $item)
        {
            switch($item)
            {
                case 'seo': 
                    $arr_meta[$item]['description'] = $vars['description'];
                    $arr_meta[$item]['keyword'] = $vars['keyword'];
                    $arr_meta[$item]['canonical']  = $vars['slug'];
                break;
                case 'google': 
                    $arr_meta[$item]['title'] = $vars['title'];
                    $arr_meta[$item]['description'] = $vars['description'];
                    $arr_meta[$item]['image'] = $vars['image'];
                    #$arr_meta[$item]['author']  = settings::google_plus_author;
                    #$arr_meta[$item]['publisher']  = settings::google_plus_author;
                    #$arr_meta[$item]['shortcut icon']  = settings::shortcut_icon_path;
                break;
                case 'facebook': 
                    $arr_meta[$item]['og:title'] = $vars['title'];
                    $arr_meta[$item]['og:description'] = $vars['description'];
                    $arr_meta[$item]['og:image'] = $vars['image'];
                    $arr_meta[$item]['og:url'] = $vars['slug'];
                    #$arr_meta[$item]['og:type'] = 'website';
                    #$arr_meta[$item]['og:site_name'] = $slug;                                
                    #$arr_meta[$item]['og:article:author'] = settings::facebook_author;
                    #$arr_meta[$item]['og:article:publisher'] = settings::facebook_author;
                break;
                case 'twitter': 
                    $arr_meta[$item]['twitter:title'] = $vars['title'];
                    $arr_meta[$item]['twitter:card'] = $vars['keyword'];
                    $arr_meta[$item]['twitter:description'] = $vars['description'];
                    $arr_meta[$item]['twitter:image:src'] = $vars['image'];
                    #$arr_meta[$item]['twitter:site'] = '@'.$author;
                    #$arr_meta[$item]['twitter:creator'] = $author;
                break;
            }
        }
        if(array_key_exists('edit',$vars) && $vars['edit'] === true){
            if(count($arr_meta)>0)
            {
                $meta = new $naming();
                $get_by  = 'get_by_'.$naming_id;
                $meta->$get_by($vars['_id']);
                $meta_list_id = array();
                foreach($meta->all as $item)
                {
                    $meta_list_id[] = $item->id;
                }   
                foreach($arr_meta as $item => $v)
                {
                    foreach($v as $_item => $_v)
                    {                           
                        $meta->get_by_id($meta_list_id[$i]);
                        $meta->status = $item;
                        $meta->title = $_item;
                        $meta->description = $_v;
                        $meta->save();
                        $meta->refresh_all();
                        $i+=1;
                    }
                }
                $i = 0;
                $meta->refresh_all();
            }
        }
        else
        {
            if(count($arr_meta))
            {
                foreach($arr_meta as $item => $v)
                {
                    foreach($v as $_item => $_v)
                    {
                        $meta = new $naming();
                        $meta->status = $item;
                        $meta->title = $_item;
                        $meta->description = $_v;
                        $meta->$naming_id = $vars['_id'];
                        if($meta->save())
                        {
                            $i+=1;
                        }
                        $meta->refresh_all();
                    }
                }
            }
        }
        
        return ($i == 14) ? true : false;
    }
    
    public function timestamps($time){
        return date('d/m/Y - H:i:s',$time);
    }
    
    public function languages()
    {
        $crud = new grocery_CRUD();
        $crud->set_table('languages');
        $crud->columns('id','name','image');
        $crud->set_subject("new language");        
        $crud->required_fields('name','image');
        $crud->add_fields('name','image');
        $crud->fields('name','image');
        $crud->edit_fields('name','image');                               
        //$crud->callback_before_insert(array($this,'_callback_before_insert_socials'));
        $crud->callback_after_insert(array($this,'callback_after_insert_languages'));
        //$crud->callback_before_update(array($this,'_callback_before_update_socials'));        
        $crud->unset_jquery();
        //$crud->unset_delete();
        //$crud->unset_jquery_ui();     
        $crud->set_rules('name', 'Mô tả', 'trim|required');
        $crud->set_rules('image', 'Status', 'trim|required');
        # CALLBACK
		$crud->display_as('id','Identity')
			 ->display_as('name','Tên ngôn ngữ')             
             ->display_as('status','Hình đại diện');             
        $js = '';        
        $this->_data['js_custom'] = $js;        
		$output = $crud->render();
		$this->_example_output($output);
    }
    
    public function callback_after_insert_languages($post , $pk){
        $return = false;
        $data = new Language($pk);
        $folder_name = $data->all[0]->name;
        $dir = APPPATH.'language/'.$folder_name.'/';
        if(!is_dir($dir)){
            @mkdir($dir);
        }
        $dest  = APPPATH.'language/vietnam/';
        #
        $get_dest = opendir($dest);
        while(($file = readdir($get_dest))!==FALSE){
            if(!copy($dest.$file,$dir.$file)){
                log_message('error','Failed to copy file');
            }
        }
        closedir($dest);
        return true;
    }
    
    public function settings()
    {
        $crud = new grocery_CRUD();
        $crud->set_table('settings');
        $crud->columns('id','name','value','description','visible','type');
        $crud->set_subject("new setting");
        $crud->required_fields('name','value','description','visible','type');
        $crud->add_fields('name','value','description','visible','type');
        $crud->fields('name','value','description','visible','type');
        $crud->edit_fields('name','value','description','visible','type');                               
        //$crud->callback_before_insert(array($this,'_callback_before_insert_socials'));
        //$crud->callback_before_update(array($this,'_callback_before_update_socials'));        
        $crud->unset_jquery();
        //$crud->unset_delete();
        //$crud->unset_jquery_ui();     
        $crud->set_rules('name', 'Mô tả', 'trim|required');
        $crud->set_rules('value', 'Status', 'trim|required');
        $crud->set_rules('description', 'Status', 'trim|required');
        $crud->set_rules('visible', 'Status', 'required|,min_length[1]|is_numeric');
        $crud->set_rules('type', 'Kiểu hiển thị', 'trim|required');
        # CALLBACK
		$crud->display_as('id','Identity')
			 ->display_as('name','Khóa')             
             ->display_as('value','Giá trị')
             ->display_as('description','Mô tả')
             ->display_as('visible','Trạng thái')
             ->display_as('type','Kiểu hiển thị');             
        $js = '';
        $this->_data['js_custom'] = $js;        
		$output = $crud->render();
        $output->output .= '<ul class="list-group"><label class="label label-danger">Lưu ý :</label> <li class="list-group-item">Những item được placeholder bởi ckeditor phải chuyển về không mã html hết.</li>
        <li class="list-group-item">Khóa phải bắt đầu bằng chử site_</li><li class="list-group-item">Trạng thái là số 0 hoặc 1</li><li class="list-group-item">Kiểu hiển thị có các loại sau : text ,radio ,textarea,ckeditor_textarea</li></ul>';
		$this->_example_output($output);
    }
    
    
    
    
    
    public function callback_after_insert($callback = null)
	{
		$this->callback_after_insert = $callback;

		return $this;
	}    
    
    # path , filename , type , width , height , fontsize ,color , fontpath , is_resize , transparent
    public function testing3()
    {
        //$post_array  =array(
//                        'path'=>'assets/uploads/contents/images/'.date('Y').'/'.date('m').'/',
//                        'filename' => 'DSC03624.JPG',
//                        'type'=>'image',                        
//                        'fontsize'=>20,
//                        'color'=>'#ffffff',
//                        'fontpath'=>BASEPATH.'fonts/texb.ttf',
//                        'is_resize'=>TRUE,
//                        'width'=>'400',
//                        'height'=>'400',
//                        'transparent'=>50
//        );
        $path = APPPATH.'logs/';
        $output = new stdClass;
        $this->load->library('listall');
        $data = $this->listall->merge_array($this->listall->language($path,true,0));
        $return = $array = $test = $content = $fetch =  array(); $i = 0; $small = '';
        foreach($data as $k=>$v){
            $temp = filemtime($v);
            $return[$v] = $temp;
            $test[$temp] = $v;            
        }
        $sort = $this->quicksort(array_values($return));        
        foreach(array_flip($sort) as $k=>$v){
            if($v == 1){
                break;
            }
            $f = fopen($test[$k],'r');
            while(!feof($f))   {                
                $small  .= "<p>{$i} <code>".fgets($f)."</code></p>\n";
                $i+=1;                            
            }
            fclose($f);
            unset($f);
        }
        $output->output = $small;        
        $this->_data['output']              = $output;#var_dump(call_user_func(array($this,'callback_upload'),$post_array));  
        $this->_data['is_index_admin']      = true;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    function test_callback($something = array())
    {
        return $something;
    }
    
    public function preg_match_image($string = ''){
        if($string == '')
        {
            return false;
        }
        preg_match_all("/(((http|https)?[\:]{1})?[www\.]?[\/\/]?[\w\s\-\_\.\/\%20\s]+[\.][jpg|JPG|png|PNG|gif|GIF]{3})/",$string,$obj);
        return (count($obj[0])>0) ? $obj[0] : false;
    }   
    
    
    public function testing2()
    {
        $cat  = '';
        $string = "<div class='image-wrapper'>
        <img  src='http://cdn.tgdd.vn/qcao/27_10_2014_16_48_16_TGDd-KY-NGUYEN-MTB-540-210.jpg' />
        <img  src='http://cdn.tgdd.vn/qcao/31_10_2014_09_55_55_TGDd-Iphone6-540x210.jpg' />
        <img  src='//cdn.tgdd.vn/qcao/28_10_2014_16_05_10_TGDd-KY-NGUYEN-LAPTOP-540-210.jpg' />
        <img  src='http://cdn.tgdd.vn/qcao/04_11_2014_16_37_41_TGd-MUA-CHUNG-540-210.jpg' />
        <img  src='/qcao/28_10_2014_16_05_10_TGDd-KY-NGUYEN-LAPTOP-540-210.jpg' />
        <img  src='/qcao/28_10_2014_16_05_10_TGDd-KY-NGUYEN-LAPTOP-540-210.aspx' />
        <img  src='qcao/28_10_2014_16_05_10_TGDd-KY-NGUYEN-LAPTOP-540-210.jpg' />
        <img  src='assets/uploads/contents/images/temp/DSC03575.JPG' />
        <img  src='assets/uploads/contents/images/temp/DSC03575.png' />
        <img src='/Pictures201411/Tan_Nhan/Thuc%20khach%20xep%20hang%20thuong%20thuc%20huong%20vi%20lau%20tu%20su%20kien%20TB.jpg'
        </div>";
        //if(preg_match_all('/(href|src)\s*=\s*"([^\s]+\/\/[^\/]+.\/[^\s]+\.(jpg|jpeg|png|gif|bmp))/ixu',$string,$arr,PREG_SET_ORDER))
//        {
//            
//        }
        //if(preg_match_all("/(((http|https)?[\:]{1})?[www\.]?\/\/[\w\-\_\.\/]+[\.][a-z]{3})/",$string,$true))
//        {
//            $cat = $true;
//        }
        $output =new stdClass();
        //$m = new Newz(38);
//        $content = $m->language->include_join_fields()->get()->all[0]->join_content;
//        $preg = $this->preg_match_image($content);
//        $replace = array();
//        foreach($preg as $item)        
//        {
//            $content  = str_replace($item,base_url($item),$content);
//        }
//        $content .= $this->replace_content($content);
        #$output->output = var_dump($this->preg_match_image($string));
        #$this->session->unset_userdata('add_success');
        
        //$user_ip = '206.190.138.20';
//        $add = '';
//        $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$user_ip"));
//        $city = $geo["geoplugin_city"];
//        $region = $geo["geoplugin_regionName"];
//        $country = $geo["geoplugin_countryName"];
//        $add .= "City: ".$city."<br>";
//        $add .= "Region: ".$region."<br>";
//        $add .= "Country: ".$country."<br>";
//        $output->output = var_dump($geo);
        
        /*
        geoplugin_request
        geoplugin_status
        geoplugin_credit
        geoplugin_city
        geoplugin_region
        geoplugin_areaCode
        geoplugin_dmaCode
        geoplugin_countryCode
        geoplugin_countryName
        geoplugin_continentCode
        geoplugin_latitude
        geoplugin_longitude
        geoplugin_regionCode
        geoplugin_regionName
        geoplugin_currencyCode
        geoplugin_currencySymbol
        geoplugin_currencySymbol_UTF8
        geoplugin_currencyConverter
        */
        //$c = array();
//        $dir = opendir(APPPATH."logs/");
//        while(($item = readdir($dir)) !== FALSE){
//            if(preg_match("/^[\w\-\_]+\.php$/",$item)){
//                $c[] = $item;
//            }
//        }
        //$this->load->library('listall');
//        #$ar  =$this->listall->language(APPPATH.'language/vietnam/',true,0);
//        $text = file_get_contents('application/language/vietnam/calendar_lang.php');
//        preg_match_all("/lang\[\'(.*)\'\]\s+\=\s+(\"|\')(.*)(\"|\')\;/",$text,$data);
//        $list_key = $data[1];
//        $list_value = $data[3];
//        $val = array();
//        foreach($list_key as $k=>$item){
//            $val[$item] = $list_value[$k];
//        }
//        $item = '';
//        foreach($val as $k=>$v){
//            $this->form_validation->set_rules($k,$k,'required');
//        }
//        if($this->form_validation->run() !== FALSE){
//            foreach($val as $k=>$v){
//                $text = str_replace($v,stripslashes($this->input->post($k)),$text);
//            }
//            $f = fopen('application/language/home_common_lang.php','w');
//            fwrite($f,$text);
//            fclose($f);
//            redirect(current_url(),'refresh');
//        }
//        $att = array('role'=>'form','id'=>'form_test');
//        $item .= form_open_multipart('dashboard/testing2',$att);
//        foreach($val as $k=>$v){
//            $item .= "<p><input class='form-control' name='{$k}' type='text' value='".($v)."'>\n</p>";
//        }
//        $item .= "<button class='btn btn-default' type='submit' value='save' name='save' >save</button>";
//        $item .= form_close();
//
        $str = "Is '' your's name O\'Reilly\"?";

// Outputs: Is your name O\'Reilly?
            $str = stripslashes($str);
            $str = preg_replace("/(\'|\")/", "\\'", $str);
        
        ##$output->output = var_dump($str);
        $this->_data['output']              = $output;#var_dump(call_user_func(array($this,'preg_match_image'),$string)).var_dump(filter_list());  
        $this->_data['is_index_admin']      = true;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    
    
    public function detect_content($content){
        $preg = $this->preg_match_image($content);
        if($preg !== false && count($preg)>0)
        {
            foreach($preg as $item)
            {            
                $content = str_replace($item,base_url($item),$content);
            }
        }
        return $content;
    }
    
    public function replace_content($content){
        $preg = $this->preg_match_image($content);
        if( $preg !== false &&  count($preg)>0)
        {
            foreach($preg as $item)
            {
                if(!preg_match('/assets\/uploads\/contents\/images\/temp/',$item))
                {
                    $name =str_replace(base_url(),"",$item);
                    $content = str_replace($item,$name,$content);
                }
            }
        }
        return $content;
    }
    
    public function testing()
    {
        #$link = 'http://saigonamthuc.thanhnien.com.vn/pictures201411/tan_nhan/dien%20vien%20hai%20tran%20thanh%20va%20sieu%20dau%20bep%20pham%20tuan%20hai%20dan%20dat%20chuong%20trinh%20tb.jpg';
        $this->load->library('curl_download');
        //$content        = file_get_contents('http://saigonamthuc.thanhnien.com.vn/pages/rssnews.aspx?Channel=Góc+sáng+tạo');
//        $xml            = new SimpleXMLElement($content);
//        $lang           = new Language();
//        $arr            = $test = $full = $replace = $list_lang = $list_id_lang = $_list_content = $list_lang_keyword = $list_content = $list_lang_title = $list_lang_description = $list_slug_title = $list_id_parent_id = $arr_meta = array();
//        $pathImage      = 'assets/uploads/temp/';
//        $path           = 'assets/uploads/contents/images/'.date('Y')."/".date('m')."/";
//        $link           = $xml->channel->link;
//        $i              = 0;
//        $limit          = 1;
//        # Lay du lieu tu RSS 
//        foreach($xml->channel->item as $item){    
//            if($i == $limit)
//            {
//                break;
//            }       
//                
//            $arr                    = array();
//            $meta                   = get_meta_tags($link."/".$item->link);
//            $full[$i]['keyword']    = preg_replace("/\;/",",",$meta['keywords']);
//            $full[$i]['link']       = $link;
//            $full[$i]['title']      = (string)$item->title;
//            $full[$i]['slug']       = url_title(convert_accented_characters(strtolower((string)$item->title)));
//            $full[$i]['avatar']     = (string)$item->enclosure['url'];
//            $full[$i]['description']= preg_replace("/(\<.*\>)|(\&?\#*\w\d*\;)/","",word_limiter((string)$item->header,30))." ..";
//            $full[$i]['list']       = $images = $this->preg_match_image((string)$item->description);
//            foreach($images as $k=>$v){
//                $arr[] = $link.$v;
//            }
//            $full[$i]['images']     = $arr;
//            $full[$i]['contents']   = ((string)$item->description);
//            
//            $i+=1;    
//        }
        
        //$html = new simple_html_dom();
//        $html->load($full[4]['contents']);
//        $ar = array();
//        foreach($html->find('img') as $item)
//        {
//            $ar[] =$item->src;
//        }
        //$output = new stdClass;
//        $info = exif_read_data('assets/uploads/temp/Zio_Bello_pizza_2_san_banh.jpg');
//        $output->output = var_dump($info).var_dump(filesize('assets/uploads/temp/Zio_Bello_pizza_2_san_banh.jpg')/1024);
        #$this->session->unset_userdata('add_success');
        $output = new stdClass;
        //$m = new Images();
//        $link = $m->get_by_description("slide")->all;
        #$link = 'http://localhost/coffee/assets/template/img/_home/restaurant-1.jpg';
        #$base_link = 'assets/uploads/temp/roast-chicken.jpg';
        $m = new Category_meta();
        $m->where('category_id',97)->get();
        #$output->output = #var_dump($this->curl_download->download($link,$base_link));
        $this->_data['output']              = $output;#var_dump(call_user_func(array($this,'preg_match_image'),$string)).var_dump(filter_list());  
        $this->_data['is_index_admin']      = true;      
	    $this->template->title("Coffee house");
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('headers','admin_theme/boostrap/headers',$this->_data);
        $this->template->build('dashboard/content/default',$this->_data);
    }
    
    public function direct_delete($path){
        $array = explode("/",$path);
        $dem = count($array);
        $thumb_path = 'assets/uploads/contents/_thumbs/Images/temp/no_picture.gif';
        if($array[$dem-2] == 'temp')
        {
            if($array[$dem-3] == 'images'){
                log_message('error',"Has array!");
                $array[$dem-3] = '_thumbs/Images';
            }
            $thumb_path = implode("/",$array);
        }
        else{
            if($array[$dem-2] > 0)
            {
                if($array[$dem-4] == 'images'){
                    log_message('error',"Has array!");
                    $array[$dem-4] = '_thumbs/Images';
                }
                $thumb_path = implode("/",$array);
            }
        }
        if(file_exists($thumb_path))
        {
            @unlink($thumb_path);
        }
        return @unlink($path);
    }
    
    public function detected_http($http){
        
    }
    
    private function delete_image_category($link,$type = 'category')
    {
        if($type == 'categories')
        {
            $min_width  = settings::width_minimum_category;
            $min_height = settings::height_minimum_category;
            $max_width  = settings::width_maximum_category;
            $max_height = settings::height_maximum_category;
        }
        elseif($type == 'news'){
            $min_width  = settings::width_thumb_new;
            $min_height = settings::height_thumb_new;
            $max_width  = settings::width_new;
            $max_height = settings::height_new;
        }
        else
        {
            $min_width  = settings::width_thumb_product;
            $min_height = settings::height_thumb_product;
            $max_width  = settings::width_product;
            $max_height = settings::height_product;
        }
        $img = preg_replace("/({$min_width}\-{$min_height})/","{$max_width}-{$max_height}",$link);
        if(file_exists($link) && file_exists($img)){
            return @unlink($link) && @unlink($img);    
        }
        return ;
    }   
      
    protected function delete_relation_category($id = array())
    {
        $cat = new Category();
        $meta = new Category_meta();
        $arr_list = null;
        $type_where = (is_array($id) && count($id)>1) ? 'where_in' : 'where';
        $cat->$type_where('parent_id',$id)->get();
        $meta->$type_where('category_id',$id)->get(); $meta->delete_all();        
        if(count($cat->all) > 1){
            $arr_list = array();
            foreach($cat->all as $item)
            {
                $arr_list[] = $item->id;
                $this->delete_image_category($item->img_avatar,'categories');
            }
        }
        elseif(count($cat->all) == 1){
            $arr_list = $cat->all[0]->id;
            $this->delete_image_category($cat->img_avatar,'categories');
        }        
        if($arr_list != NULL){
            $this->delete_relation_category($arr_list);
            $cat->delete_all();
        }else{
            return $cat;
        }
    }
    
    private function active_or_deactive_category($id,$action  = 'active')
    {
        $cat = new Category();
                
        #$meta = new Category_meta();
        $arr_list = null;
        $data = $cat->where('parent_id',$id)->get();
        $cat->refresh_all();
        if(count($data->all) > 0){
            foreach($data->all as $item)
            {
                $cat->get_by_id($item->id);            
                $cat->status = $action;
                    $this->active_or_deactive_category($item->id,$action);
                $cat->save();
                $cat->refresh_all();
            }
        }
        else
        {
            return $cat; 
        }
    }
    
    protected function active_or_deactive($id,$type,$action)
    {
        $model = 'category';
        $flag = 0;
        switch($type)
        {
            case 'categories': 
            $model = 'category'; break;
            case 'news': 
            $model = 'newz'; break;
            case 'products': 
            $model = 'product'; break;
        }
        $obj = new $model($id);
        if($model == 'category')
        {
            $obj->status = $action;
            if($this->active_or_deactive_category($id,$action))
            {
                $flag = 1;   
            }            
        }
        else{
            $obj->status = $action;
        }
        if($obj->save()){
            $flag +=1;
        }
        return ($flag > 1) ? true : false;
    }
    
    private function delete_relation($id,$type = 'product')
    {
        $n_1 = $key = null;
        switch($type)
        {
            case 'product':
            $n_1 = 'Product_meta';
            $key = 'product_id';
            break;
            case 'newz': 
            $n_1 = 'New_meta';
            $key = 'new_id';
            break;
        }
        $obj = new $n_1();
        $obj->where($key,$id)->get();
        if(count($obj->all)>0){
            log_message('error','Had data');
        }
        return $obj->delete_all();         
    }
        
    protected function extend_delete($id,$type)
    {
        $model = 'category';
        $flag = 0;
        switch($type)
        {
            case 'categories': 
            $model = 'category'; break;
            case 'news': 
            $model = 'newz'; break;
            case 'products': 
            $model = 'product'; break;
        }
        $obj = new $model($id);
        if($model == 'category')
        {
            $this->delete_image_category($obj->img_avatar,$type);
            if($this->delete_relation_category($id))
            {
                $flag = 1;   
            }            
        }
        else
        {
            # Xoa hinh avatar
            $this->delete_image_category($obj->image,$type);
            # Xoa hinh content lien quan            
            $arr = $this->preg_match_image($obj->language->include_join_fields()->get()->all[0]->join_content);
            if( $arr !== false && count($arr)>0){
                foreach($arr as $k=>$v){
                    $name = str_replace(base_url(),"",$v);
                    $this->direct_delete($name);
                }
            }
            #$obj->refresh_all();
            # Xoa relation
            if($this->delete_relation($id,$model))
            {
                $flag = 1;
            }
        }
        if($obj->delete()){
            $flag +=1;
        }
        return ($flag > 1) ? true : false;
    }
    
    function delete_selection()
    {
       $id_array = array();
       $selection = $this->input->post("selection", TRUE);
       $id_array = explode("|", $selection);
       $host  = $_SERVER['REQUEST_URI'];
       $split = preg_split("/[\/]+/",$host);
       $method = $split[3];
       if($method == 'comments'){
           foreach($id_array as $item){
              if(!$this->delete_commenter($item)){
                log_message('error',"dump");
              }
           }
       }else{
           $new =new $method();
           if(count($id_array == 1))
           {
               $new->where('id',$id_array[0])->get();
               $new->delete();
           }
           else
           foreach($id_array as $item)
           {
               $new->where('id',$item)->get();
               $new->delete();
           }     
       }  
    }
    
    public function images()
    {
        $crud = new grocery_CRUD($this);
        $crud->set_table('images');        
        $crud->columns('id','name','description','width','height','create','last_update');
        $crud->set_subject("images file");
        $crud->required_fields('name','description');
        $crud->add_fields('name','description');
        $crud->fields('name','description','width','height','create','last_update');
        $crud->edit_fields('name','description');
        $crud->field_type('width','invisible');
        $crud->field_type('height','invisible');
        $crud->field_type('create','invisible');
        $crud->field_type('last_update','invisible');
                               
        $crud->callback_before_insert(array($this,'_callback_before_insert_images'));
        $crud->callback_before_update(array($this,'_callback_before_update_images'));
        //$crud->callback_after_upload(array($this,'_callback_after_upload_images'));
        
        $crud->callback_column('create',array($this,'_callback_last_update'));
        $crud->callback_column('last_update',array($this,'_callback_last_update'));      
        $crud->callback_add_field('icon', function () {
            return $this->config->item('data_list1');
        });
        
        $crud->unset_jquery();
        //$crud->unset_delete();
        //$crud->unset_jquery_ui();                		
        
        $crud->set_rules('name', 'Tên và đường dẫn', 'trim|required|min_length[5]|max_length[255]|callback_valid_path_images');
        $crud->set_rules('description', 'Mô tả hình','trim|required|min_length[5]|max_length[255]');
        
        
        # CALLBACK
		$crud->display_as('id','Identity')
			 ->display_as('name','Tên file hình')
             ->display_as('description','Mô tả')
             ->display_as('width','Chiều rộng')
             ->display_as('height','Chiều cao')
             ->display_as('create','Ngày tạo')
             ->display_as('last_update','Cập nhật');
             
        $js = '';
        
        $this->_data['js_custom'] = $js;
        
		$output = $crud->render();
                
		$this->_example_output($output);
    }
    
    public function counters()
    {
        $crud = new grocery_CRUD();
        $crud->set_table('counters');
        $crud->columns('id','title','description','status');
        $crud->set_subject("new counter");        
        $crud->required_fields('title','description','status');
        $crud->add_fields('title','description','status');
        $crud->fields('title','description','status');
        $crud->edit_fields('title','description','status');
                               
        //$crud->callback_before_insert(array($this,'_callback_before_insert_socials'));
        //$crud->callback_before_update(array($this,'_callback_before_update_socials'));
        
        $crud->callback_add_field('status', function () {
            return '<select name="status" class="form-control selectpicker"><option value="deactive">deactive</option><option value="active">active</option></select>';
        });
        $crud->unset_jquery();
        //$crud->unset_delete();
        //$crud->unset_jquery_ui();                		
        $crud->set_rules('title', 'Tên bộ đếm', 'trim|required');
        $crud->set_rules('description', 'Mô tả', 'trim|required');
        $crud->set_rules('status', 'Status', 'trim|required');
        # CALLBACK
		$crud->display_as('id','Identity')
			 ->display_as('title','Tên bộ đếm')
             ->display_as('description','Mô tả')
             ->display_as('status','Trạng thái');
             
        $js = '';        
        $this->_data['js_custom'] = $js;        
		$output = $crud->render();
		$this->_example_output($output);
    }
    
    public function plugins()
    {
        $crud = new grocery_CRUD();
        $crud->set_table('plugins');
        $crud->columns('id','name','description','position','status');
        $crud->set_subject("new plugins");
        $crud->required_fields('name','description','position','status');
        $crud->add_fields('name','description','position','status');
        $crud->fields('name','description','position','status','create','last_update');
        $crud->edit_fields('name','description','position','status');
        $crud->field_type('create','invisible');
        $crud->field_type('last_update','invisible');
                   
        $crud->callback_before_insert(array($this,'_callback_before_insert_plugins'));
        $crud->callback_before_update(array($this,'_callback_before_update_plugins'));
        
        $crud->callback_add_field('position', function () {
            return '<select name="position" class="form-control selectpicker"><option value="header">Header</option><option value="footer">Footer</option></select>';
        });
        $crud->callback_add_field('status', function () {
            return '<select name="status" class="form-control selectpicker"><option value="deactive">deactive</option><option value="active">active</option></select>';
        });
        $crud->unset_jquery();
        //$crud->unset_delete();
        //$crud->unset_jquery_ui();
        $crud->set_rules('name', 'Tên plugins', 'trim|required|min_length[5]|max_length[255]');
        $crud->set_rules('description', 'Plugins text', 'trim|required|min_length[10]|max_length[5000]');
        $crud->set_rules('position', 'Vị trí đặt', 'required');
        $crud->set_rules('status', 'Status', 'trim|required');
        # CALLBACK
		$crud->display_as('id','Identity')
			 ->display_as('name','Tên plugins')
             ->display_as('description','Plugins text')
             ->display_as('plugins','Vị trí đặt')
             ->display_as('create','Ngày tạo')
             ->display_as('last_update','Cập nhật cuối');
             
        $js = '';
        
        $this->_data['js_custom'] = $js;
        
		$output = $crud->render();
                
		$this->_example_output($output);
    }
    
    public function socials()
    {
        $crud = new grocery_CRUD();
        $crud->set_table('socials');
        $crud->columns('id','name','link','icon','status');
        $crud->set_subject("socials link");        
        $crud->required_fields('name','link','icon','status');
        $crud->add_fields('name','link','icon','status');
        $crud->fields('name','link','icon','status');
        $crud->edit_fields('name','link','icon','status');
                               
        //$crud->callback_before_insert(array($this,'_callback_before_insert_socials'));
        //$crud->callback_before_update(array($this,'_callback_before_update_socials'));
        $crud->callback_add_field('icon', function () {
            return $this->config->item('data_list1');
        });
        $crud->callback_add_field('status', function () {
            return '<select name="status" class="form-control selectpicker"><option value="deactive">deactive</option><option value="active">active</option></select>';
        });
        $crud->unset_jquery();
        //$crud->unset_delete();
        //$crud->unset_jquery_ui();                		
        $crud->set_rules('name', 'Tên socials', 'trim|required|min_length[5]|max_length[255]');
        $crud->set_rules('link', 'Đường dẫn', 'trim|required|min_length[10]|max_length[255]|valid_url');
        $crud->set_rules('icon', 'Icon hoặc class', 'required');
        $crud->set_rules('status', 'Status', 'trim|required');
        # CALLBACK
		$crud->display_as('id','Identity')
			 ->display_as('name','Tên file')
             ->display_as('icon','Icon & class style')
             ->display_as('status','Trạng thái');
             
        $js = '';
        
        $this->_data['js_custom'] = $js;
        
		$output = $crud->render();
                
		$this->_example_output($output);
    }
    
    public function contacts()
    {
        $crud = new grocery_CRUD();
        $crud->set_table('contacts');
        $crud->columns('id','name','directory','version','last_update');
        $crud->set_subject("assets file");        
        $crud->required_fields('name','directory');
        $crud->add_fields('name','directory');
        $crud->fields('name','directory','version','last_update');
        $crud->edit_fields('name','directory');
        $crud->field_type('last_update','invisible');
        $crud->field_type('version','invisible');
                               
        $crud->callback_before_insert(array($this,'_callback_before_insert_assets'));
        $crud->callback_before_update(array($this,'_callback_before_update_assets'));
        $crud->unset_jquery();
        //$crud->unset_delete();
        //$crud->unset_jquery_ui();                		
        $crud->set_rules('name', 'Tên file', 'trim|required|min_length[5]|max_length[255]');
        $crud->set_rules('directory', 'Thư mục', 'required|min_length[5]|max_length[255]');
        # CALLBACK
		$crud->display_as('id','Identity')
			 ->display_as('name','Tên file')
             ->display_as('directory','Folder')
             ->display_as('version','Phiên bản')
             ->display_as('last_update','Sửa lần cuối');
                    
        $js = '';
        
        $this->_data['js_custom'] = $js;
        
		$output = $crud->render();
                
		$this->_example_output($output);
    }
    
    public function assets()
    {
        $crud = new grocery_CRUD();
        $crud->set_table('assets');
        $crud->columns('id','name','directory','version','last_update');
        $crud->set_subject("assets file");        
        $crud->required_fields('name','directory');
        $crud->add_fields('name','directory');
        $crud->fields('name','directory','version','last_update');
        $crud->edit_fields('name','directory');
        $crud->field_type('last_update','invisible');
        $crud->field_type('version','invisible');
                               
        $crud->callback_before_insert(array($this,'_callback_before_insert_assets'));
        $crud->callback_before_update(array($this,'_callback_before_update_assets'));
        $crud->callback_column('version',array($this,'_callback_last_update'));
        $crud->unset_jquery();
        //$crud->unset_delete();
        //$crud->unset_jquery_ui();                		
        $crud->set_rules('name', 'Tên file', 'trim|required|min_length[5]|max_length[255]');
        $crud->set_rules('directory', 'Thư mục', 'required|min_length[5]|max_length[255]');
        # CALLBACK
		$crud->display_as('id','Identity')
			 ->display_as('name','Tên file')
             ->display_as('directory','Folder')
             ->display_as('version','Phiên bản')
             ->display_as('last_update','Sửa lần cuối');
                    
        $js = '';
        
        $this->_data['js_custom'] = $js;
        
		$output = $crud->render();
                
		$this->_example_output($output);
    }
    
    public function abouts()
    {        
        $crud = new grocery_CRUD();
        $crud->set_table('abouts');
        $crud->columns('id','title','description','content','create','last_update','status');
        $crud->set_subject("Abouts table");        
        $crud->required_fields('title','description','content','status');
        $crud->add_fields('title','description','content','status','create','last_update');
        $crud->fields('title','description','content','status','create','last_update');
        $crud->edit_fields('title','description','content','status','last_update');
        $crud->field_type('create','invisible');
        $crud->field_type('last_update','invisible');
                               
        $crud->callback_before_insert(array($this,'_callback_before_insert_about'));
        $crud->callback_before_update(array($this,'_callback_before_update_about'));
        $crud->callback_add_field('status', function () {
            return '<select name="status" class="form-control"><option value="deactive">deactive</option><option value="active">active</option></select>';
        });
        //$crud->callback_before_delete(array($this,'callback_about_delete'));        
        $crud->unset_jquery();
        //$crud->unset_delete();
        //$crud->unset_jquery_ui();                		
        $crud->set_rules('title', 'Tên đại diện', 'trim|required|min_length[10]|max_length[200]');
        $crud->set_rules('description', 'Mô tả', 'required|min_length[5]|max_length[255]');
        $crud->set_rules('content', 'Nội dung', 'trim|required');        
        $crud->set_rules('status', 'Trạng thái', 'trim|required|min_length[6]|max_length[45]');        
        # CALLBACK
		$crud->display_as('id','Identity')
			 ->display_as('title','Tiêu đề')
             ->display_as('description','Mô tả')
             ->display_as('content','Nội dung')             
             ->display_as('create','Ngày tạo')
             ->display_as('last_update','Sửa lần cuối')
			 ->display_as('status','Trạng thái');
                    
        $js = '';
        
        $this->_data['js_custom'] = $js;
        
		$output = $crud->render();
                
		$this->_example_output($output);
    }
    
    public function delete_comment($primary_key){
        $flag = false;
        if($this->delete_commenter($primary_key)){
            $flag = true;
        }
        return $flag;
    }
    
    protected function delete_commenter($id = array()){
        $com = new Comment();
        $arr_list = null;
        $type_where = (is_array($id) && count($id)>1) ? 'where_in' : 'where';
        $com->$type_where('parent_id',$id)->get();
        if(count($com->all) > 1){
            $arr_list = array();
            foreach($com->all as $item)
            {
                $arr_list[] = $item->id;                
            }
        }
        elseif(count($com->all) == 1){
            $arr_list = $com->all[0]->id;
        }else{
            $com->refresh_all();
            $com->get_by_id($id);
            $com->delete();
        }       
        if($arr_list != NULL){
            $this->delete_commenter($arr_list);
            $com->delete_all();
        }else{
            return $com;
        }
    }
    
    public function comments()
    {        
        $crud = new grocery_CRUD();
        $crud->set_table('comments');
        $crud->columns('id','name','email','create','ip','user_meta','parent_id','new_id','content');
        $crud->set_subject("comment");
        $crud->fields('title','description','content','status','create','last_update');
        $crud->callback_delete('delete_comment');        
        $crud->unset_jquery();
        $crud->unset_edit();
        $crud->unset_add();
        $crud->callback_column('create',array($this,'_callback_date'));
        $crud->callback_column('content',array($this,'_callback_substr'));
        $crud->callback_column('user_meta',array($this,'_callback_substr'));
                
        # CALLBACK
		$crud->display_as('id','Identity')
			 ->display_as('name','Người gửi')
             ->display_as('email','Email')
             ->display_as('content','nội dung')             
             ->display_as('create','Ngày tạo')
             ->display_as('ip','IP address')
			 ->display_as('user_meta','User agent')
             ->display_as('parent_id','Cha')
             ->display_as('new_id','Thuộc menu');
        $js = '';
        $this->_data['js_custom'] = $js;        
		$output = $crud->render();                
		$this->_example_output($output);
    }
    
    public function _callback_substr($value){
        
        return character_limiter($value,20);
    }
    
    public function _callback_before_insert_about($post_array)
    {
        $post_array['create'] = time();
        $post_array['last_update'] = time();
        return $post_array;
    }
    
    public function _callback_before_update_about($post_array,$pk)
    {
        
        $post_array['last_update'] = time();
        return $post_array;
    }
    
    public function _callback_before_insert_assets($post_array)
    {
        $post_array['version'] = time();
        $post_array['last_update'] = time();
        return $post_array;
    }
    
    public function _callback_before_update_assets($post_array,$pk)
    {
        $post_array['version'] = time();
        $post_array['last_update'] = time();
        return $post_array;
    }
    
    public function _callback_before_insert_images($post_array)
    {
        $string = $post_array['name'];
        //$split = preg_split("/^[\/]+$/",$string);
        if(file_exists($string))
        {
            $exif = filemtime($string);
            $data = getimagesize($string);
            $post_array['width'] = $data[0];
            $post_array['height'] = $data[1];
            $post_array['create']    = $exif;
            $post_array['last_update']    = time();
        }
        return $post_array;
    }
    
    public function _callback_before_update_images($post_array,$pk)
    {
        $string = $post_array['name'];
        if(file_exists($string))
        {
            $exif = filemtime($string);
            $data = getimagesize($string);
            $post_array['width'] = $data[0];
            $post_array['height'] = $data[1];
            $post_array['create']    = $exif;
            $post_array['last_update']    = time();
        }
        return $post_array;
    }
    
    public function _callback_before_insert_plugins($post_array)
    {
        $post_array['create'] = time();
                
        $post_array['last_update'] = time();
                
        return $post_array;       
    }
    
    public function _callback_before_update_plugins($post_array,$pk)
    {
        $post = $this->input->post('description');
        
        if (strcasecmp($post_array['description'],$post) != 0)
        {
            $post_array['last_update'] = time();    
        }
        return $post_array;
    }
    
    public function _callback_last_update($value ,$row)
    {
        $all = new Assets;
        $all->where("id",$row->id)->get();
        $filename = $all->directory.DIRECTORY_SEPARATOR.$all->name;
        if(file_exists($filename))
        {
            return date('d/m/Y H:i:s',filemtime($filename));
        }
        return date('d/m/Y H:i:s',$value);
    }
    
    public function check_keyword($value)
    {
        if( ! preg_match("/([\w]+[\s]*[\,]+)/",$value))
        {
            $this->form_validation->set_message('check_keyword','Invalid keyword type.');
            return false;
        }
        return true;
    }
    
    public function valid_path_images($value)
    {
        if( ! preg_match("/(.*)((\.)(jpg|png|gif))$/",$value))
        {            
            $this->form_validation->set_message('valid_path_images',"Invalid path image");
            
            return false;
        }
        return true;
    }
          
    public function _callback_date($value, $row)
    {
      return date('d/m/Y',$value);
    }
   
    public function _callback_aboutname($value, $row)
    {
        $str = "";
      switch($value){
        case 1 : 
        $str = "<label class='text-success'>".$row->about_name."</label>";
        break;
        case 2 : 
        $str = "<label class='text-primary'>".$row->about_name."</label>";
        break;
        case 3 : 
        $str = "<label class='text-info'>".$row->about_name."</label>";
        break;
        case 4 : 
        $str = "<label class='text-warning'>".$row->about_name."</label>";
        break;
        case 5 : 
        $str = "<label class='text-danger'>".$row->about_name."</label>";
        break;
      }
      return $str;
    }
    
    private function url_encode($string){
        return urlencode(utf8_encode($string));
    }
    
    private function url_decode($string){
        return utf8_decode(urldecode($string));
    }
    
    private function is_image($type)
	{
		$png_mimes  = array('image/x-png');
		$jpeg_mimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');

		if (in_array($type, $png_mimes))
		{
			$type = 'image/png';
		}

		if (in_array($type, $jpeg_mimes))
		{
			$type = 'image/jpeg';
		}

		$img_mimes = array(
							'image/gif',
							'image/jpeg',
							'image/png',
						);

		return (in_array($type, $img_mimes, TRUE)) ? TRUE : FALSE;
	}
    
    private function add_maps($lat,$long){
        
        return "<script>
        var map;
        function initialize() {
          var mapOptions = {
            zoom: 8,
            center: new google.maps.LatLng({$lat}, {$long})
          };
          map = new google.maps.Map(document.getElementById('map-canvas'),
              mapOptions);
        }
        
        google.maps.event.addDomListener(window, 'load', initialize);
        
            </script>";
    }
}
?>