<?php
/**
 * Rbm Model
 */
namespace Model;

set_time_limit(0);

class RbmModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	public function view_rbm($id) {
		$id = intval($id);
		$r = array();
		
		$run = $this->db->query("SELECT * FROM `rbm` WHERE `ID_PETUGAS` = '$id' AND `TANGGAL_RBM` = '" . date('j') . "'");
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'id_rbm' => $run[$i]->ID_RBM,
				'nama_rbm' => $run[$i]->NAMA_RBM
			);
		}
		return $r;
	}
	
	public function view_rincian_rbm($id) {
		$id = intval($id);
		$r = array();
		$run = $this->db->query("SELECT a.ID_PELANGGAN, a.ID_RBM, a.URUT_RINCIAN_RBM, c.TARIF_PELANGGAN, c.KDTARIF_PELANGGAN, c.ID_KODEPROSES FROM rincian_rbm a, rbm b, pelanggan c WHERE b.ID_PETUGAS = '$id' AND a.ID_RBM = b.ID_RBM AND c.ID_PELANGGAN = a.ID_PELANGGAN AND b.TANGGAL_RBM = '" . date('j') . "' ORDER BY a.URUT_RINCIAN_RBM");
		
		for ($i = 0; $i < count($run); $i++) {
			//tarif p3 dan kdtarif 3 atau 2 dan kdproses AMR (5,6)
			if (trim(strtolower($run[$i]->TARIF_PELANGGAN)) == 'p3') continue;
			if ($run[$i]->KDTARIF_PELANGGAN == 2 OR $run[$i]->KDTARIF_PELANGGAN == 3) continue;
			if ($run[$i]->ID_KODEPROSES == 5 OR $run[$i]->ID_KODEPROSES == 6) continue;
			
			$r[] = array(
				'id_rbm' => $run[$i]->ID_RBM,
				'id_pel' => $run[$i]->ID_PELANGGAN,
				'no_urut' => $run[$i]->URUT_RINCIAN_RBM
			);
		}
		return $r;
	}
	
	public function view_unit_rbm($id) {
		$id = intval($id);
		$r = array();
		$run = $this->db->query("SELECT a.ID_RBM, a.NAMA_RBM FROM rbm a, petugas b, unit c WHERE a.ID_PETUGAS = b.ID_PETUGAS AND b.ID_UNIT = c.ID_UNIT AND c.ID_UNIT = '$id' ORDER BY a.NAMA_RBM");
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'id' => $run[$i]->ID_RBM,
				'nama' => $run[$i]->NAMA_RBM
			);
		}
		return $r;
	}
	
	/**
	 * Update rbm
	 */
	public function operate_rbm() {
		$post = $this->prepare_post(array('id', 'petugas', 'tanggal'));
		
		extract($post);
		$id = floatval($id);
		$petugas = floatval($petugas);
		$tanggal = intval($tanggal);
		
		$run = $this->db->query("SELECT `ID_PETUGAS`, `NAMA_RBM`, `TANGGAL_RBM` FROM `rbm` WHERE `ID_RBM` = '$id'", TRUE);
		$namarbm = $run->NAMA_RBM;
		
		$upd = array();
		if ($petugas != $run->ID_PETUGAS) $upd[] = "`ID_PETUGAS` = '$petugas'";
		if ($tanggal != $run->TANGGAL_RBM) $upd[] = "`TANGGAL_RBM` = '$tanggal'";
		
		if ( ! empty($upd))
			$upd = $this->db->query("UPDATE `rbm` SET " . implode(", ", $upd) . " WHERE `ID_RBM` = '$id'");
		
		// cari di petugas
		$idpetugas = $petugas;
		$srun = $this->db->query("SELECT `NAMA_PETUGAS` FROM `petugas` WHERE `ID_PETUGAS` = '$idpetugas'", TRUE);
		$petugas = $srun->NAMA_PETUGAS;
		
		// cari total pelanggan
		$srun = $this->db->query("SELECT COUNT(`ID_RINCIAN_RBM`) AS `HASIL` FROM `rincian_rbm` WHERE `ID_RBM` = '$id'", TRUE);
		$jumlah = $srun->HASIL;
			
		// cari daya
		$srun = $this->db->query("SELECT SUM(a.DAYA_PELANGGAN) AS TOTAL FROM pelanggan a, rincian_rbm b WHERE b.ID_RBM = '$id' AND a.ID_PELANGGAN = b.ID_PELANGGAN", TRUE);
		
		return array(
			'id' => $id,
			'nama' => $namarbm,
			'hari' => substr($namarbm, -1, 1),
			'tanggal' => $tanggal,
			'petugas' => $idpetugas,
			'nama_petugas' => $petugas,
			'total' => $jumlah,
			'daya' => number_format($srun->TOTAL, 0, ',', '.')
		);
	}
	
	/**
	 * load daftar dpm
	 */
	public function get_dpm() {
		$get = $this->prepare_get(array('unit', 'rbm', 'blth'));
		extract($get);
		$unit = intval($unit);
		$rbm = floatval($rbm);
		$blth = intval($blth);
		
		// baca bulantahun
		$run = $this->db->query("SELECT `NAMA_BLTH` FROM `blth` WHERE `ID_BLTH` = '$blth'", TRUE);
		$bln = intval(substr($run->NAMA_BLTH, 0, 2));
		$thn = intval(substr($run->NAMA_BLTH, 2, 4));
		$bln--;
		if ($bln == 0) {
			$bln = 12; $thn--;
		}
		$bln = str_pad($bln, 2, '0', STR_PAD_LEFT);
		$run = $this->db->query("SELECT `ID_BLTH` FROM `blth` WHERE `NAMA_BLTH` = '" . ($bln . $thn) ."'", TRUE);
		$blth0 = $run->ID_BLTH;
		
		// rbm
		$run = $this->db->query("SELECT `NAMA_RBM` FROM `rbm` WHERE `ID_RBM` = '$rbm'", TRUE);
		$nama_rbm = $run->NAMA_RBM;
		
		$r = array();
		$run = $this->db->query("SELECT a.ID_PELANGGAN, a.NAMA_PELANGGAN, a.ALAMAT_PELANGGAN, a.KODUK_PELANGGAN, b.NAMA_GARDU, a.TARIF_PELANGGAN, a.DAYA_PELANGGAN FROM pelanggan a, gardu b, rincian_rbm c WHERE a.ID_GARDU = b.ID_GARDU AND a.ID_PELANGGAN = c.ID_PELANGGAN AND c.ID_RBM = '$rbm' ORDER BY c.URUT_RINCIAN_RBM");
		for ($i = 0; $i < count($run); $i++) {
			$row = $run[$i];
			$idpel = $row->ID_PELANGGAN;
			
			// cari di bacameter lalu
			$srun = $this->db->query("SELECT `ID_BACAMETER`, `LWBP_BACAMETER`, `WBP_BACAMETER`, `KVARH_BACAMETER` FROM `bacameter` WHERE `ID_PELANGGAN` = '$idpel' AND `ID_BLTH` = '$blth0'", TRUE);
			if (empty($srun)) {
				$lwbp0 = $wbp0 = $kvarh0 = $idbacameter0 = 0;
			} else {
				$lwbp0 = $srun->LWBP_BACAMETER;
				$wbp0 = $srun->WBP_BACAMETER;
				$kvarh0 = $srun->KVARH_BACAMETER;
				$idbacameter0 = $srun->ID_BACAMETER;
			}
			
			// cari di koreksi
			$srun = $this->db->query("SELECT `LWBP_KOREKSI`, `WBP_KOREKSI`, `KVARH_KOREKSI` FROM `koreksi` WHERE `ID_BACAMETER` = '$idbacameter0' ORDER BY `TANGGAL_KOREKSI` DESC LIMIT 0, 1", TRUE);
			if ( ! empty($srun)) {
				$lwbp0 = $srun->LWBP_KOREKSI;
				$wbp0 = $srun->WBP_KOREKSI;
				$kvarh0 = $srun->KVARH_KOREKSI;
			}
			
			// cari di bacameter sekarang
			$srun = $this->db->query("SELECT `ID_BACAMETER`, `LWBP_BACAMETER`, `WBP_BACAMETER`, `KVARH_BACAMETER` FROM `bacameter` WHERE `ID_PELANGGAN` = '$idpel' AND `ID_BLTH` = '$blth'", TRUE);
			if (empty($srun)) {
				$lwbp = $wbp = $kvarh = $idbacameter = 0;
			} else {
				$lwbp = $srun->LWBP_BACAMETER;
				$wbp = $srun->WBP_BACAMETER;
				$kvarh = $srun->KVARH_BACAMETER;
				$idbacameter = $srun->ID_BACAMETER;
			}
			
			// cari di koreksi jika ada
			$srun = $this->db->query("SELECT `LWBP_KOREKSI`, `WBP_KOREKSI`, `KVARH_KOREKSI` FROM `koreksi` WHERE `ID_BACAMETER` = '$idbacameter' ORDER BY `TANGGAL_KOREKSI` DESC LIMIT 0, 1", TRUE);
			if ( ! empty($srun)) {
				$lwbp = $srun->LWBP_KOREKSI;
				$wbp = $srun->WBP_KOREKSI;
				$kvarh = $srun->KVARH_KOREKSI;
			}
			
			$r[] = array(
				'idpel' => $idpel,
				'nama' => $row->NAMA_PELANGGAN,
				'alamat' => $row->ALAMAT_PELANGGAN,
				'koduk' => $row->KODUK_PELANGGAN,
				'gardu' => $row->NAMA_GARDU,
				'rbm' => $nama_rbm,
				'tarif' => $row->TARIF_PELANGGAN,
				'daya' => number_format($row->DAYA_PELANGGAN, 0, ',', '.'),
				'lwbp' => number_format(floatval($lwbp), '2', ',', '.'),
				'wbp' => number_format(floatval($wbp), '2', ',', '.'),
				'kvarh' => number_format(floatval($kvarh), '2', ',', '.'),
				'lwbp0' => number_format(floatval($lwbp0), '2', ',', '.'),
				'wbp0' => number_format(floatval($wbp0), '2', ',', '.'),
				'kvarh0' => number_format(floatval($kvarh0), '2', ',', '.'),
			);
		}
		
		return $r;
	}
}