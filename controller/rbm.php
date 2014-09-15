<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: /rbm/:idPetugas
 */
$app->options('/rbm/:idPetugas', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/rbm/:idPetugas', function($id) use ($app, $ctr) {
	//$ctr->load('model', 'main');
	//is_logged($app, $ctr);
	
	$ctr->load('model', 'rbm');
	$r = $ctr->RbmModel->view_rbm($id);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: rbm
 */
$app->options('/rbm', function() use($app) { $app->status(204); $app->stop(); });
$app->post('/rbm', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'rbm');
	$r = $ctr->RbmModel->operate_rbm();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: rbm
 */
$app->delete('/rbm/:Idrbm', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'rbm');
	$r = $ctr->RbmModel->delete_rbm($id);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: rincianrbm
 */
$app->options('/rincianrbm/:idPetugas', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/rincianrbm/:idPetugas', function($id) use ($app, $ctr) {
	//$ctr->load('model', 'main');
	//is_logged($app, $ctr);
	
	$ctr->load('model', 'rbm');
	$r = $ctr->RbmModel->view_rincian_rbm($id);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: rbm/unit
 */
$app->options('/rbm/unit/:idUnit', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/rbm/unit/:idUnit', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'rbm');
	$r = $ctr->RbmModel->view_unit_rbm($id);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: listdpm
 */
$app->options('/listdpm', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/listdpm', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'rbm');
	$r = $ctr->RbmModel->get_dpm();
	json_output($app, $r);
});