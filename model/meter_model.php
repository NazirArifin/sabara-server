<?php
/**
 * Meter Model
 */
namespace Model;

set_time_limit(0);

class MeterModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	public function get_list_stand($iskoreksi = FALSE) {
		$r = array();
		$get = $this->prepare_get(array('unit', 'rbm', 'idpel', 'nometer'));
		extract($get);
		
		// jika tidak koreksi
		if ( ! $iskoreksi) {
			if ( ! empty($rbm)) {
				$rbm = intval($rbm);
				$run = $this->db->query("SELECT `ID_BLTH` FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
				$blth = $run->ID_BLTH;
				
				$run = $this->db->query("SELECT a.ID_PELANGGAN, a.NAMA_PELANGGAN FROM pelanggan a, rincian_rbm b WHERE a.ID_PELANGGAN = b.ID_PELANGGAN AND b.ID_RBM = '$rbm' ORDER BY b.URUT_RINCIAN_RBM");
				
				for ($i = 0; $i < count($run); $i++) {
					$row = $run[$i];
					$idpel = $row->ID_PELANGGAN;
					$s = $this->db->query("SELECT `ID_BACAMETER` FROM `bacameter` WHERE `ID_BLTH` = '$blth' AND `ID_PELANGGAN` = '$idpel' LIMIT 0, 1", TRUE);
					if (empty($s)) {
						$r[] = array(
							'idpel' => $idpel,
							'nama' => $row->NAMA_PELANGGAN
						);
					} else continue;
				}
				return $r;
			}
			
			if ( ! empty($idpel)) {
				$idpel = $this->db->escape_str($idpel);
				$r = $this->get_stand($idpel);
				if ($r['lwbp'] != 0) return null;
			}

		} else {
			$idpel = $this->db->escape_str($idpel);
			$r = $this->get_stand($idpel);
			//if ($r['lwbp'] == 0) return null;
		}
		
		return $r;
	}
	
	/**
	 * Format angka stand
	 */
	private function format_stand($a) {
		$a = str_replace(',' , '.', $a);
		if ($a == '') return '0.00';
		
		if (strpos($a, '.') !== FALSE) {
			list($u, $p) = explode('.', $a);
			$a = $u . '.' . str_pad($p, 2, '0', STR_PAD_LEFT);
		} else $a .= '.00';
		return $a;
	}
	
