<?php
/**
 * Controller helper
 * Dipanggil dari file Loader.php untuk persiapan file config/controller.php
 */

// ----------------------------------------------------------------
/**
 * Halt app dengan header 400 (Bad Request)
 */
function halt400($app) {
	$app->halt(400);
	$app->stop();
}
 
/**
 * Halt app dengan header 401 (Unauthorized)
 */
function halt401($app) {
	$app->halt(401);
	$app->stop();
}

/**
 * Halt app dengan header 404 (Not Found)
 */
function halt404($app) {
	$app->halt(404);
	$app->stop();
}
 
// ----------------------------------------------------------------
/**
 * Header json
 */
function json_output($app, $data) {
	$app->contentType('application/json');
	echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
}

// ----------------------------------------------------------------
/**
 * Is logged In. Fungsi ini memanggil method is_loggedin() yang ada di file Loader
 * mbulet juga :(
 */
function is_logged($app, $ctr) {
	if ( ! isset($_SERVER['PHP_AUTH_USER'])) {
		halt401($app); return;
	}
	
	$pass = FALSE;
	$pass = $ctr->MainModel->check_user($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
	if ( ! $pass) halt401($app);
}
