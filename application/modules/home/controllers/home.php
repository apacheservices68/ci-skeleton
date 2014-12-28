<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Home extends MY_Controller { 
    
    public $_data = array();    
    public $obj ;    
    public $add ;    
    public $setting ;     
    public $data ;    
    public $lang_index ;    
    public $lang_content;    
    public $category;    
    public $lang_id;    
    private $list_cat_title = array();    
    private $list_cat_slug  = array();    
    private $list_cat_image = array();    
    private $list_cat_parent = array();    
    private $list_cat_id = array();    
    private $comment_data;    
    private $comment_content;    
    private $index_meta_content ;
    public function __construct(){
        parent::__construct();
        # Check deny
        $this->check_deny();
        $this->load->library(array('form_validation'));
        $this->load->helper('text');
        $this->load->model(array('abouts','assets','images','counters','plugins','language','product','category','product_meta','category_meta','newz','new_meta','setting','comment'));        
        if(!$this->session->userdata('cf24_session_lang_name')){
            $this->session->set_userdata('cf24_session_lang_name','vietnam');
        }
        $this->lang->load('public/home_common',$this->session->userdata('cf24_session_lang_name'));        
        $this->config->load('dropdown');
        $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');        
        $this->category = new stdClass();
        # Khai bao de insert vao trong bang ket giua hai bang chinh
        $this->obj = new StdClass();
        $this->obj->prefix = '';
        $this->obj->table = '';
        # Kiem fra session last page
        if( !$this->input->is_ajax_request() && ! $this->input->is_cli_request()){            
            $this->session->set_userdata('home_last_page',current_url());
        }        
        # Fetch setting object for all site
        $this->handle_setting();       
        # Language
        $this->handle_language(); 
        # Handle assets
        $this->handle_asset();
        # Category
        $this->handle_category();      
        # Images
        $this->_data['logo'] = 'assets/template/img/logo-cafe.png';      
        # Footer
        $this->handle_footer();
        # Get menu
        $this->handle_menu(0); #
        $this->get_menu(0); #
        $this->handle_menu(1) ;#
        $this->_data['menus'] = $this->add; #
        # Lay gia tri cho bien var cua javacript base
        $this->_data['url_suffix'] = $this->config->item('url_suffix');        
        # Tao ma hash        
        if(!$this->session->userdata('cf24_secure_hash')){
            $this->handle_hash();    
        }
        # Get counter
        $this->handle_counter();
        # handle log ip
        
        if(!$this->input->is_ajax_request()){
            $this->rm_log();
            if($this->log_ip() == false){
                log_message("error","Failed to write log file in counter folder");
            }
        }
        # Analytics
        $this->handle_analytics();
    }
    
    public function handle_analytics(){
        $this->_data['analytic_js'] = '';
        if(ENVIRONMENT == 'production'){
            $this->_data['analytic_js'] = $this->analytics();
        }
        return ;
    }
    
    private function check_deny(){
        $this->load->library('blocking');
        return $this->blocking->blocks();
    }
    
    private function handle_hash(){
        if($this->check_secure_hash() === FALSE){
            if($this->generate_secure_hash() === null){
                log_message('error','Can\'t create hash code .Please check function .');
            }
        }
        return ;
    }

    private function handle_asset(){
        $a = new Assets();
        $a->order_by('last_update','ASC')->get();
        $this->_data['cf24_css'] = '';
        $this->_data['cf24_js'] = '';
        
        foreach($a->all as $item){
            if($item->name == 'jquery.min.js' || $item->name == 'jquery.tweet.min.js'){
                continue;
            }
            if(preg_match("/^[\w\.\-\_]+(\.css)$/",$item->name) && $item->name != 'addons.css'){
                $this->_data['cf24_css'] .= "<link rel=\"stylesheet\" href=\"".base_url($item->directory.$item->name)."?v=".$item->last_update."\" type=\"text/css\" media=\"screen\" />\n";
            }
            if(preg_match("/^[\w\.\-\_]+(\.js)$/",$item->name)){
                $this->_data['cf24_js'] .= "<script src=\"".base_url($item->directory.$item->name)."\" type=\"text/javascript\"></script>\n";
            }
        }
        unset($a);
        if($this->setting->site_addons_css == 1){
            $this->_data['cf24_css'] .= "<link rel=\"stylesheet\" href=\"".base_url('assets/template/css/addons.css')."\" type=\"text/css\" media=\"screen\" />\n";
        }
        return ;
    }    
    
    private function get_index_meta_content(){
        $title = $this->setting->site_name;
        $description = $this->setting->site_description;
        $keyword = $this->setting->site_keyword;
        $author = $this->setting->site_google_plus;
        $link = site_url();
        $image = base_url('assets/template/img/_blog/blog-1.jpg');
        $return = null;
        $return .= "<meta name=\"description\" content=\"".$description."\"/>\n";
        $return .= "<meta name=\"keyword\" content=\"".$keyword."\"/>\n";
        $return .= "<meta name=\"author\" content=\"".$author."\"/>\n";
        $return .= "<meta name=\"publisher\" content=\"".$author."\"/>\n";
        $return .= "<meta itemprop=\"title\" content=\"{$title}\" />\n";
        $return .= "<meta itemprop=\"description\" content=\"{$description}\" />\n";
        $return .= "<meta itemprop=\"image\" content=\"{$image}\" />\n";        
        $return .= "<meta property=\"og:title\" content=\"".$title."\"/>\n";
        $return .= "<meta property=\"og:description\" content=\"".$description."\"/>\n"; 
        $return .= "<meta property=\"og:image\" content=\"".$image."\"/>\n";
        $return .= "<meta property=\"og:url\" content=\"".$link."\"/>\n";
        $return .= "<meta name=\"twitter:card\" content=\"".$keyword."\"/>\n";
        $return .= "<meta name=\"twitter:title\" content=\"".$title."\"/>\n";
        $return .= "<meta name=\"twitter:description\" content=\"".$description."\"/>\n";
        $return .= "<meta name=\"twitter:image:src\" content=\"".$image."\"/>\n";
        return $return ;
    }
       
    private function gen_meta($vars = array()){
        $is_data = false; $return = null;
        $type = $vars['type'];
        $data = new stdClass();
        $return .= "<html itemscope itemtype=\"http://schema.org/Blog\">\n";
        if(isset($vars['is_static']) && $vars['is_static'] === true){
            $return .= $this->get_index_meta_content();
            $is_data = true;
        }else{
            $naming = ucfirst($type."_meta");
            $menu_id = $vars['id'];            
            $data = new $naming();
            $ext_id = "";
            if($vars['type'] == 'product'){
                $ext_id = "product_id";
            }elseif($vars['type'] == 'new'){
                $ext_id = "new_id";
            }else{
                $ext_id = "category_id";
            }
            $data->where($ext_id,$menu_id)->get();
            if(count($data->all) > 0){
                $is_data = true;
            }   
            foreach($data->all as $item){
                $title = strtolower($item->title);
                $description = strtolower($item->description);#preg_replace("/\<.*\>/",'',strtolower($item->description));
                $status = $item->status;
                if(preg_match("/(og\:url)|(canonical)/",$title)){
                    $content = ($vars['link']);
                }elseif((preg_match("/^(assets\/).*(\.[jpg|png|gif]{3})$/",$description))){                
                    $content = base_url($description);                
                }elseif(preg_match("/title/",$title)){
                    $content = $this->setting->site_prefix_title_0." ".$description;
                }elseif(preg_match("/(description)/",$title)){
                    $content = preg_replace("/\<p\>|\<\/p\>/",'',$description);
                    $content = html_entity_decode($content);
                }else{
                    $content = ($description);
                }
                if($status == 'twitter' || $status == 'seo'){
                    $types = 'name';
                } elseif($status == 'google'){
                    $types = 'itemprop';
                } elseif($status == 'facebook'){
                    $types = 'property';
                } else{
                    $types = 'name';
                }          
                if($title == 'canonical'){
                    $return .= "<link rel=\"".$title."\" href=\"".$content."\" />\n";
                }else{
                    $return .= "<meta {$types}=\"".$title."\" content=\"".$content."\" />\n";    
                }
            } 
        }
        unset($data);
        $return .= "<meta name=\"author\" content=\"".$this->setting->site_google_plus."\" />\n";
        $return .= "<meta name=\"publisher\" content=\"".$this->setting->site_google_plus."\" />\n";
        $return .= "<link rel=\"profile\" href=\"http://gmpg.org/xfn/11\" />\n";
        $return .= "<link rel=\"shortcut icon\" href=\"".base_url($this->setting->site_favicon_link)."\" type=\"image/x-icon\"  />\n";
        $return .= "<link rel=\"icon\" href=\"".base_url($this->setting->site_favicon_link)."\" type=\"image/x-icon\" />\n";
        $return .= "<meta property=\"og:type\" content=\"".$this->setting->site_type."\" />\n";
        $return .= "<meta property=\"fb:app_id\" content=\"".$this->setting->site_facebook_appid."\" />\n";
        $return .= "<meta property=\"og:site_name\" content=\"".$this->setting->site_name_facebook."\" />\n";
        $return .= "<meta name=\"twitter:site\" content=\"@".$this->setting->site_twitter_author."\" />\n";
        $return .= "<meta name=\"twitter:creator\" content=\"@".$this->setting->site_twitter_author."\" />\n";
        return ($is_data) ? $return : '';
    }
    
    public function handle_category()
    {        
        $category = new Category();
        #$this->category = new stdClass();
        $data = $category->get()->all;
        $i = 0;
        foreach($data as $item){
            $this->category->all[$i] = new stdClass();
            $this->category->all[$i]->id = $item->id;
            $this->list_cat_id[$item->id]  = $item->id;
            $this->category->all[$i]->parent_id = $item->parent_id;
            $this->list_cat_parent[$item->id] = $item->parent_id;
            $this->category->all[$i]->img_avatar = $item->img_avatar;
            $this->category->all[$i]->type = $item->type;
            $this->list_cat_image[$item->id] = $item->img_avatar;
            
            foreach($item->language->include_join_fields()->get_by_id($this->lang_id) as $v){
                $this->category->all[$i]->join_title       = $v->join_title;
                $this->list_cat_title[$item->id]           = $v->join_title;
                $this->list_cat_slug[$item->id]            = $v->join_slug_title;  
                $this->category->all[$i]->join_description = $v->join_description;
                $this->category->all[$i]->join_slug        = strtolower($v->join_slug_title);
                $this->category->all[$i]->join_keyword     = $v->join_keyword;
            }
            #$category->get_by_id($item->id);            
            $i+=1;
        }
        #unset($category);
        return ;
    }
    
    public function handle_language(){
        $lang = new Language();
        
        $this->_data['lang_content'] = '';
        
        $this->lang_content = $lang->get()->all;
        
        unset($lang);
        
        foreach($this->lang_content as $item)
        {
            $name = strtolower(substr($item->name,0,2));
            
            $this->_data['lang_content'] .= '<a href='.site_url('lang/'.$name).' id='.$item->name.' class="lang">'.$name.'</a>';
        }
        unset($lang);
        if(!$this->session->userdata('cf24_session_lang'))
        {
            $this->session->set_userdata('cf24_session_lang',0);
            $this->session->set_userdata('cf24_session_language_id',3);
        }        
        $this->lang_index = $this->session->userdata('cf24_session_lang');
        $this->lang_id    = $this->session->userdata('cf24_session_language_id');
        return ;
    }
    
    private function check_secure_hash(){
        $return  = null; $flag = false; $offset = 0;
        if( ! $this->session->userdata('cf24_secure_hash')){
            return $flag;
        }
        $this->load->library('user_agent');
        $ip      = $this->input->ip_address();
        $agent   = $this->agent->agent_string();
        $cookie  = $this->input->cookie('cof');
        $server  = $_SERVER['SERVER_NAME'];
        $arr     = array($server,$cookie,$agent,$ip);
        
        foreach($arr as $key=>$val){
            if($key == 1){
                $hash = $val;
            }else{
                $hash = md5($val);    
            }
            $return .= substr($hash,$offset,8);
            $offset += 8;
        }
        if(strcasecmp($return,$this->session->userdata('cf24_secure_hash')) == 0){
            $flag = true;
        }
        return $flag;
    }
    
    private function generate_secure_hash(){
        $this->load->library('user_agent');
        $return  = null; $offset = 0;
        $ip      = $this->input->ip_address();
        $agent   = $this->agent->agent_string();
        $cookie  = $this->input->cookie('cof');
        $server  = $_SERVER['SERVER_NAME'];
        $arr     = array($server,$cookie,$agent,$ip);
        
        foreach($arr as $key=>$val){
            if($key == 1){
                $hash = $val;
            }else{
                $hash = md5($val);    
            }
            $return .= substr($hash,$offset,8);
            $offset +=8;
        }
        if(strlen($return) === 32){
            $this->session->set_userdata('cf24_secure_hash',$return);
        }
        return ($this->session->userdata('cf24_secure_hash'))? $this->session->userdata('cf24_secure_hash') : null;
    }
    
    private function top_3_news(){
        $data = new Newz();
        $this->_data['top_3_news'] = '';
        foreach($data->where('category_id',$this->setting->site_top_3_news)->order_by('viewer','DESC')->get(3,0)->all as $item){
            foreach($item->language->include_join_fields()->get_by_id($this->lang_id) as $v){
                $title = $v->join_title;
                $slug  = $v->join_slug_title;    
            }
            $link  = site_url($this->setting->site_prefix_news.$slug);
            $this->_data['top_3_news'] .= '<li><div class="img-left-post">
            <a href="'.$link.'" title="'.$title.'"><img width="60" height="60" src="'.base_url($item->image).'" alt="'.$title.'" title="'.$title.'"></a>
            </div><div class="tab-text-article">
            <h2><a href="'.$link.'" title="'.$title.'">'.$title.'</a></h2>
            <p>'.date('d/m/Y',$item->create).'</p>
            </div><div class="column-clear"></div>
            </li>';
        }
        unset($data);
        return ;
    }
    
    private function get_js(){
        return '<div id="fb-root"></div><script>(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/vi_VN/sdk.js#xfbml=1&appId=305598666257353&version=v2.0";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, \'script\', \'facebook-jssdk\'));</script>';
    }
    
    public function get_comment_ajax(){
        # Kiem tra dau vao 
        if( $this->check_secure_hash() === FALSE && $this->input->is_cli_request() && !$this->input->is_ajax_request()){
            return $this->handle_404();
        }
        $name   = $this->input->post('uri_string');
        $offset = $this->input->post('offset');
        $id     = $this->input->post('id');
        $start  = 0; $return  = null; $is_page = false; $has = 0;
        # Code
        $comment = new Comment();
        $c      = $comment->where('new_id',$id)->count();
        if($c > $offset){
            $off    = $this->setting->site_default_page_cm+$offset;
            if($c > $off){
                $has = $c - $off;
            }
            $is_page = true;
        }
        
        $comment->where('new_id',$id)->get($off,0);
        $count  = count($comment->all);
        if($count !== 0){
            $this->comment_data = $comment->all;
            $return .= '<ol class="commentlist" id="top-comment">';
            $this->recursive_comment(0,0,1);        
            $return .= $this->comment_content;
            if($is_page && $has != 0){
                $return .= '<p class="text-center"><button onclick="cm_load_more(\''.$off.'\',\''.$id.'\')" class="btn btn-default btn-lg"><b>'.lang('cf24_lang_cm_more').$has.'</b><i class="fa fa-angle-double-down"></i></button></p>';
            }
            $return .= '</ol>';
        }
        #
        unset($comment);
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode(array('join'=>($return))));
    }
 
    private function get_comment($id){
        $this->_data['total_cmt'] = $has = 0; $get = $this->setting->site_default_page_cm; $is_page = false; $return = '';  
        $comment = new Comment();
        $count = $comment->where('new_id',$id)->count();
        if($count === 0){
            return $return;
        }
        $comment->refresh_all();        
        if($count > $get){
            $is_page = true;
            $has = $count - $get;
        }
        $comment->where('new_id',$id)->get($get,0);
        $this->_data['total_cmt'] = $count;
        $this->comment_data = $comment->all;
        $return .= '<ol class="commentlist" id="top-comment">';
        $this->recursive_comment(0,0,1);        
        $return .= $this->comment_content;
        if($is_page){
            $return .= '<p class="text-center"><button onclick="cm_load_more(\''.$get.'\',\''.$id.'\')" class="btn btn-default btn-lg"><b>'.lang('cf24_lang_cm_more').$has.'</b><i class="fa fa-angle-double-down"></i></button></p>';
        }
        $return .= '</ol>';
        return $return;
    }
    
    private function recursive_comment($parent = 0,$offset = 0,$pos = 1,$stop = 0){
        foreach($this->comment_data as $item){
            if($parent == $item->parent_id){
                # Check admin 
                $addon_div_class = null; $name = $item->name; $image = base_url('assets/template/img/gravatar.png');
                $extract = unserialize($item->location);
                $addon_div_class = ( isset($extract['admin']) && $extract['admin'] === TRUE) ? 'comment_admin' : ''; 
                if($addon_div_class != ''){
                    $name = 'ADMIN';
                    $image = base_url('assets/template/img/admin_avatar.png');
                }
                $class = ''; $id = 'li-comment-'; $div_id = 'comment-'; $cl = $reply = ''; 
                $id    .= $item->id; $div_id .= $item->id;
                $cl         = ($offset%2 != 0) ? 'odd' : 'even';
                $class .= 'comment '.$cl.' ';
                if($parent == 0){
                    $class .= ' thread-'.$cl.' ';
                }else{
                    $pos   += 1;                    
                    $class .= ' byuser comment-author-wpdemadmin bypostauthor ';
                }   
                if($stop < 3){
                    $reply = '<div class="reply">
                                <a class="comment-reply-link" href="javascript:;" onclick="rep_cm(\''.$item->id.'\')">'.lang('cf24_lang_reply').' »</a>
                            </div><!-- /reply -->';
                }
                $class .= 'depth-'.$pos;             
                $this->comment_content .= '<li class="'.$class.'" id="'.$id.'">';
                $this->comment_content .= '<div id="'.$div_id.'" class="comment-body '.$addon_div_class.' clearfix">
                <div class="comment-details">
                    <div class="comment-avatar">
                        <img alt="" src="'.$image.'" class="avatar avatar-45 photo" height="45" width="45">
                     </div><!-- /comment-avatar -->
                    <section class="comment-author vcard">
                		<cite class="author"><a href="javascript:;" rel="external nofollow" class="url">'.$name.'</a></cite>						
                        <span class="comment-date"> - '.date('d/m/Y - H:i:s',$item->create).'</span>
                    </section><!-- /comment-meta -->
                    <section class="comment-content">
                        <div class="comment-text">
                            <p>'.$item->content.'</p>
                        </div><!-- /comment-text -->
                        '.$reply.'
                    </section><!-- /comment-content -->
                </div><!-- /comment-details -->
                </div>';    
                $this->comment_content .= '<ul class="children">';
                $this->comment_content .= $this->recursive_comment($item->id,$offset+1,$pos,$stop+1);
                $this->comment_content .= '</ul>';
                $this->comment_content .= '</li>';
            }
            $pos = 1;    
        }
    }
    
    private function recursive_delete_comment($id = array()){
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
        }        
        if($arr_list != NULL){
            $this->recursive_delete_comment($arr_list);
            $com->delete_all();
        }else{
            unset($com);
            return ;
        }
    }
    
    public function rm_log(){
        $path = APPPATH."counters/";
        $flag = true;
        if(!is_dir($path)){
            log_message('error','Not find folder counter');
            return false;
        }
        $dir = opendir($path);
        $timestamp = time() - 7200;
        while(($file = readdir($dir))!== FALSE){
            if(preg_match("/ip\-.*/",$file)){
                $temp = explode("-",substr($file,3,-4));
                if(is_file($path.$file)){
                    if(count($temp) == 6){
                        $time = mktime($temp[0],$temp[1],$temp[2],$temp[3],$temp[4],$temp[5]);
                    }
                    if($time < $timestamp || filesize($path.$file)/1024 >= 2000){
                        if(!unlink($path.$file)){
                            $flag = false;
                            log_message('error',"Cant remove file ".$file." at ".__METHOD__);
                        }
                    }
                }
            }
            #log_message("error",$file);
        }
        closedir($dir);
        return $flag;
    }
    
    private function log_ip(){
        $path = APPPATH."counters/";
        $file = null;
        $flag = false;        
        if(!is_dir($path)){
            log_message('error','Not find folder counter');
            return $flag;
        }
        # Check
        $dir = opendir($path);
        while(($line = readdir($dir))!== FALSE){
            if(preg_match("/ip\-.*/",$line)){
                $temp = substr($line,10,-4);
                if(strcasecmp($temp,date('m-d-Y',time()) === 0)){
                    $file = $line;                    
                }
            }
        } 
        closedir($dir);
        if($file === NULL){
            $file = 'ip-'.date('H-i-s-m-d-Y',time()).'.log';
            if(!is_file($path.$file)){
                $temp = "";
                $fp = fopen($path.$file,"wb");
                fwrite($fp,$temp);
                fclose($fp);            
            }
        }
        $this->load->helper('date');
        $this->load->library('user_agent');
        $ip = $this->input->ip_address();
        $agent = substr($this->agent->agent_string(),0,64);
        $timestamp = time();
        $format = 'DATE_RFC822';        
        $times = standard_date($format, $timestamp);
        $visited_page = current_url();
        $content = file_get_contents($path.$file);
        if($content != ''){
            $content .= "\n";
        }
        $content .= $ip." - ".$times." - ".$agent." - ".$visited_page;
        file_put_contents($path.$file,$content);
        if($content != '' && $content != FALSE){
            $flag = true;
        }
        return $flag;
    }
    
    public function stop_spam_check($post_id = 224){
        $flag = false;
        if($post_id === 0){
            return $flag;
        }
        $m = new Comment();
        $this->load->library('user_agent');
        $time = 86400;
        $cur  = time()-$time;
        if($m->where('new_id',$post_id)->where_between('create',$cur,time())->where('user_meta',$this->agent->agent_string())->or_where('ip',$this->input->ip_address())->count() <5){
            $flag = true;
        }        
        return $flag;
    }
    
    # type level data id root_slug child_slug root_title child_title title
    private function get_breadcrumb($vars = array()){
        $uri = null; $menu = new Category();
        $return = '<li><a href="'.site_url().'"><span class="glyphicon glyphicon-home"></span></a></li>';
        $data = null;
        if($vars['type'] != 'default'){
            $data = $vars['data']; # url slug title id
        }
        switch($vars['type']){
            case 'category' :
            if($vars['level'] == 1){
                $return .= '<li class="active">'.$data['root_title'].'</li>';
            }else{
                $return .= '<li><a href="'.site_url($this->setting->site_slug_category.$data['root_slug']).'">'.$data['root_title'].'</a></li>';
                $return .= '<li class="active">'.$data['child_title'].'</li>';
            }                        
            break;            
            case 'product' :
            if($vars['level'] == 1){
                $return .= '<li><a href="'.site_url($this->setting->site_slug_category.$data['root_slug']).'">'.$data['root_title'].'</a></li>';
                $return .= '<li class="active">'.$data['title'].'</li>';
            }else{
                $return .= '<li><a href="'.site_url($this->setting->site_slug_category.$data['root_slug']).'">'.$data['root_title'].'</a></li>';
                $return .= '<li><a href="'.site_url($this->setting->site_slug_category.$data['root_slug'].'/'.$data['child_slug']).'">'.$data['child_title'].'</a></li>';
                $return .= '<li class="active">'.$data['title'].'</li>';
            }   
            break;
            default:
            if($vars['type'] == 'contact' && $vars['type'] == 'reservation'){
                   
            }
            break;
        }
        return $return;
    }
    
    public function detail($var){
        $type = 'product';  $is_data = false;  $metadata = new stdClass(); $i = 0 ; $relate = new stdClass();
        $this->_data['items'] = $relate_addon = null; $this->_data['is_post'] = false; $this->_data['counter_post'] = 0;
        $this->_data['js_base_facebook'] = $this->get_js();
        $last_segment = $this->uri->total_segments();
        $get = $this->uri->segment($last_segment);
        if(!preg_match("/^(p\-)|(n\-)([\w\-\.]+)$/",$get) || $this->input->is_cli_request()){
            return $this->handle_404();
        }elseif(preg_match("/^n\-/",$get)){
            $type = 'news';
        }
        switch($type){
            case 'news': 
                $data  = new Newz(); 
                $this->_data['is_post'] = true;
                # Detect viewer url
                $test_viewer = $this->detect_viewer_post();               
            break;            
            case 'product':
                $data = new Product();
            break;
        }
        #$metadata->all = $data->get()->all;
        $_data = new stdClass();
        $list_title = $list_slug = $list_description = $list_id = array();
        #$value = $data->get()->all;
        $i = 0;
        $detail = $data->where_related('language','id',$this->lang_id)->where_join_field('language', 'slug_title', $var)->get();        
        if(is_null($detail->all) || count($detail->all) == 0){
            return $this->handle_404();
        }
        # Tim thay dong du lieu 
        $is_data = true; $i = 0 ;
        # Fetch all id 
        $current_id = $detail->all[0]->id;
        $list_id = array();
        $list_id[0] = $current_id;
        if($type == 'product'){
            $detail_related = $data->where_not_in('id',$current_id)->where('category_id',$detail->all[0]->category_id)->get(3,0);
            if(count($detail_related->all)>0){
                foreach($detail_related as $item){
                    $list_id[] = $item->id;
                }                
            }
        }
        foreach($data->where_in('id',$list_id)->get()->all as $item){
            $_data->all[$i] = new stdClass();
            $_data->all[$i]->id = $item->id;
            $_data->all[$i]->create = $item->create;
            $_data->all[$i]->image = $item->image;
            $_data->all[$i]->category_id = $item->category_id;
            $_data->all[$i]->last_update = $item->last_update;  
            $_data->all[$i]->viewer = $item->viewer;
            #$list_id[] = $item->id;
            foreach($item->language->include_join_fields()->get_by_id($this->lang_id) as $v){
                $list_title[$item->id] = $v->join_title;
                $list_description[$item->id] = $v->join_description;
                $list_slug[$item->id] = $v->join_slug_title; 
                $_data->all[$i]->join_title = $v->join_title;
                $_data->all[$i]->join_slug  = $v->join_slug_title;
                $_data->all[$i]->join_description = $v->join_description;
                $_data->all[$i]->join_keyword = $v->join_keyword;
                $_data->all[$i]->join_content = $v->join_content;
                if($type == 'product'){
                    $_data->all[$i]->join_price    = $v->join_price;
                    $_data->all[$i]->join_ingredient = $v->join_ingredient;
                }
            }          
            $i+=1;
        }
        #var_dump($list_id);
        # Lay so tin lien quan 
        $prefix_setting = "site_prefix_".$type; # Lay phan setting mo rong cho kieu        
        if($type == 'product' && count($_data->all)>1){
            $i = 1; 
            foreach($_data->all as $item){
                if($i == count($_data->all)){
                    break;
                }
                $relate_addon .= '<div class="col-md-2"><div class="colmenu_img">
                <img width="60" height="60" src="'.base_url($_data->all[$i]->image).'" alt="'.$_data->all[$i]->join_title.'" title="'.$_data->all[$i]->join_title.'">
                <a href="'.site_url($this->setting->$prefix_setting.$_data->all[$i]->join_slug).'"><div class="mask_menucol"> </div> </a>
                </div><h6 class="post-title">'.$_data->all[$i]->join_title.'</h6>
                </div>'."\n";
                $i+=1;
            }
        }else{
            $relate_addon .= '';
        }
        #$count = $data->count();
        # Generate form_search
        $this->generate_form_search();
        $i = 0 ;
        foreach($_data->all as $item ){
            # Bo qua cac tin lien quan chi lay tin hien tai
            if($i == 1){
                break;
            }            
            #$title = $this->list_cat_title[$item->category_id];                           
            $create                        = $item->create;
            if($type == 'product'){
                $p                             = $item->join_price;
                $price                         = (isset($p) && $p/1000 > 1) ? number_format($p,0,",",".")." vnđ" : '$ '.$p.".00";
            }
            # Get breadcrumb
            # type level data id root_slug child_slug root_title child_title title            
            $level = ($this->list_cat_parent[$item->category_id] == 0) ? 1 : 2;
            $root_title = $root_slug = $child_title = $child_slug = '';
            if($level == 1){
                $root_title = $this->list_cat_title[$item->category_id];
                $root_slug  = $this->list_cat_slug[$item->category_id];
            }else{
                $root_title = $this->list_cat_title[$this->list_cat_parent[$item->category_id]];
                $root_slug  = $this->list_cat_slug[$this->list_cat_parent[$item->category_id]];
                $child_title = $this->list_cat_title[$item->category_id];
                $child_slug  = $this->list_cat_slug[$item->category_id]; 
            }
            $bread = array('id'=>$item->id,'title'=>$item->join_title,'root_title'=>$root_title,'child_title'=>$child_title,'root_slug'=>$root_slug,'child_slug'=>$child_slug);
            $vars = array('type'=>'product','level'=>$level,'data'=>$bread);
            $this->_data['breadcrumb'] = $this->get_breadcrumb($vars);
            $metadata->id                  = $item->id;
            $viewer                        = $item->viewer;
            $metadata->join_title          = ucfirst($item->join_title);    
            $metadata->join_description    = $item->join_description;
            $metadata->join_keyword        = $item->join_keyword;
            $metadata->join_slug           = $item->join_slug;
            $metadata->join_content        = $item->join_content;
            # Lay hinh cho category
            #$m = new Category($item->category_id);
            $category_image                = preg_replace("/".settings::width_minimum_category."-".Settings::height_minimum_category."/",Settings::width_maximum_category."-".Settings::height_maximum_category,$this->list_cat_image[$item->category_id]);                
            $this->_data['category_avatar']= '<img class="img-responsive" src="'.base_url($category_image).'" title="'.$metadata->join_title.'" alt="'.$metadata->join_title.'"> 
                                               <h1 class="post-title-page">'.character_limiter($metadata->join_title,80).'</h1>';
            $image                         = preg_replace("/".settings::width_thumb_product."-".Settings::height_thumb_product."/",Settings::width_product."-".Settings::height_product,$item->image);                
            if($type == 'news'){
                $image                     = preg_replace("/".settings::width_thumb_new."-".Settings::height_thumb_new."/",Settings::width_new."-".Settings::height_new,$item->image);
                $category_id               = $this->list_cat_parent[$item->category_id];
                $slug_second               = $this->list_cat_slug[$item->category_id];
                $category_title            = $this->list_cat_title[$item->category_id];
                if($category_id != 0){
                    $link                  = $this->list_cat_slug[$category_id];
                    $category_link         = site_url($this->setting->site_slug_category.$link."/".$slug_second);
                }else{
                    $category_link         = site_url($this->setting->site_slug_category.$slug_second);
                }
                # Dem so comment
                $this->_data['total_cmt']  = 0;
                $items                     = $this->get_comment($item->id);
                $this->_data['comment_content'] = ($items != '') ? $items : lang('cf24_lang_no_comment');
                $this->_data['comment_content'] .= '<p id="loading"><img src="'.base_url('assets/template/img/loading-ajax.gif').'" style="display:none;" /></p>';
                //$comment                   = new Comment();
                
//                unset($comment);
                $this->_data['post_title'] = $metadata->join_title;
                #$this->_data['disqus_title']    = preg_replace("/\'/","\"",$metadata->join_title);
                $this->_data['disqus_id']       = $this->setting->site_prefix_news.$metadata->join_slug;
                $this->_data['disqus_url']      = site_url($this->setting->site_prefix_news.$metadata->join_slug);
                $social                         = '<br /><div class="fb-like" data-href="'.$this->_data['disqus_url'].'"
                                             data-layout="standard" data-action="recommend" data-show-faces="true" data-share="true"></div><!-- Go to www.addthis.com/dashboard to customize your tools -->
                                             <div class="addthis_native_toolbox"></div>';
                $this->_data['custom_javascript'] = "<script>
                  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
                  ga('create', 'UA-57209284-1', 'auto');
                  ga('send', 'pageview');
                </script>";
                $this->_data['custom_javascript'] .= '<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-54717d104707b08d" async="async"></script>';
                # Meta
                $meta = $this->gen_meta(array('id'=>$item->id,'link'=>($this->_data['disqus_url']),'type'=>'new','is_static'=>false));
                $this->_data['meta']   = ($meta !== false)? $meta : "";
                
            }else{
                # Meta
                $this->_data['disqus_url'] = site_url($this->setting->site_prefix_product.$metadata->join_slug);
                $direct_links              = $this->setting->site_prefix_product.$metadata->join_slug;
                $meta = $this->gen_meta(array('id'=>$item->id,'link'=>site_url($direct_links),'type'=>'product','is_static'=>false));
                $this->_data['meta']   = ($meta !== false)? $meta : "";
            }
            unset($m);
            if($type == 'product'){
                $metadata->join_ingredient = $item->join_ingredient;    
            }  
            break;
            
        }
        if(!$is_data){
            return $this->handle_404();
        }else{
            if($type == 'product'){
                # Neu khong co du lieu lien quan 
                if($relate_addon == null){
                    $relate_addon = lang('cf24_lang_no_relate');
                }
                $this->_data['items'] .= '<div class="col-md-8 all-posts"><div class="post-solo">    
                <div class="menu_image_single">
                <img src="'.base_url($image).'"></div></div><div class="row related-food">
                <h4 class="single-related">'.lang('cf24_lang_post_relate').'</h4></div><!--end row-->  
                '.$relate_addon.'         
                </div>';
                $this->_data['items'] .= '<div class="col-md-4 sidebar">
                <h2 class="post_title_menu"> '.$metadata->join_title.'</h2>
                <div class="post-info">'.date('d/m/Y H:i:s',$create).' </div>      
                <div class="price-holder"> <h3 class="item_price2">'.$price.'</h3></div>
                <p>'.$metadata->join_description.'</p>
                <h3><em>'.lang('cf24_lang_ingredient').'</em></h3>
                '.$metadata->join_ingredient.'
                </div>';
            }else{
                # Handle viewer
                if(isset($test_viewer) && $test_viewer == TRUE){
                    if($this->session->userdata('cf24_view_page')){
                        $get_session = $this->session->userdata('cf24_view_page');
                        $slug = strtolower($metadata->join_slug);
                        if(array_key_exists($slug,$get_session)){
                            if($get_session[$slug] == FALSE){
                                $data->refresh_all();
                                $viewer += 1;
                                $data->where('id',$metadata->id)->update('viewer',$viewer);
                                $get_session[$slug] = TRUE;
                                $this->session->set_userdata('cf24_view_page',$get_session);        
                            }
                        }else{
                            $test  =null;
                            foreach($get_session as $k=>$v){
                                $test = $k;
                            }
                            log_message('error',"NO array key exists ".$test." ".$slug);
                        }
                    }    
                }
                $this->_data['counter_post'] = $viewer;
                $this->_data['items'] .= '<div class="post-solo">                
                <div class="post-info">'.date('l d F Y h:i:s A', $create).' - <a href="'.$category_link.'" title="'.$category_title.'">'.$category_title.'</a> - <a href="#top-comment" title="'.$metadata->join_title.'">'.$this->_data['total_cmt'].' Comments</a> '.$social." <a href='javascript:;'>".$viewer.' lượt xem</a>  </div>
                <p>'.$metadata->join_description.'</p>
                <div class="menu_image_single"><img class="img-responsive" src="'.base_url($image).'" alt="'.$metadata->join_title.'" title="'.$metadata->join_title.'">
                </div><p>'.$this->replace_content($metadata->join_content).'</p></div>';
                # Them uri string tai day 
                $this->_data['items'] .= "<script type=\"text/javascript\">var uri_string = \"".uri_string()."\"</script>";                
            }
            #Handle form comment
            $this->_data['form'] = '';            
            $this->load->library('recaptcha');
            $err = '';
            $re_html = $this->recaptcha->recaptcha_get_html();
            if(!$this->session->flashdata('cf24_flash_cm')){
                $this->session->unset_userdata('cm_anchor');
                #$this->session->unset_userdata('cf24_cm_spam');
                #$this->session->unset_userdata('cf24_cm_data');
            }
            $this->form_validation->set_rules('cm_name','họ tên','trim|required|min_length[6]|max_length[32]');
            $this->form_validation->set_rules('cm_email','email','trim|required|min_length[6]|valid_email');
            $this->form_validation->set_rules('cm_content','nội dung','trim|required|min_length[6]|max_length[500]');            
            if($this->form_validation->run($this) !== FALSE){
                # Chặn spam
                //if($this->stop_spam_check($current_id) == FALSE || ($this->session->userdata('cf24_cm_spam_'.$current_id) && $this->session->userdata('cf24_cm_spam_'.$current_id) >= 5)){
//                    $this->session->set_userdata('cm_anchor','commentform');
//                    $this->session->set_flashdata('cf24_flash_cm',"<p><div class=\"alert alert-danger\" role=\"alert\">".lang('cf24_lang_reply_spam')."</div></p>");
//                    return redirect(site_url($this->setting->site_prefix_news.$metadata->join_slug),'refresh');
//                }
                #
                $this->recaptcha->recaptcha_check_answer();
                if($this->recaptcha->getIsValid()){                   
                    
                    $cm_parent = 0;
                    $cm = new Comment();
                    $ip = $this->input->ip_address();
                    #$this->load->library('user_agent');
                    $replace_name = preg_replace("/[^\w\-\_\.\,\!\@]+[\s]{2}/","",trim($this->input->post('cm_name')));  
                    $replace_email = filter_var(trim($this->input->post('cm_email')),FILTER_SANITIZE_EMAIL);
                    $replace_content = preg_replace("/[\`\!\#\%\^\&\*\;\:\'\/]+/","",trim($this->input->post('cm_content')));
                    if($this->input->post('cm_reply') &&  (int)$this->input->post('cm_reply') != 0){
                        if($cm->where('id',$this->input->post('cm_reply'))->count() > 0){
                            $cm_parent = $this->input->post('cm_reply');
                        } 
                        $cm->refresh_all();
                    }
                    # Detect localhost
                    if($ip == '127.0.0.1'){
                        $random_id = array('206.190.138.20','146.185.31.215','95.211.844.70','42.117.110.171');
                        $ip = $random_id[array_rand($random_id)];
                    }
                    $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip={$ip}"));
                    $this->load->model('users');
                    
                    $cookie = null; $admin = false;
                    if($this->input->cookie('remember_code')){
                        $user = new Users();
                        $cookie = get_cookie('remember_code');
                        if($user->where('remember_code',$cookie)->count() === 1){
                            $admin = true;
                        }
                    }
                    if(!is_array($geo)){
                        $insert = array('SG','admin'=>$admin);
                    }else{
                        $insert = array(
                                'admin'=>$admin,
                                $geo['geoplugin_city'],
                                $geo['geoplugin_region'],
                                $geo['geoplugin_areaCode'],
                                $geo['geoplugin_countryCode'],
                                $geo['geoplugin_countryName'],
                                $geo['geoplugin_latitude'],
                                $geo['geoplugin_longitude'],
                                $geo['geoplugin_regionCode'],
                                $geo['geoplugin_regionName'],
                                $geo['geoplugin_currencyCode'],
                                $geo['geoplugin_dmaCode']);    
                    }
                    $cm->name = $replace_name;
                    $cm->email = $replace_email;
                    $cm->create = time();
                    $cm->ip = $ip;
                    $cm->location = serialize($insert);               
                    $cm->user_meta = $this->agent->agent_string();
                    $cm->parent_id = $cm_parent;
                    $cm->new_id = $current_id;
                    $cm->content = $replace_content;
                    if($cm->save()){                        
                        $this->session->set_flashdata('cf24_flash_cm',"<p><div class=\"alert alert-success\" role=\"alert\">".lang('cf24_lang_cm')."</div></p>");
                        # Set comment session name and email 
                        if(!$this->session->userdata('cf24_cm_data')){
                            $this->session->set_userdata('cf24_cm_data',serialize(array('cm_name_data'=>$replace_name,'cm_email_data'=>$replace_email)));    
                        }
                        # Set comment prevent
                        if(!$this->session->userdata('cf24_cm_spam_'.$current_id)){
                            $this->session->set_userdata('cf24_cm_spam_'.$current_id,1);
                        }else{
                            $this->session->set_userdata('cf24_cm_spam_'.$current_id,(int)$this->session->userdata('cf24_cm_spam_'.$current_id)+1);    
                        }
                        $this->session->set_userdata('cm_anchor','commentform'); # Set anchor
                        unset($cm);
                        redirect(site_url($this->setting->site_prefix_news.$metadata->join_slug),'refresh');
                    }else{
                        $this->session->set_flashdata('cf24_flash_cm',"<p><div class=\"alert alert-warning\" role=\"alert\">".lang('cf24_lang_cm_err')."</div></p>");
                    }
                }else{
                    $this->session->set_userdata('cm_anchor','commentform');
                    $err = lang('cf24_lang_spam');
                }
            }
            else{                
                if($this->input->post('submit')){
                    $this->session->set_userdata('cm_anchor','commentform');
                }
            }
            $cm_name = ''; $cm_email = '';
            if($this->session->userdata('cf24_cm_data')){
                $cm_array = unserialize($this->session->userdata('cf24_cm_data'));
                $cm_name  = $cm_array['cm_name_data'];
                $cm_email = $cm_array['cm_email_data'];
            }
            $attributes = array('role' => 'form', 'id' => 'commentform');
            $this->_data['form'] .= form_open_multipart($this->_data['disqus_url'],$attributes);
            $this->_data['form'] .= ($this->session->flashdata('cf24_flash_cm')) ? $this->session->flashdata('cf24_flash_cm') : ''; 
            $this->_data['form'] .= '<input name="cm_reply" id="cm_reply" value="0" type="hidden">';
            $this->_data['form'] .= '<p>
                        '.form_input(array('name'=>'cm_name','value'=>$cm_name,'placeholder'=>lang('cf24_lang_placeholder_name'),'id'=>'cm_name','class'=>'comm-field','size'=>'22','maxlength'=>32)).'
                        <span>Họ tên</span>                            
                        <p class="text-danger">'.ucwords(form_error('cm_name')).'</p>
                    </p>';
            $this->_data['form'] .= '<p>
                        '.form_input(array('name'=>'cm_email','value'=>$cm_email,'placeholder'=>lang('cf24_lang_placeholder_email'),'id'=>'cm_email','tabindex'=>1,'class'=>'comm-field','aria-required'=>'true','size'=>'22','maxlength'=>64)).'
                        <span for="cm_email">Email</span>                            
                        <p class="text-danger">'.ucwords(form_error('cm_email')).'</p>
                    </p>';
            $this->_data['form'] .= $re_html.'<p class="text-danger">'.$err.'</p>';                    
            $this->_data['form'] .= form_textarea(array('name'=>'cm_content','id'=>'cm_content','value'=>'','placeholder'=>lang('cf24_lang_placeholder_content'),'tabindex'=>4,'cols'=>'100%','rows'=>10,'maxlength'=>500)).'                            
                        <span for="cm_content">Nội dung còn <i id="status_msg">500</i> ký tự</span>
                        <p class="text-danger">'.ucwords(form_error('cm_content')).'</p>';
            $this->_data['form'] .= form_submit(array('name'=>'submit','value'=>"Submit comment",'tabindex'=>5,"id"=>'submit-comm'));
            $this->_data['form'] .= form_close();
        }
        unset($data) ;
        $output = new stdClass();
        $this->_example_output($output,false);
        $real_title = "cdsvsdvs";
        if(isset($metadata->join_title)){
            $real_title = $metadata->join_title;
        }
        $this->top_3_news();
        $this->template->title($real_title);
        $this->template->set_partial('slide','template_theme/blocks/slide_cat',$this->_data);
        
        if($type == 'news'){
            # Partials Add ons for comment and property 
            $this->template->set_partial('detail_post','home/new_detail',$this->_data);
            $this->template->build('home/new_category',$this->_data);
        }else{
            $this->template->build('home/product_detail',$this->_data);
        }
    }
    
    private function detect_viewer_post(){
        $return = false;       
        if(preg_match("/^n\-.*$/",uri_string())){
            $uri = strtolower(substr(uri_string(),2,strlen(uri_string())));
            if($this->session->userdata('cf24_view_page')){
                $get_session = $this->session->userdata('cf24_view_page');
                if(!array_key_exists($uri,$get_session)){
                    $this->session->set_userdata('cf24_view_page',array_merge($get_session,array($uri=>false)));    
                }
            }else{
                $this->session->set_userdata('cf24_view_page',array($uri=>false));
            }
            $return = true;
        }
        return $return;
    }
    
    private function handle_counter(){        
        $i  = 0;
        $counter = new Counters();
        if(!$this->session->userdata('cf24_secure_hash')){            
            $counter->get_by_id(1);
            $desc = $counter->description;
            $counter->refresh_all();
            $counter->where('id',1)->update('description', $desc+1);
        }
        $this->_data['counter_all'] = $counter->get_by_id(1)->all[0]->description;
        unset($counter); 
        # Xu ly file counter
        $path  = APPPATH."counters/dat.log";
        $array = $list_session = $list_key = array();
        $c = file_get_contents($path);
        if($c === FALSE){
            $c = '';
        }
        $array = explode(",",$c);
        $session = session_id();
        $time    = time();            
        $string  = $session."|".$time;
        if($c != ''){           
            # Neu file date khong rong tuc la co nguoi online
            if(!is_null($array) && count($array)>0){
                # Get list session id{}
                foreach($array as $k=>$v){
                    $temp  = explode("|",$v);
                    $list_session[$temp[0]] = $temp[0];
                    $list_key[$temp[0]] = $k;
                }
                if(!array_key_exists($session,$list_session)){
                    # Them dong moi vao
                    $c .= ",".$string;
                }else{
                    # Update lai time cua dong cu
                    $new = $list_session[$session]."|".$time;
                    $c = str_replace($array[$list_key[$session]],$new,$c);
                }
                # Xong het roi kiem tra va xoa
                $array = explode(",",$c);                
                foreach($array as $k=>$v){                
                    $temp = explode("|",$v);
                    if($temp[1] < $time - 120){
                        unset($array[$k]);
                    }
                }
                # Sau khi doc xong ghi file            
            }
        }else{
            # Neu chua co du lieu gi het thi them moi dong dau tien vao
            $array = array($string);
        }
        $x = implode(",",$array); 
        $file = fopen($path,'w');
        fwrite($file,$x);
        fclose($file);
        $this->_data['counter_online'] = ($c != '') ? count($array) : 0;
        return ;
    }
    
    //public function handle_counter_ajax(){
