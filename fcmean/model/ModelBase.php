<?php
namespace Model;

class ModelBase {    
    protected function __construct() { 
		require_once 'lib/Loader.php';
		$loader = \Lib\Loader::get_instance();
		if ( ! isset($this->db))
			$this->db = $loader->database();
	}
	
}