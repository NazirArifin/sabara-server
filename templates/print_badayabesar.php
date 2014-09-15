<?php

$pelanggan = $this->data['pelanggan'];
$histori = $this->data['histori'];
$now = time();

require 'config/dbconfig.php';
$host = $dbconfig_host;
$user = $dbconfig_username;
$pass = $dbconfig_password;
$dbnm = $dbconfig_database;
$conn = mysql_connect($host, $user, $pass);
$sdb = mysql_select_db($dbnm, $conn);

$query = mysql_query("SELECT d.NAMA_UNIT FROM rincian_rbm a, rbm b, petugas c, unit d WHERE a.ID_PELANGGAN = '" . $pelanggan['id'] . "' AND a.ID_RBM = b.ID_RBM AND b.ID_PETUGAS = c.ID_PETUGAS AND c.ID_UNIT = d.ID_UNIT");
$data = mysql_fetch_array($query);
$unit = $data['NAMA_UNIT'];

$query = mysql_query("SELECT `FAKTORKWH_PELANGGAN`, `FAKTORKVAR_PELANGGAN` FROM `pelanggan` WHERE `ID_PELANGGAN` = '" . $pelanggan['id'] . "'");
$data = mysql_fetch_array($query);
$fm = $data['FAKTORKWH_PELANGGAN'];
$fk = $data['FAKTORKVAR_PELANGGAN'];

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>BERITA ACARA PEMBACAAN DAN PERHITUNGAN</title>
	<style>
<?php echo file_get_contents('templates/printer.css') ?>
	</style>
</head>
<body>
	

	
<table border="0" width="100%">
	<tr>
		<td>
			<table width="100%" border="0">
				<tr>
					<td width="85" align="center"><img src="/templates/pln.png" style="width: 1.6cm" /></td>
					<td width="405"><p><strong>PT PLN (PERSERO)</strong></p>
				  <p>DISTRIBUSI JAWA TIMUR</p>
				  <p>APJ: PAMEKASAN</p>
				  <p>UPJ: <?php echo $unit ?></p></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td><hr></td>
	</tr>
	<tr>
		<th align="center"><h3><strong>BERITA ACARA PEMBACAAN DAN PERHITUNGAN</strong></h3>
	    <h3 class="centered"><strong>KWH DAN KVARH</strong></h3>
	    <h3 class="centered"><strong>Bulan: <?php echo format_date('F Y', $now) ?></strong></h3></td>
	</tr>
	<tr>
		<td><br><p>Pada Hari ini: <strong><?php echo format_date('l', $now) ?></strong>, tanggal <strong><?php echo format_date('d-m-Y', $now) ?></strong>, telah dilakukan pembacaan meter pada pelanggan:</p></td>
	</tr>
	<tr>
		<td><br><table width="100%">
			<tr>
				<td width="30%">ID Pelanggan</td><td>: <?php echo $pelanggan['id'] ?></td>
			</tr>
			<tr>
				<td>Nama</td><td>: <?php echo $pelanggan['nama'] ?></td>
			</tr>
			<tr>
				<td>Alamat</td><td>: <?php echo $pelanggan['alamat'] ?></td>
			</tr>
			<tr>
				<td>Tarif / Daya</td><td>: <?php echo $pelanggan['tarif'] ?> / <?php echo $pelanggan['daya'] ?></td>
			</tr>
			<tr>
				<td>Kode Kedudukan / Gardu Tiang</td><td>: <?php echo $pelanggan['koduk'] ?> / <?php echo $pelanggan['gardu'] ?></td>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td><br><p>Dengan data sebagai berikut:</p></td>
	</tr>
	<tr>
		<td><br>
<table border="0" class="bordered" width="100%">
  <tr>
    <th colspan="2">PENCATATAN</th>
    <th colspan="3">ANGKA KWH METER</th>
    <th colspan="2">KVA MAX</th>
    <th>KETERANGAN</th>
  </tr>
  <tr>
    <td>JAM KUNJUNGAN</td>
    <td>JAM</td>
    <td>LWBP</td>
    <td>WBP</td>
    <td>KVARH</td>
    <td>LWBP</td>
    <td>WBP</td>
    <td rowspan="2">PEMAKAIAN BULAN LALU</td>
  </tr>
  <tr>
    <td></td>
    <td></td>
    <td>&nbsp;</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td colspan="2">STAND LALU</td>
    <td><?php echo $histori['lwbp'] ?></td>
    <td><?php echo $histori['wbp'] ?></td>
    <td><?php echo $histori['kvarh'] ?></td>
    <td></td>
    <td></td>
    <td>LWBP</td>
  </tr>
  <tr>
    <td colspan="2">SELISIH</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td><?php echo $histori['lwbp'] ?></td>
  </tr>
  <tr>
    <td colspan="2">FAKTOR KALI</td>
    <td><?php echo $fm ?></td>
    <td><?php echo $fm ?></td>
    <td><?php echo $fk ?></td>
    <td></td>
    <td></td>
    <td>WBP</td>
  </tr>
  <tr>
    <td colspan="2">PEMAKAIAN</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td><?php echo $histori['wbp'] ?></td>
  </tr>
  <tr>
    <td colspan="2">JUMLAH KWH</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td>KVARH</td>
  </tr>
  <tr>
    <td colspan="2">KETERANGAN</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td><?php echo $histori['kvarh'] ?></td>
  </tr>
</table><br>
<table width="30%" class="bordered inline">
	<tr><th colspan="3">TEGANGAN</th></tr>
	<tr><td>R</td><td>S</td><td>T</td></tr>
	<tr><td>&nbsp;</td><td></td><td></td></tr>
</table>
<table width="30%" class="bordered inline">
	<tr><th colspan="3">ARUS</th></tr>
	<tr><td>R</td><td>S</td><td>T</td></tr>
	<tr><td>&nbsp;</td><td></td><td></td></tr>
</table>
		</td>
	</tr>
	<tr>
		<td><br><p>Pencatatan angka KWH &amp; KVARH diatas telah disetujui bersama.</p></td>
	</tr>
	<tr>
		<td><br><br><br>
<table width="100%">
  <tr>
    <th width="25%">PELANGGAN<br><br><br><br><br><?php echo $pelanggan['nama'] ?></th>
    <th width="50%"></th>
    <th width="25%">PEMBACA METER<br><br><br><br><br>(...........................)</th>
  </tr>
</table>
		</td>
	</tr>
</table>

	
</body>
</html>