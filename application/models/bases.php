<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bases extends Datamapper{
    protected $table ;
    function __construct($id = NULL)
	{
		parent::__construct($id);
    }
}
?>