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
}