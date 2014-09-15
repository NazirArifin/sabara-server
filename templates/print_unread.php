<?php

require 'config/dbconfig.php';
$host = $dbconfig_host;
$user = $dbconfig_username;
$pass = $dbconfig_password;
$dbnm = $dbconfig_database;
$conn = mysql_connect($host, $user, $pass);
$sdb = mysql_select_db($dbnm, $conn);

$unread = $this->data['unread'];

if ( ! isset($_GET['unit']) OR ! isset($_GET['rbm']) OR ! isset($_GET['blth'])) die('Invalid Request');
$unit = intval($_GET['unit']);
$rbm = floatval($_GET['unit']);
$blth = intval($_GET['blth']);

// cari unit
$run = mysql_query("SELECT NAMA_UNIT FROM unit WHERE ID_UNIT = '$unit'");
$unit = mysql_fetch_array($run);
$unit = $unit['NAMA_UNIT'];

// cari rbm
$run = mysql_query("SELECT NAMA_RBM FROM rbm WHERE ID_RBM = '$rbm'");
$rbm = mysql_fetch_array($run);
$rbm = $rbm['NAMA_RBM'];

// cari blth
$run = mysql_query("SELECT `NAMA_BLTH` FROM `blth` WHERE `ID_BLTH` = '$blth'");
$blth = mysql_fetch_array($run);
$blth = $blth['NAMA_BLTH'];

?><!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>DATA PELANGGAN BELUM TERBACA</title>
	<style>
<?php echo file_get_contents('templates/printer.css') ?>
	
body {
	font-size: 7pt;
}

	</style>
</head>
<body>


<table border="0" width="100%">
	<tr>
		<td>
			<h3>DATA PELANGGAN BELUM TERBACA UNIT <?php echo strtoupper($unit) ?> RBM <?php echo $rbm ?> <?php echo $blth ?></h3>
			
		</td>
	</tr>
	<tr>
		<td><hr></td>
	</tr>
	<tr>
		<td>
			<br>
			<table class="bordered" width="100%">
				<tr>
					<th>RBM</th>
					<th>IDPEL</th>
					<th>NAMA</th>
					<th>ALAMAT</th>
					<th>KODUK</th>
					<th>GARDU</th>
					<th>TARIF</th>
					<th>DAYA</th>
					<th>LWBP</th>
					<th>WBP</th>
					<th>KVARH</th>
				</tr>
				<?php for ($i = 0; $i < count($unread); $i++): $r = $unread[$i]; ?>
				<tr>
					<td><?php echo $r['rbm'] ?></td>
					<td><?php echo $r['idpel'] ?></td>
					<td><?php echo $r['nama'] ?></td>
					<td><?php echo $r['alamat'] ?></td>
					<td><?php echo $r['koduk'] ?></td>
					<td><?php echo $r['gardu'] ?></td>
					<td><?php echo $r['tarif'] ?></td>
					<td><?php echo $r['daya'] ?></td>
					<td><?php echo $r['lwbp'] ?></td>
					<td><?php echo $r['wbp'] ?></td>
					<td><?php echo $r['kvarh'] ?></td>
				</tr>
				<?php endfor ?>
			</table>
		<td>
	</tr>
</table>


</body>
</html>