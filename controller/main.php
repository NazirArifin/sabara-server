<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: blank
 */
$app->options('/', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/', function() use ($app) { 
	echo "<h1>APLIKASI BACA METER</h1>";
	//echo crypt('password', 'abmpmk');
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: ping
 */
$app->options('/ping', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/ping', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	$r = $ctr->MainModel->check_version();
	json_output($app, $r);
});
 
// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: login
 */
$app->options('/login', function() use($app) { $app->status(204); $app->stop(); });
$app->post('/login', function() use ($app, $ctr) {
	if ( ! isset($_POST['username']) || ! isset($_POST['password']))
		return halt404($app);
		
	$ctr->load('model', 'main');
	$r = $ctr->MainModel->login();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: unit
 */
$app->options('/unit', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/unit', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_table_data('unit');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: ketbaca
 */
$app->options('/ketbaca', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/ketbaca', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_table_data('keterangan_bacameter');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: kdproses
 */
$app->options('/kodeproses', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/kodeproses', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_table_data('kodeproses');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: listidpel
 */
$app->options('/listidpel', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/listidpel', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	//is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_list_idpel();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: listrbm
 */
$app->options('/listrbm', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/listrbm', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	//is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_list_rbm();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: dlpd
 */
$app->options('/dlpd', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/dlpd', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_table_data('dlpd');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: dlpd/filter
 */
$app->options('/dlpd/filter', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/dlpd/filter', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_table_data('dlpd', TRUE);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: lbkb
 */
$app->options('/lbkb', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/lbkb', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_table_data('keterangan_bacameter');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: tarif
 */
$app->options('/tarif', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/tarif', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_table_data('tarif');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: daya
 */
$app->options('/daya', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/daya', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_table_data('daya');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: map/rbm
 */
$app->options('/map/rbm/:Idrbm', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/map/rbm/:Idrbm', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_map($id);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: map/gardu
 */
$app->options('/map/gardu/:Idgardu', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/map/gardu/:Idgardu', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_map2($id);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});
