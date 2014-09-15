<?php
/**
 * Petugas Model
 */
namespace Model;

set_time_limit(0);

class PetugasModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Periksa username ada atau tidak
	 */
	public function username_exists($u = '') {
		$run = $this->db->query("SELECT COUNT(`ID_USER`) AS `HASIL` FROM `user` WHERE `USERNAME_USER` = '$u'", TRUE);
		return $run->HASIL > 0;
	}
	
	/**
	 * View petugas
	 */
	public function view($ispetugas = TRUE) {
		$tabel = ($ispetugas ? 'petugas' : 'koordinator');
		$btabel = ($ispetugas ? 'PETUGAS' : 'KOORDINATOR');
		
		$r = array(
			$tabel => array(), 'numpage' => 0
		);
		$d = $this->prepare_get(array('nama', 'unit', 'cpage'));
		$dataperpage = 15;
		extract($d);
		
		if (empty($unit)) return $r;
		$where = array();
		if ( ! empty($nama)) $where[] = "a.NAMA_{$btabel} LIKE '%" . $this->db->escape_str($nama) . "%'";
		if ($unit != '0') $where[] = "b.KODE_UNIT = '$unit'";
		
		// cari total
		$query = "SELECT COUNT(a.ID_{$btabel}) AS HASIL FROM $tabel a, unit b, user c WHERE a.ID_UNIT = b.ID_UNIT AND a.ID_USER = c.ID_USER AND c.STATUS_USER = '1'";
		if ( ! empty($where)) $query .= " AND " . implode(" AND ", $where);
		$run = $this->db->query($query, TRUE);
		$total = $run->HASIL;
		$r['numpage'] = ceil($total / $dataperpage);
		
		// cari data
		$query = "SELECT a.ID_{$btabel}, a.NAMA_{$btabel}, b.ID_UNIT, b.KODE_UNIT, b.NAMA_UNIT, c.USERNAME_USER FROM $tabel a, unit b, user c WHERE a.ID_UNIT = b.ID_UNIT AND a.ID_USER = c.ID_USER AND a.ID_USER = c.ID_USER AND c.STATUS_USER = '1'";
		if ( ! empty($where)) $query .= " AND " . implode(" AND ", $where);
		$start = $cpage * $dataperpage;
		$query .= " ORDER BY b.ID_UNIT, a.NAMA_{$btabel} LIMIT $start, $dataperpage";
		$run = $this->db->query($query);
		
		for ($i = 0; $i < count($run); $i++) {
			$r['user'][] = array(
				'id' => ($ispetugas ? $run[$i]->ID_PETUGAS : $run[$i]->ID_KOORDINATOR),
				'nama' => ($ispetugas ? $run[$i]->NAMA_PETUGAS : $run[$i]->NAMA_KOORDINATOR),
				'nama_unit' => $run[$i]->NAMA_UNIT,
				'id_unit' => $run[$i]->ID_UNIT,
				'kode_unit' => $run[$i]->KODE_UNIT,
				'username' => $run[$i]->USERNAME_USER,
				'password' => ''
			);
		}
		
		return $r;
	}
	
	/**
	 * Data petugas berdasarkan unit
	 */
	public function get_petugas_by_unit($id) {
		$id = intval($id);
		$r = array();
		$run = $this->db->query("SELECT * FROM `petugas` WHERE `ID_UNIT` = '$id' ORDER BY `NAMA_PETUGAS`");
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'id' => $run[$i]->ID_PETUGAS,
				'nama' => $run[$i]->NAMA_PETUGAS
			);
		}
		return $r;
	}
	
	/**
	 * Operasi dengan petugas
	 */
	public function operate_petugas($ispetugas = TRUE) {
		$tabel = ($ispetugas ? 'petugas' : 'koordinator');
		$btabel = ($ispetugas ? 'PETUGAS' : 'KOORDINATOR');
		
		$post = $this->prepare_post(array('id', 'nama', 'id_unit', 'username', 'password', 'password2'));
		extract($post);
		$nama = $this->db->escape_str($nama);
		$username = $this->db->escape_str($username);
		$id = intval($id);
		$id_unit = intval($id_unit);
		
		// jika id kosong berarti insert
		if (empty($id)) {
			// cek username
			if ($this->username_exists($username)) return FALSE;
			
			// masukkan ke user
			$ins = $this->db->query("INSERT INTO `user` VALUES(0, '" . ($ispetugas ? '3' : '2') . "', '$username', '" . crypt($password, 'abmpmk') . "', '1')");
			$id = $this->db->get_insert_id();
			// masukkan ke petugas
			$ins = $this->db->query("INSERT INTO `{$tabel}` VALUES(0, '$id', '$id_unit', '$nama')");
		} else {
			// edit password
			if ( ! empty($password)) {
				$run = $this->db->query("SELECT `ID_USER` FROM `{$tabel}` WHERE `ID_{$btabel}` = '$id'", TRUE);
				$upd = $this->db->query("UPDATE `user` SET `PASSWORD_USER` = '" . crypt($password, 'abmpmk') . "' WHERE `ID_USER` = '" . $run->ID_USER . "'");
				
			// edit profil
			} else {
				$run = $this->db->query("SELECT `ID_USER`, `NAMA_{$btabel}` FROM `{$tabel}` WHERE `ID_{$btabel}` = '$id'", TRUE);
				$upd = $this->db->query("UPDATE `{$tabel}` SET `NAMA_{$tabel}` = '$nama' WHERE `ID_{$tabel}` = '$id'");
				$srun = $this->db->query("UPDATE `user` SET `USERNAME_USER` = '$username' WHERE `ID_USER` = '" . $run->ID_USER . "'");
			}
		}
		
		return array();
	}
	
	/**
	 * Hapus petugas
	 */
	public function delete_petugas($id, $ispetugas = TRUE) {
		$id = intval($id);
		if ($ispetugas)
			$run = $this->db->query("SELECT `ID_USER` FROM `petugas` WHERE `ID_PETUGAS` = '$id'", TRUE);
		else
			$run = $this->db->query("SELECT `ID_USER` FROM `koordinator` WHERE `ID_KOORDINATOR` = '$id'", TRUE);
			
		$run = $this->db->query("UPDATE `user` SET `STATUS_USER` = '0' WHERE `ID_USER` = '" . $run->ID_USER . "'");
	}
	
	/**
	 * view admin
	 */
	public function view_admin($isadmin = TRUE) {
		$r = array();
		$run = $this->db->query("SELECT `ID_USER`, `USERNAME_USER` FROM `user` WHERE `STATUS_USER` = '1' AND `ID_LEVELUSER` = '"  . ($isadmin ? '1' : '4') . "' AND `ID_USER` != '1'");
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'id' => $run[$i]->ID_USER,
				'username' => $run[$i]->USERNAME_USER,
				'password' => '',
				'password2' => ''
			);
		}
		return $r;
	}
	
	/**
	 * Operasi dengan admin
	 */
	public function operate_admin($isadmin = TRUE) {
		$post = $this->prepare_post(array('id', 'username', 'password', 'password2'));
		extract($post);
		$id = intval($id);
		$username = $this->db->escape_str($username);
		
		// insert
		if (empty($id)) {
			if ($this->username_exists($username)) return FALSE;
			$run = $this->db->query("INSERT INTO `user` VALUES(0, '" . ($isadmin ? '1' : '4') . "', '$username', '" . crypt($password, 'abmpmk') ."', '1')");
		// update
		} else {
			// edit password
			if ( ! empty($password)) {
				$upd = $this->db->query("UPDATE `user` SET `PASSWORD_USER` = '" . crypt($password, 'abmpmk') . "' WHERE `ID_USER` = '$id'");
			// edit profil
			} else {
				$srun = $this->db->query("UPDATE `user` SET `USERNAME_USER` = '$username' WHERE `ID_USER` = '$id'");
			}
		}
	}
	
	/**
	 * Hapus admin
	 */
	public function delete_admin($id) {
		$id = intval($id);
		$run = $this->db->query("DELETE FROM `user` WHERE `ID_USER` = '$id'");
	}
	
	/**
	 * Ubah password
	 */
	public function change_password() {
		$post = $this->prepare_post(array('pass1', 'pass2', 'id'));
		extract($post);
		$id = floatval($id);
		
		// update
		$run = $this->db->query("UPDATE `user` SET `PASSWORD_USER` = '" . crypt($pass1, 'abmpmk') . "' WHERE `ID_USER` = '$id'");
	}
}