<?php
/**
 * Tusbung Model
 */
namespace Model;

set_time_limit(0);

class TusbungModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Tampilkan data tunggakan per rbm
	 */
	public function get_rbm_list() {
		$r = array();
		
		$get = $this->prepare_get(array('cpage', 'unit', 'keyword'));
		extract($get);
		$unit = intval($unit);
		$cpage = intval($cpage);
		$keyword = $this->db->escape_str($keyword);
		
		// pagination
		$dtpr = 15;
		$start = ($cpage * $dtpr);
		
		// total data
		$run = $this->db->query("SELECT COUNT(`ID_RBM`) AS `HASIL` FROM `rbm`", TRUE);
		$total = $run->HASIL;
		$numpage = ceil($total/$dtpr);
		
		/**
		 * OPTIMASI QUERY DIPERLUKAN !
		 */
		$this->db->query("START TRANSACTION");
		$run = $this->db->query("SELECT a.nama_rbm, a.nama_petugas, b.ID_RBM FROM bantu a, rbm b WHERE a.nama_rbm LIKE '%" . $keyword . "%' AND a.nama_rbm = b.NAMA_RBM GROUP BY(a.nama_rbm) ORDER BY a.nama_rbm, a.nama_petugas LIMIT $start, $dtpr");
		for ($i = 0; $i < count($run); $i++) {
			$rbm = $run[$i]->ID_RBM;
			$rptag = $rpbk = $plg = 0;
			
			$srun = $this->db->query("SELECT a.RPTAG_TAGIHAN, a.RPBK_TAGIHAN FROM tagihan a, rincian_rbm b WHERE b.ID_RBM = '$rbm' AND a.ID_PELANGGAN = b.ID_PELANGGAN");
			for ($j = 0; $j < count($srun); $j++) {
				$rptag += $srun[$i]->RPTAG_TAGIHAN;
				$rpbk += $srun[$i]->RPBK_TAGIHAN;
				$plg += 1;
			}
			
			$r[] = array(
				'rbm' => $run[$i]->nama_rbm,
				'petugas' => $run[$i]->nama_petugas,
				'pelanggan' => $plg,
				'rptag' => number_format($rptag, 0, ',', '.'),
				'rpbk' => number_format($rpbk, 0, ',', '.')
			);
		}
		
		$this->db->query("COMMIT");
		
		return array(
			'numpage' => $numpage,
			'data' => $r
		);
	}
	
	/**
	 * Dapatkan detail tunggak per rbm
	 */
	public function get_detail_rbm($nama) {
		$nama = $this->db->escape_str($nama);
		$r = array();
		
		$this->db->query("START TRANSACTION");
		$blth = $this->db->query("SELECT * FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		
		// cari id rbm
		$run = $this->db->query("SELECT `ID_RBM` FROM `rbm` WHERE `NAMA_RBM` = '$nama'", TRUE);
		$rbm = $run->ID_RBM;
		
		// cari rincian idpel
		$run = $this->db->query("SELECT a.ID_PELANGGAN, c.NAMA_PELANGGAN, CONCAT(c.TARIF_PELANGGAN, '/', c.DAYA_PELANGGAN) AS TARIFDAYA, c.ALAMAT_PELANGGAN, d.ID_TAGIHAN, d.LEMBAR_TAGIHAN, d.RPTAG_TAGIHAN, d.RPBK_TAGIHAN, d.STATUS_TAGIHAN FROM rincian_rbm a, rbm b, pelanggan c, tagihan d WHERE a.ID_RBM = b.ID_RBM AND b.ID_RBM = '$rbm' AND a.ID_PELANGGAN = c.ID_PELANGGAN AND a.ID_PELANGGAN = d.ID_PELANGGAN AND d.STATUS_TAGIHAN < 2 AND d.ID_BLTH = '{$blth->ID_BLTH}'");
		
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'idpel' => $run[$i]->ID_PELANGGAN,
				'nama' => $run[$i]->NAMA_PELANGGAN,
				'alamat' => $run[$i]->ALAMAT_PELANGGAN,
				'td' => $run[$i]->TARIFDAYA,
				'lembar' => $run[$i]->LEMBAR_TAGIHAN,
				'rptag' => number_format($run[$i]->RPTAG_TAGIHAN, 0, ',', '.'),
				'rpbk' => number_format($run[$i]->RPBK_TAGIHAN, 0, ',', '.'),
				'status' => $run[$i]->STATUS_TAGIHAN,
				'id' => $run[$i]->ID_TAGIHAN
			);
		}
		$this->db->query("COMMIT");
		
		return $r;
	}
	
	/**
	 * Mendapatkan daftar tusbung harian
	 */
	public function get_list() {
		$r = array();
		
		$get = $this->prepare_get(array('tgl', 'unit', 'rbm'));
		extract($get);
		$date = explode('/', $tgl);
		if (count($date) != 3) return FALSE;
		list($d, $m, $y) = $date;
		$date = $y . '-' . $m . '-' . $d;
		
		$this->db->query("START TRANSACTION");
		$idpetugas = 0;
		if ( ! empty($rbm)) {
			// cari id_petugas dengan id_rbm = $rbm
			$run = $this->db->query("SELECT `ID_PETUGAS` FROM `rbm` WHERE `ID_RBM` = '$rbm'", TRUE);
			$idpetugas = $run->ID_PETUGAS;
		}
		
		$run = $this->db->query("SELECT * FROM `cabutpasang` WHERE DATE(`TANGGAL_CABUTPASANG`) = '$date'" .( ! empty($idpetugas) ? " AND `ID_PETUGAS` = '$idpetugas'" : ''));
		
		for ($i = 0; $i < count($run); $i++) {
			$sd = array();
			
			// jam dan foto
			list($d, $t) = explode(' ', $run[$i]->TANGGAL_CABUTPASANG);
			$sd['jam'] = $t;
			$sd['foto1'] = ( ! is_file('upload/foto/' . $run[$i]->FOTO1_CABUTPASANG) ? '/img/default.jpg' : '/img/' . $run[$i]->FOTO1_CABUTPASANG);
			$sd['foto2'] = ( ! is_file('upload/foto/' . $run[$i]->FOTO2_CABUTPASANG) ? '/img/default.jpg' : '/img/' . $run[$i]->FOTO2_CABUTPASANG);
			
			// data tagihan
			$tagihan = $run[$i]->ID_TAGIHAN;
			$srun = $this->db->query("SELECT a.ID_TAGIHAN, a.ID_PELANGGAN, a.STATUS_TAGIHAN, b.NAMA_PELANGGAN, b.ALAMAT_PELANGGAN, CONCAT(b.TARIF_PELANGGAN, '/', b.DAYA_PELANGGAN) AS TARIFDAYA, a.LEMBAR_TAGIHAN, a.RPTAG_TAGIHAN, a.RPBK_TAGIHAN FROM tagihan a, pelanggan b WHERE a.ID_PELANGGAN = b.ID_PELANGGAN AND a.ID_TAGIHAN = '$tagihan'", TRUE);
			$sd['id'] = $srun->ID_TAGIHAN;
			$sd['status'] = $srun->STATUS_TAGIHAN;
			$sd['idpel'] = $srun->ID_PELANGGAN;
			$sd['nama'] = trim($srun->NAMA_PELANGGAN);
			$sd['alamat'] = trim($srun->ALAMAT_PELANGGAN);
			$sd['td'] = $srun->TARIFDAYA;
			$sd['lembar'] = $srun->LEMBAR_TAGIHAN;
			$sd['rptag'] = number_format($srun->RPTAG_TAGIHAN, 0, ',', '.');
			$sd['rpbk'] = number_format($srun->RPBK_TAGIHAN, 0, ',', '.');
			
			// petugas
			$petugas = $run[$i]->ID_PETUGAS;
			$srun = $this->db->query("SELECT `NAMA_PETUGAS` FROM `petugas` WHERE `ID_PETUGAS` = '$petugas'", TRUE);
			$sd['petugas'] = $srun->NAMA_PETUGAS;
			
			// keterangan baca
			$ketbaca = $run[$i]->ID_KETERANGAN_BACAMETER;
			$srun = $this->db->query("SELECT `KODE_KETERANGAN_BACAMETER`, `NAMA_KETERANGAN_BACAMETER` FROM `bacameter` WHERE `ID_KETERANGAN_BACAMETER` = '$ketbaca'", TRUE);
			$sd['ketbaca'] = (empty($srun) ? 'Normal' : $srun->NAMA_KETERANGAN_BACAMETER);
			$sd['kdbaca'] = (empty($srun) ? '-' : $srun->KODE_KETERANGAN_BACAMETER);
			
			$r[] = $sd;
		}
		$this->db->query("COMMIT");
		return $r;
	}
	
	/**
	 * Import file rincian dan rekap tusbung
	 */
	public function import($iofiles, $type) {
		if ( ! isset($_FILES['file'])) {
			return array('error' => 'nofile', 'status' => 'fail');
		}
		
		$file = $_FILES['file']['name'];
		$ext = @end(explode('.', $file));
		
		// cek direktori
		$dir = 'upload/npp';
		if ( ! is_dir($dir)) @mkdir($dir);
		@clearstatcache();
		
		$config['upload_path'] 	= $dir . '/';
		$iofiles->upload_config($config);
		$iofiles->upload('file');
		$filename = $iofiles->upload_get_param('file_name');
		$data = @file($dir . '/' . $filename);
			
		// detecting delimiter
		if (strpos($data[0], ';') !== FALSE) $delimiter = ';';
		else $delimiter = ',';
			
		// cari bulan tahun aktif
		$blth = $this->db->query("SELECT * FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		// id unit
		$unit = '';
		$numdata = 0;
		
		// jika import rincian tagihan
		if ($type == 'rincian') {
			$this->db->query("START TRANSACTION");
			for ($i = 0; $i < count($data); $i++) {
				$row = str_getcsv($data[$i], $delimiter);
				// jumlah kolom tidak sama
				if (count($row) != 12) continue;
				
				list($ap, $up, $idpel, $n, $t, $d, $k, $g, $a, $lembar, $rptag, $rpbk) = $row;
				// jika bukan angka
				if ( ! is_numeric($lembar)) continue;
				
				// id unit
				if (empty($unit)) {
					$run = $this->db->query("SELECT `ID_UNIT` FROM `unit` WHERE `KODE_UNIT` = '$up'", TRUE);
					$unit = $run->ID_UNIT;
				}
				
				$numdata++;
				// insert atau update
				$run = $this->db->query("SELECT `LEMBAR_TAGIHAN`, `RPTAG_TAGIHAN`, `RPBK_TAGIHAN` FROM `tagihan` WHERE `ID_BLTH` = '{$blth->ID_BLTH}' AND `ID_PELANGGAN` = '$idpel'", TRUE);
				if (empty($run)) {
					// insert
					$ins = $this->db->query("INSERT INTO `tagihan` VALUES(0, '{$blth->ID_BLTH}', '$idpel', '$lembar', '$rptag', '$rpbk', '0', NULL, NULL)");
				} else {
					// update
					$upd = array();
					if ($run->LEMBAR_TAGIHAN != $lembar) $upd[] = "`LEMBAR_TAGIHAN` = '$lembar'";
					if ($run->RPTAG_TAGIHAN != $rptag) $upd[] = "`RPTAG_TAGIHAN` = '$rptag'";
					if ($run->RPBK_TAGIHAN != $rpbk) $upd[] = "`RPBK_TAGIHAN` = '$rpbk'";
					
					if ( ! empty($upd))
						$run = $this->db->query("UPDATE `tagihan` SET " . implode(', ', $upd) . " WHERE `ID_PELANGGAN` = '$idpel' AND `ID_BLTH` = '{$blth->ID_BLTH}'");
				}
			}
			$this->db->query("COMMIT");
			$iofiles->rename($dir . '/' . $filename, $dir . '/' . $blth->NAMA_BLTH . '.csv');
		}
		
		if ($type == 'rekap') {
			$this->db->query("START TRANSACTION");
			for ($i = 0; $i < count($data); $i++) {
				$row = str_getcsv($data[$i], $delimiter);
				// jumlah kolom tidak sama
				if (count($row) != 11) continue;
				
				// buang karakter selain angka
				foreach ($row as $key => $val) 
					$row[$key] = preg_replace('/[^0-9]/', '', $val);
				
				list($up, $lembar, $plg, $jmllembar, $rpptl, $rpbpju, $rpppn, $rpmat, $rplain2, $rptag, $rpbk) = $row;
				if (empty($lembar)) continue;
				
				// id unit
				if (empty($unit)) {
					if (is_numeric($up)) {
						$run = $this->db->query("SELECT `ID_UNIT` FROM `unit` WHERE `KODE_UNIT` = '$up'", TRUE);
						$unit = $run->ID_UNIT;
					}
				}
				
				$numdata++;
				// insert atau update
				$run = $this->db->query("SELECT * FROM `rkptagihan` WHERE `ID_UNIT` = '$unit' AND `ID_BLTH` = '{$blth->ID_BLTH}' AND `LEMBAR_RKPTAGIHAN` = '$lembar'", TRUE);
				if (empty($run)) {
					$ins = $this->db->query("INSERT INTO `rkptagihan` VALUES(0, '$unit', '{$blth->ID_BLTH}', '$lembar', '$plg', '$jmllembar', '$rpptl', '$rpbpju', '$rpppn', '$rpmat', '$rplain2', '$rptag', '$rpbk')");
				} else {
					$upd = array();
					if ($run->PLG_RKPTAGIHAN != $plg) $upd[] = "`PLG_RKPTAGIHAN` = '$plg'";
					if ($run->LBR_RKPTAGIHAN != $jmllembar) $upd[] = "`LBR_RKPTAGIHAN` = '$jmllembar'";
					if ($run->RPPTL_RKPTAGIHAN != $rpptl) $upd[] = "`RPPTL_RKPTAGIHAN` = '$rpptl'";
					if ($run->RPBPJU_RKPTAGIHAN != $rpbpju) $upd[] = "`RPBPJU_RKPTAGIHAN` = '$rpbpju'";
					if ($run->RPPPN_RKPTAGIHAN != $rpppn) $upd[] = "`RPPPN_RKPTAGIHAN` = '$rpppn'";
					if ($run->RPMAT_RKPTAGIHAN != $rpmat) $upd[] = "`RPMAT_RKPTAGIHAN` = '$rpmat'";
					if ($run->RPLAIN_RKPTAGIHAN != $rplain2) $upd[] = "`RPLAIN_RKPTAGIHAN` = '$rplain2'";
					if ($run->RPTAG_RKPTAGIHAN != $rptag) $upd[] = "`RPTAG_RKPTAGIHAN` = '$rptag'";
					if ($run->RPBK_RKPTAGIHAN != $rpbk) $upd[] = "`RPBK_RKPTAGIHAN` = '$rpbk'";
					
					if ( ! empty($upd)) {
						$run = $this->db->query("UPDATE `rkptagihan` SET " . implode(', ', $upd) . " WHERE `ID_UNIT` = '$unit' AND `ID_BLTH` = '{$blth->ID_BLTH}' AND `LEMBAR_RKPTAGIHAN` = '$lembar'");
					}
				}
			}
			$this->db->query("COMMIT");
			$iofiles->rename($dir . '/' . $filename, $dir . '/' . $blth->NAMA_BLTH . '_rkp.csv');
		}
		
		return array('error' => '', 'status' => 'success', 'file' => $filename, 'numdata' => $numdata);
	}
	
	/**
	 * Edit status tagihan
	 */
	public function edit_tagihan($id, $type) {
		$type = ($type == 'cetak' ? 1 : 2);
		$f = "`" . ($type == 1 ? 'TGL_CETAK_TAGIHAN' : 'TGL_LUNAS_TAGIHAN') . "` = NOW()";
		$run = $this->db->query("UPDATE `tagihan` SET `STATUS_TAGIHAN` = '$type', $f WHERE `ID_TAGIHAN` = '$id'");
		return array('status' => 1);
	}
	
	/**
	 * Dapatkan map per gardu
	 */
	public function get_map($rbm) {
		$rbm = $this->db->escape_str($rbm);
		$r = array();
		
		// cari bulan tahun
		$blth = $this->db->query("SELECT * FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		
		$run = $this->db->query("SELECT a.ID_PELANGGAN, c.NAMA_PELANGGAN, c.TARIF_PELANGGAN, c.DAYA_PELANGGAN, c.ALAMAT_PELANGGAN, c.KOORDINAT_PELANGGAN, c.KODUK_PELANGGAN FROM rincian_rbm a, rbm b, pelanggan c WHERE a.ID_RBM = b.ID_RBM AND b.NAMA_RBM = '$rbm' AND a.ID_PELANGGAN = c.ID_PELANGGAN");
		for ($i = 0; $i < count($run); $i++) {
			$d = array();
			$idpel = $run[$i]->ID_PELANGGAN;
			$srun = $this->db->query("SELECT `STATUS_TAGIHAN`, `LEMBAR_TAGIHAN`, `RPTAG_TAGIHAN`, `RPBK_TAGIHAN` FROM `tagihan` WHERE `ID_PELANGGAN` = '$idpel' AND `ID_BLTH` = '{$blth->ID_BLTH}' AND `STATUS_TAGIHAN` < 2", TRUE);
			if (empty($srun)) continue;
			
			$koordinat = json_decode($run[$i]->KOORDINAT_PELANGGAN);
			$d['idpel'] = $idpel;
			$d['nama'] = trim($run[$i]->NAMA_PELANGGAN);
			$d['tarif'] = $run[$i]->TARIF_PELANGGAN;
			$d['daya'] = $run[$i]->DAYA_PELANGGAN;
			$d['koduk'] = $run[$i]->KODUK_PELANGGAN;
			$d['urut'] = $srun->LEMBAR_TAGIHAN;
			$d['lat'] = floatval($koordinat->latitude);
			$d['longt'] = floatval($koordinat->longitude);
			$d['lembar'] = $srun->LEMBAR_TAGIHAN;
			$d['rptag'] = $srun->RPTAG_TAGIHAN;
			$d['rpbk'] = $srun->RPBK_TAGIHAN;
			$d['status'] = $srun->STATUS_TAGIHAN;
			
			$d['stan'] = '-';
			$d['waktu'] = '-';
			$d['foto'] = '-';
			$d['bulan'] = '-';
			
			$r[] = $d;
		}
		
		if (empty($r)) {
			return array(
				'data' => array(),
				'center' => array('lat' => 0, 'longt' => 0)
			);
		}
		
		// pilih center secara acak
		$key = array_rand($r, 1);
		$center = array(
			'lat' => $r[$key]['lat'], 'longt' => $r[$key]['longt']
		);
		
		return array(
			'data' => $r,
			'center' => $center
		);
	}
	
	/**
	 * Data pelanggan yang akan dicaabut pasang dikirim ke Android
	 */
	public function tagihan_by_petugas($id) {
		$id = floatval($id);
		$r = array();
		
		$this->db->query("START TRANSACTION");
		// cari bulan tahun
		$blth = $this->db->query("SELECT * FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		
		$run = $this->db->query("SELECT a.ID_PELANGGAN, b.NAMA_PELANGGAN, b.KODUK_PELANGGAN, b.ALAMAT_PELANGGAN, b.KOORDINAT_PELANGGAN, d.LEMBAR_TAGIHAN, d.RPTAG_TAGIHAN, d.RPBK_TAGIHAN FROM rincian_rbm a, pelanggan b, rbm c, tagihan D WHERE a.ID_RBM = c.ID_RBM AND a.ID_PELANGGAN = b.ID_PELANGGAN AND c.ID_PETUGAS = '$id' AND a.ID_PELANGGAN = d.ID_PELANGGAN AND d.STATUS_TAGIHAN = '1' AND d.ID_BLTH = '{$blth->ID_BLTH}'");
		for ($i = 0; $i < count($run); $i++) {
			$k = json_decode($run[$i]->KOORDINAT_PELANGGAN);
			$r[] = array(
				'i' => $run[$i]->ID_PELANGGAN,
				'n' => trim($run[$i]->NAMA_PELANGGAN),
				'a' => trim($run[$i]->ALAMAT_PELANGGAN),
				'k' => $run[$i]->KODUK_PELANGGAN,
				'o' => array('lat' => $k->latitude, 'longt' => $k->longitude),
				'l' => $run[$i]->LEMBAR_TAGIHAN,
				't' => $run[$i]->RPTAG_TAGIHAN,
				'b' => $run[$i]->RPBK_TAGIHAN
			);
		}
		
		$this->db->query("COMMIT");
		return $r;
	}
	
	/**
	 * Upload data dari Android
	 */
	public function upload_tusbung($id, $iofiles) {
		$post = $this->prepare_post(array('idpel', 'petugas', 'standputus', 'standsambung', 'lbkb', 'latitude', 'longitude'));
		extract($post);
		
		// bulan tahun
		$blth = $this->db->query("SELECT * FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		
		$id = intval($id);
		// cari di tagihan
		$run = $this->db->query("SELECT `ID_TAGIHAN` FROM `tagihan` WHERE `ID_PELANGGAN` = '$idpel' AND `ID_BLTH` = '{$blth->ID_BLTH}' AND `STATUS_TAGIHAN` = '1'", TRUE);
		if (empty($run)) return 'GAGAL';
		
		// cek di cabut pasang
		$idtagih = $run->ID_TAGIHAN;
		$cek = $this->db->query("SELECT `ID_CABUTPASANG`, `ID_KETERANGAN_BACAMETER`, `LWBP_PASANG_CABUTPASANG`, `LWBP_CABUT_CABUTPASANG`, `KOORDINAT_CABUTPASANG` FROM `cabutpasang` WHERE `ID_TAGIHAN` = '$idtagih' AND `ID_PETUGAS` = '$id'", TRUE);
		if (empty($cek)) {
			$files = array('foto1', 'foto2');
			foreach ($files as $val) {
				$config = array();
				$config['upload_path']		= 'upload/foto/';
				$config['allowed_types']	= 'jpg|jpeg|png';
				$config['encrypt_name']		= TRUE;
				$iofiles->upload_config($config);
				$iofiles->upload($val);
				$$val = $iofiles->upload_get_param('file_name');
			}
			
			$koordinat = json_encode(array('latitude' => $latitude, 'longitude' => $longitude));
			$ins = $this->db->query("INSERT INTO `cabutpasang` VALUES(0, '$idtagih', '$id', NOW(), '$lbkb', '$standsambung', '0', '0', '$standputus', '0', '0', '$foto1', '$foto2', '$koordinat')");
		} else {
			$upd = array();
			if ($cek->ID_KETERANGAN_BACAMETER != $lbkb) $upd[] = "`ID_KETERANGAN_BACAMETER` = '$lbkb'";
			if ($cek->LWBP_PASANG_CABUTPASANG != $standsambung) $upd[] = "`LWBP_PASANG_CABUTPASANG` = '$standsambung'";
			if ($cek->LWBP_CABUT_CABUTPASANG != $standputus) $upd[] = "`LWBP_CABUT_CABUTPASANG` = '$standputus'";
			if ($cek->KOORDINAT_CABUTPASANG != $koordinat) $upd[] = "`KOORDINAT_CABUTPASANG` = '$koordinat'";
			if ( ! empty($upd)) $run = $this->db->query("UPDATE `cabutpasang` SET " . implode(', ', $upd) . " WHERE `ID_CABUTPASANG` = '{$cek->ID_CABUTPASANG}'");
		}
		return 'SUKSES';
	}
	
	/**
	 * Dapatkan laporan kinerja tusbung
	 */
	public function get_report() {
		$get = $this->prepare_get(array('unit', 'blth'));
		extract($get);
		$unit = intval($unit);
		$blth = intval($blth);
		$r = array();
		
		if (empty($unit) OR empty($blth)) return $r;
		
		$t = array();
		$this->db->query("START TRANSACTION");
		// dicetak
		$run = $this->db->query("SELECT `ID_TAGIHAN`, DATE(`TGL_CETAK_TAGIHAN`) AS `CETAK` FROM `tagihan` WHERE `ID_BLTH` = '$blth' GROUP BY DATE(`TGL_CETAK_TAGIHAN`)");
		// cacah
		for ($i = 0; $i < count($run); $i++) {
			$tgl = $run[$i]->CETAK;
			if (is_null($tgl)) continue;
			$srun = $this->db->query("SELECT SUM(`LEMBAR_TAGIHAN`) AS `LEMBAR`, SUM(`RPTAG_TAGIHAN`) AS `TAGIHAN` FROM `tagihan` WHERE DATE(`TGL_CETAK_TAGIHAN`) = '$tgl'", TRUE);
			$t[$tgl]['cetak']['lembar'] = $srun->LEMBAR;
			$t[$tgl]['cetak']['tagihan'] = $srun->TAGIHAN;
		}
		
		// dilunasi
		$run = $this->db->query("SELECT `ID_TAGIHAN`, DATE(`TGL_LUNAS_TAGIHAN`) AS `LUNAS` FROM `tagihan` WHERE `ID_BLTH` = '$blth' GROUP BY DATE(`TGL_LUNAS_TAGIHAN`)");
		// cacah
		for ($i = 0; $i < count($run); $i++) {
			$tgl = $run[$i]->LUNAS;
			if (is_null($tgl)) continue;
			$srun = $this->db->query("SELECT SUM(`LEMBAR_TAGIHAN`) AS `LEMBAR`, SUM(`RPTAG_TAGIHAN`) AS `TAGIHAN` FROM `tagihan` WHERE DATE(`TGL_LUNAS_TAGIHAN`) = '$tgl'", TRUE);
			$t[$tgl]['lunas']['lembar'] = $srun->LEMBAR;
			$t[$tgl]['lunas']['tagihan'] = $srun->TAGIHAN;
		}
		
		$this->db->query("COMMIT");
		
		// susun dalam tabel
		$total = array();
		$r = array();
		
		if (count($t) > 0) {
			foreach ($t as $key => $val) {
				$tr = array();
				$tr['tgl'] = datedb_to_tanggal($key, 'd-M-Y');
				
				// dicetak
				if ( ! isset($total['cetak'])) $total['cetak'] = array(0, 0);
				if ( ! isset($t[$key]['cetak'])) $tr['cetak_lembar'] = $tr['cetak_tagihan'] = 0;
				else {
					$tr['cetak_lembar'] = number_format($val['cetak']['lembar'], 0, ',', '.');
					$total['cetak'][0] += $val['cetak']['lembar'];
					$tr['cetak_tagihan'] = number_format($val['cetak']['tagihan'], 0, ',', '.');
					$total['cetak'][1] += $val['cetak']['tagihan'];
				}
				
				// dilunasi
				if ( ! isset($total['lunas'])) $total['lunas'] = array(0, 0);
				if ( ! isset($t[$key]['lunas'])) $tr['lunas_lembar'] = $tr['lunas_tagihan'] = 0;
				else {
					$tr['lunas_lembar'] = number_format($val['lunas']['lembar'], 0, ',', '.');
					$total['lunas'][0] += $val['lunas']['lembar'];
					$tr['lunas_tagihan'] = number_format($val['lunas']['tagihan'], 0, ',', '.');
					$total['lunas'][1] += $val['lunas']['tagihan'];
				}
				
				// tidak lunas
				if ( ! isset($total['tlunas'])) $total['tlunas'] = array(0, 0);
				$tll = $this->tonumber($tr['cetak_lembar']) - $this->tonumber($tr['lunas_lembar']);
				$tlt = $this->tonumber($tr['cetak_tagihan']) - $this->tonumber($tr['lunas_tagihan']);
				$tr['tlunas_lembar'] = $tll;
				$tr['tlunas_tagihan'] = number_format($tlt, 0, ',', '.');
				$total['tlunas'][0] += $tll;
				$total['tlunas'][1] += $tlt;
				
				// diputus
				if ( ! isset($total['p'])) $total['p'] = array(0, 0);
				$tr['p_lembar'] = $tr['p_tagihan'] = 0;
				
				// diputus lunas
				if ( ! isset($total['pl'])) $total['pl'] = array(0, 0);
				$tr['pl_lembar'] = $tr['pl_tagihan'] = 0;
				
				// sambung
				if ( ! isset($total['sambung'])) $total['sambung'] = array(0, 0);
				$tr['sambung_lembar'] = $tr['sambung_tagihan'] = 0;
				
				// rasio
				if ( ! isset($total['rasio'])) $total['rasio'] = array(0, 0);
				$rl = ($tr['cetak_lembar'] != 0 ? ($tr['lunas_lembar'] * 100) / $tr['cetak_lembar'] : 0);
				$rt = ($tr['cetak_tagihan'] != 0 ? ($tr['lunas_tagihan'] * 100) / $tr['cetak_tagihan'] : 0);
				$tr['rasio_lembar'] = number_format($rl, 2, ',', '.') . '%';
				$total['rasio'][0] += $rl;
				$tr['rasio_tagihan'] = number_format($rt, 2, ',', '.') . '%';
				$total['rasio'][1] += $rt;
				
				$r[] = $tr;
			}
			
			// hitung total
			$total['rasio'][0] = number_format($total['rasio'][0] / count($r), 2, ',', '.');
			$total['rasio'][1] = number_format($total['rasio'][1] / count($r), 2, ',', '.');
			// number_format untuk tagihan
			$total['cetak'][1] = number_format($total['cetak'][1], 0, ',', '.');
			$total['lunas'][1] = number_format($total['lunas'][1], 0, ',', '.');
			$total['p'][1] = number_format($total['p'][1], 0, ',', '.');
			$total['pl'][1] = number_format($total['pl'][1], 0, ',', '.');
			$total['sambung'][1] = number_format($total['sambung'][1], 0, ',', '.');
			$total['tlunas'][1] = number_format($total['tlunas'][1], 0, ',', '.');
			$total['rasio'][0] = $total['rasio'][0] . '%';
			$total['rasio'][1] = $total['rasio'][1] . '%';
		}
		
		return array(
			'data' => $r,
			'total' => $total
		);
	}
	private function tonumber($n) {
		return floatval(preg_replace('/[^0-9]/', '', $n));
	}
	
	/**
	 * Grafik kinerja pemutusan
	 */
	public function grafik_kinerja($type, $id) {
		$r = array(
			'categories' => array(),
			'series' => array(
				0 => array(
					'name' => 'Dicetak', 'data' => array()
				),
				1 => array(
					'name' => 'Dilunasi', 'data' => array()
				)
			)
		);
		$namabulan = array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des');
		$type = ($type == 'lembar' ? 'LEMBAR_TAGIHAN' : 'RPTAG_TAGIHAN');
		$blth = intval($id);
		// 7 bulan terakhir
		$start = $blth - 7;
		if ($start < 1) $start = 1;
		
		$this->db->query("START TRANSACTION");
		for ($i = $start; $i <= $blth; $i++) {
			$bulan = '';
			$cetak = 0;
			$lunas = 0;
			$run = $this->db->query("SELECT a.{$type}, DATE(a.TGL_CETAK_TAGIHAN) AS CETAK, DATE(a.TGL_LUNAS_TAGIHAN) AS LUNAS, LEFT(b.NAMA_BLTH, 2) AS BULAN FROM tagihan a, blth b WHERE a.ID_BLTH = b.ID_BLTH AND b.ID_BLTH = '$i' AND (DATE(a.TGL_CETAK_TAGIHAN) != 'NULL' OR DATE(a.TGL_LUNAS_TAGIHAN) != 'NULL')");
			if ( ! empty($run)) {
				for ($j = 0; $j < count($run); $j++) {
					if (empty($bulan)) $bulan = $run[$j]->BULAN;
					if ( ! is_null($run[$j]->CETAK)) $cetak += $run[$j]->{$type};
					if ( ! is_null($run[$j]->LUNAS)) $lunas += $run[$j]->{$type};
				}
			}
			if (empty($bulan)) continue;
			$r['categories'][] = $namabulan[intval($bulan) - 1];
			$r['series'][0]['data'][] = $cetak;
			$r['series'][1]['data'][] = $lunas;
		}
		$this->db->query("COMMIT");
		
		return $r;
	}
}