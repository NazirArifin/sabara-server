<?php
/**
 * Main Model
 */
namespace Model;

set_time_limit(0);

class MainModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Periksa versi
	 */
	public function check_version() {
		$get = $this->prepare_get(array('version', 'subversion'));
		extract($get);
		$v = intval($version);
		$sv = intval($subversion);
		
		$run = $this->db->query("SELECT `MAIN_VERSION`, `SUB_VERSION` FROM `version` WHERE `STATUS_VERSION` = '1'", TRUE);
		$pass = FALSE;
		if ($v >= intval($run->MAIN_VERSION)) {
			if ($sv >= intval($run->SUB_VERSION)) $pass = TRUE;
		}
		
		return array('status' => ($pass ? 'PASSED' : 'FAILED'));
	}
	
	/**
	 * Login
	 */
	public function login() {
		extract($this->prepare_post(array('username', 'password')));
		$username = $this->db->escape_str($username);
		$password = crypt($password, 'abmpmk');
		
		$r = array();
		$run = $this->db->query("SELECT COUNT(`ID_USER`) AS `HASIL` FROM `user` WHERE `USERNAME_USER` = '$username' AND `PASSWORD_USER` = '$password' AND `STATUS_USER` = '1'", TRUE);		
		
		if ($run->HASIL == 1) {
			$run = $this->db->query("SELECT a.NAMA_LEVELUSER, b.ID_LEVELUSER, b.ID_USER FROM leveluser a, user b WHERE a.ID_LEVELUSER = b.ID_LEVELUSER AND b.USERNAME_USER = '$username' AND b.PASSWORD_USER = '$password'", TRUE);
			
			$r['nama_level'] = $run->NAMA_LEVELUSER;
			$r['id_level'] = intval($run->ID_LEVELUSER);
			$r['id'] = $run->ID_USER;
			$r['username'] = $username;
			$r['password'] = crypt($password, $run->ID_USER);
			
			if ($run->ID_LEVELUSER == 3) {
				$srun = $this->db->query("SELECT a.ID_UNIT, a.KODE_UNIT, a.NAMA_UNIT, b.ID_PETUGAS, b.NAMA_PETUGAS FROM unit a, petugas b WHERE a.ID_UNIT = b.ID_UNIT AND b.ID_USER = '" . $run->ID_USER . "'", TRUE);
				
				$r['id_petugas'] = $srun->ID_PETUGAS;
				$r['id_unit'] = $srun->ID_UNIT;
				$r['kode_unit'] = $srun->KODE_UNIT;
				$r['nama_unit'] = $srun->NAMA_UNIT;
				$r['nama'] = $srun->NAMA_PETUGAS;
			}
			
			if ($run->ID_LEVELUSER == 2) {
				$srun = $this->db->query("SELECT a.ID_UNIT, a.KODE_UNIT, a.NAMA_UNIT, b.ID_KOORDINATOR, b.NAMA_KOORDINATOR FROM unit a, koordinator b WHERE a.ID_UNIT = b.ID_UNIT AND b.ID_USER = '" . $run->ID_USER . "'", TRUE);
				
				$r['id_koordinator'] = $srun->ID_KOORDINATOR;
				$r['id_unit'] = $srun->ID_UNIT;
				$r['kode_unit'] = $srun->KODE_UNIT;
				$r['nama_unit'] = $srun->NAMA_UNIT;
				$r['nama'] = $srun->NAMA_KOORDINATOR;
			}
			
			return $r;
		} else return FALSE;
	}
	
	/**
	 * Mendapatkan isi table
	 */
	public function get_table_data($t, $o = '') {
		$r = array();
		switch ($t) {
			case 'unit':
				$run = $this->db->query("SELECT * FROM `unit` ORDER BY `KODE_UNIT`");
				for ($i = 0; $i < count($run); $i++) {
					$r[] = array(
						'id' => $run[$i]->ID_UNIT, 'kode' => $run[$i]->KODE_UNIT, 'nama' => $run[$i]->NAMA_UNIT
					);
				}
				break;
				
			case 'blth':
				$run = $this->db->query("SELECT * FROM `blth`" . ($o == 'aktif' ? " WHERE `STATUS_BLTH` = '1'" : '') . " ORDER BY `NAMA_BLTH` DESC LIMIT 0, 24");
				for ($i = 0; $i < count($run); $i++) {
					$r[] = array(
						'id' => $run[$i]->ID_BLTH,
						'nama' => $run[$i]->NAMA_BLTH,
						'status' => $run[$i]->STATUS_BLTH
					);
				}
				if ($o == 'aktif') return $r[0];
				break;
				
			case 'keterangan_bacameter':
				$run = $this->db->query("SELECT * FROM `keterangan_bacameter`");
				for ($i = 0; $i < count($run); $i++) {
					$r[] = array(
						'id' => $run[$i]->ID_KETERANGAN_BACAMETER,
						'kode' => $run[$i]->KODE_KETERANGAN_BACAMETER,
						'nama' => $run[$i]->NAMA_KETERANGAN_BACAMETER
					);
				}
				break;
				
			case 'kodeproses':
				$run = $this->db->query("SELECT * FROM `kodeproses`");
				for ($i = 0; $i < count($run); $i++) {
					$r[] = array(
						'id' => $run[$i]->ID_KODEPROSES,
						'kode' => $run[$i]->KODE_KODEPROSES,
						'nama' => $run[$i]->KODE_KODEPROSES . ' - ' . $run[$i]->NAMA_KODEPROSES
					);
				}
				break;
				
			case 'dlpd':
				if ($o) {
					$filter = array(3, 4, 5, 8, 9, 10, 17);
				} else $filter = array();
				
				$run = $this->db->query("SELECT * FROM `dlpd`");
				for ($i = 0; $i < count($run); $i++) {
					if ($o) {
						if ( ! in_array($run[$i]->ID_DLPD, $filter))
							continue;
					}
					$r[] = array(
						'id' => $run[$i]->ID_DLPD,
						'kode' => $run[$i]->KODE_DLPD,
						'nama' => $run[$i]->NAMA_DLPD
					);
				}
				break;
				
			case 'tarif':
				$run = $this->db->query("SELECT DISTINCT `TARIF_PELANGGAN` FROM `pelanggan` ORDER BY `TARIF_PELANGGAN`");
				for ($i = 0; $i < count($run); $i++) {
					$r[] = array(
						'id' => $run[$i]->TARIF_PELANGGAN,
						'nama' => $run[$i]->TARIF_PELANGGAN
					);
				}
				break;
				
			case 'daya':
				$run = $this->db->query("SELECT DISTINCT `DAYA_PELANGGAN` FROM `pelanggan` ORDER BY `DAYA_PELANGGAN`");
				for ($i = 0; $i < count($run); $i++) {
					$r[] = array(
						'id' => $run[$i]->DAYA_PELANGGAN,
						'nama' => number_format($run[$i]->DAYA_PELANGGAN, 0, ',', '.')
					);
				}
				break;
		}
		return $r;
	}
	
	/**
	 * Cek user password and username
	 */
	public function check_user($username, $password) {
		$run = $this->db->query("SELECT `ID_USER`, `PASSWORD_USER` FROM `user` WHERE `USERNAME_USER` = '$username'", TRUE);
		if (empty($run)) return FALSE;
		return $password == crypt($run->PASSWORD_USER, $run->ID_USER);
	}
	
	/**
	 * LIST IDPEL
	 */
	public function get_list_idpel() {
		$q = $this->db->escape_str($_GET['q']);
		$u = intval($_GET['u']);
		//if (isset($_GET['g'])) $ganda = " AND (`TARIF_PELANGGAN` LIKE '%I2%' OR `DAYA_PELANGGAN` > 200000)";
		//else $ganda = '';
		
		if (empty($u))
			$run = $this->db->query("SELECT `ID_PELANGGAN` FROM `pelanggan` WHERE `ID_PELANGGAN` LIKE '%" . $q . "%' LIMIT 0, 15");
		else {
			$run = $this->db->query("SELECT `KODE_UNIT` FROM `unit` WHERE `ID_UNIT` = '$u'", TRUE);
			$run = $this->db->query("SELECT `ID_PELANGGAN` FROM `pelanggan` WHERE `ID_PELANGGAN` LIKE '" . $run->KODE_UNIT . "%' LIMIT 0, 15");
		}
		$r = array();
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'id' => $run[$i]->ID_PELANGGAN,
				'text' => $run[$i]->ID_PELANGGAN
			);
		}
		return $r;
	}
	
	/**
	 * LIST RBM
	 */
	public function get_list_rbm() {
		$q = $this->db->escape_str($_GET['q']);
		$u = intval($_GET['u']);
		if (empty($u))
			$run = $this->db->query("SELECT `ID_RBM`, `NAMA_RBM` FROM `rbm` WHERE `NAMA_RBM` LIKE '%" . $q . "%' LIMIT 0, 15");
		else
			$run = $this->db->query("SELECT a.ID_RBM, a.NAMA_RBM FROM rbm a, petugas b WHERE a.ID_PETUGAS = b.ID_PETUGAS AND b.ID_UNIT = '$u' AND a.NAMA_RBM LIKE '%" . $q . "%'");
		$r = array();
		for ($i = 0; $i < count($run); $i++) {
			$r[] = array(
				'id' => $run[$i]->ID_RBM,
				'text' => $run[$i]->NAMA_RBM
			);
		}
		return $r;
	}
	
	/**
	 * Dapatkan map per rbm
	 */
	public function get_map($id) {
		$id = floatval($id);
		$data = array();
		
		$runa = $this->db->query("SELECT `ID_BLTH`, `NAMA_BLTH` FROM `blth` WHERE `STATUS_BLTH`=1", TRUE);
		$id_blth = $runa->ID_BLTH;
		$blth = $runa->NAMA_BLTH;
				
		$run = $this->db->query("SELECT a.ID_PELANGGAN, a.NAMA_PELANGGAN, a.TARIF_PELANGGAN, a.DAYA_PELANGGAN, a.KOORDINAT_PELANGGAN, a.koduk_pelanggan as KODUK, b.URUT_RINCIAN_RBM, `LWBP_BACAMETER`, `TANGGAL_BACAMETER`,`FOTO_BACAMETER` FROM pelanggan a join rincian_rbm b on b.ID_PELANGGAN = a.ID_PELANGGAN join bacameter d on a.id_pelanggan=d.id_pelanggan WHERE b.ID_RBM = '$id' and d.id_blth='$id_blth' ORDER BY b.URUT_RINCIAN_RBM");
		if (  count($run) == 0 ) {
			$run = $this->db->query("SELECT a.ID_PELANGGAN, a.NAMA_PELANGGAN, a.TARIF_PELANGGAN, a.DAYA_PELANGGAN, a.KOORDINAT_PELANGGAN, a.koduk_pelanggan as KODUK, b.URUT_RINCIAN_RBM, '' as `LWBP_BACAMETER`, '' as `TANGGAL_BACAMETER`,'' as `FOTO_BACAMETER` FROM pelanggan a join rincian_rbm b on b.ID_PELANGGAN = a.ID_PELANGGAN WHERE b.ID_RBM = '$id' ORDER BY b.URUT_RINCIAN_RBM");
			for ($i = 0; $i < count($run); $i++) {
				$k = $run[$i]->KOORDINAT_PELANGGAN;
				if ( ! empty($k)) {
					$koordinat = json_decode($k);
					$data[] = array(
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
					);
				}
			}
		} else {
			for ($i = 0; $i < count($run); $i++) {
				$k = $run[$i]->KOORDINAT_PELANGGAN;
				if ( ! empty($k)) {
					$koordinat = json_decode($k);
					$data[] = array(
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
					);
				}
			}
		}
		if (count($data) > 0) {
			$rand = array_rand($data, 1);
			$center = array($data[$rand]['lat'], $data[$rand]['longt']);
		} else $center = array();
		
		return array(
			'data' => $data, 'center' => $center
		);
	}
	
	/**
	 * Dapatkan map per gardu
	 */
	public function get_map2($id) {
		$id = floatval($id);
		$data = $center = array();
		
		// DISINI QUERY
		// // ALTER TABLE  `gardu` ADD  `KOORDINAT_GARDU` VARCHAR( 120 ) NULL
		// $center diisi dengan koordinat gardu
		// baris ini contoh
		$GARDU = $this->db->query("select NAMA_GARDU,mid(koordinat_gardu,14,11) as lat,mid(koordinat_gardu,40,11) as longt from gardu where id_gardu=$id", TRUE);

			$center = array(
				'lat' => floatval($GARDU->lat), 
				'longt' => floatval($GARDU->longt),
				'nama' => $GARDU->NAMA_GARDU
			);
			
		$runa = $this->db->query("SELECT `ID_BLTH`, `NAMA_BLTH` FROM `blth` WHERE `STATUS_BLTH`=1", TRUE);
		$id_blth = $runa->ID_BLTH;
		$blth = $runa->NAMA_BLTH;
				
		$run = $this->db->query("SELECT a.ID_PELANGGAN, a.NAMA_PELANGGAN, a.TARIF_PELANGGAN, a.DAYA_PELANGGAN, mid(koordinat_pelanggan,14,11) as lat,mid(koordinat_pelanggan,40,11) as longt, a.koduk_pelanggan as KODUK,`LWBP_BACAMETER`, `TANGGAL_BACAMETER`,`FOTO_BACAMETER` FROM pelanggan a left join gardu b on b.ID_gardu = a.ID_gardu left join bacameter d on a.id_pelanggan=d.id_pelanggan WHERE b.id_GARDU = '$id' and d.id_blth='$id_blth'");
		if (  count($run) == 0 ) {
			$runx = $this->db->query("SELECT a.ID_PELANGGAN, a.NAMA_PELANGGAN, a.TARIF_PELANGGAN, a.DAYA_PELANGGAN, mid(koordinat_pelanggan,14,11) as lat,mid(koordinat_pelanggan,40,11) as longt, a.koduk_pelanggan as KODUK, b.URUT_RINCIAN_RBM, '' as `LWBP_BACAMETER`, '' as `TANGGAL_BACAMETER`,'' as `FOTO_BACAMETER` FROM pelanggan a left join gardu b on b.ID_gardu = a.ID_gardu WHERE b.ID_GARDU = '$id' ");
			if ( ! empty($runx)) {
				for ($i = 0; $i < count($runx); $i++) {
					$k = $runx[$i]->lat;
					if ( ! empty($k)) {
						//$koordinat = json_decode($k);
						$data[] = array(
							'lat' => floatval($runx[$i]->lat), 
							'longt' => floatval($runx[$i]->longt),
							'idpel' => $run[$i]->ID_PELANGGAN,
							'nama' => $run[$i]->NAMA_PELANGGAN,
							'tarif' => $run[$i]->TARIF_PELANGGAN,
							'daya' => $run[$i]->DAYA_PELANGGAN,
							'koduk' => $run[$i]->KODUK,
							'stan' => $run[$i]->LWBP_BACAMETER,
							'waktu' => $run[$i]->TANGGAL_BACAMETER,
							'foto' => $run[$i]->FOTO_BACAMETER,
							'bulan' => $blth
						);
					}
				}
			}
		} else {
			for ($i = 0; $i < count($run); $i++) {
				$k = $run[$i]->lat;
				if ( ! empty($k)) {
					//$koordinat = json_decode($k);
					$data[] = array(
						'lat' => floatval($run[$i]->lat), 
						'longt' => floatval($run[$i]->longt),
						'idpel' => $run[$i]->ID_PELANGGAN,
						'nama' => $run[$i]->NAMA_PELANGGAN,
						'tarif' => $run[$i]->TARIF_PELANGGAN,
						'daya' => $run[$i]->DAYA_PELANGGAN,
						'koduk' => $run[$i]->KODUK,
						'stan' => $run[$i]->LWBP_BACAMETER,
						'waktu' => $run[$i]->TANGGAL_BACAMETER,
						'foto' => $run[$i]->FOTO_BACAMETER,
						'bulan' => $blth
					);
				}
			}
		}
					
		return array(
			'data' => $data, 'center' => $center
		);
	}
}

