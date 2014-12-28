<?php
namespace Curl_download_np;
class Curl_download{
    private $agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.111 Safari/537.36';
    public function get($url, $referer, $timeout, $header){ // header->0
        if(!isset($timeout))
            $timeout=30;
        $curl = curl_init();
        if(strstr($referer,"://")){
            curl_setopt ($curl, CURLOPT_REFERER, $referer);
        }
        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt ($curl, CURLOPT_USERAGENT, $this->agent);
        curl_setopt ($curl, CURLOPT_HEADER, (int)$header);
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $html = curl_exec ($curl);
        curl_close ($curl);
        return $html;
    }
    
    public function post($url,$pvars,$referer,$timeout){
        if(!isset($timeout))
            $timeout=30;
        $curl = curl_init();
        $post = http_build_query($pvars);
        if(isset($referer)){
            curl_setopt ($curl, CURLOPT_REFERER, $referer);
        }
        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt ($curl, CURLOPT_USERAGENT, $this->agent);
        curl_setopt ($curl, CURLOPT_HEADER, 0);
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($curl, CURLOPT_POST, 1);
        curl_setopt ($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt ($curl, CURLOPT_HTTPHEADER,
            array("Content-type: application/x-www-form-urlencoded"));
        $html = curl_exec ($curl);
        curl_close ($curl);
        return $html;
    }
    public function download($url, $path)
	{
	  # open file to write
	  $fp = fopen ($path, 'w+');
	  # start curl
	  $ch = curl_init();            
	  curl_setopt( $ch, CURLOPT_URL, $url );
	  # set return transfer to false
	  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	  curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
	  curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );  
	  curl_setopt($ch, CURLOPT_HEADER, 0);    
	  # increase timeout to download big file
	  curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
	  # write data to local file
	  curl_setopt( $ch, CURLOPT_FILE, $fp );
	  # execute curl
	  curl_exec( $ch );
	  # close curl
	  curl_close( $ch );
	  # close local file
	  fclose( $fp );

	  if (filesize($path) > 0) return true;
	}
}

?>