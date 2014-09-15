<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: angkatan
 */
$app->options('/list/angkatan', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/list/angkatan', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'setting');
	$r = $ctr->SettingModel->view_angkatan();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: angkatan
 */
$app->options('/angkatan', function() use($app) { $app->status(204); $app->stop(); });
$app->post('/angkatan', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'setting');
	$r = $ctr->SettingModel->add_angkatan();
	if (is_array($r)) json_output($app, $r);
	else halt400($app);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: angkatan
 */
$app->delete('/angkatan/:Id', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'setting');
	$r = $ctr->SettingModel->delete_angkatan($id);
	if (is_array($r))
		json_output($app, $r);
	else halt400($app);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: setting
 */
$app->options('/setting', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/setting', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'setting');
	$r = $ctr->SettingModel->view_setting();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: setting
 */
$app->post('/setting', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'setting');
	$r = $ctr->SettingModel->save_setting();
	if (is_array($r)) json_output($app, $r);
	else halt400($app);
});