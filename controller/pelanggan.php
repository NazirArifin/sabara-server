<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: pelanggan/:idPetugas
 */
$app->options('/pelanggan/:idPetugas', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/pelanggan/:idPetugas', function($id) use ($app, $ctr) {
	//$ctr->load('model', 'main');
	//is_logged($app, $ctr);
	
	$ctr->load('model', 'pelanggan');
	$r = $ctr->PelangganModel->view_by_petugas($id);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: listpelanggan
 */
$app->options('/listpelanggan', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/listpelanggan', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'pelanggan');
	$r = $ctr->PelangganModel->view();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: pelanggan/detail
 */
$app->options('/pelanggan/detail/:Idpel', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/pelanggan/detail/:Idpel', function($idpel) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'pelanggan');
	$r = $ctr->PelangganModel->get_pelanggan($idpel);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: history
 */
$app->options('/history', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/history', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'pelanggan');
	$r = $ctr->PelangganModel->get_history();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: history
 */
$app->options('/img/:Idpel/:Blth', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/img/:Idpel/:Blth', function($idpel, $blth) use ($app, $ctr) { 
	header('Pragma: public');
	header('Cache-Control: max-age=0');
	header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time()));
	
	$f = 'upload/foto/' . $idpel . '_' . $blth . '.jpg';
	if (isset($_GET['thumb']))
		$f = preg_replace('/\.(jpg|jpeg)$/', '_thumb.$1', $f);
	
	if ( ! is_file($f)) $f = 'upload/foto/default.jpg';
	$h = getimagesize($f);
	$app->contentType($h['mime']);
	echo readfile($f);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: history
 */
$app->options('/listunread', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/listunread', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'pelanggan');
	$r = $ctr->PelangganModel->get_unread();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

$app->options('/aduan', function() use($app) { $app->status(200); $app->stop(); });
// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: aduan
 */
$app->get('/aduan', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('helper', 'date');
	$ctr->load('model', 'pelanggan');
	$r = $ctr->PelangganModel->get_aduan();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: aduan
 */
$app->post('/aduan', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'pelanggan');
	$r = $ctr->PelangganModel->add_aduan();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: aduan
 */
$app->options('/aduan/:Id', function() use($app) { $app->status(200); $app->stop(); });
$app->delete('/aduan/:Id', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'pelanggan');
	$r = $ctr->PelangganModel->delete_aduan($id);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});