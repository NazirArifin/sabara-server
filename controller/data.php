<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: blth
 */
$app->options('/blth', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/blth', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_table_data('blth');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: blth
 */
$app->options('/blth/aktif', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/blth/aktif', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_table_data('blth', 'aktif');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: blth
 */
$app->post('/blth', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$ctr->DataModel->operate_blth();
	$r = $ctr->MainModel->get_table_data('blth');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: blth
 */
$app->options('/blth/:Id', function() use($app) { $app->status(200); $app->stop(); });
$app->post('/blth/:Id', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$ctr->DataModel->operate_blth($id);
	$r = $ctr->MainModel->get_table_data('blth');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: blth
 */
$app->delete('/blth/:Id', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$ctr->DataModel->delete_blth($id);
	$r = $ctr->MainModel->get_table_data('blth');
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: unit
 */
$app->post('/unit', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->operate_unit();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: unit
 */
$app->options('/unit/:Id', function() use($app) { $app->status(200); $app->stop(); });
$app->post('/unit/:Id', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->operate_unit($id);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: unit
 */
$app->delete('/unit/:Id', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->delete_unit($id);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: npde
 */
$app->options('/npde/:Tipe', function() use($app) { $app->status(200); $app->stop(); });
$app->post('/npde/:Tipe', function($tipe) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$iofiles = new IOFiles();
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->npde($iofiles, $tipe);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: PUT
 * Verb: npde
 */
$app->options('/npde/:Tipe', function() use($app) { $app->status(200); $app->stop(); });
$app->put('/npde/:Tipe', function($tipe) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$iofiles = new IOFiles();
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->npde($iofiles, $tipe);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: npde
 */
$app->get('/npde/:Tipe', function($tipe) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->npde_read();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: bacarbm/id/tanggal
 */
$app->options('/bacarbm/:Id/:Tanggal', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/bacarbm/:Id/:Tanggal', function($id, $tgl) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->get_baca_rbm($id, $tgl);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: gardu
 */
$app->options('/gardu', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/gardu', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->get_gardu();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: gardu
 */
$app->options('/gardu', function() use($app) { $app->status(200); $app->stop(); });
$app->post('/gardu', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->save_gardu();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: gardu
 */
$app->options('/gardu/:Idgardu', function() use($app) { $app->status(200); $app->stop(); });
$app->post('/gardu/:Idgardu', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->save_gardu($id);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: gardu
 */
$app->delete('/gardu/:Idgardu', function($id) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->delete_gardu($id);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: rbm
 */
$app->options('/rbm', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/rbm', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->get_rbm();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: stmt
 */
$app->options('/stmt', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/stmt', function() use ($app, $ctr) { 
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$iofiles = new IOFiles();
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->get_stmt($iofiles);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: stmt
 */
$app->options('/download/stmt/:file', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/download/stmt/:file', function($file) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	//is_logged($app, $ctr);
	
	$iofiles = new IOFiles();
	$file = 'upload/stmt/' . $file;
	$iofiles->download($file);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: npde
 */
$app->options('/download/npde/:file', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/download/npde/:file', function($file) use ($app, $ctr) { 
	$ctr->load('model', 'main');
	//is_logged($app, $ctr);
	
	$iofiles = new IOFiles();
	$file = 'upload/npde/' . $file;
	$iofiles->download($file);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: recheck
 */
$app->options('/recheck', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/recheck', function() use ($app, $ctr) { 
	//$ctr->load('model', 'main');
	//is_logged($app, $ctr);
	
	$ctr->load('model', 'data');
	$r = $ctr->DataModel->recheck();
});