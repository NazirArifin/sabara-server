<?php

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: print/badayabesar/:Idpel
 */
$app->options('/print/badayabesar/:Idpel', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/print/badayabesar/:Idpel', function($id) use ($app, $ctr) {
	$ctr->load('model', 'pelanggan');
	$detail = $ctr->PelangganModel->get_pelanggan($id);
	$ctr->load('helper', 'date');
	
	$_GET = array('id' => $id, 'cpage' => 0);
	$histori = $ctr->PelangganModel->get_history();
	
	$app->view()->setData(array('pelanggan' => $detail, 'histori' => $histori['data'][0]));
	$app->render('print_badayabesar.php');
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: print/dpm
 */
$app->options('/print/dpm', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/print/dpm', function() use ($app, $ctr) {
	$ctr->load('model', 'rbm');
	$r = $ctr->RbmModel->get_dpm();
	
	$app->view()->setData(array('dpm' => $r));
	$app->render('print_dpm.php');
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: print/lbkb
 */
$app->options('/print/lbkb', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/print/lbkb', function() use ($app, $ctr) {
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->get_lbkb();
	
	$app->view()->setData(array('lbkb' => $r));
	$app->render('print_lbkb.php');
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: print/unread
 */
$app->options('/print/unread', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/print/unread', function() use ($app, $ctr) {
	$ctr->load('model', 'pelanggan');
	$r = $ctr->PelangganModel->get_unread();
	
	$app->view()->setData(array('unread' => $r));
	$app->render('print_unread.php');
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: excel/dpm
 */
$app->options('/excel/dpm', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/excel/dpm', function() use ($app, $ctr) {
	$ctr->load('model', 'rbm');
	$r = $ctr->RbmModel->get_dpm();
	//$r = array();
	
	$ctr->load('model', 'excel');
	$ctr->ExcelModel->dpm($r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: excel/lbkb
 */
$app->options('/excel/lbkb', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/excel/lbkb', function() use ($app, $ctr) {
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->get_lbkb();
	
	$ctr->load('model', 'excel');
	$ctr->ExcelModel->lbkb($r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: excel/lbkb/analisa
 */
$app->options('/excel/lbkb/analisa', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/excel/lbkb/analisa', function() use ($app, $ctr) {
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->get_rekap_lbkb();
	
	$ctr->load('model', 'excel');
	$ctr->ExcelModel->lbkb_analisa($r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: excel/unread
 */
$app->options('/excel/unread', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/excel/unread', function() use ($app, $ctr) {
	$ctr->load('model', 'pelanggan');
	$r = $ctr->PelangganModel->get_unread();
	
	$ctr->load('model', 'excel');
	$ctr->ExcelModel->unread($r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: excel/dlpd
 */
$app->options('/excel/dlpd', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/excel/dlpd', function() use ($app, $ctr) {
	$ctr->load('model', 'analisa');
	$r = $ctr->AnalisaModel->get_dlpd_list();
	
	$ctr->load('model', 'excel');
	$ctr->ExcelModel->dlpd($r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: excel/pelanggan
 */
$app->options('/excel/pelanggan/:Idpel', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/excel/pelanggan/:Idpel', function($id) use ($app, $ctr) {
	$ctr->load('model', 'pelanggan');
	$r = $ctr->PelangganModel->get_history($id);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: excel/rekap/catat
 */
$app->options('/excel/rekap/catat', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/excel/rekap/catat', function() use ($app, $ctr) {
	$id = intval($_GET['unit']);
	$ctr->load('model', 'meter');
	$r = $ctr->MeterModel->get_rekap_baca_prepare($id);
	
	$ctr->load('model', 'excel');
	$ctr->ExcelModel->rekap_catat($r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: excel/analisa/rbm
 */
$app->options('/excel/analisa/rbm', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/excel/analisa/rbm', function() use ($app, $ctr) {
	$ctr->load('model', 'analisa');
	$r = $ctr->AnalisaModel->get_analisa_rbm();
	
	$ctr->load('model', 'excel');
	$ctr->ExcelModel->analisa_rbm($r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: excel/analisa/tarif
 */
$app->options('/excel/analisa/tarif', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/excel/analisa/tarif', function() use ($app, $ctr) {
	$ctr->load('model', 'analisa');
	$r = $ctr->AnalisaModel->get_analisa_tarif();
	
	$ctr->load('model', 'excel');
	$ctr->ExcelModel->analisa_tarif($r);
});

// ----------------------------------------------------------------
/**
 * Method: GET
 * Verb: excel/tusbung
 */
$app->options('/excel/tusbung', function() use($app) { $app->status(200); $app->stop(); });
$app->get('/excel/tusbung', function() use ($app, $ctr) {
	$ctr->load('model', 'tusbung');
	$ctr->load('helper', 'date');
	$r = $ctr->TusbungModel->get_report();
	
	$ctr->load('model', 'excel');
	$ctr->ExcelModel->tusbung($r);
});