//        # Xu ly file counter
//        $path  = APPPATH."counters/dat.log";
//        $array = $list_session = $list_key = array();
//        $c = file_get_contents($path);
//        if($c === FALSE){
//            $c = '';
//        }
//        $array = explode(",",$c);
//        $session = session_id();
//        $time    = time();            
//        $string  = $session."|".$time;
//        if($c != ''){           
//            # Neu file date khong rong tuc la co nguoi online
//            if(!is_null($array) && count($array)>0){
//                # Get list session id{}
//                foreach($array as $k=>$v){
//                    $temp  = explode("|",$v);
//                    $list_session[$temp[0]] = $temp[0];
//                    $list_key[$temp[0]] = $k;
//                }
//                if(!array_key_exists($session,$list_session)){
//                    # Them dong moi vao
//                    $c .= ",".$string;
//                }else{
//                    # Update lai time cua dong cu
//                    $new = $list_session[$session]."|".$time;
//                    $c = str_replace($array[$list_key[$session]],$new,$c);
//                }
//                # Xong het roi kiem tra va xoa
//                $array = explode(",",$c);                
//                foreach($array as $k=>$v){                
//                    $temp = explode("|",$v);
//                    if($temp[1] < $time - 10){
//                        unset($array[$k]);
//                    }
//                }
//                # Sau khi doc xong ghi file            
//            }
//        }else{
//            # Neu chua co du lieu gi het thi them moi dong dau tien vao
//            $array = array($string);
//        }
//        $x = implode(",",$array); 
//        $file = fopen($path,'w');
//        fwrite($file,$x);
//        fclose($file);
//        $output = ($c != '') ? count($array) : 0;
//        $this->output
//        ->set_content_type('application/json')
//        ->set_output(json_encode(array('join'=>($output))));
//    }
    
    private function replace_content($content){
        #$content = preg_replace("/\<h2.*class=\"ms\-rteElement\-H2\".?\>.*\<\/h2\>/","",$content);
        $content = preg_replace("/\<a.*href=\".*\.aspx\".?\>.*\<\/a\>/","",$content);
        $content = preg_replace("/\<strong>.*<\/strong\>/","",$content);
        $content = preg_replace("/\<h2.*class=\"h2\.ms\-rteElement\-H2\".*\>.*\<\/h2\>/","",$content);
        return $content;
    }
        
    public function category($first ,$second = null,$page = 1)
    {
        if($this->input->is_cli_request()){
            return $this->handle_404();
        }   
        $this->top_3_news();
        # $m->     
        #$m = new Category();
        $list_image = $list_id = $slug = $test = $fetch = $list_title = $id = $type = array(); $types = $id_for_meta = $real_title = $first_title = $l_cat = null; $tree = false; $no_data = false; $this->_data['items'] = '';
        $this->_data['second_child_menu'] = $this->_data['category_avatar'] = ''; $pagination = $direct_links = ''; $namespace = '';
        $paging = $this->setting->site_pagination_default;
        $i = 0 ;
        $direct_links .= $this->setting->site_slug_category;
        $direct_links .= ($second != null && $second != '' && !is_numeric($second)) ? $first.'/'.$second : $first;
        # Fetch data
        foreach($this->category->all as $item){
            $slug[$item->id]        = strtolower($item->join_slug);
            $type[$item->join_slug] = strtolower($item->type);
            $id[$item->join_slug]   = $item->id;
            $list_title[$item->id] = strtolower($item->join_title);
            $_img = preg_replace("/".settings::width_minimum_category."-".Settings::height_minimum_category."/",Settings::width_maximum_category."-".Settings::height_maximum_category,$item->img_avatar);
            $list_image[$item->join_slug]           = base_url($_img);
            if($first == strtolower($item->join_slug)){
                $first_title = ucfirst($item->join_title);
            }
        }
        #
        if($second != null && !is_numeric($second)){
            if( !in_array(strtolower($second),$slug) || !in_array(strtolower($first),$slug)){
                return $this->handle_404();
            }else{
                # Neu khong ton tai khoa nay thi moi fecth
                if(!array_key_exists($second,$id)){
                    $fetch_key = new Category;
                    $fetch_key->where_related('language','id',$this->lang_id)->where_join_field('language', 'slug_title', $second)->get();
                    foreach($fetch_key as $item){
                        $types = $item->type;
                        $key = $item->id;  
                        $_img = preg_replace("/".settings::width_minimum_category."-".Settings::height_minimum_category."/",Settings::width_maximum_category."-".Settings::height_maximum_category,$item->img_avatar);
                        $_image = base_url($_img);  
                    }
                    unset($fetch_key);
                    $id_for_meta = $key;
                }else{
                    $types = $type[$second];   
                    $_image = $list_image[$second]; 
                    $id_for_meta = $id[$second];
                }                
                if($types == 'products'){
                    $product = new Product();
                }else{
                    $product = new Newz();
                }
                # Lay id cho the meta                
                
                
                $meta = $this->gen_meta(array('id'=>$id_for_meta,'link'=>site_url($direct_links),'type'=>'category','is_static'=>false));
                $this->_data['meta']   = ($meta !== false)? $meta : "";
                $real_title = $list_title[$id_for_meta];
                $this->_data['category_avatar'] .= '<img class="img-responsive" src="'.$_image.'" title="'.$real_title.'" alt="'.$real_title.'"> 
                                                 <h1 class="post-title-page">'.$real_title.'</h1>';
                $this->_data['second_child_menu'] .= '<li class="cat-item"><a href="'.site_url($this->setting->site_slug_category.$first).'" title="'.$first_title.'">'.$first_title.'</a></li>';
                $tree  = true;         
                if(isset($id_for_meta) && $id_for_meta> 0){
                    if($types == 'products'){
                        $data = $product->where('category_id',$id_for_meta)->get();
                        #$data = $product->where_related('language','id',$this->lang_id)->where_join_field('language', 'slug_title', $second)->get();
                    }else{
                        if(preg_match("/\d{1,3}/",$second)){
                            $page = $second;
                        }else{
                            $page = $page;
                        }
                        $data = $product->where('category_id',$id_for_meta)->get_paged($page,$paging);
                    } 
                }else{
                    $data = $this->lang->line('cf24_lang_no_data');
                }       
                
            }
        }else{
            if(!in_array(strtolower($first),$slug)){
                return $this->handle_404();
            }else{
                # Neu khong ton tai khoa nay thi moi fecth            
                if(!array_key_exists($first,$id)){
                    $fetch_key = new Category;
                    $fetch_key->where_related('language','id',$this->lang_id)->where_join_field('language', 'slug_title', $first)->get();
                    foreach($fetch_key as $item){
                        $types = $item->type;
                        $key = $item->id;  
                        $_img = preg_replace("/".settings::width_minimum_category."-".Settings::height_minimum_category."/",Settings::width_maximum_category."-".Settings::height_maximum_category,$item->img_avatar);
                        $_image = base_url($_img);  
                    }
                    unset($fetch_key);
                    $id_for_meta = $key;
                }else{
                    $types = $type[$first];   
                    $_image = $list_image[$first]; 
                    $id_for_meta = $id[$first];
                }
                if($types == 'products'){
                    $product = new Product();                    
                }else{
                    $product = new Newz();
                }
                foreach($this->category->all as $item){
                    if($item->parent_id == $id[$first]){
                        $fetch[] = $item;
                    }
                }
                #$fetch = $m->where('parent_id',$id[$first])->get()->all;
                # Lay id cho the meta
                $list_id[0] = $id_for_meta;
                
                foreach($fetch as $k=>$item){
                    
                    $this->_data['second_child_menu'] .= '<li class="cat-item"><a href="'.site_url($this->setting->site_slug_category.$first.'/'.$item->join_slug).'" title="'.$item->join_title.'">'.$item->join_title.'</a></li>';
                    $list_id[] = $item->id; 
                }
                #log_message('error',$id_for_meta);
                $meta = $this->gen_meta(array('id'=>$id_for_meta,'link'=>site_url($direct_links),'type'=>'category','is_static'=>false));
                $this->_data['meta']   = ($meta !== false)? $meta : "";
                
                $real_title = $list_title[$id_for_meta];
                $this->_data['category_avatar'] .= '<img class="img-responsive" src="'.$_image.'" title="'.$real_title.'" alt="'.$real_title.'"> 
                                                 <h1 class="post-title-page">'.$real_title.'</h1>';
                if(!is_null($list_id) && count($list_id)>0){
                    if($types == 'products'){
                        $data = $product->where_in('category_id',$list_id)->get();
                    }else{
                        if(preg_match("/\d{1,3}/",$second)){
                            $page = $second;
                        }
                        $data = $product->where_in('category_id',$list_id)->get_paged($page,$paging);
                    }    
                }else{
                    #log_message('error','sdfgsfvsd');
                    $data = $this->lang->line('cf24_lang_no_data');
                }   
                             
            }
        }        
        if( is_string($data) && !is_object($data)){
            $no_data = true;
        }else{
            if( count($data->all) == 0){
                $no_data = true;     
            }
            else{
            $this->generate_form_search();
            # Get breadcrumb
            # type level data id root_slug child_slug root_title child_title title
            $get_id = $id_for_meta; $level = 1; $child_slug = $child_title = $root_title = $root_slug = '';
            if($second != null && !is_numeric($second)){
                $get_id = $id_for_meta;
                $level = 2;
                $root_title = $this->list_cat_title[$this->list_cat_parent[$get_id]];
                $root_slug  = $this->list_cat_slug[$this->list_cat_parent[$get_id]];
                $child_slug   = $this->list_cat_slug[$get_id];
                $child_title  = $this->list_cat_title[$get_id];
            }else{
                $root_slug   = $this->list_cat_slug[$get_id];
                $root_title  = $this->list_cat_title[$get_id];
            }
            $bread = array('id'=>$get_id,'title'=>$item->join_title,'root_title'=>$root_title,'child_title'=>$child_title,'root_slug'=>$root_slug,'child_slug'=>$child_slug);
            $vars = array('type'=>'category','level'=>$level,'data'=>$bread);
            $this->_data['breadcrumb'] = $this->get_breadcrumb($vars);
            
            $_data = new stdClass();
            $i = 0;
            foreach($data as $item){
                $_data->all[$i] = new stdClass();
                $_data->all[$i]->id = $item->id;
                $_data->all[$i]->category_id = $item->category_id;
                $_data->all[$i]->create = $item->create;
                $_data->all[$i]->last_update = $item->last_update;
                $_data->all[$i]->image = $item->image;
                foreach($item->language->include_join_fields()->get_by_id($this->lang_id) as $v){
                    $_data->all[$i]->join_title         = $v->join_title;
                    $_data->all[$i]->join_slug          = $v->join_slug_title;
                    $_data->all[$i]->join_description   = $v->join_description;
                    $_data->all[$i]->join_keyword       = $v->join_keyword;
                    if($types == 'products'){
                        $_data->all[$i]->join_price      = $v->join_price;
                        $_data->all[$i]->join_ingredient = $v->join_ingredient;
                    }else{
                        $_data->all[$i]->join_content    = $v->join_content;
                    }
                }
                $i+=1;
            }
            switch($types)
            {
                case 'products':                
                $this->_data['items'] .= '<div class="row">';
                foreach($_data->all as $item){
                    #$test[] = $list_title[$item->category_id];
                    $title = ucfirst($item->join_title);
                    $desc  = $item->join_description;
                    $slugs = $item->join_slug;
                    $price = $item->join_price;
                    $price = ($price/1000 > 1) ? number_format($price,0,",",".")." vnđ" : '$ '.$price.".00";
                    $link  = site_url($this->setting->site_prefix_product.$slugs);
                    $image = preg_replace("/".Settings::width_thumb_product."-".Settings::height_thumb_product."/",Settings::width_product."-".Settings::height_product,$item->image);
                    //if()
//                    {
//                        $this->_data['items'] .= '<h2 class="categ_name">'.$list_title[$item->category_id].'</h2>';
//                    }
                    $this->_data['items'] .= '<div class="col-sm-6 col-md-6">
                                            <div class="foodmenu_img">
                                            <img width="60" height="60" src="'.base_url($item->image).'" alt="'.$list_title[$item->category_id].'" title="'.$list_title[$item->category_id].'" />
                                            <a class="lightbox" href="'.base_url($image).'" rel="galimages" title="'.($title).'" >
                                            <div class="mask_foodmenu"><span class="mglass">'.$this->lang->line('cf24_lang_readmore').'</span> </div> </a>
                                            </div> 
                                            <div class="foodmenu_info">
                                            <h3 class="foodmenu_item_title"><a href="'.$link.'" title="'.$title.'">'.$title.'</a>
                                            <span class="item_price">'.$price.'</span></h3>
                                            <p>'.$desc.'</p></div>
                                            <div class="column-clear"></div>
                                            </div>';           
             
                } 
                $this->_data['items'] .= '</div>';
                break;
                case 'news':                
                $com = new Comment(); 
                $i = 0;                
                $pagination .= '<ul class="pagination">';                
                foreach($_data->all as $item){ 
                    $l_cat = '';
                    $num_comment = $com->where('new_id',$item->id)->count();
                    $title       = $item->join_title;
                    $desc        = $item->join_description;
                    $slugs       = $item->join_slug;
                    $link        = site_url($this->setting->site_prefix_news.$slugs);
                    # Get link category
                    #$cat = new Category(); 
                    $parent = $this->list_cat_parent[$item->category_id];
                    if($parent != 0){
                        
                        $ext = "/".$slug[$item->category_id];
                        $link_cat = $this->setting->site_slug_category.$slug[$parent];
                        $l_cat  .= '<a href="'.site_url($link_cat.$ext).'" title="'.$list_title[$item->category_id].'">'.$list_title[$item->category_id].'</a>';    
                        $l_cat  .= ' , <a href="'.site_url($link_cat).'" title="'.$list_title[$parent].'">'.$list_title[$parent].'</a>';
                    }else{                        
                        $l_cat  .= '<a href="'.site_url($this->setting->site_slug_category.$slug[$item->category_id]).'" title="'.$list_title[$item->category_id].'">'.$list_title[$item->category_id].'</a>';
                    }
                     
                    $image = base_url(preg_replace("/".Settings::width_thumb_new."-".Settings::height_thumb_new."/",Settings::width_new."-".Settings::height_new,$item->image));
                    $this->_data['items'] .= '<div class="post-solo">
                    <h2 class="post-title"><a href="'.$link.'" title="'.$title.'">'.$title.'</a></h2>
                    <div class="post-info">'.date('l d F Y h:i:s A', $item->create).' - '.$l_cat.' - 
                    <a href="'.$link.'" title="'.$title.'">'.$num_comment." ".$this->lang->line('cf24_lang_comment').'</a>   </div>
                    <div class="post_image">
                    <a href="'.$link.'" title="'.$title.'"><img class="img-responsive" src="'.$image.'" alt="'.$title.'" title="'.$title.'" /></a>
                    <a href="#" ><div class="mask2"><span class="link-img2">'.lang('cf24_lang_readmore').'</span> </div> </a>	
                    </div>                                            
                    <p>'.$desc.'</p>                                            
                    <p class="text-right"><a href="'.$link.'" class="read-more">'.lang('cf24_lang_readmore').'</a></p>
                    <div class="column-clear"></div>
                    </div>
                    ';
                    #$i+=1;
                }
                $direct_link = ($second != null && $second != '' && !is_numeric($second)) ? $first.'/'.$second.'/': $first."/";
                $page = ($page == 0) ? 1 : $page;
                $pagination .= "<li class='active'><a href='javascript:;'>".lang('cf24_lang_current_page')." ".$page."</a></li>";
                if($product->paged->has_previous)
                {
                    $pagination .= '<li><a href="'.site_url($this->setting->site_slug_category.$direct_link.'1').'"><i class="fa fa-angle-double-left"></i> '.lang('cf24_lang_first').'</a></li>';
                    $prev       = $product->paged->next_page-2;
                    $pagination .= '<li><a href="'.site_url($this->setting->site_slug_category.$direct_link.$product->paged->previous_page).'"><i class="fa fa-angle-left"></i> '.lang('cf24_lang_prev')." ".$prev.'</a></li>';
                }
                if($product->paged->has_next)
                {
                    $pagination .= '<li><a href="'.site_url($this->setting->site_slug_category.$direct_link.$product->paged->next_page).'">'.lang('cf24_lang_next')." ".$product->paged->next_page.' <i class="fa fa-angle-right"></i></a></li>';
                    $pagination .= '<li><a href="'.site_url($this->setting->site_slug_category.$direct_link.$product->paged->total_pages).'">'.lang('cf24_lang_last').' <i class="fa fa-angle-double-right"></i></a></li>';
                }
                $pagination .= "<li><a href='javascript:;'>".lang('cf24_lang_num_of_page')." ".$product->paged->total_pages.'</a></li></ul>';
                break; 
            } 
            }  
        }
        $output = new stdClass();
        # Lay data cho menu
        #$this->get_data(); 
        $namespace  = &$pagination;
        $this->_data['items'] .= $pagination;
        #
        if($no_data == true){
            if(is_object($data)){
                $data = $this->lang->line('cf24_lang_no_data');
            }
            $this->_data['items'] .= ($data); 
        }else{
            $this->_data['items'] = $namespace.$this->_data['items'];
        }
        unset($product);#unset($m) ;
        $this->_example_output($output,false);
        $this->template->title($real_title);
        $this->template->set_partial('slide','template_theme/blocks/slide_cat',$this->_data);
        
        if($types == 'news'){
            $this->template->build('home/new_category',$this->_data);
        }else{
            $this->template->build('home/product_category',$this->_data);
        }
    }
    
    private function handle_menu($offset = 0){
        if($offset !== 0){
            #$this->add .= '<li><a href="'.site_url('contact').'">'.$this->lang->line('cf24_lang_contact').'</a></li>';
            #$this->add .= '<li><a href="'.site_url('about').'">'.$this->lang->line('cf24_lang_about').'</a></li>';
            $this->add .=              '</ul>';
        }else
        {
            $this->add .= '<ul class="menu"><li class="current-menu-item"><a href="'.site_url().'">'.$this->lang->line('cf24_lang_home').'</a></li>';    
        }
        return $this->add;        
    }
    
    public function get_data()
    {
        $item = new Category();
        $this->data = new stdClass();          
        $i = 0;
        $this->data->all = $item->order_by('parent_id','ASC')->get()->all;
        foreach($this->data->all as $item)
        {
            $this->data->all[$i]->join_title = $item->language->include_join_fields()->get()->all[$this->lang_index]->join_title;
            $this->data->all[$i]->join_slug = strtolower($item->language->include_join_fields()->get()->all[$this->lang_index]->join_slug_title);
            $i +=1;
        }
        return ;
    }
    
    public function handle_setting()
    {
        $this->setting = new stdClass();
                
        $setting = new Setting();
        foreach($setting->get()->all as $item)
        {
            $name = $item->name;
            $this->setting->$name = $item->value;
        }  
        unset($setting);      
        return ;
    }
    
    public function handle_index_news()
    {
        $new = new Newz();        
        $data = new stdClass();
        $i = 0 ;
        foreach($new->order_by('create','DESC')->get(3,5)->all as $item){
            $data->all[$i] = new stdClass();
            $data->all[$i]->image = $item->image;
            foreach($item->language->include_join_fields()->get_by_id($this->lang_id) as $v){
                $data->all[$i]->join_title = $v->join_title;
                $data->all[$i]->join_slug = $v->join_slug_title;
                $data->all[$i]->join_description = $v->join_description;
            }
            $i+=1;
        }
        $i = 0 ;
        $this->_data['footer_news'] = ''; $this->_data['footer_events'] = ''; $this->_data['footer_top_events'] = '';
        foreach($data->all as $item){
            #$new->get_by_id($item->id);
            $title = $item->join_title;$slug  = strtolower($item->join_slug);$desc  = $item->join_description;
            $this->_data['footer_news'] .= '<div class="slide">
        	<div class="slide_title">'.$title.'</div>						
    		<div class="slide_text"><div class="slide_image">
            <img src="'.base_url($item->image).'" rel="" alt="" title=""/>
            </div><p>'.$desc.'</p>
            <p class="text-right"><a href="'.site_url($this->setting->site_prefix_news.$slug).'" class="read-more">'.$this->lang->line('cf24_lang_readmore').'</a></p>
            <div class="column-clear"></div>
            </div>                      
        	</div>';
            $i+=1;
        }
        $new->refresh_all(); unset($data);
        # Events        
        $data = new stdClass();
        foreach($new->where('category_id',103)->order_by('id','ASC')->get(2,0)->all as $item){
            $data->all[$i] = new stdClass();
            $data->all[$i]->image = $item->image;
            $data->all[$i]->create = $item->create;
            foreach($item->language->include_join_fields()->get_by_id($this->lang_id) as $v){
                $data->all[$i]->join_title = $v->join_title;
                $data->all[$i]->join_slug = $v->join_slug_title;
                $data->all[$i]->join_description = $v->join_description;
            }
            $i+=1;
        }
        $i = 0 ;
        foreach($data->all as $item){
            #$new->get_by_id($item->id);
            $title = $item->join_title;$slug  = strtolower($item->join_slug); $desc  = $item->join_description;
            $this->_data['footer_events'] .= '<div class="post-home">
            <span class="home-time">'.date('d/m/Y',$item->create).'</span>
            <h2 class="home-post-title"><a href="'.site_url($this->setting->site_prefix_news.$slug).'" title="'.$title.'">'.$title.'</a></h2>
            <p>'.$desc.'</p></div>';
            $i+=1;
        }
        # events top
        unset($data); $new->refresh_all();
        $data = new stdClass();
        foreach($new->where('category_id',103)->order_by('id','DESC')->get(3,0)->all as $item){
            $data->all[$i] = new stdClass();
            $data->all[$i]->image = $item->image;
            $data->all[$i]->create = $item->create;
            foreach($item->language->include_join_fields()->get_by_id($this->lang_id) as $v){
                $data->all[$i]->join_title = $v->join_title;
                $data->all[$i]->join_slug = $v->join_slug_title;
                $data->all[$i]->join_description = $v->join_description;
            }
            $i+=1;
        }
        $i = 0 ;
        foreach($data->all as $item){
            #$new->get_by_id($item->id);
            $title = $item->join_title;$slug  = strtolower($item->join_slug);$desc  = $item->join_description;
            $image = preg_replace("/".Settings::width_thumb_new."-".Settings::height_thumb_new."/",Settings::width_new."-".Settings::height_new,$item->image);
            $this->_data['footer_top_events'] .= '<div class="col-md-4 col-home">
            <h2 class="home-title">'.$title.'</h2>
            <div class="home-img">
            <img class="img-responsive" src="'.base_url($image).'" alt="" />
            <a href="'.site_url($this->setting->site_prefix_news.$slug).'" title="'.$title.'" alt="'.$title.'" ><div class="mask"><span class="link-img">'.$this->lang->line('cf24_lang_readmore').'</span> </div> </a>
            </div>
            <p>'.$desc.'</p>
            <p class="text-right"><a href="'.site_url($this->setting->site_prefix_news.$slug).'" class="read-more">'.$this->lang->line('cf24_lang_readmore').'</a></p>
            <div class="column-clear"></div>
            </div>';
            $i+=1;
        }
        unset($new);
        return ;
    }
    
    private function test(){
        $this->top_3_news();
            $this->_data['items'] = "<div id='cse' style='width: 100%;'>Loading</div>
            <script src='//www.google.com/jsapi' type='text/javascript'></script>
            <script type='text/javascript'>
            google.load('search', '1', {language: 'vi', style: google.loader.themes.V2_DEFAULT});
            google.setOnLoadCallback(function() {
            var customSearchOptions = {};
            var orderByOptions = {};
            orderByOptions['keys'] = [{label: 'Relevance', key: ''} , {label: 'Date', key: 'date'}];
            customSearchOptions['enableOrderBy'] = true;
            customSearchOptions['orderByOptions'] = orderByOptions;
            customSearchOptions['overlayResults'] = true;
            var customSearchControl =   new google.search.CustomSearchControl('004899452839413002355:cued3ab29ns', customSearchOptions);
            customSearchControl.setResultSetSize(google.search.Search.FILTERED_CSE_RESULTSET);
            var options = new google.search.DrawOptions();
            options.setAutoComplete(true);
            customSearchControl.draw('cse', options);
            }, true);
            </script>";
        $output = new stdClass();
        $this->_example_output($output,false);
        $this->template->title("csdcsd");
        #$this->template->set_partial('slide','template_theme/blocks/slide_cat',$this->_data);
        
        $this->template->build('home/new_category',$this->_data);
    }
    
    private function search(){
        $this->top_3_news();
        $this->generate_form_search();
            $this->_data['items'] = "<h1>".lang('cf24_lang_txt_search').":</h1><div id='cse' style='width: 100%;'>Loading</div>
            <script src='//www.google.com/jsapi' type='text/javascript'></script>
            <script type='text/javascript'>
            google.load('search', '1', {language: 'vi', style: google.loader.themes.V2_DEFAULT});
            google.setOnLoadCallback(function() {
            var customSearchOptions = {};
            var orderByOptions = {};
            orderByOptions['keys'] = [{label: 'Relevance', key: ''} , {label: 'Date', key: 'date'}];
            customSearchOptions['enableOrderBy'] = true;
            customSearchOptions['orderByOptions'] = orderByOptions;
            customSearchOptions['overlayResults'] = true;
            var customSearchControl =   new google.search.CustomSearchControl('004899452839413002355:cued3ab29ns', customSearchOptions);
            customSearchControl.setResultSetSize(google.search.Search.FILTERED_CSE_RESULTSET);
            var options = new google.search.DrawOptions();
            options.setAutoComplete(true);
            customSearchControl.draw('cse', options);
            }, true);
            </script>";
        $output = new stdClass();
        $meta = $this->gen_meta(array('id'=>1,'link'=>site_url(),'type'=>'category','is_static'=>true));
        $this->_data['meta']   = ($meta !== false)? $meta : "";
        $this->_example_output($output,false);
        $this->template->title("Tìm kiếm");
        $this->template->set_partial('slide','template_theme/blocks/slide_cat',$this->_data);
        
        $this->template->build('home/new_category',$this->_data);
    }
    
    //public function search(){
