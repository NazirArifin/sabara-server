<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: /npp
 */
$app->options('/npp/:Jenis', function() use($app) { $app->status(204); $app->stop(); });
$app->post('/npp/:Jenis', function($j) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$iofiles = new IOFiles();
	$ctr->load('model', 'tusbung');
	$r = $ctr->TusbungModel->import($iofiles, $j);
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: /tunggakan
 */
$app->options('/tunggakan', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/tunggakan', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'tusbung');
	$r = $ctr->TusbungModel->get_rbm_list();
	json_output($app, $r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: /tunggakan/detail/nama
 */
$app->options('/tunggakan/detail/:namaRbm', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/tunggakan/detail/:namaRbm', function($nama) use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'tusbung');
	$r = $ctr->TusbungModel->get_detail_rbm($nama);
	json_output($app, $r);
});


// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: /tusbung
 */
$app->options('/tusbung', function() use($app) { $app->status(204); $app->stop(); });
$app->get('/tusbung', function() use ($app, $ctr) {
	$ctr->load('model', 'main');
	is_logged($app, $ctr);
	
	$ctr->load('model', 'tusbung');
	$r = $ctr->TusbungModel->get_list();
	json_output($app, $r);
});