	/**
	 * Proses kwh dari stand, berikan dlpd bila ada
	 */
	private function proses_kwh($data, $idpel = '', $id_blth = '', $iskoreksi = FALSE) {
		$lwbp = $data['lwbp'];
		$wbp = $data['wbp'];
		$kvarh = $data['kvarh'];
		
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
	
	/**
	 * Operasi stand
	 */
	public function operate_stand($iskoreksi = FALSE, $ishp = FALSE, $data = array()) {
		if ($ishp) {
			$post = $this->prepare_post(array('id_petugas', 'id_pelanggan', 'lwbp', 'wbp', 'kvarh', 'keterangan', 'latitude', 'longitude', 'waktu', 'gardu'));
			extract($post);
			$idpel = $id_pelanggan;
			$ketbaca = $keterangan;
			$tipekirim = 'H';
			$idpetugas = $id_petugas;
		} else {
			if (empty($data)) {
				$post = $this->prepare_post(array('longitude', 'latitude', 'idpel', 'gardu', 'lwbp0', 'lwbp', 'wbp0', 'wbp', 'kvarh0', 'kvarh', 'ketbaca'));
				extract($post);
				$waktu = date('');
				$tipekirim = 'W';
				$idpetugas = '';
			} else {
				// import pembacaan
				extract($data);
				$tipekirim = 'H';
			}
		}
		
		// dapatkan bulan tahun
		$run = $this->db->query("SELECT `ID_BLTH`, `NAMA_BLTH` FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		$id_blth = $run->ID_BLTH;
		$nm_blth = $run->NAMA_BLTH;
		
		$bln = intval(substr($nm_blth, 0, 2));
		$thn = intval(substr($nm_blth, 2, 4));
		$bln--;
		if ($bln == 0) {
			$bln = 12; $thn--;
		}
		$bln = str_pad($bln, 2, '0', STR_PAD_LEFT);
		$run = $this->db->query("SELECT `ID_BLTH` FROM `blth` WHERE `NAMA_BLTH` = '" . ($bln . $thn) ."'", TRUE);
		$id_blth0 = $run->ID_BLTH;
		
		// entry data
		if ( ! $iskoreksi) {
			// cari di bacameter, siapa tau udah ada
			$run = $this->db->query("SELECT COUNT(`ID_BACAMETER`) AS `HASIL` FROM `bacameter` WHERE `ID_BLTH` = '$id_blth' AND `ID_PELANGGAN` = '$idpel'", TRUE);
			
			if (strlen($lwbp) > 0) $lwbp = $this->format_stand($lwbp);
			$wbp = $this->format_stand($wbp);
			$kvarh = $this->format_stand($kvarh);
			
			$plbkb = 0;
			if ( ! isset($lwbp0)) {
				$srun = $this->db->query("SELECT `ID_KETERANGAN_BACAMETER`, `LWBP_BACAMETER`, `WBP_BACAMETER`, `KVARH_BACAMETER`, `PLBKB_BACAMETER` FROM `bacameter` WHERE `ID_PELANGGAN` = '$idpel' AND `ID_BLTH` = '$id_blth0'", TRUE);
				if ( ! empty($srun)) {
					$lwbp0 = $srun->LWBP_BACAMETER;
					$wbp0 = $srun->WBP_BACAMETER;
					$kvarh0 = $srun->KVARH_BACAMETER;
					$lbkb0 = $srun->ID_KETERANGAN_BACAMETER;
					$plbkb = $srun->PLBKB_BACAMETER;
				} else {
					$lwbp0 = $wbp0 = $kvarh0 = $lbkb0 = 0;
				}
			}
			$lwbp0 = $this->format_stand($lwbp0);
			$wbp0 = $this->format_stand($wbp0);
			$kvarh0 = $this->format_stand($kvarh0);
			
			if (empty($run->HASIL)) {
				$run = $this->db->query("SELECT `KOORDINAT_PELANGGAN`, `ID_GARDU` FROM `pelanggan` WHERE `ID_PELANGGAN` = '$idpel'", TRUE);
				
				// lihat lbkb bulan lalu
				if ( ! isset($lbkb0)) {
					$erun = $this->db->query("SELECT `ID_KETERANGAN_BACAMETER`, `PLBKB_BACAMETER` FROM `bacameter` WHERE `ID_PELANGGAN` = '$idpel' AND `ID_BLTH` = '$id_blth0'", TRUE);
					if ( ! empty($erun)) {
						if ($ketbaca != 0) {
							if ($ketbaca == $erun->ID_KETERANGAN_BACAMETER) {
								$plbkb = $erun->PLBKB_BACAMETER + 1;
							} else $plbkb = 1;
						}
					} else {
						if ($ketbaca != 0) $plbkb = 1;
					}
				}
				
				// cari id petugas
				if (empty($idpetugas)) {
					$prun = $this->db->query("SELECT b.ID_PETUGAS FROM rincian_rbm a, rbm b WHERE a.ID_RBM = b.ID_RBM AND a.ID_PELANGGAN = '$idpel'", TRUE);
					$idpetugas = $prun->ID_PETUGAS;
				}
				
				// insert ke bacameter
				$ins = $this->db->query("INSERT INTO `bacameter` VALUES(0, '$idpetugas', '$ketbaca', '$idpel', '$id_blth', " . (empty($waktu) ? "NOW()" : "'$waktu'") . ", '$lwbp', '$wbp', '$kvarh', '', NOW(), '$plbkb', '$tipekirim',0)");
				
				$upd = array();
				// koordinat
				$koordinat = json_encode(array('latitude' => $latitude, 'longitude' => $longitude));
				if ($run->KOORDINAT_PELANGGAN != $koordinat AND ! empty($latitude)) $upd[] = "`KOORDINAT_PELANGGAN` = '$koordinat'";			
				// gardu
				if ( ! empty($gardu)) {
					$gardu = strtoupper(strtolower($gardu));
					$prun = $this->db->query("SELECT `ID_GARDU` FROM `gardu` WHERE `NAMA_GARDU` = '$gardu'", TRUE);
					if ( ! empty($run->ID_GARDU)) {
						if ( ! empty($prun->ID_GARDU)) {
							$id_gardu = $prun->ID_GARDU;
							if ($id_gardu != $run->ID_GARDU) $upd[] = "`ID_GARDU` = '$id_gardu'";
						}
					}
				}
				if ( ! empty($upd)) $run = $this->db->query("UPDATE `pelanggan` SET " . implode(", ", $upd) . " WHERE `ID_PELANGGAN` = '$idpel'");
				
				// hitung kwh jika rumah tidak kosong
				$dlpd = $this->proses_kwh(array(
					'lwbp' => array($lwbp0, $lwbp),
					'wbp' => array($wbp0, $wbp),
					'kvarh' => array($kvarh0, $kvarh)
				), $idpel, $id_blth);
				
				echo 'TERSIMPAN'; return;
			} else {
				// jika data bacameter sudah ada update lwbp, wbp dan kvarh
				$run = $this->db->query("UPDATE `bacameter` SET `LWBP_BACAMETER` = '$lwbp', `WBP_BACAMETER` = '$wbp', `KVARH_BACAMETER` = '$kvarh' WHERE `ID_PELANGGAN` = '$idpel' AND `ID_BLTH` = '$id_blth'");
				
				// hitung kwh jika rumah tidak kosong
				$dlpd = $this->proses_kwh(array(
					'lwbp' => array($lwbp0, $lwbp),
					'wbp' => array($wbp0, $wbp),
					'kvarh' => array($kvarh0, $kvarh)
				), $idpel, $id_blth);
				
				echo 'GAGAL';
			}
		
		// koreksi data
		} else {
			// cari lbkb bulan ini
			$run = $this->db->query("SELECT `ID_BACAMETER` FROM `bacameter` WHERE `ID_BLTH` = '$id_blth' AND `ID_PELANGGAN` = '$idpel'", TRUE);
			$idbacameter = $run->ID_BACAMETER;
			
			// cari lbkb bulan lalu
			$run = $this->db->query("SELECT `ID_KETERANGAN_BACAMETER` FROM `bacameter` WHERE `ID_BLTH` = '$id_blth0' AND `ID_PELANGGAN` = '$idpel'", TRUE);
			if (empty($run)) {
				if ($ketbaca != 0) {
					$u = $this->db->query("UPDATE `bacameter` SET `PLBKB_BACAMETER` = '1' WHERE `ID_BACAMETER` = '$idbacameter'");
				}
			} else {
				if ($run->ID_KETERANGAN_BACAMETER != $ketbaca) {
					if ($ketbaca != 0) {
						$u = $this->db->query("UPDATE `bacameter` SET `PLBKB_BACAMETER` = '1' WHERE `ID_BACAMETER` = '$idbacameter'");
					} else {
						$u = $this->db->query("UPDATE `bacameter` SET `PLBKB_BACAMETER` = '0' WHERE `ID_BACAMETER` = '$idbacameter'");
					}
				} else {
					$u = $this->db->query("UPDATE `bacameter` SET `PLBKB_BACAMETER` + 1 WHERE `ID_BACAMETER` = '$idbacameter'");
				}
			}
			
			$ins = $this->db->query("INSERT INTO `koreksi` VALUES(0, '$idbacameter', '$ketbaca', '$lwbp', '$wbp', '$kvarh', NOW(),'$idpel')");
			$upt = $this->db->query("UPDATE `bacameter` SET `KOREKSI`=1 WHERE `ID_BACAMETER` = '$idbacameter'");
			// hitung kwh
			$dlpd = $this->proses_kwh(array(
				'lwbp' => array($lwbp0, $lwbp),
				'wbp' => array($wbp0, $wbp),
				'kvarh' => array($kvarh0, $kvarh)
			), $idpel, $id_blth, TRUE);
			
			if ( ! empty($_FILES)) {
				$file = $_FILES['file'];
				$filename = strtolower($file['name']);
				@move_uploaded_file($file['tmp_name'], 'upload/foto/' . $filename);
				$filefoto= 'upload/foto/'.$filename;
				$run = $this->db->query("UPDATE bacameter SET FOTO_BACAMETER = '$filefoto' WHERE ID_PELANGGAN = '$idpel' AND ID_BLTH = '$blth'");
			}
		}
	}
	
	/**
	 * Input baca meter dari android
	 */
	public function bacameter($iofiles = '') {
		// input dari android
		if (empty($_FILES)) {
			$this->operate_stand(FALSE, TRUE);
			return;
		}
		
		// ekstrak nama
		$nama = $_FILES['file']['name'];
		if ( ! preg_match('/^.+_([0-9]+)_([0-9\-]+)_([0-9]+).*\.txt/', $nama)) return FALSE;
		
		$config['upload_path']		= 'upload/baca/';
		$config['allowed_types']	= 'txt';
		$config['encrypt_name']		= TRUE;
		$iofiles->upload_config($config);
		$iofiles->upload('file');
		$file = $_FILES['file']['name'];
		
		$filename 	= $iofiles->upload_get_param('file_name');
		$data = @file('upload/baca/' . $filename);
		$r = array('normal' => 0, 'keterangan' => 0, 'total' => 0);
		
		ob_start();
		$this->db->query("START TRANSACTION");
		for ($i = 0; $i < count($data); $i++) {
			$line = trim($data[$i]);
			if (empty($line)) continue;
			list($time, $idpetugas, $idpel, $lwbp, $wbp, $kvarh, $ketbaca, $latitude, $longitude, $gardu) = explode(';', $data[$i]);
			$r['total']++;
			if ($ketbaca == 0) $r['normal']++;
			else $r['keterangan']++;
			
			$d = array(
				'idpetugas' => $idpetugas,
				'idpel' => $idpel,
				'lwbp' => $lwbp,
				'wbp' => $wbp,
				'kvarh' => $kvarh,
				'ketbaca' => $ketbaca,
				'latitude' => $latitude,
				'longitude' => $longitude,
				'waktu' => $time,
				'gardu' => $gardu
			);
			$this->operate_stand(FALSE, FALSE, $d);
		}
		$this->db->query("COMMIT");
		ob_end_clean();
		return $r;
	}
	
	public function get_stand($id) {
		$r = array();
		// cari informasi pelanggan
		$run = $this->db->query("SELECT a.ID_PELANGGAN, a.NAMA_PELANGGAN, a.ALAMAT_PELANGGAN, a.TARIF_PELANGGAN, a.DAYA_PELANGGAN, a.KOORDINAT_PELANGGAN, b.NAMA_GARDU FROM pelanggan a, gardu b WHERE a.ID_PELANGGAN = '$id' AND a.ID_GARDU = b.ID_GARDU", TRUE);
		
		if ( ! empty($run->KOORDINAT_PELANGGAN)) {
			$koordinat = json_decode($run->KOORDINAT_PELANGGAN);
			$r['latitude'] = $koordinat->latitude;
			$r['longitude'] = $koordinat->longitude;
		} else {
			$r['latitude'] = $r['longitude'] = '';
		}
		
		$r['idpel'] = $run->ID_PELANGGAN;
		$r['nama'] = $run->NAMA_PELANGGAN;
		$r['tarif'] = $run->TARIF_PELANGGAN;
		$r['daya'] = $run->DAYA_PELANGGAN;
		$r['alamat'] = $run->ALAMAT_PELANGGAN;
		$r['gardu'] = $run->NAMA_GARDU;
		
		// cari informasi blth
		$run = $this->db->query("SELECT `ID_BLTH`, `NAMA_BLTH` FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		$bln = intval(substr($run->NAMA_BLTH, 0, 2));
		$thn = intval(substr($run->NAMA_BLTH, 2, 4));
		$namablth = $run->NAMA_BLTH;
		$blth = $run->ID_BLTH;
		
		// bulan lalu
		$bln--;
		if ($bln == 0) {
			$bln = 12; $thn--;
		}
		if (strlen($bln) == 1) $bln = '0' . $bln;
		$run = $this->db->query("SELECT `ID_BLTH` FROM `blth` WHERE `NAMA_BLTH` = '" . ($bln . $thn) ."'", TRUE);
		if (empty($run))
			$r['lwbp0'] = $r['wbp0'] = $r['kvarh0'] = 0;
		else {
			$blth0 = $run->ID_BLTH;
			$run = $this->db->query("SELECT `ID_BACAMETER`, `LWBP_BACAMETER`, `WBP_BACAMETER`, `KVARH_BACAMETER` FROM `bacameter` WHERE `ID_PELANGGAN` = '$id' AND `ID_BLTH` = '$blth0'", TRUE);
			if (empty($run)) {
				$r['lwbp0'] = $r['wbp0'] = $r['kvarh0'] = 0;
			} else {
				$idbacameter = $run->ID_BACAMETER;
				$r['lwbp0'] = str_replace('.', ',', $run->LWBP_BACAMETER);
				$r['wbp0'] = str_replace('.', ',', $run->WBP_BACAMETER);
				$r['kvarh0'] = str_replace('.', ',', $run->KVARH_BACAMETER);
				
				// cari di koreksi
				$run = $this->db->query("SELECT `LWBP_KOREKSI`, `WBP_KOREKSI`, `KVARH_KOREKSI` FROM `koreksi` WHERE `ID_BACAMETER` = '$idbacameter'", TRUE);
				if ( ! empty($run)) {
					$r['lwbp0'] = str_replace('.', ',', $run->LWBP_KOREKSI);
					$r['wbp0'] = str_replace('.', ',', $run->WBP_KOREKSI);
					$r['kvarh0'] = str_replace('.', ',', $run->KVARH_KOREKSI);
				}
			}
		}
		
		// bulan sekarang
		$run = $this->db->query("SELECT `ID_BACAMETER`, `ID_KETERANGAN_BACAMETER`, `LWBP_BACAMETER`, `WBP_BACAMETER`, `KVARH_BACAMETER`, `FOTO_BACAMETER` FROM `bacameter` WHERE `ID_PELANGGAN` = '$id' AND `ID_BLTH` = '$blth'", TRUE);
		if ( ! empty($run)) $idbacameter = $run->ID_BACAMETER;
		else $idbacameter = 0;
		
		if (empty($run)) {
			$r['lwbp'] = $r['wbp'] = $r['kvarh'] = $r['foto'] = '';
			$r['ketbaca'] = 0;
		} else {
			$r['lwbp'] = str_replace('.', ',', $run->LWBP_BACAMETER);
			$r['wbp'] = str_replace('.', ',', $run->WBP_BACAMETER);
			$r['kvarh'] = str_replace('.', ',', $run->KVARH_BACAMETER);
			$r['ketbaca'] = $run->ID_KETERANGAN_BACAMETER;
			$r['foto'] = $run->FOTO_BACAMETER;
			
			// cari di koreksi
			$run = $this->db->query("SELECT `ID_KETERANGAN_BACAMETER`, `LWBP_KOREKSI`, `WBP_KOREKSI`, `KVARH_KOREKSI` FROM `koreksi` WHERE `ID_BACAMETER` = '$idbacameter' ORDER BY `TANGGAL_KOREKSI` DESC LIMIT 0, 1", TRUE);
			if ( ! empty($run)) {
				$r['lwbp'] = str_replace('.', ',', $run->LWBP_KOREKSI);
				$r['wbp'] = str_replace('.', ',', $run->WBP_KOREKSI);
				$r['kvarh'] = str_replace('.', ',', $run->KVARH_KOREKSI);
				$r['ketbaca'] = $run->ID_KETERANGAN_BACAMETER;
			}
		}
		
		if (empty($r['lwbp']))
			$r['plwbp'] = $r['pwbp'] = $r['pkvarh'] = '0';
		else {
			$r['plwbp'] = $this->get_pakai($r['lwbp0'], $r['lwbp']);
			$r['pwbp'] = $this->get_pakai($r['wbp0'], $r['wbp']);
			$r['pkvarh'] = $this->get_pakai($r['kvarh0'], $r['kvarh']);
		}
		if (empty($r['foto'])) $r['foto'] = "img/$id/$namablth";
		return $r;
	}
	
	private function get_pakai($o, $n) {
		$o = floatval(str_replace(',', '.', $o));
		$n = floatval(str_replace(',', '.', $n));
		return number_format(($n - $o), 2, ',', '.');
	}
	
	/**
	 * Save foto
	 */
	public function save_foto($iofiles) {
		if ( ! isset($_FILES)) return FALSE;
		
		// cari bulan tahun
		$run = $this->db->query("SELECT `ID_BLTH` FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		$blth = $run->ID_BLTH;
		$file = $_FILES['file'];
		$numfiles = 0;
		
		$this->db->query("START TRANSACTION");
		for ($i = 0; $i < count($file['name']); $i++) {
			$file = $_FILES['file'];
			$filename = strtolower($file['name'][$i]);
			
			if (@end(explode('.', $filename)) == 'zip') {
				// zip
				//zip_extract($zipfile, $destdir)
				@move_uploaded_file($file['tmp_name'][$i], 'upload/tmp/' . $filename);
				$iofiles->zip_extract('upload/tmp/' . $filename, 'upload/tmp');
				$files = scandir('upload/tmp');
				foreach ($files as $file) {
					if ($file == '.' OR $file == '..') continue;
					if (preg_match('/\.zip$/', $file)) {
						@unlink('upload/tmp/' . $file);
						continue;
					}
					if (preg_match('/^([0-9]{12,15})_([0-9]{6,6})\.(jpg|jpeg|png)$/', $file)) {
						$nama = preg_replace('/\.(jpg|jpeg|png)$/', '', $file);
						list($idpel, $blth) = explode('_', $nama);
						$filefoto='upload/foto/'.$file;
						$run = $this->db->query("UPDATE bacameter SET FOTO_BACAMETER = '$filefoto' WHERE ID_PELANGGAN = '$idpel' AND ID_BLTH = '$blth'");
						
						$config = array();
						$config['source_image'] = 'upload/tmp/' . $file;
						$config['new_image'] = 'upload/foto/' . str_replace('.', '_thumb.', $file);
						$config['width'] = 120;
						$config['height'] = 120;
						$config['maintain_ratio'] = FALSE;
						$iofiles->image_config($config);
						$iofiles->image_resize();
						
						$iofiles->move('upload/tmp/' . $file, 'upload/foto/' . $file);
						$numfiles++;
					}
				}
			} else {
				if ( ! preg_match('/^([0-9]{12,15})_([0-9]{6,6})\.(jpg|jpeg|png)$/', $filename)) continue;
				
				@move_uploaded_file($file['tmp_name'][$i], 'upload/foto/' . $filename);
				// update database
				$nama = preg_replace('/\.(jpg|jpeg|png)$/', '', $filename);
				list($idpel, $blth) = explode('_', $nama);
				$filefoto='upload/foto/'.$file;
				$run = $this->db->query("UPDATE bacameter SET FOTO_BACAMETER = '$filefoto' WHERE ID_PELANGGAN = '$idpel' AND ID_BLTH = '$blth'");
				$numfiles++;
				
				$config = array();
				$config['source_image'] = 'upload/foto/' . $filename;
				$config['new_image'] = 'upload/foto/' . str_replace('.', '_thumb.', $filename);
				$config['width'] = 120;
				$config['height'] = 120;
				$config['maintain_ratio'] = FALSE;
				$iofiles->image_config($config);
				$iofiles->image_resize();
			}
		}
		$this->db->query("COMMIT");
		
		return array('numfiles' => $numfiles);
	}
	
	/**
	 * Mendapatkan daftar lbkb
	 */
	public function get_lbkb() {
		$get = $this->prepare_get(array('unit', 'rbm', 'blth', 'lbkb'));
		extract($get);
		$unit = intval($unit);
		$rbm = floatval($rbm);
		$blth = intval($blth);
		if (empty($lbkb)) $lbkb = array();
		
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
		if (empty($run)) $blth0 = 0;
		else $blth0 = $run->ID_BLTH;
		
		// rbm
		if ($rbm != '') {
			$run = $this->db->query("SELECT `NAMA_RBM` FROM `rbm` WHERE `ID_RBM` = '$rbm'", TRUE);
			$nama_rbm = $run->NAMA_RBM;
		}
		
		// cari keterangan bacameter
		$run = $this->db->query("SELECT * FROM `keterangan_bacameter` ORDER BY `ID_KETERANGAN_BACAMETER`");
		for ($i = 0; $i < count($run); $i++) {
			$datalbkb[] = array(
				'id' => $run[$i]->ID_KETERANGAN_BACAMETER,
				'kode' => $run[$i]->KODE_KETERANGAN_BACAMETER,
				'nama' => $run[$i]->NAMA_KETERANGAN_BACAMETER
			);
		}
		
		$r = array();
		
		if ($rbm != '') {
			// cari di pelanggan, gardu, bacameter, rincian_rbm
			$run = $this->db->query("SELECT a.ID_PELANGGAN, a.NAMA_PELANGGAN, a.ALAMAT_PELANGGAN, a.KODUK_PELANGGAN, b.NAMA_GARDU, a.TARIF_PELANGGAN, a.DAYA_PELANGGAN, c.ID_BACAMETER, c.ID_KETERANGAN_BACAMETER, c.LWBP_BACAMETER, c.WBP_BACAMETER, c.KVARH_BACAMETER, c.PLBKB_BACAMETER, DATE(c.TANGGAL_BACAMETER) AS TANGGAL FROM pelanggan a, gardu b, bacameter c, rincian_rbm d WHERE a.ID_GARDU = b.ID_GARDU AND a.ID_PELANGGAN = c.ID_PELANGGAN AND c.ID_BLTH = '$blth' AND a.ID_PELANGGAN = d.ID_PELANGGAN AND d.ID_RBM = '$rbm'");
		} else {
			$run = $this->db->query("SELECT a.ID_PELANGGAN, a.NAMA_PELANGGAN, a.ALAMAT_PELANGGAN, a.KODUK_PELANGGAN, b.NAMA_GARDU, a.TARIF_PELANGGAN, a.DAYA_PELANGGAN, c.ID_BACAMETER, c.ID_KETERANGAN_BACAMETER, c.LWBP_BACAMETER, c.WBP_BACAMETER, c.KVARH_BACAMETER, c.PLBKB_BACAMETER, DATE(c.TANGGAL_BACAMETER) AS TANGGAL, e.NAMA_RBM FROM pelanggan a, gardu b, bacameter c, rincian_rbm d, rbm e WHERE a.ID_GARDU = b.ID_GARDU AND a.ID_PELANGGAN = c.ID_PELANGGAN AND c.ID_BLTH = '$blth' AND a.ID_PELANGGAN = d.ID_PELANGGAN AND d.ID_RBM = e.ID_RBM AND e.ID_UNIT = '$unit'");
		}
		
		for ($i = 0; $i < count($run); $i++) {
			$row = $run[$i];
			$idpel = $row->ID_PELANGGAN;
			$ketbaca = $row->ID_KETERANGAN_BACAMETER;
			if ($ketbaca == 0) continue;
			
			if ($rbm == '') {
				$nama_rbm = $run[$i]->NAMA_RBM;
			}
			
			if (count($lbkb) > 0) {
				if ( ! in_array($ketbaca, $lbkb)) continue;
			}
			
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
			
			list ($y, $m, $d) = explode('-', $row->TANGGAL);
			$date = "$d/$m/$y";
				
			$lwbp = $row->LWBP_BACAMETER;
			$wbp = $row->WBP_BACAMETER;
			$kvarh = $row->KVARH_BACAMETER;
				
			// cari di koreksi jika ada
			$srun = $this->db->query("SELECT `LWBP_KOREKSI`, `WBP_KOREKSI`, `KVARH_KOREKSI` FROM `koreksi` WHERE `ID_BACAMETER` = '" . $row->ID_BACAMETER . "' ORDER BY `TANGGAL_KOREKSI` DESC LIMIT 0, 1", TRUE);
			if ( ! empty($srun)) {
				$lwbp = $srun->LWBP_KOREKSI;
				$wbp = $srun->WBP_KOREKSI;
				$kvarh = $srun->KVARH_KOREKSI;
			}
			
			$kdbaca = '-';
			$nmbaca = 'Normal';
			for ($j = 0; $j < count($datalbkb); $j++) {
				if ($ketbaca == $datalbkb[$j]['id']) {
					$kdbaca = $datalbkb[$j]['kode'];
					$nmbaca = $datalbkb[$j]['nama'];
					break;
				}
			}
			
			$r[] = array(
				'idpel' => $idpel,
				'progress' => $row->PLBKB_BACAMETER,
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
				'kdbaca' => $kdbaca,
				'nmbaca' => $nmbaca,
				'tanggal' => $date
			);
		}
		
		return $r;
	}
	
	/**
	 * mendapatkan rekab lbkb
	 */
	public function get_rekap_lbkb() {
		$get = $this->prepare_get(array('unit', 'blth1', 'blth2'));
		extract($get);
		$unit = intval($unit);
		$blth1 = intval($blth1);
		$blth2 = intval($blth2);
		if ($blth1 > $blth2) {
			$t = $blth1;
			$blth1 = $blth2;
			$blth2 = $t;
		}
		
		// unit
		$run = $this->db->query("SELECT `KODE_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$kdunit = $run->KODE_UNIT;
		
		$blth = $total = $title = array();
		$run = $this->db->query("SELECT `ID_BLTH`, `NAMA_BLTH` FROM `blth` WHERE `ID_BLTH` <= $blth2 AND `ID_BLTH` >= $blth1");
		for ($i = 0; $i < count($run); $i++) {
			$blth[] = $run[$i]->ID_BLTH;
			$title[] = $run[$i]->NAMA_BLTH;
			$total[] = 0;
		}
		
		$run = $this->db->query("SELECT * FROM `keterangan_bacameter`");
		for ($i = 0; $i < count($run); $i++) {
			$ketbaca[] = array(
				'id' => $run[$i]->ID_KETERANGAN_BACAMETER,
				'kode' => $run[$i]->KODE_KETERANGAN_BACAMETER,
				'nama' => $run[$i]->NAMA_KETERANGAN_BACAMETER,
				'data' => array_fill(0, count($blth), 0)
			);
		}
		
		
		for ($i = 0; $i < count($ketbaca); $i++) {
			$idketbaca = $ketbaca[$i]['id'];
			for ($j = 0; $j < count($blth); $j++) {
				$srun = $this->db->query("SELECT `ID_BACAMETER`, `ID_KETERANGAN_BACAMETER` FROM `bacameter` WHERE `ID_KETERANGAN_BACAMETER` = '$idketbaca' AND `ID_BLTH` = '{$blth[$j]}' AND `ID_PELANGGAN` LIKE '$kdunit%'");
				for ($k = 0; $k < count($srun); $k++) {
					$idbcmtr = $srun[$k]->ID_BACAMETER;
					$ktbc = $srun[$k]->ID_KETERANGAN_BACAMETER;
					// cari di koreksi
					$krun = $this->db->query("SELECT `ID_KETERANGAN_BACAMETER` FROM `koreksi` WHERE `ID_BACAMETER` = '$idbcmtr'", TRUE);
					// jika tidak sama ubah
					if ( ! empty($krun)) {
						if ($ktbc != $krun->ID_KETERANGAN_BACAMETER)
							$ktbc = $krun->ID_KETERANGAN_BACAMETER;
					}
					if ($ktbc == 0) continue;
					
					// tambahkan
					$ketbaca[$ktbc - 1]['data'][$j] += 1;
				}
			}
		}
		
		// hitung total
		for ($i = 0; $i < count($blth); $i++) {
			$t = 0;
			for ($j = 0; $j < count($ketbaca); $j++) {
				$t += $ketbaca[$j]['data'][$i];
			}
			$total[$i] = $t;
		}
		
		return array(
			'title' => $title,
			'data' => $ketbaca,
			'total' => $total
		);
	}
	
	/**
	 * Persiapan rekap pembacaan perbulan
	 */
	public function get_rekap_baca_prepare($id) {
		// baca bulantahun
		$run = $this->db->query("SELECT `ID_BLTH`, `NAMA_BLTH` FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		$idblth = $run->ID_BLTH;
		$namablth = $run->NAMA_BLTH;
		
		$run = $this->db->query("TRUNCATE TABLE bantu");
		
		$run = $this->db->query("insert into bantu
SELECT a.id_pelanggan,nama_rbm,nama_petugas,koordinat_pelanggan, '', '','' FROM `pelanggan` a join rincian_rbm b on a.id_pelanggan=b.id_pelanggan join rbm c on b.id_rbm=c.id_rbm join petugas d on c.id_petugas=d.id_petugas");
		
		$run = $this->db->query("SELECT `ID_PELANGGAN`, if(id_keterangan_bacameter=0,0,1) as `id_keterangan_bacameter`, foto_bacameter FROM bacameter where id_blth={$idblth}");
		for ($i=0; $i < count($run); $i++) {
			$r = $run[$i];
			$idpel=$r->ID_PELANGGAN;
			$lbkb=$r->id_keterangan_bacameter;
			$foto=$r->foto_bacameter;
			$upd = $this->db->query("update bantu set lbkb_pelanggan='$lbkb',foto_bacameter='$foto',terbaca=1 where id_pelanggan='$idpel'");
		}
		return array();
	}
	
	/**
	 * Mendapatkan rekap pembacaaan perbulan berdasarkan rbm
	 */
	public function get_rekap_baca($id) {
		$idunit = intval($id);
		
		// baca bulantahun
		$run = $this->db->query("SELECT `ID_BLTH`, `NAMA_BLTH` FROM `blth` WHERE `STATUS_BLTH` = '1'", TRUE);
		$idblth = $run->ID_BLTH;
		$namablth = $run->NAMA_BLTH;
		
		$run = $this->db->query("select z.RBM,z.petugas,sum(z.plg) as jml, sum(z.terbaca) as `terbaca`, sum(z.plg)-sum(z.terbaca) as `blm_terbaca`, sum(z.gps) as `gps`, if(sum(z.terbaca)<sum(z.gps),0,sum(z.terbaca)-sum(z.gps)) as `non_gps`,sum(z.lbkb) as `lbkb`, sum(z.foto) as `foto`, sum(z.terbaca)-sum(z.foto) as `non_foto` from
(
SELECT 
nama_rbm as RBM, 
nama_petugas as petugas, 
count(id_pelanggan) as plg,
sum(terbaca) as terbaca,
0 as gps,
0 as lbkb,
0 as foto 
FROM bantu group by nama_rbm
union all
select 
nama_rbm as RBM, 
nama_petugas as petugas,
0 as plg, 
0 as terbaca, 
count(id_pelanggan) as gps,
0 as lbkb,
0 as foto
FROM bantu where koordinat_pelanggan!='' group by nama_rbm
union all
select 
nama_rbm as RBM, 
nama_petugas as petugas,
0 as plg, 
0 as terbaca, 
0 as gps,
sum(lbkb_pelanggan) as lbkb, 
0 as foto
FROM bantu where lbkb_pelanggan!='' group by nama_rbm
union all
select 
nama_rbm as RBM, 
nama_petugas as petugas,
0 as plg, 
0 as terbaca, 
0 as gps,
0 as lbkb, 
count(id_pelanggan) as foto
FROM bantu where foto_bacameter!='' group by nama_rbm
) z
group by z.RBM,z.petugas");
		
		//// RBM, jml, petugas, terbaca, blm_terbaca, gps, non_gps, lbkb, non_lbkb, foto, non_foto
		$r = array();
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'rbm' => $run[$i]->RBM,
				'petugas' => $run[$i]->petugas,
				'plg' => $run[$i]->jml,
				'terbaca' => $run[$i]->terbaca,
				'blm_terbaca' => $run[$i]->blm_terbaca,
				'gps' => $run[$i]->gps,
				'non_gps' => $run[$i]->non_gps,
				'lbkb' => $run[$i]->lbkb,
				'foto' => $run[$i]->foto,
				'non_foto' => $run[$i]->non_foto,
			);
		}
		return $r;
	}
}