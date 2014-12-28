<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['main'] = 'main';
$route['home'] = 'home';
$route['uc'] = 'home/hot';
$route['stop_spam_check'] = 'home/stop_spam_check';
$route['load-more-comment'] = 'home/get_comment_ajax';
//$route['online/info']        = 'home/handle_counter_ajax';
//$route['online']        = 'home/handle_counter_ajax';
$route['lang/([\w\-\.]+)'] = 'home/detect_lang/$1';
$route['c/(.*)'] = 'home/category/$1/$2/$3';
$route['p-(.*)'] = 'home/detail/$1';
$route['n-(.*)'] = 'home/detail/$1';
$route['key']    = 'home/search';
$route['test']    = 'home/test';
$route['admin_theme'] = 'admin_theme';
$route['dashboard'] = 'dashboard';
$route['template_theme'] = 'template_theme';
$route['dashboard/categories'] = 'dashboard/categories/view';
$route['dashboard/products'] = 'dashboard/products/view';
$route['dashboard/configs'] = 'dashboard/configs';
$route['dashboard/news'] = 'dashboard/news/view';
$route['dashboard/([\w]+)/ajax_list/delete_selection'] = 'dashboard/delete_selection';
$route['login'] = 'login';
$route['auth'] = 'administrator/auth';
$route['auth/(.*)'] = 'administrator/auth/$1';
$route['default_controller'] = "home";
$route['404_override'] = '';
/* End of file routes.php */
/* Location: ./application/config/routes.php */