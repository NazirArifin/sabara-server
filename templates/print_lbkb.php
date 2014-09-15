<?php

require 'config/dbconfig.php';
$host = $dbconfig_host;
$user = $dbconfig_username;
$pass = $dbconfig_password;
$dbnm = $dbconfig_database;
$conn = mysql_connect($host, $user, $pass);
$sdb = mysql_select_db($dbnm, $conn);

$lbkb = $this->data['lbkb'];

if ( ! isset($_GET['unit']) OR ! isset($_GET['rbm']) OR ! isset($_GET['blth']) OR ! isset($_GET['lbkb'])) die('Invalid Request');
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
	<title>DATA LBKB</title>
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
			<h3>DATA LBKB UNIT <?php echo strtoupper($unit) ?> RBM <?php echo $rbm ?> <?php echo $blth ?></h3>
			
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
					<th class="vtop" rowspan="2">LBKB</th>
					<th class="vtop" rowspan="2">PROGRES</th>
					<th class="vtop" rowspan="2">TGL CATAT</th>
					<th class="vtop" rowspan="2">RBM</th>
					<th class="vtop" rowspan="2">IDPEL</th>
					<th class="vtop" rowspan="2">NAMA</th>
					<th class="vtop" rowspan="2">ALAMAT</th>
					<th class="vtop" rowspan="2">KODUK</th>
					<th class="vtop" rowspan="2">GARDU</th>
					<th class="vtop" rowspan="2">TARIF</th>
					<th class="vtop" rowspan="2">DAYA</th>
					<th class="vtop" colspan="2">LWBP</th>
					<th class="vtop" colspan="2">WBP</th>
					<th class="vtop" colspan="2">KVARH</th>
				</tr>
				<tr>
					<th>LALU</th>
					<th>INI</th>
					<th>LALU</th>
					<th>INI</th>
					<th>LALU</th>
					<th>INI</th>
				</tr>
				<?php for ($i = 0; $i < count($lbkb); $i++): $r = $lbkb[$i]; ?>
				<tr>
					<td><strong><?php echo $r['kdbaca'] ?></strong></td>
					<td><strong><?php echo $r['progress'] ?></strong></td>
					<td><strong><?php echo $r['tanggal'] ?></strong></td>
					<td><?php echo $r['rbm'] ?></td>
					<td><?php echo $r['idpel'] ?></td>
					<td><?php echo $r['nama'] ?></td>
					<td><?php echo $r['alamat'] ?></td>
					<td><?php echo $r['koduk'] ?></td>
					<td><?php echo $r['gardu'] ?></td>
					<td><?php echo $r['tarif'] ?></td>
					<td><?php echo $r['daya'] ?></td>
					<td><?php echo $r['lwbp0'] ?></td>
					<td><?php echo $r['lwbp'] ?></td>
					<td><?php echo $r['wbp0'] ?></td>
					<td><?php echo $r['wbp'] ?></td>
					<td><?php echo $r['kvarh0'] ?></td>
					<td><?php echo $r['kvarh'] ?></td>
				</tr>
				<?php endfor ?>
			</table>
		<td>
	</tr>
</table>


</body>
</html>