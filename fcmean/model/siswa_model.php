<?php
/**
 * Siswa Model
 */
namespace Model;

class SiswaModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	public function view() {
		$g = array('searchin', 'query', 'curpage');
		foreach ($g as $val) {
			if ( ! isset($_GET[$val]))
				$$val = '';
			else
				$$val = $this->db->escape_str($_GET[$val]);
		}
		$curpage = intval($curpage);
		
		// cari angkatan
		$run = $this->db->query("SELECT `ID_ANGKATAN`, `NAMA_ANGKATAN` FROM `angkatan` WHERE `STATUS_ANGKATAN` = '1'", TRUE);
		$id_angk = $run->ID_ANGKATAN;
		$nama_angk = $run->NAMA_ANGKATAN;
		
		$where = '';
		if ( ! empty($query)) {
			switch ($searchin) {
				case 1:
					$where = "`NIS_SISWA` LIKE '%" . $query . "%'"; break;
				case 2:
					$where = "`NAMA_SISWA` LIKE '%" . $query . "%'"; break;
				case 3:
					$where = "`ALAMAT_SISWA` LIKE '%" . $query . "%'"; break;
			}
		}
		
		// hitung jumah
		$dataperpage = 15;
		$t = "SELECT COUNT(`ID_SISWA`) AS `HASIL` FROM `siswa` WHERE `ID_ANGKATAN` = '$id_angk'" . ( ! empty($where) ? " AND $where" : '');
		$run = $this->db->query($t, TRUE);
		$total = ceil($run->HASIL / $dataperpage);
		
		$q = "SELECT * FROM `siswa` WHERE `ID_ANGKATAN` = '$id_angk'";
		if ( ! empty($where)) $q .= " AND $where";
		$start = $curpage * $dataperpage;
		
		$q .= " ORDER BY `NIS_SISWA` LIMIT $start, $dataperpage";
		$run = $this->db->query($q);
		
		$r = array();
		for ($i = 0; $i < count($run); $i++) {
			list($y, $m, $d) = explode('-', $run[$i]->TGL_LHR_SISWA);
			$tgl = "$d/$m/$y";
			$r[] = array(
				'id' => $run[$i]->ID_SISWA,
				'nis' => $run[$i]->NIS_SISWA,
				'kelas' => $run[$i]->KELAS_SISWA,
				'nama' => $run[$i]->NAMA_SISWA,
				'tgl' => $tgl,
				'tmp' => $run[$i]->TMP_LHR_SISWA,
				'jk' => $run[$i]->JK_SISWA,
				'alamat' => $run[$i]->ALAMAT_SISWA,
				'ortu' => $run[$i]->ORTU_SISWA,
				'angkatan' => $nama_angk
			);
		}
		return array(
			'siswa' => $r,
			'curpage' => $curpage,
			'numpage' => $total
		);
	}
	
	public function add() {
		if (isset($_FILES['file'])) {
			$file = 'model/siswa.csv';
			@move_uploaded_file($_FILES['file']['tmp_name'], $file);
			if ( ! is_file($file)) return 401;
			
			extract($_POST);
			$data = file($file);
			
			// dapatkan angkatan sekarang
			$run = $this->db->query("SELECT `ID_ANGKATAN` FROM `angkatan` WHERE `STATUS_ANGKATAN` = '1'", TRUE);
			$angkatan = $run->ID_ANGKATAN;
			
			for ($i = 0; $i < count($data); $i++) {
				$row = str_getcsv($data[$i], $delimiter);
				if ( ! is_string($row[0]) || strlen($row[0]) == 0) continue;
				if (count($row) != 7) continue;
				list($nis, $nama, $tmp, $tgl, $jk, $alamat, $ortu) = $row;
				
				// cari apa nis sudah ada
				$run = $this->db->query("SELECT COUNT(`ID_SISWA`) AS `HASIL` FROM `siswa` WHERE `NIS_SISWA` = '$nis'", TRUE);
				if ($run->HASIL == 0) {
					list($d, $m, $y) = explode('/', $tgl);
					$tgl = $y . '-' . $m . '-' . $d;
					$jk = strtoupper($jk);
					$run = $this->db->query("INSERT INTO `siswa` VALUES(0, '$angkatan', '$nis', '$kelas', '$nama', '$tgl', '$tmp', '$jk', '$alamat', '$ortu')");
				}
			}
			
			@unlink($file);
			return 200;
			
		} else {
			$a = array('id', 'nis', 'kelas', 'nama', 'tmp', 'tgl', 'jk', 'alamat', 'ortu');
			foreach ($a as $val) {
				if ( ! isset($_POST[$val]))
					$$val = '';
				else
					$$val = $this->db->escape_str($_POST[$val]);
			}
			
			if (empty($id)) {
				if (empty($tgl)) $tgl = date('d/m/Y');
				list($d, $m, $y) = explode('/', $tgl);
				
				$tgl = $y . '-' . $m . '-' . $d;
				
				// dapatkan angkatan sekarang
				$run = $this->db->query("SELECT `ID_ANGKATAN` FROM `angkatan` WHERE `STATUS_ANGKATAN` = '1'", TRUE);
				$angkatan = $run->ID_ANGKATAN;
				
				$run = $this->db->query("INSERT INTO `siswa` VALUES(0, '$angkatan', '$nis', '$kelas', '$nama', '$tgl', '$tmp', '$jk', '$alamat', '$ortu')");
				return 201;
				
			} else {
				list($d, $m, $y) = explode('/', $tgl);
				$tgl = $y . '-' . $m . '-' . $d;
				
				$id = floatval($id);
				$run = $this->db->query("SELECT * FROM `siswa` WHERE `ID_SISWA` = '$id'", TRUE);
				$upd = array();
				
				if ($nis != $run->NIS_SISWA) $upd[] = "`NIS_SISWA` = '$nis'";
				if ($kelas != $run->KELAS_SISWA) $upd[] = "`KELAS_SISWA` = '$kelas'";
				if ($nama != $run->NAMA_SISWA) $upd[] = "`NAMA_SISWA` = '$nama'";
				if ($tgl != $run->TGL_LHR_SISWA) $upd[] = "`TGL_LHR_SISWA` = '$tgl'";
				if ($tmp != $run->TMP_LHR_SISWA) $upd[] = "`TMP_LHR_SISWA` = '$tmp'";
				if ($jk != $run->JK_SISWA) $upd[] = "`JK_SISWA` = '$jk'";
				if ($alamat != $run->ALAMAT_SISWA) $upd[] = "`ALAMAT_SISWA` = '$alamat'";
				if ($ortu != $run->ORTU_SISWA) $upd[] = "`ORTU_SISWA` = '$ortu'";
				
				if ( ! empty($upd)) {
					$run = $this->db->query("UPDATE `siswa` SET " .  implode(', ', $upd) . " WHERE `ID_SISWA` = '$id'");
				}
			}
		}
		
		return 200;
	}
	
	public function delete($id) {
		$id = floatval($id);
		// hapus nilai
		$run = $this->db->query("DELETE FROM `nilai` WHERE `ID_SISWA` = '$id'");
		
		// hapus rerata nilai
		$run = $this->db->query("DELETE FROM `rerata_nilai` WHERE `ID_SISWA` = '$id'");
		
		// hapus siswa
		$run = $this->db->query("DELETE FROM `siswa` WHERE `ID_SISWA` = '$id'");
		
		return array();
	}
}