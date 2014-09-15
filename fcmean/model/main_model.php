<?php
/**
 * Main Model
 */
namespace Model;

class MainModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Login
	 */
	public function login() {
		$r = array();
		$user = $this->db->escape_str($_POST['username']);
		$passw = crypt($_POST['password'], 'fcmean');
		
		$run = $this->db->query("SELECT COUNT(`ID_ADMIN`) AS `HASIL` FROM `admin` WHERE `USERNAME_ADMIN` = '$user' AND `PASSWORD_ADMIN` = '$passw' AND `STATUS_ADMIN` = '1'", TRUE);
		if ($run->HASIL == 1) {
			$user = $this->db->query("SELECT * FROM `admin` WHERE `USERNAME_ADMIN` = '$user' AND `PASSWORD_ADMIN` = '$passw' AND `STATUS_ADMIN` = '1'", TRUE);
			return array(
				'id' => $user->ID_ADMIN,
				'nama' => $user->NAMA_ADMIN,
				'username' => $user->USERNAME_ADMIN,
				'password' => crypt($user->PASSWORD_ADMIN, $user->USERNAME_ADMIN),
				'newpassword' => ''
			);
		}
		return FALSE;
	}
	
	/**
	 * Cek user password and username
	 */
	public function check_user($username, $password) {
		$run = $this->db->query("SELECT `PASSWORD_ADMIN` FROM `admin` WHERE `USERNAME_ADMIN` = '$username'", TRUE);
		if (empty($run)) return FALSE;
		return $password == crypt($run->PASSWORD_ADMIN, $username);
	}
	
	/**
	 * Dapatkan isi dari database berdasarkan parameter
	 */
	public function getData($param) {
		switch ($param) {
			case 'angkatan':
				$run = $this->db->query("SELECT * FROM `angkatan` WHERE `STATUS_ANGKATAN` = '1'", TRUE);
				return array(
					'id' => $run->ID_ANGKATAN,
					'nama' => $run->NAMA_ANGKATAN
				);
				break;
				
			case 'review':
				$angkatan = $this->getData('angkatan');
				// cari jurusan
				$run = $this->db->query("SELECT COUNT(`ID_JURUSAN`) AS `HASIL` FROM `jurusan`", TRUE);
				$jurusan = $run->HASIL;
				
				// mapel
				$run = $this->db->query("SELECT COUNT(`ID_MAPEL`) AS `HASIL` FROM `mata_pelajaran`", TRUE);
				$mapel = $run->HASIL;
				
				// siswa
				$run = $this->db->query("SELECT COUNT(`ID_SISWA`) AS `HASIL` FROM `siswa` WHERE `ID_ANGKATAN` = '{$angkatan['id']}'", TRUE);
				$siswa = $run->HASIL;
				
				return array(
					'jurusan' => $jurusan,
					'mapel' => $mapel,
					'angkatan' => $angkatan['nama'],
					'siswa' => $siswa
				);
				break;
		}
	}
	
	public function edit_profile() {
		extract($_POST);
		$id = intval($id);
		$nama = $this->db->escape_str($nama);
		$username = $this->db->escape_str($username);
		if ( ! empty($newpassword))
			$password = crypt($newpassword, 'fcmean');
		else
			$password = '';
		
		$query = "UPDATE `admin` SET `NAMA_ADMIN` = '$nama', `USERNAME_ADMIN` = '$username'" . ( ! empty($password) ? ", `PASSWORD_ADMIN` = '$password'" : '') . " WHERE `ID_ADMIN` = '$id'";
		$run = $this->db->query($query);
		return array();
	}
	
	public function process_cluster() {
		// ambil id angkatan
		$run = $this->db->query("SELECT `ID_ANGKATAN` FROM `angkatan` WHERE `STATUS_ANGKATAN` = '1'", TRUE);
		$angkatan = $run->ID_ANGKATAN;
		
		// data jurusan
		$jurusan = array();
		$run = $this->db->query("SELECT `ID_JURUSAN`, `NAMA_JURUSAN` FROM `jurusan`");
		for ($i = 0; $i < count($run); $i++) {
			$jurusan[] = array(
				'id' => $run[$i]->ID_JURUSAN,
				'nama' => $run[$i]->NAMA_JURUSAN
			);
		}
		
		// cari data siswa
		$siswa = $data = array();
		$run = $this->db->query("SELECT `ID_SISWA`, `NIS_SISWA` FROM `siswa` WHERE `ID_ANGKATAN` = '$angkatan' ORDER BY `NIS_SISWA`");
		for ($i = 0; $i < count($run); $i++) {
			$siswa[$i] = array(
				'id' => $run[$i]->ID_SISWA,
				'nis' => $run[$i]->NIS_SISWA
			);
			$srun = $this->db->query("SELECT `DATA_RERATA_NILAI` FROM `rerata_nilai` WHERE `ID_SISWA` = '{$run[$i]->ID_SISWA}' ORDER BY `ID_JURUSAN`");
			if (count($srun) != count($jurusan)) continue;
			for ($j = 0; $j < count($srun); $j++) {
				$siswa[$i]['nilai'][] = $srun[$j]->DATA_RERATA_NILAI;
				$data[$i][] = $srun[$j]->DATA_RERATA_NILAI;
			}
		}
		
		// setting
		$r = parse_ini_file('config/setting.ini');
		$opt = array(
			'c' => count($jurusan),
			'm' => $r['pangkat'],
			'max_iter' => $r['maxiterasi']
		);
		$fcm = new \FCM($opt);
		$fcm->set_data($data);
		$u = $fcm->cluster();
		
		// set id dan nis ke siswa
		for ($i = 0; $i < count($siswa); $i++) {
			$u['data'][$i]['nis'] = $siswa[$i]['nis'];
			$u['data'][$i]['id'] = $siswa[$i]['id'];
		}
		
		// info
		$info = array(
			'numdata' => count($siswa),
			'tanggal' => time(),
			'cluster' => count($jurusan),
			'angkatan' => $angkatan ,
			'jurusan' => $jurusan
		);
		
		// simpan ke database
		$ins = $this->db->query("INSERT INTO `hasil` VALUES(0, '" . serialize($info) . "', '" . serialize($u) . "')");
		
		// tampilkan hasil
		return $this->process_history();
	}
	
	public function delete_process_history($id) {
		$id = intval($id);
		$del = $this->db->query("DELETE FROM `hasil` WHERE `ID_HASIL` = '$id'");
		return $this->process_history();
	}
	
	public function process_history() {
		$run = $this->db->query("SELECT * FROM `hasil` ORDER BY `ID_HASIL` DESC LIMIT 0, 30");
		$r = array();
		if (empty($run)) return $r;
		
		for ($i = 0; $i < count($run); $i++) {
			$info = unserialize($run[$i]->INFO_HASIL);
			$srun = $this->db->query("SELECT `NAMA_ANGKATAN` FROM `angkatan` WHERE `ID_ANGKATAN` = '{$info['angkatan']}'", TRUE);
			
			$r[] = array(
				'id' => $run[$i]->ID_HASIL,
				'siswa' => $info['numdata'],
				'cluster' => $info['cluster'],
				'angkatan' => $srun->NAMA_ANGKATAN,
				'tanggal' => date('d/m/Y H:i', $info['tanggal'])
			);
		}
		return $r;
	}
	
	public function get_detail_process($id) {
		$r = array();
		$id = intval($id);
		
		$run = $this->db->query("SELECT * FROM `hasil` WHERE `ID_HASIL` = '$id'", TRUE);
		$in = unserialize($run->INFO_HASIL);
		$dt = unserialize($run->DATA_HASIL);
		
		// nama jurusan
		$j = $count = array();
		for ($i = 0; $i < count($in['jurusan']); $i++) {
			$j[] = $in['jurusan'][$i]['nama'];
			$count[] = 'Cluster ' . ($i + 1);
			$jur[] = $in['jurusan'][$i]['nama'];
		}
		
		// centroid
		$c = array();
		for ($i = 0; $i < count($dt['centroid']); $i++) {
			foreach ($dt['centroid'][$i] as $key => $val)
				$dt['centroid'][$i][$key] = number_format($val, 2, '.', ',');
			$c[] = '[' . implode(', ', $dt['centroid'][$i]) . ']';
		}
		
		$r['info'] = array(
			'cluster' => $in['cluster'],
			'jurusan' => implode(', ', $j),
			'siswa' => $in['numdata'],
			'angkatan' => $in['angkatan'],
			'centroid' => '[' . implode(', ', $c) . ']',
			'c' => $count,
			'j' => $jur
		);
		
		// tentukan cluster masuk jurusan apa berdasarkan centroid
		$tjurusan = array();
		foreach ($dt['centroid'] as $centroid) {
			$h = 0;
			$k = 0;
			for ($i = 0; $i < count($centroid); $i++) {
				if ($centroid[$i] > $h) {
					$h = $centroid[$i];
					$k = $i;
				}
			}
			$tjurusan[] = $in['jurusan'][$k]['nama'];
		}
		
		$dataperpage = 50;
		$cpage = (isset($_GET['cpage']) ? intval($_GET['cpage']) : 0);
		$totaldata = count($dt['data']);
		$numpage = ceil($totaldata / $dataperpage);
		$r['numpage'] = $numpage;
		$r['cpage'] = $cpage;
		
		// awal dan akhir data
		$start = $cpage * $dataperpage;
		$end = $start + $dataperpage;
		
		// analisa datanya
		$data = array();
		for ($i = $start; $i < $end; $i++) {
			if ( ! isset($dt['data'][$i])) break;
			
			// cari nama siswa
			$siswa = $dt['data'][$i];
			$run = $this->db->query("SELECT `NAMA_SISWA`, `KELAS_SISWA` FROM `siswa` WHERE `ID_SISWA` = '{$siswa['id']}'", TRUE);
			$t = array(
				'no' => ($i + 1),
				'nis' => $siswa['nis'],
				'nama' => $run->NAMA_SISWA,
				'kelas' => $run->KELAS_SISWA,
				'dk' => array(),
				'nilai' => array(),
				'keputusan' => ''
			);
			$keputusan = 0;
			$nilai = 0;
			
			foreach ($siswa as $key => $val) {
				if (is_numeric($key)) {
					if ($val > $nilai) {
						$keputusan = ($key + 1);
						$nilai = $val;
					}
					$t['dk'][] = number_format($val, 3, '.', ',');
					
					// cari nilai siswa
					$idjurusan = $in['jurusan'][$key]['id'];
					$run = $this->db->query("SELECT `DATA_RERATA_NILAI` FROM `rerata_nilai` WHERE `ID_JURUSAN` = '$idjurusan' AND `ID_SISWA` = '{$siswa['id']}'", TRUE);
					$t['nilai'][] = number_format($run->DATA_RERATA_NILAI, 2, '.', ',');
				}
			}
			
			$t['keputusan'] = 'C' . $keputusan;
			$t['jurusan'] = $tjurusan[$keputusan - 1];
			$data[] = $t;
		}
		
		$r['data'] = $data;

		return $r;
	}
	
	public function export_process($id) {
		$r = $this->get_detail_process(intval($id));
		$info = $r['info'];
		$data = $r['data'];
		
		include_once('lib/phpexcel/PHPExcel.php');
		$obj = new \PHPExcel();
		
		$obj->getProperties()->setCreator('Sukmasari')
							 ->setLastModifiedBy('Sukmasari')
							 ->setTitle("Hasil Cluster")
							 ->setSubject('Cluster')
							 ->setDescription('Clustering FCMean')
							 ->setKeywords('cluster', 'fcmean')
							 ->setCategory('Laporan');
		$obj->setActiveSheetIndex(0);
		$obj->getDefaultStyle()->getFont()->setName('Arial');
		$obj->getDefaultStyle()->getFont()->setSize(9);
		
		$obj->getActiveSheet()->setCellValue('A1', "PENENTUAN JURUSAN ANGKATAN " . $info['angkatan']);
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);
		
		$obj->getActiveSheet()->setCellValue('A3', 'NO'); $obj->getActiveSheet()->mergeCells('A3:A4');
		$obj->getActiveSheet()->setCellValue('B3', 'NIS'); $obj->getActiveSheet()->mergeCells('B3:B4');
		$obj->getActiveSheet()->setCellValue('C3', 'NAMA SISWA'); $obj->getActiveSheet()->mergeCells('C3:C4');
		$obj->getActiveSheet()->setCellValue('D3', 'KELAS'); $obj->getActiveSheet()->mergeCells('D3:D4');
		$obj->getActiveSheet()->setCellValue('E3', 'RERATA NILAI');
		for ($i = 0, $c = 'E'; $i < count($info['j']); $i++, $c++) {
			$obj->getActiveSheet()->setCellValue($c . '4', $info['j'][$i]);
		}
		$obj->getActiveSheet()->setCellValue($c . '3', 'KEPUTUSAN'); $obj->getActiveSheet()->mergeCells("{$c}3:{$c}4");
		$obj->getActiveSheet()->getStyle('A3:' . $c . '4')->getFont()->setBold(true);
		$obj->getActiveSheet()->mergeCells('A1:' . $c . '1');
		
		$row = 5;
		for ($i = 0; $i < count($data); $i++, $row++) {
			$obj->getActiveSheet()->setCellValue('A' . $row, ($i + 1));
			$obj->getActiveSheet()->setCellValue('B' . $row, $data[$i]['nis']);
			$obj->getActiveSheet()->setCellValue('C' . $row, $data[$i]['nama']);
			$obj->getActiveSheet()->setCellValue('D' . $row, $data[$i]['kelas']);
			for ($j = 0, $col = 'E'; $j < count($data[$i]['nilai']); $j++, $col++) {
				$obj->getActiveSheet()->setCellValue($col . $row, $data[$i]['nilai'][$j]);
			}
			$obj->getActiveSheet()->setCellValue($c . $row, $data[$i]['jurusan']);
		}
		$obj->getActiveSheet()->getStyle('A3:' . $c . ($row-1))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		
		$obj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$obj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
		
		$objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"fcmean-" . $id . ".xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}
}