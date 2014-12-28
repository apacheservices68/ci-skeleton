<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Upload extends MY_Controller {
    
    public $_data = array();
    
    public $configImage = array();
    
    public $image ;
    
    public $ratio = true;
    
    public $i  = null;
    
    public $mimes = array();
    
    public $download ;
    
    const content_temp_path = 'assets/uploads/contents/images/temp/';
    
    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('administrator/ion_auth'));
        if ( ! $this->ion_auth->logged_in())
		{
			redirect('auth/login');
		}
        $this->load->library(array('filter','upload','image_lib','image_moo','curl_download'));
        
        $this->download = new Curl_download();
        
        $this->load->helper('thumbnail');
    }
    
    public function get_image()
    {
        return ($this->image != '') ? $this->image : '' && log_message('error',"Not image !");
    }
    
    public function set_null()
    {
        return $this->image = '';
    }
    
    public function reset_all(){
        return $this->configImage = null && $this->_data = null && $this->image = '';
    }
    
    /*
    public function handle_content($vars = array())
    {
        $temp_folder = $this->_data['full_path'] =  self::content_temp_path.$vars['filename'];
        
        if(file_exists($temp_folder))
        {       
            $this->_data['path'] = $vars['path'];#'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';
            $this->_data['slug'] = $vars['slug'];
            $this->_data['maxWidth']  = settings::width_content;
            $this->_data['maxHeight'] = settings::height_content;
            $this->_data['ext']  = end(explode(".",$vars['filename']));
            $this->i             = $vars['index'];
            $path_folder         = '';
                    
            $this->check_dir($this->_data['path']);
            
            if($this->i != null)
            {
                $path_folder = $this->_data['path'].$this->_data['slug']."-".$this->_data['maxWidth']."-".$this->_data['maxHeight']."-".$this->i.$this->_data['ext'];
            }    
            log_message('error',implode(",",$vars));
            if($this->download->download($temp_folder,$path_folder))
            {
                log_message('error','Downloaded!');
                $this->image = $path_folder;
                $this->handle_upload();
            }
        }
        else
        {
            log_message('error','Can not upload curl');
            return ;
        }
    }
   */
    # allowed types - max size - name - path - content_type
    public function handler($vars = array())
    {
        $this->_data['pathImage']       = "assets/uploads/temp/"; 
        $this->_data['imageTemp']       =   $moo  = "";
        $this->_data['slug']            = $vars['slug'];
        # Initialize Config Image
		$config['upload_path']          = $this->_data['pathImage'];
		$config['allowed_types']        = $vars['allowed_types'];#'gif|jpg|png';
		$config['max_size']	            = $vars['max_size'];#2000;#KB    			
		$this->upload->initialize($config);     
        
        # End Initialize Config for Image
        
        # Hinh hien thi
        
        # Kiem tra rang do chinh la hinh anh va lay ten hinh anh 
        
        if($this->upload->do_upload($vars['name']))
		{
            $uploadData = $this->upload->data();
            
            if($uploadData['is_image'] == TRUE)
            {
				$this->_data['imageTemp'] = $uploadData['file_name'];
                
                $this->_data['ext']       = $uploadData['file_ext'];
			}
			elseif(file_exists($this->_data['pathImage'].$uploadData['file_name']))
			{
				@unlink($this->_data['pathImage'].$uploadData['file_name']);
			}
            unset($uploadData);
		}
        if($this->_data['imageTemp'] != '')
        {
            if(file_exists($this->_data['pathImage'].$this->_data['imageTemp']))
            {       
                $this->_data['path'] = $vars['path'];#'assets/uploads/contents/images/'.date("Y").'/'.date("m").'/';
                
                $this->check_dir($this->_data['path']);
                # Phan nay kiem tra neu hinh do co phai la noi dung hay khong neu hinh do la noi dung thi khong can
                # Tao thumb o day .Con neu hinh do la noi dung thi tao thumb theo kich thuoc duoc truyen vao .
                if($vars['content_type'] === true)
                {
                    $this->_data['maxWidth'] = settings::width_content;
                    $this->_data['maxHeight'] = settings::height_content;
                    #$this->i = $vars['index'];
                    #$fullpath = $this->_data['path'].$this->_data['slug']."-".$this->_data['maxWidth']."-".$this->_data['maxHeight']."-".$this->i.$this->_data['ext'];
                    
                    #if($this->download(self::content_temp_path.$vars['filename'],$fullpath))
                    #{
                    #    log_message('error','Downloaded!');
                    #}
                    
                    #$this->image = $this->_data['path'].$this->_data['slug']."-".$this->_data['maxWidth']."-".$this->_data['maxHeight'].$this->_data['ext'];
                    #$this->handle_upload();
                }
                else
                {
                    for($i = 1;$i <= 2; $i++)
                    {
                        if($vars['type'] == 'categories')
                        {
                            switch($i)
                            {
                                case 1:
                                $this->_data['maxWidth'] = settings::width_maximum_category;
                                $this->_data['maxHeight'] = settings::height_maximum_category;
                                break;
                                case 2:
                                $this->_data['maxWidth'] = settings::width_minimum_category;
                                $this->_data['maxHeight'] = settings::height_minimum_category;
                                break;
                            }
                        }
                        # Chừa chổ cho new nữa nha ba !!!
                        elseif($vars['type'] == 'news')
                        {
                            switch($i)
                            {
                                case 1:
                                $this->_data['maxWidth'] = settings::width_new;
                                $this->_data['maxHeight'] = settings::height_new;
                                break;
                                case 2:
                                $this->_data['maxWidth'] = settings::width_thumb_new;
                                $this->_data['maxHeight'] = settings::height_thumb_new;
                                break;
                            }
                            if($i == 2)
                            {
                                $this->ratio = false;
                            }
                        }
                        else
                        {
                            switch($i)
                            {
                                case 1:
                                $this->_data['maxWidth'] = settings::width_product;
                                $this->_data['maxHeight'] = settings::height_product;
                                break;
                                case 2:
                                $this->_data['maxWidth'] = settings::width_thumb_product;
                                $this->_data['maxHeight'] = settings::height_thumb_product;
                                break;
                            }
                            if($i == 2)
                            {
                                $this->ratio = false;
                            }
                        }
                        if($i == 2)
                        {
                            $this->image = $this->_data['path'].$this->_data['slug']."-".$this->_data['maxWidth']."-".$this->_data['maxHeight'].$this->_data['ext'];
                        }
                        # maxwidth maxheight ext pathimage imagetemp path slug
                        $this->handle_upload();
                    }
                }
                //@unlink($this->_data['pathImage'].$this->_data['imageTemp']);                    
            }
        }
    }
    
    public function curl_upload_link($vars = array()){
        if(is_null($vars['upload_content']) || !is_array($vars['upload_content']))
        {
            log_message('error',"false!");
            return ;
        }
        $dem = 0; $return = array();        
        $this->_data['pathImage'] = $vars['pathImage'];
        chmod($this->_data['pathImage'], 0755);
        $this->_data['path']      = $vars['path'];
        $this->_data['slug']      = $slug = $vars['slug'];
        $ten = '';
        # Thuc hien tao tac upload hinh anh vao thu muc tam roi lay ten hinh do cho vao array de tiep tuc xu ly
        foreach($vars['upload_content'] as $k=>$v)
        {
            $name = end(explode("/",$v));
            $this->_data['ext'] = ".".end(explode(".",$name));
            if($this->download->download($v,$this->_data['pathImage'].$name)){
                $dem++;
                $return[$k] = $this->_data['pathImage'].$name;
                $ten = end(explode("/",$v));
            }
        }
        if($dem == count($vars['upload_content'])){
            foreach($return as $item){
                for($i  =1;$i<=2 ; $i++){
                    switch($i)
                    {
                        case 1:
                        $this->_data['maxWidth']  = settings::width_new;
                        $this->_data['maxHeight'] = settings::height_new;
                        break;
                        case 2:
                        $this->_data['maxWidth']  = settings::width_thumb_new;
                        $this->_data['maxHeight'] = settings::height_thumb_new;
                        break;
                    }
                    $this->_data['imageTemp']     = $ten;
                    $this->handle_upload();
                    $filename = $this->_data['path'].$this->_data['slug']."-".$this->_data['maxWidth']."-".$this->_data['maxHeight'].$this->_data['ext'];
                    if($i == 2)
                    {
                        $this->image = $filename;
                    }
                }
            }
            return $return;
        }
    }
    
    public function curl_upload($vars = array()){
        if(is_null($vars['upload_content']) || !is_array($vars['upload_content'])){
            return ;
        }
        $dem =  0 ;$resize = true; $return  = array();
        foreach($vars['upload_content'] as $k=>$v)
        {
            $slug = $vars['slug'];
            $ext  = end(explode(".",end(explode("/",$v))));
            $name = $slug . "-" . time() . "-" . $k . "." . $ext;
            if($this->download->download($v,$vars['path'].$name)){
                $dem+=1;
                $return[$k] = $vars['path'].$name;
            }
        }
        if($vars['path'] == 'assets/uploads/temp/'){
            $resize = false;
        }
        if($dem == count($vars['upload_content'])){
            foreach($return as $item){
                $post_array  =array(
                                'path'=>$vars['path'],
                                'filename' => end(explode("/",$item)),
                                'type'=>'image',                        
                                'fontsize'=>20,
                                'color'=>'#ffffff',
                                'fontpath'=>BASEPATH.'fonts/texb.ttf',
                                'is_resize'=>$resize,
                                'width'=>600,
                                'height'=>400,
                                'transparent'=>50
                );
                call_user_func(array($this,'callback_upload'),$post_array);
            }
            return $return;
        }
    }
    
    public function handle_upload()
    {
        $this->_data['ext']                 = ($this->_data['ext'] != null ) ? $this->_data['ext'] : '.jpg';
        
        # Giai thich cho nay 
        # Neu ton tai i tuc la se resize theo content 
        # Neu khong ton tai i tuc la resize binh thuong theo categories and products
        
        $_sizeImage                               = size_thumbnail($this->_data['pathImage'].$this->_data['imageTemp'],$this->_data['maxWidth'],$this->_data['maxHeight']);
        $this->configImage['source_image']        = $this->_data['pathImage'].$this->_data['imageTemp'];
        $this->configImage['new_image']           = $this->_data['path'].$this->_data['slug']."-".$this->_data['maxWidth']."-".$this->_data['maxHeight'].$this->_data['ext'];
        $moo                                      = $this->_data['slug']."-".$this->_data['maxWidth']."-".$this->_data['maxHeight'].$this->_data['ext'];
        $this->configImage['maintain_ratio']      = $this->ratio;
        $this->configImage['width']               = $_sizeImage['width'];
        $this->configImage['height']              = $_sizeImage['height'];
                                
        $this->image_lib->initialize($this->configImage);
        $this->image_lib->resize();
        $this->image_lib->clear();
        
        # Phan dong dau anh
        $post_array  =array(
                        'path'=>$this->_data['path'],
                        'filename' => $moo,
                        'type'=>'image',                        
                        'fontsize'=>20,
                        'color'=>'#ffffff',
                        'fontpath'=>BASEPATH.'fonts/texb.ttf',
                        'is_resize'=>FALSE,
                        'width'=>$this->_data['maxWidth'],
                        'height'=>$this->_data['maxHeight'],
                        'transparent'=>50
        );
        call_user_func(array($this,'callback_upload'),$post_array);
    }
    
    public function callback_upload($post_data = array())
    {
        $action = $resize = $process = null;
        
        if(is_null($post_data))
        {
            log_message('error','The method callback image should be a post data as an array and not null');
            
            return false;
        }
        $path = $post_data['path'].$post_data['filename'];
        try{
            switch($post_data['type'])
            {
                case 'image':
                    $action = 'load_watermark';
                    $dir = settings::watermark_image;
                break;
                case 'text':
                    $action = "make_watermark_text";
                    $dir = settings::watermark_text.",". $post_data['fontpath'].",".$post_data['fontsize'].",". $post_data['color'].",". 30;
                break;
            }
            if($post_data['is_resize'] === true)
            {
                $resize  = "resize";   
            }
            if($action !== null)
            {
                $this->image_moo->set_watermark_transparency($post_data['transparent']);
                if($resize !== null)
                {
                    return $this->image_moo->load($path)->$action($dir)->$resize($post_data['width'],$post_data['height'])->watermark(3,3)->save($path,true);    
                }                                            
                return $this->image_moo->load($path)->$action($dir)->watermark(3,5)->save($path,true);
            }
        }
        catch(Exception $e){
            if($this->image_moo->error)
            {
                log_message('error',$this->image_moo->display_errors("<p class='label label-danger'>","</p>"));
                return $this->image_moo->display_errors("<p class='label label-danger'>","</p>");
            }
        }        
        #$this->image_moo->set_watermark_transparency(60);
        
        #return $this->image_moo->load($file_upload)->make_watermark_text('vinh banh', BASEPATH.'fonts/texb.ttf', $size=56, $colour="#ffffff", $angle=30)->watermark(2)->save($file_upload,true);
    }
    
    public function check_dir($dir)
    {
         
        if(!is_dir($dir))
        {
            if (!mkdir($dir, 0777, true)) {
                log_message('error','Could not create folder '.$dir);
                return show_error('Could not create folder','500',"An error was encountered")  ;                          
            } 
            @write_file($dir.'index.html', '<p>Directory access is forbidden.</p>');
            return chmod($dir, 0755);
        }
        return ;
    }
    
    public function remove_non_image($path = null)
    {
        if(!is_dir($path))
        {
            return false;
        }
        $this->check_dir($path);
        //$_ext = array('jpg','png','gif');
        $fp = opendir($path);
        $dem = 0 ;
        $flag = true;
        $_mimes = array('gif'=>'image/gif','jpg'=>'image/jpeg','png'=>'image/png');                        
        while(($data = readdir($fp)) !== FALSE){
            if($data == 'index.html' || $data == 'index.htm')
            {
                continue;
            }
            else
            if(filesize($path.$data)/1024<=1){
                $dem +=1;
            }            
            $ext = strtolower(end(explode(".",$data)));
            if(!array_key_exists(strtolower($ext),$_mimes)){
                $dem+=1;
            }
            if($dem >=1){
                if(file_exists($path.$data)){
                    if(!unlink($path.$data)){
                        $flag = false;
                    }
                }
            }
            $dem = 0;
        }
        closedir($fp);
        return $flag;
    }
}
?>