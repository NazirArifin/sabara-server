<?php
/**
 * Driver mysqli langsung diletakkan disini
 */
namespace Lib;
 
class Db {
	protected $conn_id,
			$queries = array(),
			$result_id,
			$query_count = 0,
			$affected_rows = 0,
			$error = '';
	
	public function __construct($params) {
		if (is_array($params)) {
			foreach ($params as $key => $val) {
				$this->$key = $val;
			}
		}
		$this->initialize();
	}
	
	public function initialize() {
		if (is_resource($this->conn_id) OR is_object($this->conn_id))
			return TRUE;
		
		$this->conn_id = $this->db_connect();
		
		if ( ! $this->conn_id) {
			$e = $this->connect_error();
			echo 'Database connection failed with message: "' . $e[1] . '"';
			return FALSE;
		}
		
		if ($this->database != '') {
			if ( ! $this->db_select()) {
				echo 'Can\'t select database';
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	public function version() {
		if ($this->_version() === FALSE) {
			return FALSE;
		}
		
		$sql = $this->_version();
		$query = $this->query($sql, TRUE);
		return $query->ver;
	}
	
	public function query($sql, $first = FALSE, $return_object = TRUE) {
		if ($sql == '') return FALSE;
		
		// Simpan query
		$this->queries[] = $sql;
		
		// Run Query
		$this->result_id = $this->simple_query($sql);
		
		if (FALSE === $this->result_id) {
			$msg = 'Database query error in: ' . $this->error_number() . ', with message: ' . $this->error_message() . ', for query: ' . $sql;
			$this->error = $msg;
			return FALSE;
		}
		
		$this->query_count++;
		
		if (preg_match('/^\\s*(insert|delete|update|replace|alter) /i', $sql)) {
			$this->affected_rows = $this->get_affected_rows();
			if (preg_match('/^\\s*(insert|replace) /i', $sql)) {
				$this->insert_id = $this->get_insert_id();
			}
			
			$r = $this->affected_rows;
		} else {
			if (is_bool($this->result_id)) return $this->result_id;
			
			$r = array();
			
			if ($return_object === TRUE) {
				$r =& $this->fetch_object();
			} else {
				$r =& $this->fetch_array();
			}
			
			$this->free_result();
			
			if ($first === TRUE) {
				$r = $r[0];
			}
		}
		
		return $r;
	}
	
	public function get_error() {
		return $this->error;
	}
	
	protected function simple_query($sql) {
		if ( ! $this->conn_id) {
			$this->initialize();
		}

		return $this->_execute($sql);
	}
	
	public function close() {
		if (is_resource($this->conn_id) OR is_object($this->conn_id)) {
			$this->_close($this->conn_id);
		}
		$this->conn_id = FALSE;
	}
	
	public function total_queries() {
		return $this->query_count;
	}
	
	protected function db_connect() {
		if ($this->port != '') {
			return @mysqli_connect($this->hostname, $this->username, $this->password, $this->database, $this->port);
		} else {
			return @mysqli_connect($this->hostname, $this->username, $this->password, $this->database);
		}
	}
	
	public function db_select() {
		return @mysqli_select_db($this->conn_id, $this->database);
	}
	
	protected function connect_error() {
		if (is_null($this->conn_id))
			return FALSE;
			
		return array(@mysqli_connect_errno(), @mysqli_connect_error());
	}
	
	protected function db_set_charset($charset, $collation) {
		return @mysqli_query($this->conn_id, "SET NAMES '" . $this->escape_str($charset) . "' COLLATE '" . $this->escape_str($collation) . "'");
	}
	
	public function escape_str($str, $like = FALSE) {
		if (is_array($str)) {
			foreach ($str as $key => $val) {
				$str[$key] = $this->escape_str($val, $like);
			}
			
			return;
		}
		
		if (function_exists('mysqli_real_escape_string') && is_object($this->conn_id)) {
			$str = @mysqli_real_escape_string($this->conn_id, $str);
		} else if (function_exists('mysql_escape_string')) {
			$str = @mysql_escape_string($str);
		} else {
			$str = addslashes($str);
		}
		
		// Escape LIKE condition
		if ($like) {
			$str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
		}
		
		return $str;
	}
	
	protected function _version() {
		return "SELECT version() AS ver";
	}
	
	protected function _execute($sql) {
		$result = @mysqli_query($this->conn_id, $sql);
		return $result;
	}
	
	protected function error_message() {
		return @mysqli_error($this->conn_id);
	}

	protected function error_number() {
		return @mysqli_errno($this->conn_id);
	}
	
	public function get_affected_rows() {
		return @mysqli_affected_rows($this->conn_id);
	}
	
	public function get_insert_id() {
		return @mysqli_insert_id($this->conn_id);
	}
	
	protected function &fetch_object() {
		if (empty($this->result_id)) return false;
		while($row = @mysqli_fetch_object($this->result_id)) {
			$r[] = $row;
		}
		return $r;
	}
	
	protected function &fetch_array() {
		if (empty($this->result_id)) return false;
		while($row = @mysqli_fetch_array($this->result_id, MYSQLI_BOTH)) {
			$r[] = $row;
		}
		return $r;
	}
	
	protected function free_result() {
		if (empty($this->result_id)) return;
		@mysqli_free_result($this->result_id);
	}
	
	function _close($conn_id) {
		@mysqli_close($conn_id);
	}
}