<?php
/**
 * Jurusan Model
 */
namespace Model;

class JurusanModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	public function view() {
		$r = array();
		$run = $this->db->query("SELECT * FROM `jurusan` ORDER BY `NAMA_JURUSAN`");
		for ($i = 0; $i < count($run); $i++) {
			$srun = $this->db->query("SELECT COUNT(`ID_MAPEL`) AS `HASIL` FROM `mata_pelajaran` WHERE `ID_JURUSAN` = '" . $run[$i]->ID_JURUSAN ."'", TRUE);
			$r[] = array(
				'id' => $run[$i]->ID_JURUSAN,
				'nama' => $run[$i]->NAMA_JURUSAN,
				'del' => $srun->HASIL == 0
			);
		}
		return $r;
	}
	
	public function add() {
		foreach ($_POST as $key => $val)
			$$key = $this->db->escape_str($val);
		
		if ( ! isset($id) || ! isset($nama)) return 400;
		if (empty($id)) {
			$run = $this->db->query("INSERT INTO `jurusan` VALUES(0, '$nama')");
		} else {
			$id = intval($id);
			$run = $this->db->query("UPDATE `jurusan` SET `NAMA_JURUSAN` = '$nama' WHERE `ID_JURUSAN` = '$id'");
		}
		
		return $this->view();
	}
	
	public function delete($id) {
		$id = intval($id);
		$run = $this->db->query("DELETE FROM `jurusan` WHERE `ID_JURUSAN` = '$id'");
		return $this->view();
	}
}