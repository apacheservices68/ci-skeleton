<?php

/**
 * @author LongL
 * @copyright 2014
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Excel library for Code Igniter applications
* Based on: Derek Allard, Dark Horse Consulting, www.darkhorse.to, April 2006
* Tweaked by: Moving.Paper June 2013
*/
class Export{
    
    public function __construct(){
        
    }
    
    function to_excel($array, $filename='out')
    {
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename='.$filename.'.xls');
        //$string_to_export = "\xFF\xFE" .mb_convert_encoding($string_to_export, 'UTF-16LE', 'UTF-8');    
        // Filter all keys, they'll be table headers
        $h = array();
        foreach($array as $row)
            foreach($row as $key=>$val)
                if(!in_array($key, $h))
                    $h[] = $key;
    
        echo '<table><tr>';
        foreach($h as $key) {
            $key = ucwords($key);
            echo '<th>'.$key.'</th>';
        }
        echo '</tr>';
    
        foreach($array as $val)
            $this->_writeRow($val, $h);
    
        echo '</table>';
    }
    
    function _writeRow($row, $h, $isHeader=false) {
        echo '<tr>';
        foreach($h as $r) {
            if($isHeader)
                echo '<th>'.utf8_decode(@$row[$r]).'</th>';
            else
                echo '<td>'.utf8_decode(@$row[$r]).'</td>';
        }
        echo '</tr>';
    }

}


?>