//        if($this->check_secure_hash() === FALSE && !$this->input->post('txt_search') && strlen(trim($this->input->post('txt_search')))<2){
//            return $this->handle_404();
//        }
//        $key = $this->input->post('txt_search');
//        $key = preg_replace("/[^\w\-\_\.\,\@\(\)\[\]\{\}\;\:\?\s]+/","",$key);
//        $data = new Newz();
//        $c = $data->where_related('language','id',$this->lang_id)->like_join_field('language', 'title', $key)->count();
//        if($c == 0){
//            
//        }else{
//            if(!$this->session->userdata('cf24_search_text')){
//                $this->session->set_userdata('cf24_search_text',$key);
//            }
//            $com = new Comment(); 
//            $i = 0;                
//            $pagination .= '<ul class="pagination">';                
//            foreach($_data->all as $item){ 
//                $l_cat = '';
//                $num_comment = $com->where('new_id',$item->id)->count();
//                $title       = $item->join_title;
//                $desc        = $item->join_description;
//                $slugs       = $item->join_slug;
//                $link        = site_url($this->setting->site_prefix_news.$slugs);
//                # Get link category
//                #$cat = new Category(); 
//                $parent = $this->list_cat_parent[$item->category_id];
//                if($parent != 0){
//                    
//                    $ext = "/".$slug[$item->category_id];
//                    $link_cat = $this->setting->site_slug_category.$slug[$parent];
//                    $l_cat  .= '<a href="'.site_url($link_cat.$ext).'" title="'.$list_title[$item->category_id].'">'.$list_title[$item->category_id].'</a>';    
//                    $l_cat  .= ' , <a href="'.site_url($link_cat).'" title="'.$list_title[$parent].'">'.$list_title[$parent].'</a>';
//                }else{                        
//                    $l_cat  .= '<a href="'.site_url($this->setting->site_slug_category.$slug[$item->category_id]).'" title="'.$list_title[$item->category_id].'">'.$list_title[$item->category_id].'</a>';
//                }
//                $image = base_url(preg_replace("/".Settings::width_thumb_new."-".Settings::height_thumb_new."/",Settings::width_new."-".Settings::height_new,$item->image));
//                $this->_data['items'] .= '<div class="post-solo">
//                <h2 class="post-title"><a href="'.$link.'" title="'.$title.'">'.$title.'</a></h2>
//                <div class="post-info">'.date('d/m/Y',$item->create).' - '.$l_cat.' - 
//                <a href="'.$link.'" title="'.$title.'">'.$num_comment." ".$this->lang->line('cf24_lang_comment').'</a>   </div>
//                <div class="post_image">
//                <a href="'.$link.'" title="'.$title.'"><img class="img-responsive" src="'.$image.'" alt="'.$title.'" title="'.$title.'" /></a>
//                <a href="#" ><div class="mask2"><span class="link-img2">'.lang('cf24_lang_readmore').'</span> </div> </a>	
//                </div>                                            
//                <p>'.$desc.'</p>                                            
//                <p class="text-right"><a href="'.$link.'" class="read-more">'.lang('cf24_lang_readmore').'</a></p>
//                <div class="column-clear"></div>
//                </div>
//                ';
//                #$i+=1;
//            }
//            $direct_link = ($second != null && $second != '' && !is_numeric($second)) ? $first.'/'.$second.'/': $first."/";
//            $page = ($page == 0) ? 1 : $page;
//            $pagination .= "<li class='active'><a href='javascript:;'>".lang('cf24_lang_current_page')." ".$page."</a></li>";
//            if($product->paged->has_previous)
//            {
//                $pagination .= '<li><a href="'.site_url($this->setting->site_slug_category.$direct_link.'1').'"><i class="fa fa-angle-double-left"></i> '.lang('cf24_lang_first').'</a></li>';
//                $prev       = $product->paged->next_page-2;
//                $pagination .= '<li><a href="'.site_url($this->setting->site_slug_category.$direct_link.$product->paged->previous_page).'"><i class="fa fa-angle-left"></i> '.lang('cf24_lang_prev')." ".$prev.'</a></li>';
//            }
//            if($product->paged->has_next)
//            {
//                $pagination .= '<li><a href="'.site_url($this->setting->site_slug_category.$direct_link.$product->paged->next_page).'">'.lang('cf24_lang_next')." ".$product->paged->next_page.' <i class="fa fa-angle-right"></i></a></li>';
//                $pagination .= '<li><a href="'.site_url($this->setting->site_slug_category.$direct_link.$product->paged->total_pages).'">'.lang('cf24_lang_last').' <i class="fa fa-angle-double-right"></i></a></li>';
//            }
//            $pagination .= "<li><a href='javascript:;'>".lang('cf24_lang_num_of_page')." ".$product->paged->total_pages.'</a></li></ul>';
//        }
//    }
    
    private function generate_form_search(){
        $this->_data['search'] = "<div id='cse' style='width: 100%;'>Loading</div>
            <script src='//www.google.com/jsapi' type='text/javascript'></script>
            <script type='text/javascript'>
            google.load('search', '1', {language: 'vi', style: google.loader.themes.V2_DEFAULT});
            google.setOnLoadCallback(function() {
            var customSearchOptions = {};
            var orderByOptions = {};
            orderByOptions['keys'] = [{label: 'Relevance', key: ''} , {label: 'Date', key: 'date'}];
            customSearchOptions['enableOrderBy'] = true;
            customSearchOptions['orderByOptions'] = orderByOptions;
            customSearchOptions['overlayResults'] = true;
            var customSearchControl =   new google.search.CustomSearchControl('004899452839413002355:cued3ab29ns', customSearchOptions);
            customSearchControl.setResultSetSize(google.search.Search.FILTERED_CSE_RESULTSET);
            var options = new google.search.DrawOptions();
            options.setAutoComplete(true);
            customSearchControl.draw('cse', options);
            }, true);
            </script>";
        #$this->_data['search'] .= '<button type="button" onclick="window.location.href=\''.site_url('key').'\'" class="btn btn-danger" name="submit_search" id="submit_search" value="" >Tìm gì đó? Click</button>';        
        return ;
    }
    
    public function handle_footer()
    {
        $footer = new abouts();
        $footer->get_by_description('about');
        $this->_data['about'] = '<div class="footer-widget widget_text">
                                <h4 class="widgettitle2">'.$footer->all[0]->title.'</h4>			
                                <div class="textwidget"><p>'.$footer->all[0]->content.'</p>
                                </div>
                                </div>';
        $footer->get_by_description('contact');
        $this->_data['contact'] = '<div class="footer-widget widget_text">
                                <h4 class="widgettitle2">'.$footer->all[0]->title.'</h4>			
                                <div class="textwidget"><p>'.$footer->all[0]->content.'</p>
                                </div>
                                </div>'; 
        unset($footer);       
        $this->_data['copyright'] = $this->setting->site_footer_text;
        return ;
    }
    
    public function handle_image(){
        
        $this->_data['slide'] = '';
        $new = new Newz();
        $new_data = new stdClass();
        $i = 0;
        foreach($new->order_by('create',"DESC")->get(4,0)->all as $item){
            $new_data->all[$i] = new stdClass();
            $new_data->all[$i]->image = $item->image;
            foreach($item->language->include_join_fields()->get_by_id($this->lang_id) as $v){
                $new_data->all[$i]->join_title = $v->join_title;
                $new_data->all[$i]->join_slug = $v->join_slug_title;
            }
            $i+=1;
        }
        unset($new);
        $i = 0;
        foreach($new_data->all as $item){
            $_image  = preg_replace("/(".Settings::width_thumb_new."-".settings::height_thumb_new.")/",Settings::width_new."-".settings::height_new,$new_data->all[$i]->image);
            $link    = site_url($this->setting->site_prefix_news.$new_data->all[$i]->join_slug);
            $this->_data['slide'] .= '<div class="col-md-3 relative" >
                                	   <h6 class="txt-white item-top"><a class="full-width" href="'.$link.'">'.$new_data->all[$i]->join_title.'</a></h6>
                                       <a class="img-item-top" href="'.$link.'"><img src="'.base_url($_image).'" class="img-responsive "/></a>
                                     </div>';
            $i+=1;
        }
        return ;
    }
    
    public function detect_lang($value)
    {
        if(($this->input->is_ajax_request() && $this->input->is_cli_request()) && ($lang !== 'vi' && $lang !== 'en'))
        {
            return Modules::run('home/show_404');
        }        
        $i = 0;
        foreach($this->lang_content as $item){
            $name = strtolower(substr($item->name,0,2));
            if($name == $value){
                $this->session->set_userdata('cf24_session_lang_name',$item->name);
                $this->session->set_userdata('cf24_session_lang',$i);
                $this->session->set_userdata('cf24_session_language_id',$item->id);
            }
            $i+=1;
        }
        return redirect(site_url(),'location');
    }
    
    public function view()
    {
        $this->template->title('Coffee house 24/7 Administrator');
        $this->template->set_theme('bootstrap_theme');
        $this->template->set_layout('default');
        
        $this->template->set_partial('header','admin_theme/boostrap/header',$this->_data);
        //$this->template->build('dashboard/content/default');
    }
    
    public function index()
    {
        $this->handle_index_news();
        $this->handle_image();
        $meta = $this->gen_meta(array('id'=>1,'link'=>site_url(),'type'=>'category','is_static'=>true));
        $this->_data['meta']   = ($meta !== false)? $meta : "";
        $output = new stdClass();
        #
        $this->_example_output($output,true);
        $this->template->title(ucfirst($this->setting->site_name));
        $this->template->build('home/default',$this->_data);
    }
    
    public function get_menu($parent = 0,$slug = '')
    {        
        foreach($this->category->all as $item){
            if($parent == $item->parent_id ) {
                $this->add .= '<li>';
                if($item->id == 34){
                    $this->_data['index_menu_link'] = site_url($this->setting->site_slug_category.$item->join_slug);
                }
                if($slug !=''){
                    $this->add  .= '<a href="'.site_url($this->setting->site_slug_category.$slug."/".$item->join_slug).'">'.$item->join_title.'</a>';
                }else{
                    $this->add  .= '<a href="'.site_url($this->setting->site_slug_category.$item->join_slug).'">'.$item->join_title.'</a>';
                }
                $this->add  .= '<ul class="sub-menu">';
                $this->add  .= $this->get_menu($item->id,$item->join_slug);
                $this->add  .= '</ul>';
                $this->add  .= '</li>';
            }
        }
    } 
    
    public function _example_output($output = null,$index = true)
	{
	    $this->_data['output']              = $output;  
        $this->_data['is_index_admin']      = $index;      
	    #$this->template->title("Coffee house");
        $this->template->set_theme('default_theme');
        $this->template->set_layout('default');
        $this->_data['description']         = settings::settingDescription;
        $this->_data['author']              = settings::settingAuthor;
        $this->_data['keyword']             = settings::settingKeyword;
        $this->_data['copyright']           = settings::settingCopyright;
        $this->template->set_partial('header','template_theme/blocks/header',$this->_data);
        $this->template->set_partial('breadcrumb','template_theme/blocks/breadcrumb',$this->_data);        
        $this->template->set_partial('menu','template_theme/blocks/menu',$this->_data);
        if($index){
            $this->template->set_partial('slide','template_theme/blocks/slide',$this->_data);
            $this->template->set_partial('explode','template_theme/blocks/explode',$this->_data);
        }
        $this->template->set_partial('footer','template_theme/blocks/footer',$this->_data);
        #$this->template->build('home/default',$this->_data);
	}
   
    public function handle_404()
    {
        //$this->template->title('Coffee house 404 not found');
//        $this->template->set_theme('bootstrap_theme');
//        $this->template->set_layout('default');
//        $this->_data['is_index_admin']      = true;
//        $this->template->set_partial('header','admin_theme/boostrap/header',$this->_data);
//        $this->template->build('admin_theme/404_pages');
        $output = new stdClass();        
        $this->_example_output($output,false);
        $this->template->title($this->lang->line('cf24_lang_404'));
        $this->template->build('home/404_page',$this->_data);
    }
    

    
    public function timestamps($time){
        return date('d/m/Y - H:i:s',$time);
    }
    
    private function analytics(){
        $add = '<!-- Piwik -->
        <script type="text/javascript">
          var _paq = _paq || [];
          _paq.push([\'trackPageView\']);
          _paq.push([\'enableLinkTracking\']);
          (function() {
            var u="//24h.shophangdep.net/piwik/";
            _paq.push([\'setTrackerUrl\', u+\'piwik.php\']);
            _paq.push([\'setSiteId\', 1]);
            var d=document, g=d.createElement(\'script\'), s=d.getElementsByTagName(\'script\')[0];
            g.type=\'text/javascript\'; g.async=true; g.defer=true; g.src=u+\'piwik.js\'; s.parentNode.insertBefore(g,s);
          })();
        </script>
        <noscript><p><img src="//24h.shophangdep.net/piwik/piwik.php?idsite=1" style="border:0;" alt="" /></p></noscript>
        <!-- End Piwik Code -->';
        return $add;
    }
}
?>