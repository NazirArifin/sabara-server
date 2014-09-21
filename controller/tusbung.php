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