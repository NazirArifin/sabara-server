<?php
/**
 * FCM (Fuzzy C-Mean) Algorithm
 *
 * Mohammad Nazir Arifin
 * ceylon.rizan@gmail.com / nazoftware.blogspot.com
 *
 */

class FCM {
	/**
	 * jumlah cluster
	 */
	private $c = 2;
	
	/**
	 * maksimum iterasi
	 */
	private $max_iter = 100;
	
	/**
	 * pangkat ke-fuzzy
	 */
	private $m = 2;
	
	/**
	 * error terkecil
	 */
	private $e = 0.001;
	
	/**
	 * derajad keanggotaan
	 */
	private $u = array();
	
	/**
	 * data yang akan dicluster
	 */
	protected $data = array();
	
	/**
	 * jumlah atribut
	 */
	protected $attr = 0;
	
	/**
	 * set options
	 */
	public function set_options($a = array()) {
		if (isset($a['c'])) $this->c = intval($a['c']);
		if (isset($a['m'])) $this->m = intval($a['m']);
		if (isset($a['max_iter'])) $this->max_iter = intval($a['max_iter']);
		if (isset($a['e'])) $this->e = intval($a['e']);
	}
	
	/**
	 * set data
	 */
	public function set_data($d = array()) {
		if ( ! is_array($d)) {
			trigger_error('Data harus berupa array!', E_USER_ERROR);
			return FALSE;
		}
		if (empty($d)) {
			trigger_error('Data kosong', E_USER_WARNING);
			return FALSE;
		}
		
		if (empty($this->attr)) {
			foreach ($d as $key => $val) {
				$this->attr = count($d[$key]);
				break;
			}
		}
		
		$this->data = $d;
	}
	
	/**
	 * constructor
	 */
	public function __construct($a = array()) {
		if ( ! empty($a)) 
			$this->set_options($a);
	}
	
	/**
	 * hitung 
	 */
	public function cluster() {
		$this->generate_u();
		$centroid = array();
		
		$ce = 0; // error saat ini
		$ci = 1; // iterasi saat ini
		
		
		do {
			
			// hitung pusat cluster
			// derajad keanggotaan dikuadratkan, lalu dikalikan dengan nilai masing2 cluster
			$tu = array_fill(0, $this->c, 0);
			$tc = $centroid = array_fill(0, $this->c, $tu);
			$dk = array();
			
			foreach ($this->u as $key => $val) {
				for ($i = 0; $i < count($val); $i++) {
					// pangkatkan fuzzy
					$dk[$key][$i] = $pu = pow($val[$i], $this->m);
					$tu[$i] += $pu;
					
					// kalikan $pu dengan masing2 nilai data
					for ($j = 0; $j < count($val); $j++) {
						$tc[$i][$j] += ($pu * $this->data[$key][$j]);
					}
				}
			}
			
			// pusat cluster
			for ($i = 0; $i < count($tu); $i++) {
				for ($j = 0; $j < count($tu); $j++) {
					$centroid[$i][$j] = $tc[$i][$j] / $tu[$i];
				}
			}
			
			// hitung fungsi objektifnya
			// hitung jarak dengan euclidian pada masing2 centroid cluster
			$ed = array();
			
			foreach ($this->data as $key => $val) { // cacah data
				for ($i = 0; $i < count($centroid); $i++) { // cacah centroid
					$tcent = 0;
					for ($j = 0; $j < count($val); $j++) {
						$min = pow($centroid[$i][$j] - $val[$j], 2);
						$tcent += $min;
					}
					$ed[$key][$i] = $tcent;
				}
			}
			
			// L1 dan L2 (perkalian jarak dengan kuadrat keanggotaan)
			$fo = 0;
			foreach ($this->data as $key => $val) {
				$tr = 0;
				for ($i = 0; $i < count($val); $i++) {
					$tr += ($ed[$key][$i] * $dk[$key][$i]);
				}
				$fo += $tr;
			}
			
			// perbaiki derajad keanggotaan
			$nm = $tn = array();
			foreach ($this->data as $key => $val) {
				$tr = 0;
				for ($i = 0; $i < count($val); $i++) {
					$nm[$key][$i] = $r = (1 / $ed[$key][$i]);
					$tr += $r;
				}
				$tn[$key] = $tr;
			}
			
			
			foreach ($this->u as $key => $val) {
				for ($i = 0; $i < count($val); $i++) {
					$this->u[$key][$i] = round($nm[$key][$i] / $tn[$key], 3);
				}
			}
						
			$ce = ($ci == 1 ? $fo : abs($fo - $ce));
			if ($ce < $this->e) break;
			$ci++;
		} while($ci < $this->max_iter);
		
		return array(
			'data' => $this->u,
			'centroid' => $centroid
		);
	}
	
	/**
	 * bangkitkan derajad keanggotaan
	 */
	private function generate_u() {
		
		$max = mt_getrandmax();
		foreach ($this->data as $key => $val) {
			for ($i = 0; $i < $this->c; $i++) {
				$this->u[(string)$key][] = round((mt_rand() / $max), 3);
			}
		}
	}
}




/** ujicoba 
$data = array(
	1 => array(73.06, 70.88),
	2 => array(69.91, 69.13),
	3 => array(69.69, 70.38),
	4 => array(69.06, 69.25),
	5 => array(68.59, 69.38),
	6 => array(68.94, 70.50),
	7 => array(70.13, 69.75),
	8 => array(70.25, 69.25),
	9 => array(68.75, 69.50),
	10 => array(70.19, 69.50),
	11 => array(68.44, 69.63),
	12 => array(68.81, 70.00),
	13 => array(69.63, 69.13),
	14 => array(68.19, 69.63),
	15 => array(68.91, 69.25),
	16 => array(70.69, 69.63),
	17 => array(69.38, 69.75),
	18 => array(73.63, 76.38),
	19 => array(70.50, 72.25),
	20 => array(67.00, 69.75),
	21 => array(68.78, 70.00),
	22 => array(68.25, 70.38),
	23 => array(70.69, 70.00),
	24 => array(67.00, 70.00),
	25 => array(71.19, 69.75),
	26 => array(71.50, 70.75),
	27 => array(70.69, 71.25),
	28 => array(71.00, 69.50),
	29 => array(72.19, 70.38),
	30 => array(70.94, 70.63),
	31 => array(71.25, 75.88),
	32 => array(67.94, 69.25),
	33 => array(69.94, 69.63),
);

$fcm = new FCM;
$fcm->set_data($data);
$u = $fcm->cluster();
var_dump($u);

***/