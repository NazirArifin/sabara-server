<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: siswa
 */
$app->options('/siswa', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/siswa', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'siswa');
	$r = $ctr->SiswaModel->view();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: siswa
 */
$app->options('/siswa', function() use($app) { $app->status(204); $app->stop(); });
$app->post('/siswa', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'siswa');
	$r = $ctr->SiswaModel->add();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: siswa
 */
$app->options('/siswa/:Id', function() use($app) { $app->status(204); $app->stop(); });
$app->delete('/siswa/:Id', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'siswa');
	$r = $ctr->SiswaModel->delete($id);
	if (is_array($r))
		json_output($app, $r);
	else halt400($app);
});
