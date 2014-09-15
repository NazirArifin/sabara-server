<?php
namespace Model;

set_time_limit(0);

class ModelBase {    
    /**
	 * Parent Constructor
	 */
	protected function __construct() { 
		require_once 'lib/Loader.php';
		$loader = \Lib\Loader::get_instance();
		if ( ! isset($this->db))
			$this->db = $loader->database();
	}
	
	/**
	 * Persiapkan $_POST, jika tidak ada diisi string kosong
	 */
	protected function prepare_post($d = array()) {
		$r = array();
		foreach ($d as $val) {
			if ( ! isset($_POST[$val])) {
				$r[$val] = '';
			} else {
				$r[$val] = $_POST[$val];
			}
		}
		return $r;
	}
	
	/**
	 * Persiapkan $_GET, jika tidak ada diisi string kosong
	 */
	protected function prepare_get($d = array()) {
		$r = array();
		foreach ($d as $val) {
			if ( ! isset($_GET[$val])) {
				$r[$val] = '';
			} else {
				$r[$val] = $_GET[$val];
			}
		}
		return $r;
	}
}