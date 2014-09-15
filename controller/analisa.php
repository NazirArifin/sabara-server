<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: hitung/dlpd
 */
$app->options('/hitung/dlpd', function() use($app) { $app->status(204); $app->stop(); });
$app->post('/hitung/dlpd', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'analisa');
	$r = $ctr->AnalisaModel->hitung_dlpd();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: liststmt
 */
$app->options('/liststmt', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/liststmt', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'analisa');
	$r = $ctr->AnalisaModel->get_stmt();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: liststmt
 */
$app->options('/listnpde', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/listnpde', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'analisa');
	$r = $ctr->AnalisaModel->get_npde();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: listdlpd
 */
$app->options('/listdlpd', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/listdlpd', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'analisa');
	$r = $ctr->AnalisaModel->get_dlpd_list();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: listdlpd
 */
$app->options('/graph/lbkb', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/graph/lbkb', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'analisa');
	$r = $ctr->AnalisaModel->get_graph_lbkb();
	json_output($app, $r);
});
