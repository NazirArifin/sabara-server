<?php
/**
 * Setting Model
 */
namespace Model;

class SettingModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	public function view_angkatan() {
		$r = array();
		$run = $this->db->query("SELECT * FROM `angkatan` ORDER BY `NAMA_ANGKATAN`");
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'id' => $run[$i]->ID_ANGKATAN,
				'nama' => $run[$i]->NAMA_ANGKATAN,
				'status' => $run[$i]->STATUS_ANGKATAN,
				'status_label' => ($run[$i]->STATUS_ANGKATAN == 1 ? 'AKTIF' : 'NONAKTIF')
			);
		}
		return $r;
	}
	
	public function add_angkatan() {
		$r = array('id', 'nama', 'status');
		foreach ($r as $val) {
			if (isset($_POST[$val])) $$val = $this->db->escape_str($_POST[$val]);
			else $$val = '';
		}
		$id = intval($id);
		// mode add
		if (empty($id)) {
			$run = $this->db->query("SELECT COUNT(`ID_ANGKATAN`) AS `HASIL` FROM `angkatan` WHERE `NAMA_ANGKATAN` = '$nama'", TRUE);
			if ($run->HASIL > 0) return 400;
			
			if ($status == 1) {
				$run = $this->db->query("UPDATE `angkatan` SET `STATUS_ANGKATAN` = '0'");
				$run = $this->db->query("INSERT INTO `angkatan` VALUES(0, '$nama', '1')");
			} else {
				$run = $this->db->query("INSERT INTO `angkatan` VALUES(0, '$nama', '0')");
			}
			
		// mode edit
		} else {
			if ($status == 1)
				$run = $this->db->query("UPDATE `angkatan` SET `STATUS_ANGKATAN` = '0'");
			$run = $this->db->query("UPDATE `angkatan` SET `NAMA_ANGKATAN` = '$nama', `STATUS_ANGKATAN` = '$status' WHERE `ID_ANGKATAN` = '$id'");
		}
		
		return $this->view_angkatan();
	}
	
	public function delete_angkatan($id) {
		$id = intval($id);
		$run = $this->db->query("SELECT `ID_SISWA` FROM `siswa` WHERE `ID_ANGKATAN` = '$id'");
		$siswa = array();
		for ($i = 0; $i < count($run); $i++)
			$siswa[] = $run[$i]->ID_SISWA;
			
		foreach ($siswa as $val) {
			// hapus rerata
			$run = $this->db->query("DELETE FROM `rerata_nilai` WHERE `ID_SISWA` = '$val'");
			
			// hapus nilai
			$run = $this->db->query("DELETE FROM `nilai` WHERE `ID_SISWA` = '$val'");
			
			// hapus siswa
			$run = $this->db->query("DELETE FROM `siswa` WHERE `ID_SISWA` = '$val'");
		}
		
		$run = $this->db->query("DELETE FROM `angkatan` WHERE `ID_ANGKATAN` = '$id'");
		return array();
	}
	
	public function view_setting() {
		$r = parse_ini_file('config/setting.ini');
		return $r;
	}
	
	public function save_setting() {
		$file = file('config/setting.ini');
		$data = parse_ini_file('config/setting.ini');
		
		for ($i = 0; $i < count($file); $i++) {
			$file[$i] = trim($file[$i]);
		}
		
		$r = array('pangkat', 'maxiterasi', 'zeta');
		foreach ($r as $val) {
			if ( ! isset($_POST[$val]))
				return 400;
			else {
				if ( ! is_numeric($_POST[$val])) return 400;
				$$val = $_POST[$val];
			}
		}
		
		$any = FALSE;
		$zeta = '"' . $zeta . '"';
		
		for ($i = 1, $j = 0; $i <= count($r); $i++, $j++) {
			$str = $r[$j] . ' = ' . $_POST[$r[$j]];
			if (trim($file[$i]) != $str) {
				$any = TRUE;
				$file[$i] = $str;
			}
		}
		if ($any) {
			$fp = @fopen('config/setting.ini', 'w');
			@fwrite($fp, implode(PHP_EOL, $file));
			@fclose($fp);
		}
		return $this->view_setting();
	}
}