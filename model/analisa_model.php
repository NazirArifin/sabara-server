<?php
/**
 * Analisa Model
 */
namespace Model;

set_time_limit(0);

class AnalisaModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Tampilkan stmt
	 */
	public function get_stmt() {
		$get = $this->prepare_get(array('unit', 'cpage', 'numpage'));
		extract($get);
		$unit = intval($unit);
		$cpage = intval($cpage);
		$datapp = 15;
		$start = $datapp * $cpage;
		$r = array('data' => array(), 'numpage' => 0);
		
		// total data
		$run = $this->db->query("SELECT COUNT(`ID_STMT`) AS `HASIL` FROM `stmt` WHERE `ID_UNIT` = '$unit'", TRUE);
		$r['numpage'] = ceil($run->HASIL / $datapp);
		
		$run = $this->db->query("SELECT b.NAMA_UNIT, c.KODE_KODEPROSES, c.NAMA_KODEPROSES, a.PELANGGAN_STMT, a.DAYA_STMT, a.LWBP_STMT, a.WBP_STMT, a.KVARH_STMT, a.TANGGAL_STMT, a.FILE_STMT FROM stmt a, unit b, kodeproses c WHERE a.ID_UNIT = '$unit' AND a.ID_UNIT = b.ID_UNIT AND a.ID_KODEPROSES = c.ID_KODEPROSES ORDER BY a.TANGGAL_STMT DESC LIMIT $start, $datapp");
		for ($i = 0; $i < count($run); $i++) {
			list($date, $time) = explode(' ', $run[$i]->TANGGAL_STMT);
			list($y, $m, $d) = explode('-', $date);
			$tanggal = "$d/$m/$y $time";
			$r['data'][] = array(
				'namaunit' => $run[$i]->NAMA_UNIT,
				'kodeproses' => $run[$i]->KODE_KODEPROSES . ' - ' . $run[$i]->NAMA_KODEPROSES,
				'total' => $run[$i]->PELANGGAN_STMT,
				'daya' => $run[$i]->DAYA_STMT,
				'lwbp' => number_format($run[$i]->LWBP_STMT, 2, ',', '.'),
				'wbp' => number_format($run[$i]->WBP_STMT, 2, ',', '.'),
				'kvarh' => number_format($run[$i]->KVARH_STMT, 2, ',', '.'),
				'tanggal' => $tanggal,
				'file' => preg_replace('/^upload/', 'download', $run[$i]->FILE_STMT),
			);
		}
		return $r;
	}
	
	/**
	 * Tampilkan npde
	 */
	public function get_npde() {
		$get = $this->prepare_get(array('unit', 'cpage', 'numpage'));
		extract($get);
		$unit = intval($unit);
		$cpage = intval($cpage);
		$datapp = 15;
		$start = $datapp * $cpage;
		$r = array('data' => array(), 'numpage' => 0);
		
		// total data
		$run = $this->db->query("SELECT COUNT(`ID_NPDE`) AS `HASIL` FROM `npde` WHERE `ID_UNIT` = '$unit'", TRUE);
		$r['numpage'] = ceil($run->HASIL / $datapp);
		
		$run = $this->db->query("SELECT a.NAMA_UNIT, b.TIPE_NPDE, b.BLTH_NPDE, b.KODE_NPDE, b.JUMLAH_NPDE, b.TANGGAL_NPDE, b.FILE_NPDE FROM unit a, npde b WHERE a.ID_UNIT = b.ID_UNIT AND b.ID_UNIT = '$unit' ORDER BY b.TANGGAL_NPDE DESC");
		
		for ($i = 0; $i < count($run); $i++) {
			list($date, $time) = explode(' ', $run[$i]->TANGGAL_NPDE);
			list($y, $m, $d) = explode('-', $date);
			$tanggal = "$d/$m/$y $time";
			
			$r['data'][] = array(
				'namaunit' => $run[$i]->NAMA_UNIT,
				'tipe' => ($run[$i]->TIPE_NPDE == '1' ? 'Paska Bayar' : 'Pra Bayar'),
				'blth' => $run[$i]->BLTH_NPDE,
				'kode' => $run[$i]->KODE_NPDE,
				'jumlah' => number_format($run[$i]->JUMLAH_NPDE, 0, ',', '.'),
				'tanggal' => $tanggal,
				'file' => 'download/' . $run[$i]->FILE_NPDE
			);
		}
		return $r;
	}
	
	/**
	 * Daftar DLPD
	 */
	public function get_dlpd_list() {
		$get = $this->prepare_get(array('unit', 'blth', 'rbm', 'dlpd'));
		extract($get);
		$unit = intval($unit);
		$blth = intval($blth);
		$rbm = floatval($rbm);
		$r = array();
		
		// dapatkan bulan tahun
		$run = $this->db->query("SELECT `NAMA_BLTH` FROM `blth` WHERE `ID_BLTH` = '$blth'", TRUE);
		$nm_blth = $run->NAMA_BLTH;
		
		$bln = intval(substr($nm_blth, 0, 2));
		$thn = intval(substr($nm_blth, 2, 4));
		$bln--;
		if ($bln == 0) {
			$bln = 12; $thn--;
		}
		$bln = str_pad($bln, 2, '0', STR_PAD_LEFT);
		$run = $this->db->query("SELECT `ID_BLTH` FROM `blth` WHERE `NAMA_BLTH` = '" . ($bln . $thn) ."'", TRUE);
		$blth0 = $run->ID_BLTH;
		
		// nama unit
		$run = $this->db->query("SELECT `NAMA_UNIT`, `KODE_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$namaunit = $run->NAMA_UNIT;
		$kodeunit = $run->KODE_UNIT;
		
		// nama rbm
		$nmrbm = '';
		if ( ! empty($rbm)) {
			$run = $this->db->query("SELECT `NAMA_RBM` FROM `rbm` WHERE `ID_RBM` = '$rbm'", TRUE);
			$nmrbm = $run->NAMA_RBM;
		}
		
		// susun dlpd
		$adlpd = array();
		foreach ($dlpd as $val)
			$adlpd[] = "a.ID_DLPD = '$val'";
		$sdlpd = implode(' OR ', $adlpd);
		
		// cari di mtrpakai
		if (empty($nmrbm)) {
			$run = $this->db->query("SELECT a.KWH_MTRPAKAI, a.KVARH_MTRPAKAI, a.JAM_NYALA, b.ID_PELANGGAN, b.NAMA_PELANGGAN, b.TARIF_PELANGGAN, b.DAYA_PELANGGAN, c.KODE_DLPD, c.NAMA_DLPD FROM mtrpakai a join pelanggan b on a.ID_PELANGGAN = b.ID_PELANGGAN join dlpd c on a.ID_DLPD = c.ID_DLPD WHERE a.ID_BLTH = '$blth' AND SUBSTR(b.ID_PELANGGAN, 1, 5) = '$kodeunit' AND ($sdlpd)");
		} else {
			$run = $this->db->query("SELECT a.KWH_MTRPAKAI, a.KVARH_MTRPAKAI, a.JAM_NYALA, b.ID_PELANGGAN, b.NAMA_PELANGGAN, b.TARIF_PELANGGAN, b.DAYA_PELANGGAN, c.KODE_DLPD, c.NAMA_DLPD FROM mtrpakai a join pelanggan b on a.ID_PELANGGAN = b.ID_PELANGGAN join dlpd c on a.ID_DLPD = c.ID_DLPD WHERE SUBSTR(b.KODUK_PELANGGAN, 1, 7) = '$nmrbm' AND a.ID_BLTH = '$blth' AND SUBSTR(b.ID_PELANGGAN, 1, 5) = '$kodeunit' AND ($sdlpd)");
		}
		
		for ($i = 0; $i < count($run); $i++) {
			$row = $run[$i];
			$id_pelanggan = $row->ID_PELANGGAN;
			
			// cari dibacameter lalu
			$srun = $this->db->query("SELECT `ID_BACAMETER`, `LWBP_BACAMETER`, `WBP_BACAMETER`, `KVARH_BACAMETER` FROM `bacameter` WHERE `ID_PELANGGAN` = '$id_pelanggan' AND `ID_BLTH` = '$blth0'", TRUE);
			if (empty($srun)) $lwbp0 = $wbp0 = $kvarh0 = $idbacameter = 0;
			else {
				$lwbp0 = $srun->LWBP_BACAMETER;
				$wbp0 = $srun->WBP_BACAMETER;
				$kvarh0 = $srun->KVARH_BACAMETER;
				$idbacameter = $srun->ID_BACAMETER;
			}
			// cari di koreksi lalu
			if ( ! empty($idbacameter)) {
				$srun = $this->db->query("SELECT `LWBP_KOREKSI`, `WBP_KOREKSI`, `KVARH_KOREKSI` FROM `koreksi` WHERE `ID_BACAMETER` = '$idbacameter'", TRUE);
				if ( ! empty($srun)) {
					$lwbp0 = $srun->LWBP_KOREKSI;
					$wbp0 = $srun->WBP_KOREKSI;
					$kvarh0 = $srun->KVARH_KOREKSI;
				}
 			}
			
			// cari di meter ini
			$srun = $this->db->query("SELECT `ID_BACAMETER`, `LWBP_BACAMETER`, `WBP_BACAMETER`, `KVARH_BACAMETER`,`KODE_KETERANGAN_BACAMETER` FROM `bacameter` a LEFT JOIN `keterangan_bacameter` b on a.id_keterangan_bacameter=b.id_keterangan_bacameter WHERE `ID_PELANGGAN` = '$id_pelanggan' AND `ID_BLTH` = '$blth'", TRUE);
			if (empty($srun)) $lwbp = $wbp = $kvarh = $idbacameter = 0;
			else {
				$lwbp = $srun->LWBP_BACAMETER;
				$wbp = $srun->WBP_BACAMETER;
				$kvarh = $srun->KVARH_BACAMETER;
				$idbacameter = $srun->ID_BACAMETER;
				$lbkb = $srun->KODE_KETERANGAN_BACAMETER;
				if($lbkb==null) $lbkb='';
			}
			// cari di koreksi
			if ( ! empty($idbacameter)) {
				$srun = $this->db->query("SELECT `LWBP_KOREKSI`, `WBP_KOREKSI`, `KVARH_KOREKSI`,`ID_KETERANGAN_BACAMETER` FROM `koreksi` WHERE `ID_BACAMETER` = '$idbacameter'", TRUE);
				if ( ! empty($srun)) {
					$lwbp = $srun->LWBP_KOREKSI;
					$wbp = $srun->WBP_KOREKSI;
					$kvarh = $srun->KVARH_KOREKSI;
					$lbkb = $srun->ID_KETERANGAN_BACAMETER;
					if($lbkb==null) $lbkb='';
				}
			}
			
			$r[] = array(
				'kwh' => number_format($row->KWH_MTRPAKAI, 0, ',', '.'),
				'jam' => number_format($row->JAM_NYALA, 0, ',', '.'),
				'idpel' => $row->ID_PELANGGAN,
				'nama' => $row->NAMA_PELANGGAN,
				'tarif' => $row->TARIF_PELANGGAN,
				'daya' => number_format($row->DAYA_PELANGGAN, 0, ',', '.'),
				'kodedlpd' => $row->KODE_DLPD,
				'dlpd' => $row->NAMA_DLPD,
				'lwbp' => number_format(floatval($lwbp), 2, ',', '.'),
				'wbp' => number_format(floatval($wbp), 2, ',', '.'),
				'kvarh' => number_format(floatval($kvarh), 2, ',', '.'),
				'lwbp0' => number_format(floatval($lwbp0), 2, ',', '.'),
				'wbp0' => number_format(floatval($wbp0), 2, ',', '.'),
				'kvarh0' => number_format(floatval($kvarh0), 2, ',', '.'),
				'lbkb' => $lbkb
			);
		}
		return $r;
	}
	
	public function get_graph_lbkb() {
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
		
		// cari unit
		$run = $this->db->query("SELECT `KODE_UNIT`, `NAMA_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$nmunit = $run->NAMA_UNIT;
		$kdunit = $run->KODE_UNIT;
		
		// blth
		$run = $this->db->query("SELECT `ID_BLTH`, `NAMA_BLTH` FROM `blth` WHERE `ID_BLTH` >= $blth1 && `ID_BLTH` <= $blth2");
		// subtitle
		if (count($run) == 1) $subtitle = 'BULAN TAHUN ' . $run[0]->NAMA_BLTH;
		else $subtitle = 'BULAN TAHUN ' . $run[0]->NAMA_BLTH . ' - ' . $run[count($run) - 1]->NAMA_BLTH;
		
		$blth = $categories = array();
		for ($i = 0; $i < count($run); $i++) {
			$categories[] = $run[$i]->NAMA_BLTH;
			$blth[] = $run[$i]->ID_BLTH;
		}
		
		// keterangan baca
		$ketbaca = $series = array();
		$run = $this->db->query("SELECT `ID_KETERANGAN_BACAMETER`, `KODE_KETERANGAN_BACAMETER` FROM `keterangan_bacameter` ORDER BY `ID_KETERANGAN_BACAMETER`");
		for ($i = 0; $i < count($run); $i++) {
			$ketbaca[] = $run[$i]->ID_KETERANGAN_BACAMETER;
			$series[] = array(
				'name' => $run[$i]->KODE_KETERANGAN_BACAMETER,
				'data' => array_fill(0, count($categories), 0)
			);
		}
		
		// cari di keterangan bacameter
		for ($i = 0; $i < count($ketbaca); $i++) {
			$idketbaca = $ketbaca[$i];
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
					$series[$ktbc - 1]['data'][$j] += 1;
				}
			}
		}
		
		//d.title, d.subtitle, d.categories, d.series)
		$r = array(
			'title' => 'DATA LBKB UNIT ' . strtoupper($nmunit), 
			'subtitle' => $subtitle,
			'categories' => $categories,
			'series' => $series
		);
		return $r;
	}
	
	/**
	 * Analisa RBM kodeproses
	 */
	public function get_analisa_rbm() {
		$get = $this->prepare_get(array('unit', 'blth', 'kdproses'));
		extract($get);
		$unit = intval($unit);
		$blth = intval($blth);
		$kdproses = intval($kdproses);
		
		$run = $this->db->query("select x.* from 
(select z.RBM, sum(z.JML) as JML_PLG, sum(z.STLALU) as `ST_LALU`, sum(z.STINI) as `ST_INI`, sum(z.Kwhini) as KWH_INI, sum(z.KWH_LALU) as KWH_LALU  from 
( 
select 
	substr(koduk_pelanggan,1,7) as RBM,
	count(b.id_pelanggan) as JML, 
	0 as STLALU, 
	sum(lwbp_bacameter) as STINI,
	0 as Kwhini, 
	0 as kwh_lalu 
from pelanggan a join bacameter b on a.id_pelanggan=b.id_pelanggan 
where id_blth={$blth} and id_kodeproses={$kdproses} group by substr(koduk_pelanggan,1,7) 
union all 
select 
	substr(koduk_pelanggan,1,7) as RBM,
	0 as JML, 
	sum(lwbp_bacameter) as STLALU,
	0 as STINI,
	0 as Kwhini, 
	0 as kwh_lalu 
from pelanggan a join bacameter b on a.id_pelanggan=b.id_pelanggan 
where id_blth=({$blth}-1) and id_kodeproses={$kdproses} group by substr(koduk_pelanggan,1,7) 
union all
select 
	substr(a.koduk_pelanggan,1,7) as RBM,
	0 as JML, 
	0 as STLALU,
	0 as STINI,
	0 as Kwhini, 
	sum(b.kwh_mtrpakai) as kwh_lalu 
from pelanggan a join mtrpakai b on a.id_pelanggan=b.id_pelanggan 
where id_blth=({$blth}-1) and id_kodeproses={$kdproses} group by substr(a.koduk_pelanggan,1,7)
union all
select 
	substr(a.koduk_pelanggan,1,7) as RBM,
	0 as JML, 
	0 as STLALU,
	0 as STINI,
	sum(b.kwh_mtrpakai) as Kwhini, 
	0 as kwh_lalu 
from pelanggan a join mtrpakai b on a.id_pelanggan=b.id_pelanggan 
where id_blth=({$blth}) and id_kodeproses={$kdproses} group by substr(a.koduk_pelanggan,1,7)
) z 
group by z.RBM
) x where JML_PLG != 0");
		$r = array();
		$plg = $stlalu = $stini = $kwhlalu = $kwhini = 0;
		
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'rbm' => $run[$i]->RBM,
				'plg' => $run[$i]->JML_PLG,
				'st_lalu' => number_format($run[$i]->ST_LALU, 2, ',', '.'),
				'st_ini' => number_format($run[$i]->ST_INI, 2, ',', '.'),
				'kwh_lalu' => number_format($run[$i]->KWH_LALU, 0, ',' ,'.'),
				'kwh_ini' => number_format($run[$i]->KWH_INI, 0, ',' ,'.')
			);
			
			$plg += $run[$i]->JML_PLG;
			$stlalu += $run[$i]->ST_LALU;
			$stini += $run[$i]->ST_INI;
			$kwhlalu += $run[$i]->KWH_LALU;
			$kwhini += $run[$i]->KWH_INI;
		}
		
		return array(
			'data' => $r,
			'total' => array(
				'plg' => $plg,
				'st_lalu' => number_format($stlalu, 2, ',', '.'),
				'st_ini' => number_format($stini, 2, ',', '.'),
				'kwh_lalu' => number_format($kwhlalu, 0, ',' ,'.'),
				'kwh_ini' => number_format($kwhini, 0, ',' ,'.'),
			)
		);
	}
	
	/**
	 * Analisa Tarif kodeproses
	 */
	public function get_analisa_tarif() {
		$get = $this->prepare_get(array('unit', 'blth', 'kdproses'));
		extract($get);
		$unit = intval($unit);
		$blth = intval($blth);
		$kdproses = intval($kdproses);
		
		if ($kdproses != 2) {
			$run = $this->db->query("select x.* from 
(select z.tarif as `tarif`,z.daya as `daya`, sum(z.JML) as JML_PLG, sum(z.STLALU) as `ST_LALU`, sum(z.STINI) as `ST_INI`, (sum(z.STINI)-sum(z.STLALU)) as KWH_INI, sum(z.KWH_LALU) as KWH_LALU  from 
( 
select 
	tarif_pelanggan as tarif,
    daya_pelanggan as daya,
	count(b.id_pelanggan) as JML, 
	0 as STLALU, 
	sum(lwbp_bacameter) as STINI,
	0 as Kwhini, 
	0 as kwh_lalu 
from pelanggan a join bacameter b on a.id_pelanggan=b.id_pelanggan 
where id_blth={$blth} and id_kodeproses={$kdproses} group by tarif_pelanggan, daya_pelanggan
union all 
select 
	tarif_pelanggan as tarif,
    daya_pelanggan as daya,
	0 as JML, 
	sum(lwbp_bacameter) as STLALU,
	0 as STINI,
	0 as Kwhini, 
	0 as kwh_lalu 
from pelanggan a join bacameter b on a.id_pelanggan=b.id_pelanggan 
where id_blth=({$blth}-1)  and id_kodeproses={$kdproses} group by tarif_pelanggan, daya_pelanggan
union all
select 
	tarif_pelanggan as tarif,
    daya_pelanggan as daya,
	0 as JML, 
	0 as STLALU,
	0 as STINI,
	0 as Kwhini, 
	sum(b.kwh_mtrpakai) as kwh_lalu 
from pelanggan a join mtrpakai b on a.id_pelanggan=b.id_pelanggan 
where id_blth=({$blth}-1)  and id_kodeproses={$kdproses} group by substr(a.koduk_pelanggan,1,7)
) z 
group by z.tarif,z.daya
) x where JML_PLG != 0");
		
			$r = array();
			for ($i = 0; $i < count($run); $i++) {
				$r[] = array(
					'tarif' => $run[$i]->tarif,
					'daya' => $run[$i]->daya,
					'plg' => $run[$i]->JML_PLG,
					'st_lalu' => number_format($run[$i]->ST_LALU, 2, ',', '.'),
					'st_ini' => number_format($run[$i]->ST_INI, 2, ',', '.'),
					'kwh_lalu' => number_format($run[$i]->KWH_LALU, 0, ',', '.'),
					'kwh_ini' => number_format($run[$i]->KWH_INI, 0, ',', '.')
				);
			}
		} else {
			$run = $this->db->query("select x.* from 
(select z.tarif as `tarif`,z.daya as `daya`, sum(z.JML) as JML_PLG, sum(z.LWBPLALU) as `LWBPLALU`,sum(z.WBPLALU) as `WBPLALU`, sum(z.KVARLALU) as `KVARLALU`,sum(z.LWBPINI) as `LWBPINI`,sum(z.WBPINI) as `WBPINI`, sum(z.KVARINI) as `KVARHINI`,(sum(z.LWBPINI)+sum(z.WBPINI)-sum(z.LWBPLALU)-sum(z.WBPLALU))*SUM(z.FAKTORKWH_PELANGGAN) as PEMKWHINI, (sum(z.KVARINI)-sum(z.KVARLALU))*SUM(z.FAKTORKVAR_PELANGGAN) AS PEMKVARHINI, sum(z.P_KWH_LALU) as KWHLALU  from 
( 
select 
	tarif_pelanggan as tarif,
    daya_pelanggan as daya,
	count(b.id_pelanggan) as JML, 
	FAKTORKWH_PELANGGAN,
	FAKTORKVAR_PELANGGAN,
	0 as LWBPLALU, 
	0 as WBPLALU, 
	0 as KVARLALU, 
	sum(lwbp_bacameter) as LWBPINI,
    sum(wbp_bacameter) as WBPINI,
    sum(kvarh_bacameter) as KVARINI,
	0 as P_KWH_INI, 
	0 as P_KVAR_INI, 
	0 as P_KWH_LALU, 
	0 as P_KVAR_LALU
from pelanggan a join bacameter b on a.id_pelanggan=b.id_pelanggan 
where id_blth=5 and id_kodeproses=2 group by tarif_pelanggan, daya_pelanggan
union all 
select 
	tarif_pelanggan as tarif,
    daya_pelanggan as daya,
	0 as JML, 
	0 AS FAKTORKWH_PELANGGAN,
	0 AS FAKTORKVAR_PELANGGAN,
	0 as LWBPINI, 
	0 as WBPINI, 
	0 as KVARINI, 
	sum(lwbp_bacameter) as LWBPLALU,
    sum(wbp_bacameter) as WBPLALU,
    sum(kvarh_bacameter) as KVARLALU,
	0 as P_KWH_INI, 
	0 as P_KVAR_INI, 
	0 as P_KWH_LALU, 
	0 as P_KVAR_LALU
from pelanggan a join bacameter b on a.id_pelanggan=b.id_pelanggan 
where id_blth=4 and id_kodeproses=2 group by tarif_pelanggan, daya_pelanggan
union all
select 
	tarif_pelanggan as tarif,
    daya_pelanggan as daya,
	0 AS FAKTORKWH_PELANGGAN,
	0 AS FAKTORKVAR_PELANGGAN,
	0 as JML, 
	0 as LWBPINI, 
	0 as WBPINI, 
	0 as KVARINI, 
	0 as LWBPLALU,
    0 as WBPLALU,
    0 as KVARLALU,
	0 as P_KWH_INI, 
	0 as P_KVAR_INI, 
	sum(b.kwh_mtrpakai) as P_KWH_LALU, 
	sum(b.KVARH_mtrpakai) as P_KVAR_LALU
from pelanggan a join mtrpakai b on a.id_pelanggan=b.id_pelanggan 
where id_blth=4 and id_kodeproses=2 group by tarif_pelanggan, daya_pelanggan
) z 
group by z.tarif,z.daya
) x where JML_PLG!=0
");
			
			$r = array();
			for ($i = 0; $i < count($run); $i++) {
				$r[] = array(
					'tarif' => $run[$i]->tarif,
					'daya' => $run[$i]->daya,
					'plg' => $run[$i]->JML_PLG,
					'lwbp_lalu' => $run[$i]->LWBPLALU,
					'wbp_lalu' => $run[$i]->WBPLALU,
					'kvarh_lalu' => $run[$i]->KVARHLALU,
					'lwbp_ini' => $run[$i]->LWBPINI,
					'wbp_ini' => $run[$i]->WBPINI,
					'kvarh_ini' => $run[$i]->KVARHINI,
					'kwh_ini' => $run[$i]->PEMKWHINI,
					'pkvarh_ini' => $run[$i]->PEMKVARHINI,
					'kwh_lalu' => $run[$i]->KWHLALU,
				);
			}
		}
			
		return $r;
	}
}