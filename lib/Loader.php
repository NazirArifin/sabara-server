<?php
/*!
 * file ini berperan sebagai loader
 * digunakan untuk meload helper, file, dan model
 */
namespace Lib;

class Loader {
	private function __construct() {}
	private static $instance;
	public static function get_instance() {
		if (is_null(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	
	/**
	 * Load controller dan router
	 */
	public function controller() {
		// router dengan Slim
		require 'lib/Slim/Slim.php';
		\Slim\Slim::registerAutoloader();
		$this->app = new \Slim\Slim();
		
		$this->load('helper', 'controller');
		$app =& $this->app;
		$ctr = $this;
		
		// controller file
		foreach (scandir('controller') as $file) {
			if (is_file('controller/' . $file))
				require('controller/' . $file);
		}
		
		$this->app->run();
	}
	
	/**
	 * Load database
	 */
	public function database() {
		static $host, $user, $pass, $dbnm, $drvr, $port;
		static $conn;
		if (empty($host)) {
			require 'config/dbconfig.php';
			$host = $dbconfig_host;
			$user = $dbconfig_username;
			$pass = $dbconfig_password;
			$dbnm = $dbconfig_database;
			$drvr = $dbconfig_driver;
			$port = $dbconfig_port;
		}
		
		if (is_null($conn)) {
			$data = array(
				'hostname' => $host,
				'username' => $user,
				'password' => $pass,
				'database' => $dbnm,
				'port' => $port
			);
			include 'lib/db_' . strtolower($drvr) . '.php';
			return $conn = new Db($data);
		}
		return $conn;
	}
	
	/**
	 * Load interface
	 */
	public function load($type, $param) {
		switch ($type) {
			case 'model':
				$m = $this->model($param);
				$this->$m[0] = $m[1];
				break;
			case 'helper':
				$this->helper($param);
				break;
			case 'file':
				$this->file($param);
				break;
		}
	}
	
	/**
	 * Load model
	 */
	protected function model($m) {
		$model = 'model/' . $m . '_model.php';
		if ( ! is_file($model)) {
			$this->app->halt(500, 'Cant load Model');
			$this->app->stop();
		}
		require_once 'model/ModelBase.php';
		require $model;
		$class = '\\Model\\' . ucfirst($m) . 'Model';
		return array(ucfirst($m) . 'Model', new $class);
	}
	
	/**
	 * Load helper
	 */
	protected function helper($h) {
		require_once 'helper/' . $h . '_helper.php';
	}
	
	/**
	 * Load file
	 */
	protected function file($f) {
		require_once $f;
	}
}