<?php

$field = array(
		'noka' => 9, 
		'tarif' => 3,
		'ktarif' => 1,
		'daya' => 9,
		'nm' => 25,
		'alm' => 26,
		'mut' => 9,
		'koduk' => 12, // 7 rbm, 5 urut
		'kbca' => 3,
		'filler' => 1,
		'pdh' => 45,
		'fm' => 9,
		'lwbp1' => 6,
		'dgt3' => 3,
		'lwbp0' => 6,
		'dgt2' => 3,
		'wbp1' => 6,
		'dgt1' => 3,
		'wbp0' => 6,
		'dgt0' => 3,
		'fkvar' => 9,
		'kvarh1' => 6,
		'dgtkvar1' => 3,
		'kvarh0' => 6,
		'dgtkvarh0' => 3,
		'k_idpel' => 1,
		'idpel' => 12,
		'klpk' => 2,
		'blth' => 6,
		'klp' => 3,
		'ekor' => 6
	);

include 'config/dbconfig.php';
$file = 'upload/npde/npde5170020140551700B_s.txt';

$conn = mysql_connect($dbconfig_host, $dbconfig_username, $dbconfig_password);
$sdb = mysql_select_db($dbconfig_database, $conn);

var_dump(is_file($file));
var_dump(is_writable($file));
$data = @file($file);
var_dump($data);

for ($i = 0; $i < count($data); $i++) {
	$s = 0;
	$row = trim($data[$i]);
	if (empty($row)) continue;
	$r = array();
	
	foreach ($field as $key => $val) {
		$r[$key] = substr($row, $s, $val);
		$s += $val;
	}
	
	extract($r);
	
	$run = mysql_query("SELECT `ID_PELANGGAN` FROM `pelanggan` WHERE `ID_PELANGGAN` = '$idpel'");
	$data = mysql_fetch_array($run);
	if (empty($data))
		echo $idpel . '<br>';
	
	unset($r);
}
