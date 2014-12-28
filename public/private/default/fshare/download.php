<?php 
include('start.php');

function _login()
{
	global $html, $curl, $config;
	
	#preg_match('#<input type="hidden" value="(.*?)" name="fs_csrf" />#',$html,$fs_csrf);
	$d = '5b506e9f54f0a179196d9fe120b1e9398978c684';
	$data = 'fs_csrf=' . $d . '&LoginForm%5Bemail%5D=' . urlencode($config['id']). '&LoginForm%5Bpassword%5D=' . urlencode($config['password']) . '&LoginForm%5BrememberMe%5D=0&LoginForm%5BrememberMe%5D=1&yt0=%C4%90%C4%83ng+nh%E1%BA%ADp';
	$curl->post('https://www.fshare.vn/login',$data);
}

$html = $curl->get('http://www.fshare.vn/file/9DQC8UAMZSSC');
if(!preg_match('#<a style="cursor: pointer;color: \#999999;" title="(.*?)"#', $html, $acc))
{
	// chua dang nhap
	_login();
	
}

$link = $curl->get('https://www.fshare.vn/download/index');

echo $link;