<?php
/**
 * Mapel Model
 */
namespace Model;

class MapelModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	public function view() {
		$r = array();
		$run = $this->db->query("SELECT a.ID_MAPEL, a.ID_JURUSAN, a.KODE_MAPEL, a.NAMA_MAPEL, b.NAMA_JURUSAN FROM mata_pelajaran  a, jurusan b WHERE a.ID_JURUSAN = b.ID_JURUSAN ORDER BY b.NAMA_JURUSAN, a.NAMA_MAPEL");
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'id' => $run[$i]->ID_MAPEL,
				'id_jurusan' => $run[$i]->ID_JURUSAN,
				'nama' => $run[$i]->NAMA_MAPEL,
				'kode' => $run[$i]->KODE_MAPEL,
				'nama_jurusan' => $run[$i]->NAMA_JURUSAN
			);
		}
		return $r;
	}
	
	public function add() {
		$a = array('id', 'kode', 'nama', 'id_jurusan', 'nama_jurusan');
		foreach ($a as $val) {
			if ( ! isset($_POST[$val]))
				$$val = '';
			else
				$$val = $this->db->escape_str($_POST[$val]);
		}
		
		$id = intval($id);
		if (empty($id)) {
			$run = $this->db->query("INSERT INTO `mata_pelajaran` VALUES(0, '$id_jurusan', '$kode', '$nama')");
		} else {
			$run = $this->db->query("UPDATE `mata_pelajaran` SET `ID_JURUSAN` = '$id_jurusan', `KODE_MAPEL` = '$kode', `NAMA_MAPEL` = '$nama' WHERE `ID_MAPEL` = '$id'");
		}
		
		return $this->view();
	}
	
	public function delete($id) {
		$id = intval($id);
		
		// hapus nilai
		$run = $this->db->query("DELETE FROM `nilai` WHERE `ID_MAPEL` = '$id'");
		
		// hapus mapel
		$run = $this->db->query("DELETE FROM `mata_pelajaran` WHERE `ID_MAPEL` = '$id'");
		
		return array();
	}
}