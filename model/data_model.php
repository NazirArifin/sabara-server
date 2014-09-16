<?php
/**
 * Data Model
 */
namespace Model;

class DataModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	public function operate_blth($id = 0) {
		$id = intval($id);
		$d = $this->prepare_post(array('id', 'nama', 'status'));
		extract($d);
		$nama = $this->db->escape_str($nama);
		$cek = $this->db->query("select `id_blth` from `blth` where `nama_blth`='$nama'");
		
		if ($status == 1) {
			$run = $this->db->query("UPDATE `blth` SET `STATUS_BLTH` = '0'");
		}
		if ($id == 0 and count($cek) == 0) {
			$run = $this->db->query("INSERT INTO `blth` VALUES(0, '$nama', '$status')");
			// set pelanggan 0
			$run = $this->db->query("UPDATE `pelanggan` SET `STATUS_PELANGGAN` = '0'");
			// truncate tabel rincian_rbm
			$run = $this->db->query("TRUNCATE TABLE `rincian_rbm`");
		} else
			$run = $this->db->query("UPDATE `blth` SET `NAMA_BLTH` = '$nama', `STATUS_BLTH` = '$status' WHERE `ID_BLTH` = '$id'");
	}
	
	public function delete_blth($id = 0) {
		$id = intval($id);
		$run = $this->db->query("DELETE FROM `blth` WHERE `ID_BLTH` = '$id'");
	}
	
	public function operate_unit($id = 0) {
		$id = intval($id);
		$post = $this->prepare_post(array('id', 'kode', 'nama'));
		extract($post);
		$kode = $this->db->escape_str($kode);
		$nama = $this->db->escape_str($nama);
		
		if ($id == 0)
			$run = $this->db->query("INSERT INTO `unit` VALUES(0, '$kode', '$nama')");
		else
			$run = $this->db->query("UPDATE `unit` SET `KODE_UNIT` = '$kode', `NAMA_UNIT` = '$nama' WHERE `ID_UNIT` = '$id'");
		return array();
	}
	
	public function delete_unit($id = 0) {
		$id = intval($id);
		$run = $this->db->query("DELETE FROM `unit` WHERE `ID_UNIT` = '$id'");
		return array();
	}
	
	private $field = array(
		'noka' => 9, 
		'tarif' => 3,
		'ktarif' => 1,
		'daya' => 9,
		'nm' => 25,
		'alm' => 26,
		'mut' => 9,
		'koduk' => 12, // 7 rbm, 5 urut
		'kbca' => 3,
		'filler' => 1,
		'pdh' => 45,
		'fm' => 9,
		'lwbp1' => 6,
		'dgt3' => 3,
		'lwbp0' => 6,
		'dgt2' => 3,
		'wbp1' => 6,
		'dgt1' => 3,
		'wbp0' => 6,
		'dgt0' => 3,
		'fkvar' => 9,
		'kvarh1' => 6,
		'dgtkvar1' => 3,
		'kvarh0' => 6,
		'dgtkvarh0' => 3,
		'k_idpel' => 1,
		'idpel' => 12,
		'klpk' => 2,
		'blth' => 6,
		'klp' => 3,
		'ekor' => 6
	);
	
	private $kodeproses = array();
	private function get_kode_proses() {
		if ( ! empty($this->kodeproses)) return;
		$run = $this->db->query("SELECT * FROM `kodeproses`");
		for ($i = 0; $i < count($run); $i++) {
			$this->kodeproses[] = array(
				'id' => $run[$i]->ID_KODEPROSES,
				'kode' => $run[$i]->KODE_KODEPROSES,
				'nama' => $run[$i]->NAMA_KODEPROSES,
			);
		}
	}
	private function get_id_kodeproses($kode) {
		$this->get_kode_proses();
		for ($i = 0; $i < count($this->kodeproses); $i++) {
			if ($kode == $this->kodeproses[$i]['kode'])
				return $this->kodeproses[$i]['id'];
		}
	}
	
	private $blth = array();
	private function get_blth() {
		if ( ! empty($this->blth)) return;
		$run = $this->db->query("SELECT * FROM `blth`");
		for ($i = 0; $i < count($run); $i++) {
			$this->blth[] = array(
				'id' => $run[$i]->ID_BLTH,
				'nama' => $run[$i]->NAMA_BLTH,
				'status' => $run[$i]->STATUS_BLTH
			);
		}
	}
	private function get_id_blth($nama) {
		$this->get_blth();
		for ($i = 0; $i < count($this->blth); $i++) {
			if ($nama == $this->blth[$i]['nama'])
				return $this->blth[$i]['id'];
		}
	}
	
	/**
	 * proses npde versi 2
	 */
	private function proses_npde_pasca2($a, $unit) {
		extract($a);
		
		// cari di rbm, dapatkan idnya lalu masukkan ke rincian
		$rbm = substr($koduk, 0, 7);
		$urut = substr($koduk, 7, 5);
		$urut = intval($urut);
		
		$run = $this->db->query("SELECT `ID_RBM` FROM `rbm` WHERE `NAMA_RBM` = '$rbm'", TRUE);
		
		if (empty($run->ID_RBM)) {
			$ins = $this->db->query("INSERT INTO `rbm` VALUES(0, '0', '$unit', '$rbm','1')");
			$idrbm = $this->db->get_insert_id();
		} else $idrbm = $run->ID_RBM;
		
		$run = $this->db->query("SELECT `ID_RINCIAN_RBM` FROM `rincian_rbm` WHERE `ID_PELANGGAN` = '$idpel' AND `ID_RBM` = '$idrbm'", TRUE);
		if (empty($run->ID_RINCIAN_RBM))
			$ins = $this->db->query("INSERT INTO `rincian_rbm` VALUES(0, '$idpel', '$idrbm', '$urut')");
		
		$run = $this->db->query("SELECT `ID_KODEPROSES`, `DAYA_PELANGGAN`, `FAKTORKWH_PELANGGAN`, `KODUK_PELANGGAN`, `FAKTORKVAR_PELANGGAN` FROM `pelanggan` WHERE `ID_PELANGGAN` = '$idpel'", TRUE);
			
		$id_blth = $this->get_id_blth($blth);
		$id_blth = $id_blth - 1;
		$klp = trim($klp);
		$kodeproses = $this->get_id_kodeproses($klp);
		$tarif = trim($tarif);
		$daya = floatval($daya);
		$fm = floatval($fm);
		$fk = floatval($fkvar);
		
		// db escape
		$nm = $this->db->escape_str($nm);
		$alm = $this->db->escape_str($alm);
		$koduk = $this->db->escape_str($koduk);
		
		if (empty($run)) {
			$ins = $this->db->query("INSERT INTO `pelanggan` VALUES('$idpel', '$kodeproses', '1', '$nm', '$tarif', '$ktarif', '$daya', '$alm', '$koduk', '', '', '$fm', '$fk', '1')");
			
		} else {
			$upd = array();
			if ($run->ID_KODEPROSES != $kodeproses) $upd[] = "`ID_KODEPROSES` = '$kodeproses'";
			if ($run->DAYA_PELANGGAN != $daya) $upd[] = "`DAYA_PELANGGAN` = '$daya'";
			if ($run->KODUK_PELANGGAN != $koduk) $upd[] = "`KODUK_PELANGGAN` = '$koduk'";
			if ($run->FAKTORKWH_PELANGGAN != $fm) $upd[] = "`FAKTORKWH_PELANGGAN` = '$fm'";
			if ($run->FAKTORKVAR_PELANGGAN != $fk) $upd[] = "`FAKTORKVAR_PELANGGAN` = '$fk'";
			$upd[] = "`STATUS_PELANGGAN` = '1'";
			$u = $this->db->query("UPDATE `pelanggan` SET " . implode(", ", $upd) . " WHERE `ID_PELANGGAN` = '$idpel'");
		}
			
			
		// insert ke bacameter jika bacameter masih kosong
		//$run = $this->db->query("SELECT COUNT(`ID_BACAMETER`) AS `HASIL` FROM `bacameter` WHERE `ID_PELANGGAN` = '$idpel' AND `KIRIM_BACAMETER` = 'N'", TRUE);	
		//$run = $this->db->query("SELECT COUNT(`ID_BACAMETER`) AS `HASIL` FROM `bacameter` WHERE `ID_PELANGGAN` = '$idpel' AND `ID_BLTH` = '$id_blth'", TRUE);

		//if ($run->HASIL == 0) {		
 			$lwbp0 = floatval($lwbp0) . '.00';
			$wbp0 = floatval($wbp0) . '.00';
			$kvarh0 = floatval($kvarh0) . $dgtkvarh0;
 			$ins = $this->db->query("INSERT INTO `bacameter` VALUES(0, '0', '0', '$idpel', '$id_blth', NOW(), '$lwbp0', '$wbp0', '$kvarh0', '', NOW(), '0', 'N',0)");
		//}
		/*else {
		//backup bacameter ke history
			$ceka = $this->db->query("SELECT COUNT(`ID_BACAMETER`) AS `HASIL` FROM `history` WHERE `ID_PELANGGAN` = '$idpel' AND `ID_BLTH` = '$id_blth'", TRUE);
			if ($ceka->HASIL == 0) {
			$ins = $this->db->query("INSERT INTO `history` select * from `bacameter` where `ID_PELANGGAN` = '$idpel' AND `ID_BLTH` = '$id_blth' AND `KIRIM_BACAMETER`!='N'");
			}
 			$ins = $this->db->query("update `bacameter` set lwbp_bacameter='$lwbp0', wbp_bacameter='$wbp0' , kvarh_bacameter='$kvarh0', terkirim_bacameter=NOW(), kirim_bacameter='N' where id_pelanggan='$idpel' and id_blth='$id_blth'");
		}*/
	}
	
	public function npde($iofiles, $tipe) {
		$file = $_FILES['file']['name'];
		$ext = @end(explode('.', $file));
		
		if (strtolower($ext) == 'zip') {
			$config['upload_path']		= 'upload/npde/';
			$iofiles->upload_config($config);
			$iofiles->upload('file');
			$filename 	= $iofiles->upload_get_param('file_name');
			$iofiles->zip_extract('upload/npde/' . $filename, 'upload/npde');
			// hapus zip
			@unlink('upload/npde/' . $filename);
			$pfile = preg_replace('/\.zip$/', '', $filename);
			
			// cari file
			$arext = array('.txt', '.csv', '');
			foreach ($arext as $val) {
				if (is_file('upload/npde/' . $pfile . $val)) {
					$filename = $pfile . $val;
					break;
				}
			}
		} else {
			$config['upload_path']		= 'upload/npde/';
			$iofiles->upload_config($config);
			$iofiles->upload('file');
			$pfile = preg_replace('/\.(txt|csv)$/', '', $file);
			$filename 	= $iofiles->upload_get_param('file_name');
			
			if (strtolower($ext) == 'rar') {
				@unlink('upload/npde/' . $filename);
				return FALSE;
			}
		}
		
		$data = @file('upload/npde/' . $filename);
		$progress = 0;
		$dataperfile = 250;
		
		$npde = substr($file, 0, 4);
		$unit = substr($file, 4, 5);
		$blth = substr($file, 9, 6);
		$numb = substr($file, 15, 5);
		$kode = substr($file, 20, 1);
		
		$run = $this->db->query("SELECT `ID_UNIT`, `NAMA_UNIT` FROM `unit` WHERE `KODE_UNIT` = '$unit'", TRUE);
		$unit = $run->ID_UNIT;
		$namaunit = $run->NAMA_UNIT;
		
		if ($tipe == '1') {
			if (count($data) > $dataperfile) {
				// cari di temp, jika sudah ada batalkan proses
				$np = ceil(count($data) / $dataperfile);
				
				for ($i = 1; $i <= $np; $i++) {
					$cfile = "upload/tmp/{$pfile}__{$i}.txt";
					if (is_file($cfile)) {
						clearstatcache();
						return array('progress' => 1);
					}
				}
				
				// jika belum pernah diupload
				for ($i = 1; $i <= $np; $i++) {
					$start = ($i - 1) * $dataperfile;
					$end = $start + $dataperfile;
					
					$cfile = "upload/tmp/{$pfile}__{$i}.txt";
					$fp = fopen($cfile, 'wb');
					for ($j = $start; $j < $end; $j++) {
						if ( ! isset($data[$j])) break;
						$row = trim($data[$j]);
						if (empty($row)) continue;
						fwrite($fp, $row . ($j == ($end - 1) ? '' : PHP_EOL));
					}
					fclose($fp);
				}
				
				$progress = 1;
			} else {
				$this->db->query("START TRANSACTION");
				
				for ($i = 0; $i < count($data); $i++) {
					$s = 0;
					$row = trim($data[$i]);
					if (empty($row)) continue;
					$r = array();
					
					foreach ($this->field as $key => $val) {
						$r[$key] = substr($row, $s, $val);
						$s += $val;
					}
					
					// proses $r
					$this->proses_npde_pasca2($r, $unit);
				}
				
				$this->db->query("COMMIT");
				$progress = 0;
			}
			
			$total = count($data);
			unset($data);
		}
		
		$ins = $this->db->query("INSERT INTO `npde` VALUES(0, '" . $run->ID_UNIT . "', '$tipe', '$blth', '$kode', '$total', NOW(), 'npde/$filename')");
		$run = $this->db->query("SELECT `NAMA_KODEPROSES` FROM `kodeproses` WHERE `ID_KODEPROSES` = '$tipe'", TRUE);
		$tipe = $run->NAMA_KODEPROSES;
		
		return array(
			'progress' => $progress,
			'result' => array(
				'unit' => $namaunit,
				'tipe' => $tipe,
				'blth' => $blth,
				'jumlah' => $total,
				'file' => $file
			)
		);
	}
	
	/**
	 * Baca file di tmp
	 */
	public function npde_read() {
		$files = @scandir('upload/tmp');
		$nfiles = array();
		foreach ($files as $file) {
			if ($file == '.' OR $file == '..') continue;
			$nfiles[] = $file;
		}
		
		if (empty($nfiles)) return array('progress' => 0);
		// ambil file pertama di array
		$file = $nfiles[0];
		$data = @file('upload/tmp/' . $file);
		
		$unit = substr($file, 4, 5);
		$run = $this->db->query("SELECT `ID_UNIT` FROM `unit` WHERE `KODE_UNIT` = '$unit'", TRUE);
		$unit = $run->ID_UNIT;
		
		$this->db->query("START TRANSACTION");
		for ($i = 0; $i < count($data); $i++) {
			$s = 0;
			$row = trim($data[$i]);
			
			foreach ($this->field as $key => $val) {
				$r[$key] = substr($row, $s, $val);
				$s += $val;
			}
				
			// proses $r
			$this->proses_npde_pasca2($r, $unit);
		}
		$this->db->query("COMMIT");
		@unlink('upload/tmp/' . $file);
		
		clearstatcache();
		$files = scandir('upload/tmp');
		if (count($files) == 2) return array('progress' => 0);
		else return array('progress' => 1, 'file' => $file);
	}
	
	/**
	 * Lihat data bacameter berdasarkan stand
	 */
	public function get_baca_rbm($id, $tgl) {
		$id = intval($id);
		$d = substr($tgl, 0, 2);
		$m = substr($tgl, 2, 2);
		$y = substr($tgl, 4, 4);
		$date = $y . '-' . $m . '-' . $d;
		
		$r = array();
		$this->db->query("START TRANSACTION");
		$tn = 0;
	
		$run = $this->db->query("SELECT a.ID_KETERANGAN_BACAMETER, a.ID_PELANGGAN, a.KIRIM_BACAMETER, TIME(a.TANGGAL_BACAMETER) AS JAM, a.LWBP_BACAMETER, a.WBP_BACAMETER, a.KVARH_BACAMETER, c.DAYA_PELANGGAN, c.TARIF_PELANGGAN FROM bacameter a, rincian_rbm b, pelanggan c WHERE DATE(a.TANGGAL_BACAMETER) = '$date' AND a.ID_PELANGGAN = b.ID_PELANGGAN AND b.ID_RBM = '$id' AND a.ID_PELANGGAN = c.ID_PELANGGAN AND (a.KIRIM_BACAMETER = 'W' OR a.KIRIM_BACAMETER = 'H') ORDER BY a.TANGGAL_BACAMETER DESC");
		for ($i = 0; $i < count($run); $i++) {
			$srun = $this->db->query("SELECT `NAMA_KETERANGAN_BACAMETER` FROM `keterangan_bacameter` WHERE `ID_KETERANGAN_BACAMETER` = '" . $run[$i]->ID_KETERANGAN_BACAMETER . "'", TRUE);
			$keterangan = (empty($srun) ? 'Normal' : $srun->NAMA_KETERANGAN_BACAMETER);
			if ($keterangan != 'Normal') $tn++;
			
			$r[] = array(
				'idpel' => $run[$i]->ID_PELANGGAN,
				'jam' => $run[$i]->JAM,
				'lwbp' => $run[$i]->LWBP_BACAMETER,
				'wbp' => $run[$i]->WBP_BACAMETER,
				'kvarh' => $run[$i]->KVARH_BACAMETER,
				'tarif' => $run[$i]->TARIF_PELANGGAN,
				'daya' => $run[$i]->DAYA_PELANGGAN,
				'keterangan' => $keterangan,
				'kirim' => ($run[$i]->KIRIM_BACAMETER == 'W' ? 'WEB' : 'HP')
			);
		}
		
		$trbm = $this->db->query("SELECT COUNT(`ID_RINCIAN_RBM`) AS `HASIL` FROM `rincian_rbm` WHERE `ID_RBM` = '$id'", TRUE);
		$total = $trbm->HASIL;
		
		$this->db->query("COMMIT");
		return array(
			'total' => $total,
			'data' => $r,
			'tidaknormal' => $tn
		);
	}
	
	/**
	 * Tampilkan RBM perhalaman
	 */
	public function get_rbm() {
		$d = $this->prepare_get(array('unit', 'keyword', 'cpage'));
		extract($d);
		$unit = intval($unit); $cpage = intval($cpage); $keyword = $this->db->escape_str($keyword);
		$dtpp = 15;
		$r = array();
		
		$run = $this->db->query("SELECT COUNT(`ID_RBM`) AS `HASIL` FROM `rbm` WHERE `ID_UNIT` = '$unit' AND `NAMA_RBM` LIKE '%$keyword%'", TRUE);
		$r['numpage'] = ceil($run->HASIL / $dtpp);
		
		$start = $cpage * $dtpp;
		$run = $this->db->query("SELECT a.ID_RBM, a.ID_PETUGAS, a.NAMA_RBM, a.TANGGAL_RBM FROM rbm a, unit b WHERE a.ID_UNIT = b.ID_UNIT AND a.ID_UNIT = '$unit' AND a.NAMA_RBM LIKE '%$keyword%' ORDER BY a.NAMA_RBM LIMIT $start, $dtpp");
		
		for ($i = 0; $i < count($run); $i++) {
			$idpetugas = $run[$i]->ID_PETUGAS;
			// cari di petugas
			if ( ! empty($idpetugas)) {
				$srun = $this->db->query("SELECT `NAMA_PETUGAS` FROM `petugas` WHERE `ID_PETUGAS` = '$idpetugas'", TRUE);
				$petugas = $srun->NAMA_PETUGAS;
			} else $petugas = 'UNSET';
			
			// cari total pelanggan
			$srun = $this->db->query("SELECT COUNT(`ID_RINCIAN_RBM`) AS `HASIL` FROM `rincian_rbm` WHERE `ID_RBM` = '" . $run[$i]->ID_RBM . "'", TRUE);
			$jumlah = $srun->HASIL;
			
			// cari daya
			$srun = $this->db->query("SELECT SUM(a.DAYA_PELANGGAN) AS TOTAL FROM pelanggan a, rincian_rbm b WHERE b.ID_RBM = '" . $run[$i]->ID_RBM . "' AND a.ID_PELANGGAN = b.ID_PELANGGAN", TRUE);
			
			$r['data'][] = array(
				'id' => $run[$i]->ID_RBM,
				'nama' => $run[$i]->NAMA_RBM,
				'hari' => substr($run[$i]->NAMA_RBM, -1, 1),
				'tanggal' => $run[$i]->TANGGAL_RBM,
				'petugas' => $idpetugas,
				'nama_petugas' => $petugas,
				'total' => $jumlah,
				'daya' => number_format($srun->TOTAL, 0, ',', '.')
			);
		}
		
		/*
		$run = $this->db->query("SELECT a.ID_RBM, a.NAMA_RBM, b.ID_PETUGAS, b.NAMA_PETUGAS FROM rbm a, petugas b, unit c WHERE a.ID_PETUGAS = b.ID_PETUGAS AND b.ID_UNIT = c.ID_UNIT AND c.ID_UNIT = '$unit' AND a.NAMA_RBM LIKE '%$keyword%' ORDER BY a.NAMA_RBM LIMIT $start, $dtpp");
		
		for ($i = 0; $i < count($run); $i++) {
			$srun = $this->db->query("SELECT COUNT(`ID_RINCIAN_RBM`) AS `HASIL` FROM `rincian_rbm` WHERE `ID_RBM` = '" . $run[$i]->ID_RBM . "'", TRUE);
			$jumlah = $srun->HASIL;
			
			$srun = $this->db->query("SELECT SUM(a.DAYA_PELANGGAN) AS TOTAL FROM pelanggan a, rincian_rbm b WHERE b.ID_RBM = '" . $run[$i]->ID_RBM . "' AND a.ID_PELANGGAN = b.ID_PELANGGAN", TRUE);
			
			$r['data'][] = array(
				'id' => $run[$i]->ID_RBM,
				'nama' => $run[$i]->NAMA_RBM,
				'hari' => substr($run[$i]->NAMA_RBM, -1, 1),
				'petugas' => $run[$i]->ID_PETUGAS,
				'nama_petugas' => $run[$i]->NAMA_PETUGAS,
				'total' => $jumlah,
				'daya' => number_format($srun->TOTAL, 0, ',', '.')
			);
		}
		$this->db->query("COMMIT");
		*/
		
		return $r;
	}
	
	/**
	 * Tampilkan data gardu perhalaman
	 */
	public function get_gardu() {
		$d = $this->prepare_get(array('unit', 'cpage'));
		extract($d);
		$unit = intval($unit); $cpage = intval($cpage);
		$dtpp = 20;
		$start = $cpage * $dtpp;
		$r = array();
		
		// total data
		$run = $this->db->query("SELECT COUNT(`ID_GARDU`) AS `HASIL` FROM `gardu` WHERE `ID_UNIT` = '$unit' AND `NAMA_GARDU` != ''", TRUE);
		$r['numpage'] = ceil($run->HASIL / $dtpp);
		$r['data'] = array();
		
		$run = $this->db->query("SELECT * FROM `gardu` WHERE `ID_UNIT` = '$unit' AND `NAMA_GARDU` != '' ORDER BY `NAMA_GARDU` LIMIT $start, $dtpp");
		for ($i = 0; $i < count($run); $i++) {
			$srun = $this->db->query("SELECT COUNT(a.ID_PELANGGAN) AS JUMLAH, SUM(DAYA_PELANGGAN) AS TOTAL FROM pelanggan a, gardu b WHERE a.ID_GARDU = b.ID_GARDU AND a.ID_GARDU = '" . $run[$i]->ID_GARDU . "'", TRUE);
			
			$r['data'][] = array(
				'id' => $run[$i]->ID_GARDU,
				'unit' => $run[$i]->ID_UNIT,
				'nama' => $run[$i]->NAMA_GARDU,
				'pelanggan' => number_format($srun->JUMLAH, 0, ',', '.'),
				'daya' => number_format($srun->TOTAL, 0, ',', '.')
			);
		}
		
		return $r;
	}
	
	/**
	 * Save gardu
	 */
	public function save_gardu($id = 0) {
		$d = $this->prepare_post(array('id', 'unit', 'nama'));
		extract($d);
		$id = intval($id);
		$unit = intval($unit);
		$nama = $this->db->escape_str($nama);
		
		// ALTER TABLE  `gardu` ADD  `KOORDINAT_GARDU` VARCHAR( 120 ) NULL
		
		if ($id > 0)
			$run = $this->db->query("UPDATE `gardu` SET `ID_UNIT` = '$unit', `NAMA_GARDU` = '$nama' WHERE `ID_GARDU` = '$id'");
		else
			$run = $this->db->query("INSERT INTO `gardu` VALUES(0, '$unit', '$nama')");
		return array();
	}
	
	/**
	 * Delete gardu
	 */
	public function delete_gardu($id = 0) {
		$id = intval($id);
		$run = $this->db->query("UPDATE `pelanggan` SET `ID_GARDU` = '1' WHERE `ID_GARDU` = '$id'");
		$run = $this->db->query("DELETE FROM `gardu` WHERE `ID_GARDU` = '$id'");
		return array();
	}
	
	/**
	 * get stmt
	 */
	public function get_stmt($iofiles) {
		if (isset($_GET['tgl'])) {
			$get = $this->prepare_get(array('unit', 'blth', 'tgl', 'kode'));
			extract($get);
			list ($d, $m, $y) = explode('/', $tgl);
			$date = $y . '-' . $m . '-'. $d;
			$tanggal = $d . $m . $y;
			$run = $this->db->query("SELECT a.ID_PELANGGAN, a.DAYA_PELANGGAN, a.TARIF_PELANGGAN, b.KODE_KODEPROSES, c.ID_BACAMETER, c.ID_PETUGAS, c.ID_KETERANGAN_BACAMETER, c.LWBP_BACAMETER, c.WBP_BACAMETER, c.KVARH_BACAMETER, d.NAMA_BLTH , SUBSTR(KODUK_PELANGGAN,1,7) as NMRBM FROM pelanggan a, kodeproses b, bacameter c, blth d, petugas e WHERE a.ID_PELANGGAN = c.ID_PELANGGAN AND a.ID_KODEPROSES = b.ID_KODEPROSES AND c.ID_BLTH = d.ID_BLTH AND c.ID_PETUGAS = e.ID_PETUGAS AND e.ID_UNIT = '$unit' AND d.ID_BLTH = '$blth' AND b.ID_KODEPROSES = '$kode' AND DATE(c.TANGGAL_BACAMETER) = '$date'");
			$r = array('file' => '');
			
			$tlwbp = $twbp = $tkvarh = $tpelanggan = $tdaya = 0;
			
			// jika ada data
			if ( ! empty($run)) {
				$str = array();
				$tpelanggan = count($run);
				$nblth = '';
				
				for ($i = 0; $i < count($run); $i++) {
					// cari nama rbm
					
					//$srun = $this->db->query(" SELECT `NAMA_RBM` FROM `rbm` WHERE `ID_PETUGAS` = '" . $run[$i]->ID_PETUGAS . "'", TRUE);
					$rbm = $run[$i]->NMRBM;
					
					// cari keterangan bacameter
					$idketbaca = 0;
					if ($run[$i]->ID_KETERANGAN_BACAMETER != '0') {
						$srun = $this->db->query("SELECT `ID_KETERANGAN_BACAMETER`, `KODE_KETERANGAN_BACAMETER` FROM `keterangan_bacameter` WHERE `ID_KETERANGAN_BACAMETER` = '" . $run[$i]->ID_KETERANGAN_BACAMETER . "'", TRUE);
						$ketbaca = $srun->KODE_KETERANGAN_BACAMETER;
						$idketbaca = $srun->ID_KETERANGAN_BACAMETER;
					} else {
						$ketbaca = ' ';
						$idketbaca = 0;
					}
					
					$lwbp = (floatval($run[$i]->LWBP_BACAMETER) * 100);
					$wbp = (floatval($run[$i]->WBP_BACAMETER) * 100);
					$kvarh = (floatval($run[$i]->KVARH_BACAMETER) * 100);
					
					// cari koreksi
					$srun = $this->db->query("SELECT `ID_KETERANGAN_BACAMETER`, `LWBP_KOREKSI`, `WBP_KOREKSI`, `KVARH_KOREKSI` FROM `koreksi` WHERE `ID_BACAMETER` = '" . $run[$i]->ID_BACAMETER . "' ORDER BY `TANGGAL_KOREKSI` LIMIT 0, 1", TRUE);
					if ( ! empty($srun)) {
						$lwbp = (floatval($srun->LWBP_KOREKSI) * 100);
						$wbp = (floatval($srun->WBP_KOREKSI) * 100);
						$kvarh = (floatval($srun->KVARH_KOREKSI) * 100);
						
						// ubah keterangan baca
						if ($idketbaca != $srun->ID_KETERANGAN_BACAMETER) {
							if ($srun->ID_KETERANGAN_BACAMETER != '0') {
								$prun = $this->db->query("SELECT `KODE_KETERANGAN_BACAMETER` FROM `keterangan_bacameter` WHERE `ID_KETERANGAN_BACAMETER` = '" . $srun->ID_KETERANGAN_BACAMETER . "'", TRUE);
								$ketbaca = $prun->KODE_KETERANGAN_BACAMETER;
							} else $ketbaca = ' ';
						}
					}
					
					$tlwbp += floatval($lwbp);
					$twbp += floatval($wbp);
					$tkvarh += floatval($kvarh);
					$tdaya += floatval($run[$i]->DAYA_PELANGGAN);
					
					if (empty($nblth)) {
						$nmblth = $run[$i]->NAMA_BLTH;
						$bulanx = substr($nmblth, 0, 2);
						$tahunx = substr($nmblth, 2, 4);
						$bulanx = intval($bulanx) + 1;
						if ($bulanx > 12) {
							$bulanx = '01';
							$tahunx = intval($tahunx) + 1;
						}
						$bulanx = str_pad($bulanx, 2, '0', STR_PAD_LEFT);
						$nblth = $bulanx . $tahunx;
					}
					
					if ($kode == 2)
						$str[] = $run[$i]->ID_PELANGGAN . 'I' . $nblth . $rbm . $this->format_stand($lwbp) . $this->format_stand($wbp) . $this->format_stand($kvarh) . $ketbaca . '000000' . ' ' . $tanggal;
					else
						$str[] = $run[$i]->ID_PELANGGAN . 'I' . $nblth . $rbm . $this->format_stand($lwbp) . $ketbaca . $tanggal . '      ';
				}
				
				// tulis ke file
				// cari kode proses
				$run = $this->db->query("SELECT `KODE_KODEPROSES` FROM `kodeproses` WHERE `ID_KODEPROSES` = '$kode'", TRUE);
				$kodeproses = $run->KODE_KODEPROSES;
				
				// cari unit
				$run = $this->db->query("SELECT `NAMA_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
				$namaunit = strtoupper(strtolower($run->NAMA_UNIT));
				$file = 'upload/stmt/' . $kodeproses . '0STMT' . $m . '_' . $tanggal . '.' . $namaunit;
				
				// insert ke stmt
				$ins = $this->db->query("INSERT INTO `stmt` VALUES(0, '$unit', '$kode', '$tpelanggan', '$tdaya', '$tlwbp', '$twbp', '$tkvarh', NOW(), '$file')");
				
				// tulis ke file
				$iofiles->write($file, implode("\r\n", $str), "wb");
				$r['file'] = 'download/stmt/' . basename($file);
			}
			
			return $r;
		}
	}
	
	private function format_stand($a) {
		$a = str_replace(',', '', $a);
		return str_pad($a, 8, '0', STR_PAD_LEFT);
	}
	
	/*
	 * Recheck file NPDE
	 */
	public function recheck() {
		
		/*
		$run = $this->db->query("SELECT `ID_RINCIAN_RBM`, `ID_PELANGGAN` FROM `rincian_rbm` WHERE SUBSTR(`ID_PELANGGAN`, 1, 5) = '51790' ");
		
		$d = array();
		for ($i = 0; $i < count($run); $i++) {
			$r = $run[$i];
			if ( ! isset($d[$r->ID_PELANGGAN])) {
				$d[$r->ID_PELANGGAN] = array($r->ID_RINCIAN_RBM);
			} else {
				$d[$r->ID_PELANGGAN][] = $r->ID_RINCIAN_RBM;
			}
		}
		foreach ($d as $key => $val) {
			if (count($val) == 1) continue;
			else {
				echo $key . ' ==== ' . implode(', ', $val) . '<br>';
				$del = $this->db->query("DELETE FROM `rincian_rbm` WHERE `ID_RINCIAN_RBM` = '{$val[1]}'");
				//var_dump($this->db->get_error());
				//echo 'ID: ' . $val[1] . ' terhapus<br>';
				//echo "DELETE FROM `rincian_rbm` WHERE `ID_RINCIAN_RBM` = '{$val[1]}'; <br>";
			}
		}
		*/		
		///*//================================hapus dobel bacameter================================
		$run = $this->db->query("SELECT `ID_BACAMETER`, `ID_PELANGGAN`, `KIRIM_BACAMETER`,LWBP_BACAMETER FROM `bacameter` WHERE id_blth='7' ORDER BY LWBP_BACAMETER") ;

		$d = array();
		for ($i = 0; $i < count($run); $i++) {
			$r = $run[$i];
			if ( ! isset($r->ID_PELANGGAN)) {
				$d[$r->ID_PELANGGAN] = array($r->ID_BACAMETER . '-' . $r->LWBP_BACAMETER);
			} else {
				$d[$r->ID_PELANGGAN][] = $r->ID_BACAMETER . '-' . $r->LWBP_BACAMETER;
			}
		}
		 echo "hapus dobel bacameter <br>";
		foreach ($d as $key => $val) {
			if (count($val) == 1) continue;
			else {
				$x = explode('-',$val[0]);
				$y = explode('-',$val[1]);
				//echo $key . ' ==== ' . $val[2] . '<br>';
				echo $key . ' ==== ' . implode(', ', $val) . '<br>';
				if($x[1]<$y[1])
				{$del = $this->db->query("DELETE FROM `bacameter` WHERE `ID_BACAMETER` = '{$val[0]}'");}
				else 
				{$del = $this->db->query("DELETE FROM `bacameter` WHERE `ID_BACAMETER` = '{$val[1]}'");}
			}
		}
		//*/
		/*//============================hapus dobel meterpakai==============================
		
		$run = $this->db->query("SELECT `ID_MTRPAKAI`, `ID_PELANGGAN`, `kwh_mtrpakai` FROM `mtrpakai` WHERE `id_blth`='7' order by id_pelanggan,kwh_mtrpakai") ;
		$d = array();
		for ($i = 0; $i < count($run); $i++) {
			$r = $run[$i];
			if ( ! isset($r->ID_PELANGGAN)) {
				$d[$r->ID_PELANGGAN] = array($r->ID_MTRPAKAI . '-' . $r->kwh_mtrpakai);
			} else {
				$d[$r->ID_PELANGGAN][] = $r->ID_MTRPAKAI . '-' . $r->kwh_mtrpakai;
			}
		}
		echo 'hapus dobel meter pakai <br>';
		foreach ($d as $key => $val) {
			if (count($val) == 1) continue;
			else {
				echo $key . ' ==== ' . implode(', ', $val) . '<br>';
				$del = $this->db->query("DELETE FROM `mtrpakai` WHERE `ID_MTRPAKAI` = '{$val[1]}'");
			}
		}*/
				/*
		//================================hitung ulang kwh dlpd =========================
		
	$run = $this->db->query("SELECT `ID_BLTH` FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
			$id_blth = $run->ID_BLTH;
	$run = $this->db->query("select 
	id_pelanggan, 
	if(sum(lkoreksi0)=0 ,sum(lwbp0),sum(lkoreksi0)) as stan0, 
	if(sum(lkoreksi)=0 ,sum(lwbp),sum(lkoreksi)) as stan, 
	if(sum(wkoreksi0)=0 ,sum(wbp0),sum(wkoreksi0)) as wstan0, 
	if(sum(wkoreksi)=0 ,sum(wbp),sum(wkoreksi)) as wstan, 
	if(sum(kvkor0)=0 ,sum(kvarh0),sum(kvkor0)) as kvstan0, 
	if(sum(kvkor)=0,sum(kvarh),sum(kvkor)) as kvstan
	from
	(
	select id_pelanggan, 
	lwbp_bacameter as lwbp0, 
	0 as lwbp, 
	wbp_bacameter as wbp0, 
	0 as wbp, 
	kvarh_bacameter as kvarh0, 
	0 as kvarh, 
	0 as lkoreksi0, 
	0 as lkoreksi,
	0 as wkoreksi0,
	0 as wkoreksi,  
	0 as kvkor0, 
	0 as kvkor from history where id_blth='($id_blth)-1'
	union all
	select id_pelanggan, 
	0 as lwbp0, 
	lwbp_bacameter as lwbp, 
	0 as wbp0, 
	wbp_bacameter as wbp, 
	0 as kvarh0,
	kvarh_bacameter as kvarh, 
	0 as lkoreksi, 0 as lkoreksi0,0 as wkoreksi, 0 as wkoreksi0, 0 as kvkor, 0 as kvkor0 from history where id_blth='$id_blth'
	union all
	select b.id_pelanggan, 
	0 as lwbp0, 
	0 as lwbp, 
	0 as wbp0, 
	0 as wbp, 0 as kvarh0, 0 as kvarh, 
	lwbp_koreksi as lkoreksi, 0 as lkoreksi0, 
	wbp_koreksi as wkoreksi, 0 as wkoreksi0,  
	kvarh_koreksi as kvkor, 0 as kvkor0
	from koreksi a join history b on a.id_bacameter=b.id_bacameter where id_blth='$id_blth'
	union all
	select b.id_pelanggan, 0 as lwbp0, 
	0 as lwbp, 
	0 as wbp0, 0 as wbp,
	0 as kvarh0, 0 as kvarh,
	lwbp_koreksi as lkoreksi0,0 as lkoreksi, 
	wbp_koreksi as wkoreksi0, 0 as wkoreksi, 
	kvarh_koreksi as kvkor0, 0 as kvkor from koreksi a join history b on a.id_bacameter=b.id_bacameter where id_blth='($id_blth)-1'
	) a
	group by id_pelanggan");

	for ($i = 0; $i < count($run); $i++) 
	{
		$r = $run[$i];
		$idpel = $r->id_pelanggan;
		$lwbp = array($r->stan0,$r->stan);
		$wbp = array($r->wstan0,$r->wstan);
		$kvarh = array($r->kvstan0,$r->kvstan);
		
		// cari DAYA, TARIF, FAKTORMETER, FAKTORKWH
		$run = $this->db->query("SELECT `TARIF_PELANGGAN`, `DAYA_PELANGGAN`, `FAKTORKWH_PELANGGAN`, `FAKTORKVAR_PELANGGAN` FROM `pelanggan` WHERE `ID_PELANGGAN` = '$idpel'", TRUE);
		$tarif = strtolower($run->TARIF_PELANGGAN);
		$daya = $run->DAYA_PELANGGAN;
		$fkwh = $run->FAKTORKWH_PELANGGAN;
		$fkvar = $run->FAKTORKVAR_PELANGGAN;
		
		if ($lwbp[1] < $lwbp[0]) {
			if ($tarif == 'i2' OR $daya > 200000) {
				$l = (999999 - ($lwbp[0] + $lwbp[1])) * $fkwh;
				$w = (999999 - ($wbp[0] + $wbp[1])) * $fkwh;
				$kwh = $l + $w;
				
				$jam = ($kwh / $daya) * 1000;
				$k = ($kvarh[1] - $kvarh[0]) * $fkvar;
			} else {
				$kwh = (99999 - ($lwbp[0] + $lwbp[1])) * $fkwh;
				$jam = ($kwh / $daya) * 1000;
				$k = 0;
			}
		} else {
			// hitung kwh
			if ($tarif == 'i2' OR $daya > 200000) {
				$l = ($lwbp[1] - $lwbp[0]) * $fkwh;
				$w = ($wbp[1] - $wbp[0]) * $fkwh;
				$kwh = $l + $w;
				$jam = ($kwh / $daya) * 1000;
				$k = ($kvarh[1] - $kvarh[0]) * $fkvar;
			} else {
				$kwh = ($lwbp[1] - $lwbp[0]) * $fkwh;
				$jam = ($kwh / $daya) * 1000;
				$k = 0;
			}
		}
		
		// rancang dlpd
		$run = $this->db->query("SELECT `KWH_MTRPAKAI`, `JAM_NYALA`, `ID_DLPD`, `PDLPD_MTRPAKAI` FROM `mtrpakai` WHERE `ID_PELANGGAN` = '$idpel' ORDER BY `ID_BLTH`");
		if (empty($run)) {
			$kwhlalu = 0; $kwhrata2 = 0;
		} else {
			$kwhlalu = $run[count($run) - 1]->KWH_MTRPAKAI;
			$totalkwh = 0;
			// rata2 jika lebih dari 
			$numkwh = count($run);
			if ($numkwh > 2) {
				for ($i = 0; $i < $numkwh; $i++) $totalkwh += $run[$i]->KWH_MTRPAKAI;
				$kwhrata2 = $totalkwh / $numkwh;
			} else {
				$kwhrata2 = 0;
			}
		}
	
		$dlpd = 0;
		// jam nyala < 60
		if ($lwbp[1] < $lwbp[0] and $jam > 720) {
			$dlpd = 9;
		} else if ($jam >= 720) {
			$dlpd = 10;
		} else if ($kwh == 0) {
			$dlpd = 8;
		} else if ($kwhlalu == $kwh) {
			$dlpd = 17;
		} else if ($jam < 60) {
			$dlpd = 3;
		} else if ($kwhlalu != 0) {
			if ($kwhrata2 > 0) {
				$kwh50 = ($kwhrata2 / 2);
				// kwh turun < rata2 50%
				if ($kwh < ($kwhrata2 - $kwh50)) $dlpd = 4;
				// kwh naik > rata2 50%
				if ($kwh > ($kwhrata2 + $kwh50)) $dlpd = 5;
			}
		} 
		
		$pdlpd = 0;
		
		$run = $this->db->query("SELECT `ID_MTRPAKAI` FROM `mtrpakai` WHERE `ID_BLTH` = '$id_blth' AND `ID_PELANGGAN` = '$idpel'", TRUE);
		if (empty($run))
			$ins = $this->db->query("INSERT INTO `mtrpakai` VALUES(0, '$idpel', '$id_blth', '$dlpd', '$pdlpd', '$kwh', '$k', '$jam')");
		else
			$run = $this->db->query("UPDATE `mtrpakai` SET `ID_DLPD` = '$dlpd', `PDLPD_MTRPAKAI` = '$pdlpd', `KWH_MTRPAKAI` = '$kwh', `KVARH_MTRPAKAI` = '$k', `JAM_NYALA` = '$jam' WHERE `ID_BLTH` = '$id_blth' AND `ID_PELANGGAN` = '$idpel'");
	}	
	*/
		/*//============================== update id_petugas 0 =========================
		$run = $this->db->query("SELECT a.`ID_PELANGGAN`, b.`ID_PETUGAS` FROM `rincian_rbm` a JOIN rbm b ON a.id_rbm = b.id_rbm where b.id_petugas!=0");
		// where id_petugas='0' and id_blth=5 and kirim_bacameter!='N'");
		$d=array();	
		for ($i=0; $i < count($run); $i++) {
			$r = $run[$i];
			if ( ! isset($r->ID_PELANGGAN)) {
				$d[$r->ID_PELANGGAN] = array($r->ID_PETUGAS);
			} else {
				$d[$r->ID_PELANGGAN][] = $r->ID_PETUGAS;
			}
		}	
		foreach ($d as $key => $val) {
			echo $key . ' === ' . implode(', ', $val) . '<br>';
			//$upd = $this->db->query("update bacameter set id_petugas='{$val[0]}' where id_pelanggan='{$key}' and id_blth='5' and kirim_bacameter!='N'");	
			//echo $key . ' === ' . $val[0] . '<br>';
		}*/
	}
}