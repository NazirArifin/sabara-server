<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: jurusan
 */
$app->options('/jurusan', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/jurusan', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'jurusan');
	$r = $ctr->JurusanModel->view();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: jurusan
 */
$app->options('/jurusan', function() use($app) { $app->status(204); $app->stop(); });
$app->post('/jurusan', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'jurusan');
	$r = $ctr->JurusanModel->add();
	if (is_array($r))
		json_output($app, $r);
	else halt400($app);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: jurusan
 */
$app->options('/jurusan/:Id', function() use($app) { $app->status(204); $app->stop(); });
$app->delete('/jurusan/:Id', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'jurusan');
	$r = $ctr->JurusanModel->delete($id);
	if (is_array($r))
		json_output($app, $r);
	else halt400($app);
});
