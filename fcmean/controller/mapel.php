<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: mapel
 */
$app->options('/mapel', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/mapel', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'mapel');
	$r = $ctr->MapelModel->view();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: mapel
 */
$app->options('/mapel', function() use($app) { $app->status(204); $app->stop(); });
$app->post('/mapel', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'mapel');
	$r = $ctr->MapelModel->add();
	if (is_array($r))
		json_output($app, $r);
	else halt400($app);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: mapel
 */
$app->options('/mapel/:Id', function() use($app) { $app->status(204); $app->stop(); });
$app->delete('/mapel/:Id', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'mapel');
	$r = $ctr->MapelModel->delete($id);
	if (is_array($r))
		json_output($app, $r);
	else halt400($app);
});
