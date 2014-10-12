<?php
/**
 * Pelanggan Model
 */
namespace Model;

set_time_limit(0);

class PelangganModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	// view untuk data pelanggan per page
	public function view() {
		$d = $this->prepare_get(array('unit', 'idpel', 'nometer'));
		extract($d);
		$unit = intval($unit);
		$r = array();
		
		// kode unit
		$run = $this->db->query("SELECT `KODE_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		if (empty($run)) return FALSE;
		$kdunit = $run->KODE_UNIT;
		
		$run = $this->db->query("SELECT a.ID_PELANGGAN, b.NAMA_GARDU, a.NAMA_PELANGGAN, a.TARIF_PELANGGAN, a.DAYA_PELANGGAN, a.ALAMAT_PELANGGAN, a.KODUK_PELANGGAN FROM pelanggan a, gardu b WHERE a.STATUS_PELANGGAN = '1' AND a.ID_GARDU = b.ID_GARDU AND a.ID_PELANGGAN LIKE '%$idpel%' LIMIT 0, 20");
		for ($i = 0; $i < count($run); $i++) {
			$row = $run[$i];
			// rbm
			$srun = $this->db->query("SELECT a.NAMA_RBM FROM rbm a join rincian_rbm b on a.ID_RBM = b.ID_RBM  WHERE b.ID_PELANGGAN = '" . $row->ID_PELANGGAN . "'", TRUE);
			if ( ! empty($srun)) $rbm = $srun->NAMA_RBM;
			else $rbm = '';
						
			$r[] = array(
				'id' => $row->ID_PELANGGAN,
				'nama' => $row->NAMA_PELANGGAN,
				'alamat' => $row->ALAMAT_PELANGGAN,
				'tarif' => $row->TARIF_PELANGGAN,
				'daya' => number_format($row->DAYA_PELANGGAN, 0, ',', '.'),
				'koduk' => $row->KODUK_PELANGGAN,
				'rbm' => $rbm,
				'gardu' => $row->NAMA_GARDU
			);
		}
		
		return $r;
	}
	
	// view untuk cater, id adalah id cater
	public function view_by_petugas($id) {
		$id = intval($id);
		
		$this->db->query("START TRANSACTION");
		$r = array();
		// cari bulan aktif
		$run = $this->db->query("SELECT `NAMA_BLTH` FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		$m = intval(substr($run->NAMA_BLTH, 0, 2));
		$y = intval(substr($run->NAMA_BLTH, 2, 4));
		$m--;
		if ($m == 0) {
			$m = 12;
			$y--;
		}
		if (strlen($m) < 2) $m = '0' . $m;
		$lalu = $m . $y;
		$run = $this->db->query("SELECT `ID_BLTH` FROM `blth` WHERE `NAMA_BLTH` = '$lalu'", TRUE);
		$idblth = $lwbp = $wbp = $kvarh = 0;
		if ( ! empty($run)) $idblth = $run->ID_BLTH;
		
		$run = $this->db->query("SELECT a.ID_PELANGGAN, a.NAMA_PELANGGAN, a.KODUK_PELANGGAN, a.TARIF_PELANGGAN, a.DAYA_PELANGGAN, a.ID_GARDU FROM pelanggan a join  rincian_rbm c on a.ID_PELANGGAN = c.ID_PELANGGAN join rbm b on b.ID_RBM = c.ID_RBM WHERE b.ID_PETUGAS = '$id' AND b.TANGGAL_RBM = '" . date('j') . "' ORDER BY b.ID_RBM, a.ID_PELANGGAN");
		for ($i = 0; $i < count($run); $i++) {
			if ( ! empty($idblth)) {
				$srun = $this->db->query("SELECT `LWBP_BACAMETER`, `WBP_BACAMETER`, `KVARH_BACAMETER` FROM `history` WHERE `ID_PELANGGAN` = '" . $run[$i]->ID_PELANGGAN . "' AND `ID_BLTH` = '$idblth'", TRUE);
				if ( ! empty($srun)) {
					$lwbp = number_format(floatval($srun->LWBP_BACAMETER), 2, '.', '');
					$wbp = number_format(floatval($srun->WBP_BACAMETER), 2, '.', '');
					$kvarh = number_format(floatval($srun->KVARH_BACAMETER), 2, '.', '');
				}
			}
			/*if (empty($run[$i]->ID_GARDU)) $gardu = '';
			else {
				$srun = $this->db->query("SELECT `NAMA_GARDU` FROM `gardu` WHERE `ID_GARDU` = '" . $run[$i]->ID_GARDU . "'", TRUE);
				$gardu = $srun->NAMA_GARDU;
			}*/
			
			$r[] = array(
				'id' => $run[$i]->ID_PELANGGAN,
				'nama' => $run[$i]->NAMA_PELANGGAN,
				'koduk' => $run[$i]->KODUK_PELANGGAN,
				'daya' => $run[$i]->DAYA_PELANGGAN,
				'tarif' => $run[$i]->TARIF_PELANGGAN,
				'lwbp0' => $lwbp,
				'wbp0' => $wbp,
				'kvarh0' => $kvarh,
				'gardu' => ''
			);
		}
		
		$this->db->query("COMMIT");
		return $r;
	}
	
	/**
	 * Mendapatkan detail pelanggan
	 */
	public function get_pelanggan($id) {
		$r = array();
		$id = $this->db->escape_str($id);
		$run = $this->db->query("SELECT a.ID_PELANGGAN, b.KODE_KODEPROSES, b.NAMA_KODEPROSES, c.NAMA_GARDU, a.NAMA_PELANGGAN, a.TARIF_PELANGGAN, a.DAYA_PELANGGAN, a.ALAMAT_PELANGGAN, a.KODUK_PELANGGAN, a.KOORDINAT_PELANGGAN FROM pelanggan a, kodeproses b, gardu c WHERE a.ID_KODEPROSES = b.ID_KODEPROSES AND a.ID_GARDU = c.ID_GARDU AND a.ID_PELANGGAN = '$id'", TRUE);
		
		if (empty($run)) return FALSE;
		
		$r['id'] = $run->ID_PELANGGAN;
		$r['kodeproses'] = $run->KODE_KODEPROSES . ' - ' . $run->NAMA_KODEPROSES;
		$r['gardu'] = $run->NAMA_GARDU;
		$r['nama'] = $run->NAMA_PELANGGAN;
		$r['tarif'] = $run->TARIF_PELANGGAN;
		$r['daya'] = number_format($run->DAYA_PELANGGAN, 0, ',', '.');
		$r['alamat'] = $run->ALAMAT_PELANGGAN;
		$r['koduk'] = $run->KODUK_PELANGGAN;
		
		// koordinat 
		if ( ! empty($run->KOORDINAT_PELANGGAN)) {
			$koordinat = json_decode($run->KOORDINAT_PELANGGAN);
			$r['koordinat'][] = $koordinat->latitude;
			$r['koordinat'][] = $koordinat->longitude;
		} else $r['koordinat'] = array();
		
		// rbm
		$run = $this->db->query("SELECT a.NAMA_RBM FROM rbm a, rincian_rbm b WHERE a.ID_RBM = b.ID_RBM AND b.ID_PELANGGAN = '$id'", TRUE);
		$r['rbm'] = (empty($run) ? '' : $run->NAMA_RBM);
		
		return $r;
	}
	
	/**
	 * Dapatkan history pelanggan per page
	 */
	public function get_history($id = '') {
		$r = array();
		
		if (empty($id)) {
			$d = $this->prepare_get(array('id', 'cpage'));
			extract($d);
			$cpage = intval($cpage);
			$dtpp = 12;
			
			// total
			$run = $this->db->query("SELECT COUNT(`ID_BACAMETER`) AS `HASIL` FROM `bacameter` WHERE `ID_PELANGGAN` = '$id'", TRUE);
			$r['numpage'] = ceil($run->HASIL / $dtpp);
			
			// cari data
			$start = $cpage * $dtpp;
			$run = $this->db->query("SELECT a.ID_BACAMETER, a.ID_KETERANGAN_BACAMETER, a.ID_BLTH, b.NAMA_BLTH, DATE(a.TANGGAL_BACAMETER) AS TANGGAL, a.LWBP_BACAMETER, a.WBP_BACAMETER, a.KVARH_BACAMETER, a.FOTO_BACAMETER FROM bacameter a, blth b WHERE a.ID_BLTH = b.ID_BLTH AND a.ID_PELANGGAN = '$id' ORDER BY a.ID_BLTH, a.TANGGAL_BACAMETER DESC LIMIT $start, $dtpp");
			
			$r['data'] = array();
			for ($i = 0; $i < count($run); $i++) {
				list($y, $m, $d) = explode('-', $run[$i]->TANGGAL);
				$tanggal = "$d/$m/$y";
				
				// cari kode lbkb
				if ($run[$i]->ID_KETERANGAN_BACAMETER == 0) {
					$kodelbkb = '0';
					$lbkb = 'Normal';
				} else {
					$srun = $this->db->query("SELECT `KODE_KETERANGAN_BACAMETER`, `NAMA_KETERANGAN_BACAMETER` FROM `keterangan_bacameter` WHERE `ID_KETERANGAN_BACAMETER` = '" . $run[$i]->ID_KETERANGAN_BACAMETER . "'", TRUE);
					$kodelbkb = $srun->KODE_KETERANGAN_BACAMETER;
					$lbkb = $srun->NAMA_KETERANGAN_BACAMETER;
				}
				
				// lwbp
				$lwbp = $run[$i]->LWBP_BACAMETER;
				$wbp = $run[$i]->WBP_BACAMETER;
				$kvarh = $run[$i]->KVARH_BACAMETER;
				$idbacameter = $run[$i]->ID_BACAMETER;
				
				// cari di koreksi
				$srun = $this->db->query("SELECT `ID_KETERANGAN_BACAMETER`, `LWBP_KOREKSI`, `WBP_KOREKSI`, `KVARH_KOREKSI` FROM `koreksi` WHERE `ID_BACAMETER` = '$idbacameter' ORDER BY `ID_KOREKSI` DESC LIMIT 0, 1", TRUE);
				if ( ! empty($srun)) {
					$idlbkb = $srun->ID_KETERANGAN_BACAMETER;
					$lwbp = $srun->LWBP_KOREKSI;
					$wbp = $srun->WBP_KOREKSI;
					$kvarh = $srun->KVARH_KOREKSI;
				} else 
					$idlbkb = $run[$i]->ID_KETERANGAN_BACAMETER;
				
				// lbkb
				if ($run[$i]->ID_KETERANGAN_BACAMETER != $idlbkb) {
					$srun = $this->db->query("SELECT `KODE_KETERANGAN_BACAMETER`, `NAMA_KETERANGAN_BACAMETER` FROM `keterangan_bacameter` WHERE `ID_KETERANGAN_BACAMETER` = '$idlbkb'", TRUE);
					$kodelbkb = $srun->KODE_KETERANGAN_BACAMETER;
					$lbkb = $srun->NAMA_KETERANGAN_BACAMETER;
				}
				
				// pemakaian kwh
				$idblth = $run[$i]->ID_BLTH;
				$srun = $this->db->query("SELECT `ID_DLPD`, `KWH_MTRPAKAI`, `JAM_NYALA` FROM `mtrpakai` WHERE `ID_PELANGGAN` = '$id' AND `ID_BLTH` = '$idblth'", TRUE);
				if (empty($srun)) {
					$kwh = $jam = 0;
					$dlpd = $kodedlpd = '';
				} else {
					$kwh = $srun->KWH_MTRPAKAI;
					$jam = $srun->JAM_NYALA;
					$dlpd = $kodedlpd = '';
					if ( ! empty($srun->ID_DLPD)) {
						$srun = $this->db->query("SELECT `KODE_DLPD`, `NAMA_DLPD` FROM `dlpd` WHERE `ID_DLPD` = '" . $srun->ID_DLPD . "'", TRUE);
						$dlpd = $srun->NAMA_DLPD;
						$kodedlpd = $srun->KODE_DLPD;
					}
				}
				
				$r['data'][] = array(
					'tanggal' => $tanggal,
					'blth' => $run[$i]->NAMA_BLTH,
					'lwbp' => $lwbp,
					'wbp' => $wbp,
					'kvarh' => $kvarh,
					'kodelbkb' => $kodelbkb,
					'lbkb' => $lbkb,
					'kwh' => number_format($kwh, 0, ',', '.'),
					'kodedlpd' => $kodedlpd,
					'dlpd' => $dlpd,
					'jam' => number_format($jam, 0, ',', '.')
				);			
			}
		} else {
			$run = $this->db->query("SELECT a.ID_BACAMETER, a.ID_KETERANGAN_BACAMETER, a.ID_BLTH, b.NAMA_BLTH, DATE(a.TANGGAL_BACAMETER) AS TANGGAL, a.LWBP_BACAMETER, a.WBP_BACAMETER, a.KVARH_BACAMETER, a.FOTO_BACAMETER FROM bacameter a, blth b WHERE a.ID_BLTH = b.ID_BLTH AND a.ID_PELANGGAN = '$id' ORDER BY a.TANGGAL_BACAMETER DESC");
			
			for ($i = 0; $i < count($run); $i++) {
				list($y, $m, $d) = explode('-', $run[$i]->TANGGAL);
				$tanggal = "$d/$m/$y";
				
				// cari kode lbkb
				$srun = $this->db->query("SELECT * FROM `bacameter` WHERE `ID_KETERANGAN_BACAMETER` = '" . $run[$i]->ID_KETERANGAN_BACAMETER . "'", TRUE);
				if (empty($srun)) {
					$kodelbkb = '0';
					$lbkb = 'NORMAL';
				} else {
					$idlbkb = $srun->ID_KETERANGAN_BACAMETER;
					$kodelbkb = $srun->KODE_KETERANGAN_BACAMETER;
					$lbkb = $srun->NAMA_KETERANGAN_BACAMETER;
				}
				
				$lwbp = $run[$i]->LWBP_BACAMETER;
				$wbp = $run[$i]->WBP_BACAMETER;
				$kvarh = $run[$i]->KVARH_BACAMETER;
				$idbacameter = $run[$i]->ID_BACAMETER;
				
				// cari di koreksi
				$srun = $this->db->query("SELECT `ID_KETERANGAN_BACAMETER`, `LWBP_KOREKSI`, `WBP_KOREKSI`, `KVARH_KOREKSI` FROM `koreksi` WHERE `ID_BACAMETER` = '$idbacameter' ORDER BY `ID_KOREKSI` DESC LIMIT 0, 1", TRUE);
				if ( ! empty($srun)) {
					$lwbp = $srun->LWBP_KOREKSI;
					$wbp = $srun->WBP_KOREKSI;
					$kvarh = $srun->KVARH_KOREKSI;
					
					if ($srun->ID_KETERANGAN_BACAMETER != $idlbkb) {
						$idlbkb = $srun->ID_KETERANGAN_BACAMETER;
						$prun = $this->db->query("SELECT * FROM `bacameter` WHERE `ID_KETERANGAN_BACAMETER` = '$idlbkb'", TRUE);
						$kodelbkb = $prun->KODE_KETERANGAN_BACAMETER;
						$lbkb = $prun->NAMA_KETERANGAN_BACAMETER;
					}
				}
				
				// pemakaian kwh
				$idblth = $run[$i]->ID_BLTH;
				$srun = $this->db->query("SELECT `ID_DLPD`, `KWH_MTRPAKAI`, `JAM_NYALA` FROM `mtrpakai` WHERE `ID_PELANGGAN` = '$id' AND `ID_BLTH` = '$idblth'", TRUE);
				if (empty($srun)) {
					$kwh = $jam = 0;
					$dlpd = $kodedlpd = '';
				} else {
					$kwh = $srun->KWH_MTRPAKAI;
					$jam = $srun->JAM_NYALA;
					$dlpd = $kodedlpd = '';
					if ( ! empty($srun->ID_DLPD)) {
						$srun = $this->db->query("SELECT `KODE_DLPD`, `NAMA_DLPD` FROM `dlpd` WHERE `ID_DLPD` = '" . $srun->ID_DLPD . "'", TRUE);
						$dlpd = $srun->NAMA_DLPD;
						$kodedlpd = $srun->KODE_DLPD;
					}
				}
				
				$r[] = array(
					'tanggal' => $tanggal,
					'blth' => $run[$i]->NAMA_BLTH,
					'lwbp' => $lwbp,
					'wbp' => $wbp,
					'kvarh' => $kvarh,
					'kodelbkb' => $kodelbkb,
					'lbkb' => $lbkb,
					'kwh' => number_format($kwh, 0, ',', '.'),
					'kodedlpd' => $kodedlpd,
					'dlpd' => $dlpd,
					'jam' => number_format($jam, 0, ',', '.')
				);			
			}
		}
		
		return $r;
	}
	
	/**
	 * Dapatkan data pelanggan belum terbaca
	 */
	public function get_unread() {
		$get = $this->prepare_get(array('unit', 'rbm'));
		extract($get);
		$unit = intval($unit);
		$rbm = floatval($rbm);
		
		// cari bulan tahun
		$run = $this->db->query("SELECT `ID_BLTH`, `NAMA_BLTH` FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		$id_blth = $run->ID_BLTH;
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
			$srun = $this->db->query("SELECT COUNT(`ID_BACAMETER`) AS `HASIL` FROM `bacameter` WHERE `ID_PELANGGAN` = '" . $run[$i]->ID_PELANGGAN . "' AND `ID_BLTH` = '$id_blth'", TRUE);
			if ($srun->HASIL > 0) continue;
			
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
			
			$r[] = array(
				'idpel' => $idpel,
				'nama' => $row->NAMA_PELANGGAN,
				'alamat' => $row->ALAMAT_PELANGGAN,
				'koduk' => $row->KODUK_PELANGGAN,
				'gardu' => $row->NAMA_GARDU,
				'rbm' => $nama_rbm,
				'tarif' => $row->TARIF_PELANGGAN,
				'daya' => number_format($row->DAYA_PELANGGAN, 0, ',', '.'),
				'lwbp' => '-',
				'wbp' => '-',
				'kvarh' => '-',
				'lwbp0' => number_format(floatval($lwbp0), '2', ',', '.'),
				'wbp0' => number_format(floatval($wbp0), '2', ',', '.'),
				'kvarh0' => number_format(floatval($kvarh0), '2', ',', '.')
			);
		}
		return $r;
	}
	
	/**
	 * Mendapatkan data aduan
	 */
	public function get_aduan() {
		$get = $this->prepare_get(array('cpage'));
		extract($get);
		$cpage = intval($cpage);
		$r = array();
		
		// total halaman
		$dtpg = 20;
		$run = $this->db->query("SELECT COUNT(`ID_ADUAN`) AS `TOTAL` FROM `aduan` LIMIT 0, 400", TRUE);
		$total = $run->TOTAL;
		$numpage = ceil($total/$dtpg);
		
		$start = ($cpage * $dtpg);
		$run = $this->db->query("SELECT a.ID_ADUAN, a.ID_PELANGGAN, b.ALAMAT_PELANGGAN, a.TELEPON_ADUAN, a.ISI_ADUAN, a.TL_ADUAN, a.TANGGAL_ADUAN, a.NAMA_ADUAN, b.ALAMAT_PELANGGAN FROM aduan a, pelanggan b WHERE a.ID_PELANGGAN = b.ID_PELANGGAN ORDER BY a.TANGGAL_ADUAN DESC LIMIT $start, $dtpg");
		
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'id' => $run[$i]->ID_ADUAN,
				'idpel' => $run[$i]->ID_PELANGGAN,
				'nama' => trim($run[$i]->NAMA_ADUAN),
				'alamat' => trim($run[$i]->ALAMAT_PELANGGAN),
				'info' => trim($run[$i]->NAMA_ADUAN) . '<br>' . trim($run[$i]->ALAMAT_PELANGGAN),
				'telepon' => $run[$i]->TELEPON_ADUAN,
				'aduan' => $run[$i]->ISI_ADUAN,
				'tl' => $run[$i]->TL_ADUAN,
				'tanggal' => datedb_to_tanggal($run[$i]->TANGGAL_ADUAN, 'd/m/Y H:i')
			);
		}
		
		return array(
			'data' => $r, 'numpage' => $numpage
		);
	}
	
	/** Tambah aduan pelanggan **/
	public function add_aduan() {
		$post = $this->prepare_post(array('id', 'idpel', 'nama', 'telepon', 'aduan', 'tl'));
		extract($post);
		
		$id = intval($id);
		$idpel = floatval($idpel);
		$nama = $this->db->escape_str($nama);
		$telepon = $this->db->escape_str($telepon);
		$aduan = $this->db->escape_str($aduan);
		$tl = $this->db->escape_str($tl);
		
		if (empty($id)) {
			// insert
			$ins = $this->db->query("INSERT INTO `aduan` VALUES(0, '$idpel', '$nama', '$telepon', '$aduan', '$tl', NOW())");
			return array();
		} else {
			$data = $this->db->query("SELECT * FROM `aduan` WHERE `ID_ADUAN` = '$id'", TRUE);
			if (empty($data)) return array();
			$upd = array();
			if ($data->ID_PELANGGAN != $idpel) $upd[] = "`ID_PELANGGAN` = '$idpel'";
			if ($data->NAMA_ADUAN != $nama) $upd[] = "`NAMA_ADUAN` = '$nama'";
			if ($data->TELEPON_ADUAN != $telepon) $upd[] = "`TELEPON_ADUAN` = '$telepon'";
			if ($data->ISI_ADUAN != $aduan) $upd[] = "`ISI_ADUAN` = '$aduan'";
			if ($data->TL_ADUAN != $tl) $upd[] = "`TL_ADUAN` = '$tl'";
			
			if ( ! empty($upd)) {
				$run = $this->db->query("UPDATE `aduan` SET " . implode(', ', $upd) . "  WHERE `ID_ADUAN` = '$id'");
			}
		}
	}
	
	/**
	 * Hapus data aduan pelanggan
	 */
	public function delete_aduan($id) {
		$id = intval($id);
		$del = $this->db->query("DELETE FROM `aduan` WHERE `ID_ADUAN` = '$id'");
		return array();
	}
}