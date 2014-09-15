<?php

if ( ! function_exists('format_date')) {
	function format_date($f , $t = 0, $h = FALSE) {
		global $ctr;
		if ( ! is_numeric($t))
			$t = 0;
		
		if ( ! class_exists('go2hi'))
			$ctr->load('file', 'lib/go2hi.php');
		$i = new go2hi;
		return $i->date($f, ($h === FALSE ? GO2HI_GREG : GO2HI_HIJRI), $t, 1);
	}
}

if ( ! function_exists('datedb_to_tanggal')) {
	function datedb_to_tanggal($d, $f) {
		// Asumsi tanggal dari database adalah yyyy-mm-dd
		if (strpos($d, ':') !== FALSE) {
			
			preg_match('/([0-9]{4})\-([0-9]{2})\-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $d, $m);
			$t = mktime($m[4], $m[5], $m[6], $m[2], $m[3], $m[1]);
		
		} else {
			
			preg_match('/([0-9]{4})\-([0-9]{2})\-([0-9]{2})/', $d, $m);
			$t = mktime(0, 0, 1, $m[2], $m[3], $m[1]);
		}
		
		return format_date($f, $t);
	}
}

if ( ! function_exists('tanggal_to_datedb')) {
	function tanggal_to_datedb($d, $ds = '/', $ts = ':') {
		$p = '([0-9]{1,2})' . $ds . '([0-9]{1,2})' . $ds . '([0-9]{2,4})';
		
		// Escape delimiter untuk $ds dan $ts
		$ds = str_replace('!', '\!', $ds);
		$ts = str_replace('!', '\!', $ts);
		
		// Menggunakan time
		if (strpos($d, ' ') !== FALSE) {
			
			$p .= ' ([0-9]{1,2})' . $ts . '([0-9]{1,2})' . $ts . '([0-9]{1,2})';
		}
		
		preg_match('!' . $p . '!', $d, $m);
		if (empty($m)) return FALSE;
		
		// Prepend dengan 0
		for ($i = 1; $i < count($m); $i++) {
			if (strlen($m[$i]) < 2)
				$m[$i] = '0' . $m[$i];
		}
		
		$rest = sprintf('%s-%s-%s', $m[3], $m[2], $m[1]);
		if (isset($m[4])) {
			$rest .= sprintf(' %s:%s:%s', $m[4], $m[5], $m[6]);
		}
		
		return $rest;
	}
}

if ( ! function_exists('x_week_range')) {
	function x_week_range($date) {
		$ts = strtotime($date);
		$start = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
		return array(date('Y-m-d', $start),
					 date('Y-m-d', strtotime('next saturday', $start)));
	}
}
