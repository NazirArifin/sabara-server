<?php
/**
 * Nilai Model
 */
namespace Model;

class NilaiModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	public function view($id = 0) {
		if (empty($id)) {
			$r = array();	
			// cari angkatan
			$run = $this->db->query("SELECT * FROM `angkatan` WHERE `STATUS_ANGKATAN` = '1'", TRUE);
			$id_angkatan = $run->ID_ANGKATAN;
			
			// cari nilai siswa berdasarkan mapel dan kelas
			if (isset($_GET['mapel']) && isset($_GET['kelas'])) {
				$mapel = intval($_GET['mapel']);
				$kelas = $this->db->escape_str($_GET['kelas']);
				$show = intval($_GET['show']);
				
				// cari siswa pada kelas tersebut
				$run = $this->db->query("SELECT `ID_SISWA`, `NIS_SISWA`, `NAMA_SISWA`, `KELAS_SISWA` FROM `siswa` WHERE `KELAS_SISWA` = '$kelas' AND `ID_ANGKATAN` = '$id_angkatan' ORDER BY `NIS_SISWA`");
				for ($i = 0; $i < count($run); $i++) {
					$id = $run[$i]->ID_SISWA;
					$srun = $this->db->query("SELECT `JUMLAH_NILAI` FROM `nilai` WHERE `ID_SISWA` = '$id' AND `ID_MAPEL` = '$mapel'", TRUE);
					$nilai = (empty($srun) ? '' : number_format($srun->JUMLAH_NILAI, 2, ',', '.'));
					
					if ($show == 0) {
						$r[] = array(
							'id' => $id,
							'nama' => $run[$i]->NAMA_SISWA,
							'nis' => $run[$i]->NIS_SISWA,
							'kelas' => $run[$i]->KELAS_SISWA,
							'nilai' => $nilai
						);
					} else {
						$n = str_replace(',', '.', $nilai);
						$n = intval($n);
						if ( ! empty($n))
							continue;
						else {
							$r[] = array(
								'id' => $id,
								'nama' => $run[$i]->NAMA_SISWA,
								'nis' => $run[$i]->NIS_SISWA,
								'kelas' => $run[$i]->KELAS_SISWA,
								'nilai' => $nilai
							);
						}
					}
				}
			}			
			return $r;
		} else {
			// data siswa
			$run = $this->db->query("SELECT `NIS_SISWA`, `NAMA_SISWA` FROM `siswa` WHERE `ID_SISWA` = '$id'", TRUE);
			$nama = $run->NAMA_SISWA;
			$nis = $run->NIS_SISWA;
			?>
	  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Tutup</span></button>
			<h4 class="modal-title" id="myModalLabel"><?php echo $nis ?> - <?php echo $nama ?></h4>
      </div>
      <div class="modal-body">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>JURUSAN</th>
						<th>MATA PELAJARAN</th>
						<th>NILAI</th>
					</tr>
				</thead>
				<tbody>
					<?php
		$d = array();
		$run = $this->db->query("SELECT `ID_JURUSAN`, `NAMA_JURUSAN` FROM `jurusan`");
		for ($i = 0; $i < count($run); $i++) {
			// cari rerata
			$srun = $this->db->query("SELECT `DATA_RERATA_NILAI` FROM `rerata_nilai` WHERE `ID_JURUSAN` = '{$run[$i]->ID_JURUSAN}' AND `ID_SISWA` = '$id'", TRUE);
			$rata1 = $srun->DATA_RERATA_NILAI;
			$r = array(
				'jurusan' => $run[$i]->NAMA_JURUSAN,
				'rerata' => number_format($rata1, 2, ',', '.'),
				'nilai' => array()
			);
			
			// cari matapelajaran, nilai
			$srun = $this->db->query("SELECT `ID_MAPEL`, `NAMA_MAPEL` FROM `mata_pelajaran` WHERE `ID_JURUSAN` = '{$run[$i]->ID_JURUSAN}'");
			for ($j = 0; $j < count($srun); $j++) {
				$trun = $this->db->query("SELECT `JUMLAH_NILAI` FROM `nilai` WHERE `ID_MAPEL` = '{$srun[$j]->ID_MAPEL}' AND `ID_SISWA` = '$id'", TRUE);
				$n = (empty($trun) ? 0 : $trun->JUMLAH_NILAI);
				$r['nilai'][] = array(
					'mapel' => $srun[$j]->NAMA_MAPEL,
					'nilai' => number_format($n, 2, ',', '.')
				);
			}
			$d[] = $r;
		}
		
		for ($i = 0; $i < count($d); $i++) {
			//if (count($d[$i]['nilai']) == 0) continue;
					?>
					<tr>
						<td rowspan="<?php echo count($d[$i]['nilai']) + 1 ?>"><strong><?php echo $d[$i]['jurusan'] ?></strong></td>
						<td><?php echo $d[$i]['nilai'][0]['mapel'] ?></td>
						<td><?php echo $d[$i]['nilai'][0]['nilai'] ?></td>
					</tr>
					<?php
			for ($j = 1; $j < count($d[$i]['nilai']); $j++) {
				?>
					<tr>
						<td><?php echo $d[$i]['nilai'][$j]['mapel'] ?></td>
						<td><?php echo $d[$i]['nilai'][$j]['nilai'] ?></td>
					</tr>
				<?php
			}
			?>
					<tr>
						<td class="warning"><strong>RATA-RATA</strong></td>
						<td class="warning"><?php echo $d[$i]['rerata'] ?></td>
					</tr>
			<?php
		}
					?>
				</tbody>
			</table>
      </div>
      <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
      </div>			
			<?php
		}
	}
	
	public function add() {
		if (isset($_FILES['file'])) {
			$file = 'model/nilai.csv';
			@move_uploaded_file($_FILES['file']['tmp_name'], $file);
			if ( ! is_file($file)) return 401;
			
			extract($_POST);
			$data = file($file);
			$mapel = intval($mapel);
			
			// detecting delimiter
			if (strpos($data[0], ';') !== FALSE) $delimiter = ';';
			else $delimiter = ',';
			
			// cari jurusan untuk rerata
			$run = $this->db->query("SELECT a.ID_JURUSAN, b.NAMA_MAPEL FROM jurusan a, mata_pelajaran b WHERE b.ID_MAPEL = '$mapel' AND a.ID_JURUSAN = b.ID_JURUSAN", TRUE);
			$idjurusan = $run->ID_JURUSAN;
			
			$run = $this->db->query("SELECT COUNT(`ID_MAPEL`) AS `HASIL` FROM `mata_pelajaran` WHERE `ID_JURUSAN` = '$idjurusan'", TRUE);
			$nummapel = $run->HASIL;
			
			for ($i = 0; $i < count($data); $i++) {
				$row = str_getcsv($data[$i], $delimiter);
				if (count($row) != 2) continue;
				list($nis, $nilai) = $row;
				$nilai = str_replace(',', '.', $nilai);
				
				// cari id siswa
				$run = $this->db->query("SELECT `ID_SISWA` FROM `siswa` WHERE `NIS_SISWA` = '$nis'", TRUE);
				if (empty($run)) continue;
				$idsiswa = $run->ID_SISWA;
				
				// cari nilai
				$run = $this->db->query("SELECT `ID_NILAI`, `JUMLAH_NILAI` FROM nilai WHERE `ID_SISWA` = '$idsiswa' AND `ID_MAPEL` = '$mapel'", TRUE);
				if (empty($run)) {
					// insert
					$ins = $this->db->query("INSERT INTO `nilai` VALUES(0, '$mapel', '$idsiswa', '$nilai')");
				} else {
					$idnilai = $run->ID_NILAI;
					$nilaidb = $run->JUMLAH_NILAI;
					// update jika beda
					if ($nilaidb != $nilai) {
						$upd = $this->db->query("UPDATE `nilai` SET `JUMLAH_NILAI` = '$nilai' WHERE `ID_NILAI` = '$idnilai'");
					}
				}
				
				// hitung rata2
				// ambil semua nilai siswa pada jurusan tersebut
				// nilai, mata_pelajaran, jurusan, siswa
				$run = $this->db->query("SELECT SUM(a.JUMLAH_NILAI) AS `TOTAL` FROM nilai a, mata_pelajaran b WHERE a.ID_SISWA = '$idsiswa' AND a.ID_MAPEL = b.ID_MAPEL AND b.ID_JURUSAN = '$idjurusan'", TRUE);
				$rata2 = round($run->TOTAL / $nummapel, 2);
				
				// masukkan ke tabel rerata
				$run = $this->db->query("SELECT COUNT(`ID_RERATA_NILAI`) AS `HASIL` FROM `rerata_nilai` WHERE `ID_SISWA` = '$idsiswa' AND `ID_JURUSAN` = '$idjurusan'", TRUE);
				if ($run->HASIL == 0) {
					$run = $this->db->query("INSERT INTO `rerata_nilai` VALUES(0, '$idjurusan', '$idsiswa', '$rata2')");
				} else {
					$run = $this->db->query("UPDATE `rerata_nilai` SET `DATA_RERATA_NILAI` = '$rata2' WHERE `ID_SISWA` = '$idsiswa' AND `ID_JURUSAN` = '$idjurusan'");
				}				
			}
			@unlink($file);
			return 200;
			
		} else {
			$id = floatval($_POST['id']);
			$mapel = intval($_POST['mapel']);
			$nilai = str_replace(',', '.', $_POST['nilai']);
			$nilai = floatval($nilai);
			$run = $this->db->query("SELECT COUNT(`ID_NILAI`) AS `HASIL` FROM `nilai` WHERE `ID_MAPEL` = '$mapel' AND `ID_SISWA` = '$id'", TRUE);
			
			if (empty($run->HASIL))
				$run = $this->db->query("INSERT INTO `nilai` VALUES(0, '$mapel', '$id', '$nilai')");
			else
				$run = $this->db->query("UPDATE `nilai` SET `JUMLAH_NILAI` = '$nilai' WHERE `ID_MAPEL` = '$mapel' AND `ID_SISWA` = '$id'");
			
			// hitung rerata nilai
			// cari kode jurusan
			$run = $this->db->query("SELECT `ID_JURUSAN` FROM `mata_pelajaran` WHERE `ID_MAPEL` = '$mapel'", TRUE);
			$jurusan = $run->ID_JURUSAN;
			
			// total mapel
			$run = $this->db->query("SELECT COUNT(`ID_MAPEL`) AS `HASIL` FROM `mata_pelajaran` WHERE `ID_JURUSAN` = '$jurusan'", TRUE);
			$nummapel = $run->HASIL;
			
			// ambil semua nilai siswa pada jurusan tersebut
			// nilai, mata_pelajaran, jurusan, siswa
			$run = $this->db->query("SELECT SUM(a.JUMLAH_NILAI) AS `TOTAL` FROM nilai a, mata_pelajaran b WHERE a.ID_SISWA = '$id' AND a.ID_MAPEL = b.ID_MAPEL AND b.ID_JURUSAN = '$jurusan'", TRUE);
			$rata2 = round($run->TOTAL / $nummapel, 2);
			
			// masukkan ke tabel rerata
			$run = $this->db->query("SELECT COUNT(`ID_RERATA_NILAI`) AS `HASIL` FROM `rerata_nilai` WHERE `ID_SISWA` = '$id' AND `ID_JURUSAN` = '$jurusan'", TRUE);
			if ($run->HASIL == 0) {
				$run = $this->db->query("INSERT INTO `rerata_nilai` VALUES(0, '$jurusan', '$id', '$rata2')");
			} else {
				$run = $this->db->query("UPDATE `rerata_nilai` SET `DATA_RERATA_NILAI` = '$rata2' WHERE `ID_SISWA` = '$id' AND `ID_JURUSAN` = '$jurusan'");
			}
		}		
		//return 201;
	}
}