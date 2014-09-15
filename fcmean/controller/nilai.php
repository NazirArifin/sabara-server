<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: nilai
 */
$app->options('/nilai', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/nilai', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'nilai');
	$r = $ctr->NilaiModel->view();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: nilai
 */
$app->options('/nilai/:Id', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/nilai/:Id', function($id) use ($app, $ctr) {
	$ctr->load('model', 'nilai');
	$r = $ctr->NilaiModel->view($id);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: nilai
 */
$app->options('/nilai', function() use($app) { $app->status(204); $app->stop(); });
$app->post('/nilai', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'nilai');
	$r = $ctr->NilaiModel->add();
	json_output($app, $r);
});