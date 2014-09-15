<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: blank
 */
$app->get('/', function() use ($app) { 
	echo "<h1>FUZZY C-MEAN CLUSTERING</h1>";
	echo crypt('password', 'fcmean');
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: ping
 */
$app->get('/ping', function() use ($app) { 
	echo "<h1>FUZZY C-MEAN CLUSTERING</h1>";
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
 * Verb: img
 */
$app->get('/img/:file', function($file) use ($app) {
	$f = 'upload/' . $file;
	if ( ! is_file($f)) $f = 'upload/logo.png';
	$h = getimagesize($f);
	$app->contentType($h['mime']);
	echo readfile($f);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: angkatan
 */
$app->options('/angkatan', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/angkatan', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	json_output($app, $ctr->MainModel->getData('angkatan'));
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: review
 */
$app->options('/review', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/review', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	json_output($app, $ctr->MainModel->getData('review'));
});

// ----------------------------------------------------------------
/**
 * Method: POST
 * Verb: profile
 */
$app->options('/profile', function() use($app) { $app->status(204); $app->stop(); });
$app->post('/profile', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->edit_profile();
	if (is_array($r))
		json_output($app, $r);
	else halt400($app);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: proses
 */
$app->options('/proses', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/proses', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->process_history();
	json_output($app, $r);
});
 
// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: process
 */
$app->options('/process', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/process', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('file', 'lib/FCM.php');
	$r = $ctr->MainModel->process_cluster();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: proses
 */
$app->options('/proses/:Id', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/proses/:Id', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->get_detail_process($id);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: excel
 */
$app->options('/excel/:Id', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/excel/:Id', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	$r = $ctr->MainModel->export_process($id);
});

// ----------------------------------------------------------------
/**
 * Method: DELETE
 * Verb: proses
 */
$app->options('/proses/:Id', function() use($app) { $app->status(204); $app->stop(); });
$app->delete('/proses/:Id', function($id) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$r = $ctr->MainModel->delete_process_history($id);
	json_output($app, $r);
});
 