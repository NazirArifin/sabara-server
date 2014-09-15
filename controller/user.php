<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: petugas
 */
$app->options('/petugas', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/petugas', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->view();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: petugas
 */
$app->options('/petugas/unit/:Idunit', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/petugas/unit/:Idunit', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->get_petugas_by_unit($id);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: petugas
 */
$app->get('/petugas/unit/:Idunit', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->get_petugas_by_unit($id);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: petugas
 */
$app->post('/petugas', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->operate_petugas();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: petugas
 */
$app->options('/petugas/:Idpetugas', function() use($app) { $app->status(200); $app->stop(); });
$app->delete('/petugas/:Idpetugas', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->delete_petugas($id);
	json_output($app, $r);
});


// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: koordinator
 */
$app->options('/koordinator', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/koordinator', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->view(FALSE);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: koordinator
 */
$app->post('/koordinator', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->operate_petugas(FALSE);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: koordinator
 */
$app->options('/koordinator/:Idkoordinator', function() use($app) { $app->status(200); $app->stop(); });
$app->delete('/koordinator/:Idkoordinator', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->delete_petugas($id, FALSE);
	json_output($app, $r);
});


// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: administrator
 */
$app->options('/administrator', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/administrator', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->view_admin();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: administrator
 */
$app->post('/administrator', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->operate_admin();
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: administrator
 */
$app->options('/administrator/:Idadmin', function() use($app) { $app->status(200); $app->stop(); });
$app->delete('/administrator/:Idadmin', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->delete_admin($id);
	json_output($app, $r);
});


// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: observer
 */
$app->options('/observer', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/observer', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->view_admin(FALSE);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: observer
 */
$app->post('/observer', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->operate_admin(FALSE);
	if ($r === FALSE) 
		return halt401($app);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: observer
 */
$app->options('/observer/:Idobserver', function() use($app) { $app->status(200); $app->stop(); });
$app->delete('/observer/:Idobserver', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->delete_admin($id);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: password
 */
$app->options('/password', function() use($app) { $app->status(200); $app->stop(); });
$app->post('/password', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'petugas');
	$r = $ctr->PetugasModel->change_password();
	json_output($app, $r);
});
