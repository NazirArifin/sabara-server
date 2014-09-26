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
		
		$this->db->query("START TRANSACTION");
		$run = $this->db->query("SELECT `nama_rbm`, `nama_petugas` FROM `bantu` WHERE `nama_rbm` LIKE '%" . $keyword . "%' GROUP BY `nama_rbm` ORDER BY `nama_rbm`, `nama_petugas` LIMIT $start, $dtpr");
		for ($i = 0; $i < count($run); $i++) {
			$rptag = $rpbk = $plg = 0;
			
			$namarbm = $run[$i]->nama_rbm;
			// daftar idpel
			$srun = $this->db->query("SELECT a.ID_PELANGGAN FROM rincian_rbm a, rbm b WHERE b.NAMA_RBM = '$namarbm' AND b.ID_RBM = a.ID_RBM");
			
			for ($j = 0; $j < count($srun); $j++) {
				$idpel = $srun[$j]->ID_PELANGGAN;
				$trun = $this->db->query("SELECT `RPTAG_TAGIHAN`, `RPBK_TAGIHAN` FROM `tagihan` WHERE `ID_PELANGGAN` = '$idpel'", TRUE);
				
				if ( ! empty($trun)) {
					$rptag += $trun->RPTAG_TAGIHAN;
					$rpbk += $trun->RPBK_TAGIHAN;
					$plg += 1;
				}
			}
			
			$r[] = array(
				'rbm' => $namarbm,
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
		
		// cari rincian idpel
		$run = $this->db->query("SELECT a.ID_PELANGGAN, c.NAMA_PELANGGAN, CONCAT(c.TARIF_PELANGGAN, '/', c.DAYA_PELANGGAN) AS TARIFDAYA, c.ALAMAT_PELANGGAN FROM rincian_rbm a, rbm b, pelanggan c WHERE a.ID_RBM = b.ID_RBM AND b.NAMA_RBM = '$nama' AND a.ID_PELANGGAN = c.ID_PELANGGAN");
		for ($i = 0; $i < count($run); $i++) {
			$idpel = $run[$i]->ID_PELANGGAN;
			// cari di tagihan
			$tag = $bk = $lmbr = 0;
			$srun = $this->db->query("SELECT `LEMBAR_TAGIHAN`, `RPTAG_TAGIHAN`, `RPBK_TAGIHAN` FROM `tagihan` WHERE `ID_PELANGGAN` = '$idpel'", TRUE);
			if ( ! empty($srun)) {
				$tag = $srun->RPTAG_TAGIHAN;
				$bk = $srun->RPBK_TAGIHAN;
				$lmbr = $srun->LEMBAR_TAGIHAN;
			} else continue;
			
			$r[] = array(
				'idpel' => $run[$i]->ID_PELANGGAN,
				'nama' => $run[$i]->NAMA_PELANGGAN,
				'alamat' => $run[$i]->ALAMAT_PELANGGAN,
				'td' => $run[$i]->TARIFDAYA,
				'lembar' => $lmbr,
				'rptag' => number_format($tag, 0, ',', '.'),
				'rpbk' => number_format($bk, 0, ',', '.'),
			);
		}
		
		return $r;
	}
	
	/**
	 * Mendapatkan daftar tusbung harian
	 */
	public function get_list() {
		$r = array();
		
		$get = $this->prepare_get(array('tgl'));
		extract($get);
		$date = explode('/', $tgl);
		if (count($date) != 3) return FALSE;
		list($d, $m, $y) = $date;
		$date = $y . '-' . $m . '-' . $d;
		
		$this->db->query("START TRANSACTION");
		$run = $this->db->query("SELECT * FROM `cabutpasang` WHERE DATE(`TANGGAL_CABUTPASANG`) = '$date'");
		for ($i = 0; $i < count($run); $i++) {
			$sd = array();
			
			// jam dan foto
			list($d, $t) = explode(' ', $run[$i]->TANGGAL_CABUTPASANG);
			$sd['jam'] = $t;
			$sd['foto1'] = ( ! is_file('upload/foto/' . $run[$i]->FOTO1_CABUTPASANG) ? '/img/default.jpg' : '/img/' . $run[$i]->FOTO1_CABUTPASANG);
			$sd['foto2'] = ( ! is_file('upload/foto/' . $run[$i]->FOTO2_CABUTPASANG) ? '/img/default.jpg' : '/img/' . $run[$i]->FOTO2_CABUTPASANG);
			
			// data tagihan
			$tagihan = $run[$i]->ID_TAGIHAN;
			$srun = $this->db->query("SELECT a.ID_PELANGGAN, b.NAMA_PELANGGAN, b.ALAMAT_PELANGGAN, CONCAT(b.TARIF_PELANGGAN, '/', b.DAYA_PELANGGAN) AS TARIFDAYA, a.LEMBAR_TAGIHAN, a.RPTAG_TAGIHAN, a.RPBK_TAGIHAN FROM tagihan a, pelanggan b WHERE a.ID_PELANGGAN = b.ID_PELANGGAN AND a.ID_TAGIHAN = '$tagihan'", TRUE);
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
			$srun = $this->db->query("SELECT `NAMA_KETERANGAN_BACAMETER` FROM `bacameter` WHERE `ID_KETERANGAN_BACAMETER` = '$ketbaca'", TRUE);
			$sd['ketbaca'] = (empty($srun) ? '-' : $srun->NAMA_KETERANGAN_BACAMETER);
			
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
					$ins = $this->db->query("INSERT INTO `tagihan` VALUES(0, '{$blth->ID_BLTH}', '$idpel', '$lembar', '$rptag', '$rpbk')");
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
	 * Dapatkan map per gardu
	 */
	public function get_map($rbm) {
		/*
		$nama = $this->db->escape_str($nama);
		$r = array();
		
		// cari rincian idpel
		$run = $this->db->query("SELECT a.ID_PELANGGAN, c.NAMA_PELANGGAN, CONCAT(c.TARIF_PELANGGAN, '/', c.DAYA_PELANGGAN) AS TARIFDAYA, c.ALAMAT_PELANGGAN FROM rincian_rbm a, rbm b, pelanggan c WHERE a.ID_RBM = b.ID_RBM AND b.NAMA_RBM = '$nama' AND a.ID_PELANGGAN = c.ID_PELANGGAN");
		
		'lat' => floatval($koordinat->latitude), 
						'longt' => floatval($koordinat->longitude),
						'idpel' => $run[$i]->ID_PELANGGAN,
						'urut' => $run[$i]->URUT_RINCIAN_RBM,
						'nama' => $run[$i]->NAMA_PELANGGAN,
						'tarif' => $run[$i]->TARIF_PELANGGAN,
						'daya' => $run[$i]->DAYA_PELANGGAN,
						'koduk' => $run[$i]->KODUK,
						'stan' => $run[$i]->LWBP_BACAMETER,
						'waktu' => $run[$i]->TANGGAL_BACAMETER,
						'foto' => $run[$i]->FOTO_BACAMETER,
						'bulan' => $blth
		
		*/
		$rbm = $this->db->escape_str($rbm);
		
		
		return array(
			'data' => array(),
			'center' => array()
		);
	}
}