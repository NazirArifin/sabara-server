<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: liststand
 */
$app->options('/liststand', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/liststand', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->get_list_stand();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: liststandkoreksi
 */
$app->options('/liststandkoreksi', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/liststandkoreksi', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->get_list_stand(TRUE);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: bacameter
 */
$app->options('/bacameter', function() use($app) { $app->status(200); $app->stop(); });
$app->post('/bacameter', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	if ( ! empty($_FILES)) {
		is_logged($app, $ctr);
		$iofiles = new IOFiles();
		$ctr->load('model', 'meter');
		$r = $ctr->MeterModel->bacameter($iofiles);
		json_output($app, $r);
	} else {
		$ctr->load('model', 'meter');
		$r = $ctr->MeterModel->bacameter();
	}
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: stand/idpel
 */
$app->options('/stand/:Idpel', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/stand/:Idpel', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->get_stand($id);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: stand
 */
$app->options('/stand', function() use($app) { $app->status(200); $app->stop(); });
$app->post('/stand', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->operate_stand();
	if ($r === FALSE) 
		return halt401($app);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: koreksi
 */
$app->options('/koreksi', function() use($app) { $app->status(200); $app->stop(); });
$app->post('/koreksi', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->operate_stand(TRUE);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: foto
 */
$app->options('/foto', function() use($app) { $app->status(200); $app->stop(); });
$app->post('/foto', function() use ($app, $ctr) { 
	//$ctr->load('model', 'main');
	//is_logged($app, $ctr);
	
	$ctr->load('model', 'meter');
	$iofiles = new IOFiles();
	$r = $ctr->MeterModel->save_foto($iofiles);
	echo json_encode($r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: listlbkb
 */
$app->options('/listlbkb', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/listlbkb', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->get_lbkb();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: listrekaplbkb
 */
$app->options('/listrekaplbkb', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/listrekaplbkb', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->get_rekap_lbkb();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: rekapbaca
 */
$app->options('/rekapbaca/:IdUnit', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/rekapbaca/:IdUnit', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->get_rekap_baca($id);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: rekapbaca
 */
$app->options('/rekapbaca/prepare/:IdUnit', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/rekapbaca/prepare/:IdUnit', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->get_rekap_baca_prepare($id);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});