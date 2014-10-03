<?php
/**
 * Excel Model
 */
namespace Model;

set_time_limit(0);

class ExcelModel extends ModelBase {
	public function __construct() {
		parent::__construct();
	}
	
	public function dpm($data) {
		$get = $this->prepare_get(array('unit', 'rbm', 'blth'));
		extract($get);
		$unit = intval($unit);
		$rbm = floatval($rbm);
		$blth = intval($blth);
		
		// cari unit
		$run = $this->db->query("SELECT `NAMA_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$namaunit = $run->NAMA_UNIT;
		
		// cari nama rbm
		$run = $this->db->query("SELECT `NAMA_RBM` FROM `rbm` WHERE `ID_RBM` = '$rbm'", TRUE);
		$namarbm = $run->NAMA_RBM;
		
		// blth
		$run = $this->db->query("SELECT `NAMA_BLTH` FROM `blth` WHERE `ID_BLTH` = '$blth'", TRUE);
		$namablth = $run->NAMA_BLTH;
		
		include_once('lib/phpexcel/PHPExcel.php');
		$obj = new \PHPExcel();
		
		$obj->getProperties()->setCreator('Sistem Analisa Baca Meter Madura')
							 ->setLastModifiedBy('Sistem Analisa Baca Meter Madura')
							 ->setTitle("DPM RBM $namarbm UNIT $namaunit $namablth")
							 ->setSubject('DPM')
							 ->setDescription('Data DPM')
							 ->setKeywords('rbm', 'dpm', $namarbm, $namaunit)
							 ->setCategory('Laporan');
		$obj->setActiveSheetIndex(0);
		$obj->getDefaultStyle()->getFont()->setName('Arial');
		$obj->getDefaultStyle()->getFont()->setSize(9);
		
		$obj->getActiveSheet()->setCellValue('A1', "DATA DPM UNIT " . strtoupper($namaunit) . " RBM $namarbm BLTH $namablth");
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);
		
		$obj->getActiveSheet()->setCellValue('A3', 'RBM');
		$obj->getActiveSheet()->setCellValue('B3', 'IDPEL');
		$obj->getActiveSheet()->setCellValue('C3', 'NAMA');
		$obj->getActiveSheet()->setCellValue('D3', 'ALAMAT');
		$obj->getActiveSheet()->setCellValue('E3', 'KODUK');
		$obj->getActiveSheet()->setCellValue('F3', 'GARDU');
		$obj->getActiveSheet()->setCellValue('G3', 'TARIF');
		$obj->getActiveSheet()->setCellValue('H3', 'DAYA');
		$obj->getActiveSheet()->setCellValue('I3', 'LWBP');
		$obj->getActiveSheet()->setCellValue('K3', 'WBP');
		$obj->getActiveSheet()->setCellValue('M3', 'KVARH');
		$obj->getActiveSheet()->setCellValue('I4', 'LALU');
		$obj->getActiveSheet()->setCellValue('J4', 'INI');
		$obj->getActiveSheet()->setCellValue('K4', 'LALU');
		$obj->getActiveSheet()->setCellValue('L4', 'INI');
		$obj->getActiveSheet()->setCellValue('M4', 'LALU');
		$obj->getActiveSheet()->setCellValue('N4', 'INI');
		
		$obj->getActiveSheet()->mergeCells('A3:A4');
		$obj->getActiveSheet()->mergeCells('B3:B4');
		$obj->getActiveSheet()->mergeCells('C3:C4');
		$obj->getActiveSheet()->mergeCells('D3:D4');
		$obj->getActiveSheet()->mergeCells('E3:E4');
		$obj->getActiveSheet()->mergeCells('F3:F4');
		$obj->getActiveSheet()->mergeCells('G3:G4');
		$obj->getActiveSheet()->mergeCells('H3:H4');
		$obj->getActiveSheet()->getStyle('A3:N4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$obj->getActiveSheet()->getStyle('A3:N4')->getFont()->setBold(true);
		
		$currow = 5;
		// cacah data
		for ($i = 0; $i < count($data); $i++, $currow++) {
			$r = $data[$i];
			$obj->getActiveSheet()->setCellValue("A{$currow}", $r['rbm']);
			$obj->getActiveSheet()->setCellValueExplicit("B{$currow}", $r['idpel'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue("C{$currow}", $r['nama']);
			$obj->getActiveSheet()->setCellValue("D{$currow}", $r['alamat']);
			$obj->getActiveSheet()->setCellValue("E{$currow}", $r['koduk']);
			$obj->getActiveSheet()->setCellValue("F{$currow}", $r['gardu']);
			$obj->getActiveSheet()->setCellValue("G{$currow}", $r['tarif']);
			$obj->getActiveSheet()->setCellValueExplicit("H{$currow}", $r['daya'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue("I{$currow}", $r['lwbp0']);
			$obj->getActiveSheet()->setCellValue("J{$currow}", $r['lwbp']);
			$obj->getActiveSheet()->setCellValue("K{$currow}", $r['wbp0']);
			$obj->getActiveSheet()->setCellValue("L{$currow}", $r['wbp']);
			$obj->getActiveSheet()->setCellValue("M{$currow}", $r['kvarh0']);
			$obj->getActiveSheet()->setCellValue("N{$currow}", $r['kvarh']);
		}
		
		$obj->getActiveSheet()->getStyle('A3:N' . $currow)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		
		$obj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$obj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
		
		$objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"dpm-{$namarbm}-{$namaunit}-{$namablth}.xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}
	
	public function lbkb($data) {
		$get = $this->prepare_get(array('unit', 'rbm', 'blth'));
		extract($get);
		$unit = intval($unit);
		$rbm = floatval($rbm);
		$blth = intval($blth);
		
		// cari unit
		$run = $this->db->query("SELECT `NAMA_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$namaunit = $run->NAMA_UNIT;
		
		// cari nama rbm
		$run = $this->db->query("SELECT `NAMA_RBM` FROM `rbm` WHERE `ID_RBM` = '$rbm'", TRUE);
		$namarbm = $run->NAMA_RBM;
		
		// blth
		$run = $this->db->query("SELECT `NAMA_BLTH` FROM `blth` WHERE `ID_BLTH` = '$blth'", TRUE);
		$namablth = $run->NAMA_BLTH;
		
		include_once('lib/phpexcel/PHPExcel.php');
		$obj = new \PHPExcel();
		
		$obj->getProperties()->setCreator('Sistem Analisa Baca Meter Madura')
							 ->setLastModifiedBy('Sistem Analisa Baca Meter Madura')
							 ->setTitle("LBKB RBM $namarbm UNIT $namaunit $namablth")
							 ->setSubject('LBKB')
							 ->setDescription('Data LBKB')
							 ->setKeywords('rbm', 'lbkb', $namarbm, $namaunit)
							 ->setCategory('Laporan');
		$obj->setActiveSheetIndex(0);
		$obj->getDefaultStyle()->getFont()->setName('Arial');
		$obj->getDefaultStyle()->getFont()->setSize(9);
		
		$obj->getActiveSheet()->setCellValue('A1', "DATA LBKB UNIT " . strtoupper($namaunit) . " RBM $namarbm BLTH $namablth");
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);
		
		$obj->getActiveSheet()->setCellValue('A3', 'LBKB');
		$obj->getActiveSheet()->setCellValue('B3', 'TGL CATAT');
		$obj->getActiveSheet()->setCellValue('C3', 'RBM');
		$obj->getActiveSheet()->setCellValue('D3', 'IDPEL');
		$obj->getActiveSheet()->setCellValue('E3', 'NAMA');
		$obj->getActiveSheet()->setCellValue('F3', 'ALAMAT');
		$obj->getActiveSheet()->setCellValue('G3', 'KODUK');
		$obj->getActiveSheet()->setCellValue('H3', 'GARDU');
		$obj->getActiveSheet()->setCellValue('I3', 'TARIF');
		$obj->getActiveSheet()->setCellValue('J3', 'DAYA');
		$obj->getActiveSheet()->setCellValue('K3', 'LWBP');
		$obj->getActiveSheet()->setCellValue('M3', 'WBP');
		$obj->getActiveSheet()->setCellValue('O3', 'KVARH');
		$obj->getActiveSheet()->setCellValue('K4', 'LALU');
		$obj->getActiveSheet()->setCellValue('L4', 'INI');
		$obj->getActiveSheet()->setCellValue('M4', 'LALU');
		$obj->getActiveSheet()->setCellValue('N4', 'INI');
		$obj->getActiveSheet()->setCellValue('O4', 'LALU');
		$obj->getActiveSheet()->setCellValue('P4', 'INI');
		
		$obj->getActiveSheet()->mergeCells('A3:A4');
		$obj->getActiveSheet()->mergeCells('B3:B4');
		$obj->getActiveSheet()->mergeCells('C3:C4');
		$obj->getActiveSheet()->mergeCells('D3:D4');
		$obj->getActiveSheet()->mergeCells('E3:E4');
		$obj->getActiveSheet()->mergeCells('F3:F4');
		$obj->getActiveSheet()->mergeCells('G3:G4');
		$obj->getActiveSheet()->mergeCells('H3:H4');
		$obj->getActiveSheet()->mergeCells('I3:I4');
		$obj->getActiveSheet()->mergeCells('J3:J4');
		$obj->getActiveSheet()->getStyle('A3:S4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$obj->getActiveSheet()->getStyle('A3:S4')->getFont()->setBold(true);
		
		$currow = 5;
		// cacah data
		for ($i = 0; $i < count($data); $i++, $currow++) {
			$r = $data[$i];
			$obj->getActiveSheet()->setCellValue("A{$currow}", $r['kdbaca']);
			$obj->getActiveSheet()->setCellValue("B{$currow}", $r['tanggal']);
			$obj->getActiveSheet()->setCellValue("C{$currow}", $r['rbm']);
			$obj->getActiveSheet()->setCellValueExplicit("D{$currow}", $r['idpel'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue("E{$currow}", $r['nama']);
			$obj->getActiveSheet()->setCellValue("F{$currow}", $r['alamat']);
			$obj->getActiveSheet()->setCellValue("G{$currow}", $r['koduk']);
			$obj->getActiveSheet()->setCellValue("H{$currow}", $r['gardu']);
			$obj->getActiveSheet()->setCellValue("I{$currow}", $r['tarif']);
			$obj->getActiveSheet()->setCellValueExplicit("J{$currow}", $r['daya'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue("K{$currow}", $r['lwbp0']);
			$obj->getActiveSheet()->setCellValue("L{$currow}", $r['lwbp']);
			$obj->getActiveSheet()->setCellValue("M{$currow}", $r['wbp0']);
			$obj->getActiveSheet()->setCellValue("N{$currow}", $r['wbp']);
			$obj->getActiveSheet()->setCellValue("O{$currow}", $r['kvarh0']);
			$obj->getActiveSheet()->setCellValue("P{$currow}", $r['kvarh']);
		}
		
		$obj->getActiveSheet()->getStyle('A3:P' . $currow)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		
		$obj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$obj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
		
		$objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"lbkb-{$namarbm}-{$namaunit}-{$namablth}.xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}

	public function unread($data) {
		$get = $this->prepare_get(array('unit', 'rbm'));
		extract($get);
		$unit = intval($unit);
		$rbm = floatval($rbm);
		
		// cari unit
		$run = $this->db->query("SELECT `NAMA_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$namaunit = $run->NAMA_UNIT;
		
		// cari nama rbm
		$run = $this->db->query("SELECT `NAMA_RBM` FROM `rbm` WHERE `ID_RBM` = '$rbm'", TRUE);
		$namarbm = $run->NAMA_RBM;
		
		include_once('lib/phpexcel/PHPExcel.php');
		$obj = new \PHPExcel();
		
		$obj->getProperties()->setCreator('Sistem Analisa Baca Meter Madura')
							 ->setLastModifiedBy('Sistem Analisa Baca Meter Madura')
							 ->setTitle("Pelanggan Belum Terbaca RBM $namarbm UNIT $namaunit")
							 ->setSubject('Pelanggan Belum Terbaca')
							 ->setDescription('Data Pelanggan Belum Terbaca')
							 ->setKeywords('rbm', $namarbm, $namaunit)
							 ->setCategory('Laporan');
		$obj->setActiveSheetIndex(0);
		$obj->getDefaultStyle()->getFont()->setName('Arial');
		$obj->getDefaultStyle()->getFont()->setSize(9);
		
		$obj->getActiveSheet()->setCellValue('A1', "DATA PELANGGAN BELUM TERBACA UNIT " . strtoupper($namaunit) . " RBM $namarbm");
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);
		
		$obj->getActiveSheet()->setCellValue('A3', 'RBM');
		$obj->getActiveSheet()->setCellValue('B3', 'IDPEL');
		$obj->getActiveSheet()->setCellValue('C3', 'NAMA');
		$obj->getActiveSheet()->setCellValue('D3', 'ALAMAT');
		$obj->getActiveSheet()->setCellValue('E3', 'KODUK');
		$obj->getActiveSheet()->setCellValue('F3', 'GARDU');
		$obj->getActiveSheet()->setCellValue('G3', 'TARIF');
		$obj->getActiveSheet()->setCellValue('H3', 'DAYA');
		$obj->getActiveSheet()->setCellValue('I3', 'LWBP');
		$obj->getActiveSheet()->setCellValue('J3', 'WBP');
		$obj->getActiveSheet()->setCellValue('K3', 'KVARH');
		
		$obj->getActiveSheet()->getStyle('A3:K3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$obj->getActiveSheet()->getStyle('A3:K3')->getFont()->setBold(true);
		
		$currow = 4;
		// cacah data
		for ($i = 0; $i < count($data); $i++, $currow++) {
			$r = $data[$i];
			$obj->getActiveSheet()->setCellValue("A{$currow}", $r['rbm']);
			$obj->getActiveSheet()->setCellValueExplicit("B{$currow}", $r['idpel'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue("C{$currow}", $r['nama']);
			$obj->getActiveSheet()->setCellValue("D{$currow}", $r['alamat']);
			$obj->getActiveSheet()->setCellValue("E{$currow}", $r['koduk']);
			$obj->getActiveSheet()->setCellValue("F{$currow}", $r['gardu']);
			$obj->getActiveSheet()->setCellValue("G{$currow}", $r['tarif']);
			$obj->getActiveSheet()->setCellValueExplicit("H{$currow}", $r['daya'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue("I{$currow}", $r['lwbp']);
			$obj->getActiveSheet()->setCellValue("J{$currow}", $r['wbp']);
			$obj->getActiveSheet()->setCellValue("K{$currow}", $r['kvarh']);
		}
		
		$obj->getActiveSheet()->getStyle('A3:K' . $currow)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		
		$obj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$obj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
		
		$objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"pelangganblmterbaca-{$namarbm}-{$namaunit}.xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}
	
	/**
	 * Excel untuk DLPD
	 */
	public function dlpd($data) {
		$get = $this->prepare_get(array('unit', 'blth', 'dlpd'));
		extract($get);
		$unit = intval($unit);
		$blth = intval($blth);
		
		// cari unit
		$run = $this->db->query("SELECT `NAMA_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$namaunit = $run->NAMA_UNIT;
		
		// blth
		$run = $this->db->query("SELECT `NAMA_BLTH` FROM `blth` WHERE `ID_BLTH` = '$blth'", TRUE);
		$namablth = $run->NAMA_BLTH;
		
		include_once('lib/phpexcel/PHPExcel.php');
		$obj = new \PHPExcel();
		
		$obj->getProperties()->setCreator('Sistem Analisa Baca Meter Madura')
							 ->setLastModifiedBy('Sistem Analisa Baca Meter Madura')
							 ->setTitle("Data DLPD UNIT $namaunit")
							 ->setSubject('DLPD')
							 ->setDescription('Data DLPD')
							 ->setKeywords('dlpd', $namaunit)
							 ->setCategory('Laporan');
		$obj->setActiveSheetIndex(0);
		$obj->getDefaultStyle()->getFont()->setName('Arial');
		$obj->getDefaultStyle()->getFont()->setSize(9);
		
		$obj->getActiveSheet()->setCellValue('A1', "DATA DLPD " . strtoupper($namaunit) . " BLTH $namablth");
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);
		
		// tulis daftar kode
		$ids = array();
		foreach ($dlpd as $val) 
			$ids[] = "`ID_DLPD` = '$val'";
		
		$run = $this->db->query("SELECT * FROM `dlpd` WHERE " . implode(" OR ", $ids) . " ORDER BY `KODE_DLPD`");
		$obj->getActiveSheet()->getColumnDimension('A')->setWidth(4);
		$obj->getActiveSheet()->getColumnDimension('B')->setWidth(29);
		for ($i = 0, $row = 3; $i < count($run); $i++, $row++) {
			$obj->getActiveSheet()->setCellValue('A' . $row, $run[$i]->KODE_DLPD);
			$obj->getActiveSheet()->setCellValue('B' . $row, $run[$i]->NAMA_DLPD);
		}
		$obj->getActiveSheet()->getStyle('A3:B' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		$obj->getActiveSheet()->getColumnDimension('C')->setWidth(2);
		
		$obj->getActiveSheet()->setCellValue('D3', 'DLPD');
		$obj->getActiveSheet()->setCellValue('E3', 'IDPEL');
		$obj->getActiveSheet()->setCellValue('F3', 'NAMA');
		$obj->getActiveSheet()->setCellValue('G3', 'TARIF');
		$obj->getActiveSheet()->setCellValue('H3', 'DAYA');
		$obj->getActiveSheet()->setCellValue('I3', 'LWBP');
		$obj->getActiveSheet()->setCellValue('K3', 'WBP');
		$obj->getActiveSheet()->setCellValue('M3', 'KVARH');
		$obj->getActiveSheet()->setCellValue('O3', 'KWH');
		$obj->getActiveSheet()->setCellValue('P3', 'JAM NYALA');
		$obj->getActiveSheet()->setCellValue('P3', 'JAM NYALA');
		$obj->getActiveSheet()->setCellValue('I4', 'LALU');
		$obj->getActiveSheet()->setCellValue('J4', 'INI');
		$obj->getActiveSheet()->setCellValue('K4', 'LALU');
		$obj->getActiveSheet()->setCellValue('L4', 'INI');
		$obj->getActiveSheet()->setCellValue('M4', 'LALU');
		$obj->getActiveSheet()->setCellValue('N4', 'INI');
		
		$obj->getActiveSheet()->mergeCells('D3:D4');
		$obj->getActiveSheet()->mergeCells('E3:E4');
		$obj->getActiveSheet()->mergeCells('F3:F4');
		$obj->getActiveSheet()->mergeCells('G3:G4');
		$obj->getActiveSheet()->mergeCells('H3:H4');
		$obj->getActiveSheet()->mergeCells('O3:O4');
		$obj->getActiveSheet()->mergeCells('P3:P4');
		$obj->getActiveSheet()->getStyle('D3:N4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$obj->getActiveSheet()->getStyle('D3:N4')->getFont()->setBold(true);
		
		$obj->getActiveSheet()->getColumnDimension('F')->setWidth(26);
		
		for ($i = 0, $row = 5; $i < count($data); $i++, $row++) {
			$obj->getActiveSheet()->setCellValue('D' . $row, $data[$i]['kodedlpd']);
			$obj->getActiveSheet()->setCellValueExplicit("E{$row}", $data[$i]['idpel'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue('F' . $row, $data[$i]['nama']);
			$obj->getActiveSheet()->setCellValue('G' . $row, $data[$i]['tarif']);
			$obj->getActiveSheet()->setCellValueExplicit("H{$row}", $data[$i]['daya'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue('I' . $row, $data[$i]['lwbp0']);
			$obj->getActiveSheet()->setCellValue('J' . $row, $data[$i]['lwbp']);
			$obj->getActiveSheet()->setCellValue('K' . $row, $data[$i]['wbp0']);
			$obj->getActiveSheet()->setCellValue('L' . $row, $data[$i]['wbp']);
			$obj->getActiveSheet()->setCellValue('M' . $row, $data[$i]['kvarh0']);
			$obj->getActiveSheet()->setCellValue('N' . $row, $data[$i]['kvarh']);
			$obj->getActiveSheet()->setCellValueExplicit("O{$row}", $data[$i]['kwh'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValueExplicit("P{$row}", $data[$i]['jam'], \PHPExcel_Cell_DataType::TYPE_STRING);
		}
		$obj->getActiveSheet()->getStyle('D3:P' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		
		$obj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$obj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
		
		$objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"dlpd-{$namaunit}-{$namablth}.xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}
	
	/**
	 * Data histori Pelanggan
	 */
	public function histori($id, $data) {
		// cari data pelanggan
		$pelanggan = $this->db->query("SELECT `NAMA_PELANGGAN`, `ALAMAT_PELANGGAN`, `TARIF_PELANGGAN`, `DAYA_PELANGGAN` FROM `pelanggan` WHERE `ID_PELANGGAN` = '$id'", TRUE);
		
		include_once('lib/phpexcel/PHPExcel.php');
		$obj = new \PHPExcel();
		$obj->getProperties()->setCreator('Sistem Analisa Baca Meter Madura')
							 ->setLastModifiedBy('Sistem Analisa Baca Meter Madura')
							 ->setTitle("Data Pencatatan Pelanggan")
							 ->setSubject('Pencatatan')
							 ->setDescription('Data Pencatatan')
							 ->setKeywords('catat', 'pelanggan')
							 ->setCategory('Laporan');
		$obj->setActiveSheetIndex(0);
		$obj->getDefaultStyle()->getFont()->setName('Arial');
		$obj->getDefaultStyle()->getFont()->setSize(9);
		
		$obj->getActiveSheet()->setCellValue('A1', "DATA PENCATATAN PELANGGAN");
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);
		
		$obj->getActiveSheet()->setCellValue('A3', 'IDPEL');
		$obj->getActiveSheet()->setCellValue('A4', 'NAMA');
		$obj->getActiveSheet()->setCellValue('A5', 'ALAMAT');
		$obj->getActiveSheet()->setCellValue('A6', 'TARIF');
		$obj->getActiveSheet()->setCellValue('A7', 'DAYA');
		$obj->getActiveSheet()->setCellValue('B3', ': ' . $id);
		$obj->getActiveSheet()->setCellValue('B4', ': ' . $pelanggan->NAMA_PELANGGAN);
		$obj->getActiveSheet()->setCellValue('B5', ': ' . $pelanggan->ALAMAT_PELANGGAN);
		$obj->getActiveSheet()->setCellValue('B6', ': ' . $pelanggan->TARIF_PELANGGAN);
		$obj->getActiveSheet()->setCellValueExplicit('B7', ': ' . $pelanggan->DAYA_PELANGGAN, \PHPExcel_Cell_DataType::TYPE_STRING);
		
		$obj->getActiveSheet()->getColumnDimension('A')->setWidth(9);
		$obj->getActiveSheet()->getColumnDimension('B')->setWidth(31);
		$obj->getActiveSheet()->getColumnDimension('C')->setWidth(1);
		$obj->getActiveSheet()->getColumnDimension('D')->setWidth(14);
		$obj->getActiveSheet()->getColumnDimension('I')->setWidth(19);
		$obj->getActiveSheet()->getColumnDimension('L')->setWidth(19);
		
		$obj->getActiveSheet()->getStyle('A3:B7')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A3:B7')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		
		$obj->getActiveSheet()->setCellValue('D3', 'TANGGAL BACA');
		$obj->getActiveSheet()->setCellValue('E3', 'BLTH');
		$obj->getActiveSheet()->setCellValue('F3', 'LWBP');
		$obj->getActiveSheet()->setCellValue('G3', 'WBP');
		$obj->getActiveSheet()->setCellValue('H3', 'KVARH');
		$obj->getActiveSheet()->setCellValue('I3', 'KELAINAN BACA');
		$obj->getActiveSheet()->setCellValue('J3', 'KWH');
		$obj->getActiveSheet()->setCellValue('K3', 'JAM NYALA');
		$obj->getActiveSheet()->setCellValue('L3', 'DLPD');
		$obj->getActiveSheet()->getStyle('D3:L3')->getFont()->setBold(true);
		
		for ($i = 0, $row = 4; $i < count($data); $i++, $row++) {
			$obj->getActiveSheet()->setCellValue('D' . $row, $data[$i]['tanggal']);
			$obj->getActiveSheet()->setCellValue('E' . $row, $data[$i]['blth']);
			$obj->getActiveSheet()->setCellValue('F' . $row, $data[$i]['lwbp']);
			$obj->getActiveSheet()->setCellValue('G' . $row, $data[$i]['wbp']);
			$obj->getActiveSheet()->setCellValue('H' . $row, $data[$i]['kvarh']);
			$obj->getActiveSheet()->setCellValue('I' . $row, $data[$i]['lbkb']);
			$obj->getActiveSheet()->setCellValue('J' . $row, $data[$i]['kwh']);
			$obj->getActiveSheet()->setCellValue('K' . $row, $data[$i]['jam']);
			$obj->getActiveSheet()->setCellValue('L' . $row, $data[$i]['dlpd']);
		}
		
		$obj->getActiveSheet()->getStyle('D3:L' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		
		$obj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$obj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
		
		$objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"pelanggan-{$id}.xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}
	
	/**
	 * Data rekap LBKB
	 */
	public function lbkb_analisa($data) {
		$get = $this->prepare_get(array('unit'));
		extract($get);
		$unit = intval($unit);
		
		// cari unit
		$run = $this->db->query("SELECT `NAMA_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$namaunit = $run->NAMA_UNIT;
		
		include_once('lib/phpexcel/PHPExcel.php');
		$obj = new \PHPExcel();
		$obj->getProperties()->setCreator('Sistem Analisa Baca Meter Madura')
							 ->setLastModifiedBy('Sistem Analisa Baca Meter Madura')
							 ->setTitle("Data Rekap LBKB")
							 ->setSubject('Rekap LBKB')
							 ->setDescription('Data Rekap LBKB')
							 ->setKeywords('rekap', 'lbkb')
							 ->setCategory('Laporan');
		$obj->setActiveSheetIndex(0);
		$obj->getDefaultStyle()->getFont()->setName('Arial');
		$obj->getDefaultStyle()->getFont()->setSize(9);
		
		$obj->getActiveSheet()->setCellValue('A1', "DATA REKAP LBKB UNIT " . strtoupper($namaunit));
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);
		
		$obj->getActiveSheet()->setCellValue('A3', 'KETERANGAN');
		for ($i = 0, $col = 'B'; $i < count($data['title']); $i++, $col++) {
			$obj->getActiveSheet()->setCellValue($col . '3', $data['title'][$i]);
		}
		$obj->getActiveSheet()->getStyle('A3:' . $col . '3')->getFont()->setBold(true);
		$obj->getActiveSheet()->getColumnDimension('A')->setWidth(41);
		
		for ($i = 0, $row = 3; $i < count($data['data']); $i++, $row++) {
			if ($i == 0) continue;
			$datarow = $data['data'][$i];
			$obj->getActiveSheet()->setCellValue('A' . $row, $datarow['nama']);
			for ($j = 0, $col = 'B'; $j < count($datarow['data']); $j++, $col++) {
				$obj->getActiveSheet()->setCellValue($col . $row, $datarow['data'][$j]);
			}
		}
		
		// total
		$obj->getActiveSheet()->setCellValue('A' . $row, 'T O T A L');
		for ($i = 0, $col = 'B'; $i < count($data['total']); $i++, $col++) {
			$obj->getActiveSheet()->setCellValue($col . $row, $data['total'][$i]);
		}
		$obj->getActiveSheet()->getStyle('A' . $row . ':' . $col . $row)->getFont()->setBold(true);
		
		$obj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$obj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
		
		$objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"lbkb-{$namaunit}.xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}
	
	/**
	 * Rekap Pencatatan
	 */
	public function rekap_catat($data) {
		$get = $this->prepare_get(array('unit'));
		extract($get);
		$unit = intval($unit);
		
		// cari unit
		$run = $this->db->query("SELECT `NAMA_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$namaunit = $run->NAMA_UNIT;
		
		include_once('lib/phpexcel/PHPExcel.php');
		$obj = new \PHPExcel();
		$obj->getProperties()->setCreator('Sistem Analisa Baca Meter Madura')
							 ->setLastModifiedBy('Sistem Analisa Baca Meter Madura')
							 ->setTitle("Rekap Pencatatan")
							 ->setSubject('Rekap Catat')
							 ->setDescription('Data Rekap Pencatatan')
							 ->setKeywords('rekap', 'catat')
							 ->setCategory('Laporan');
		$obj->setActiveSheetIndex(0);
		$obj->getDefaultStyle()->getFont()->setName('Arial');
		$obj->getDefaultStyle()->getFont()->setSize(9);
		
		$obj->getActiveSheet()->setCellValue('A1', "DATA REKAP PENCATATAN UNIT " . strtoupper($namaunit));
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);
		
		$obj->getActiveSheet()->setCellValue('A3', 'NO');
		$obj->getActiveSheet()->setCellValue('B3', 'RBM');
		$obj->getActiveSheet()->setCellValue('C3', 'CATER');
		$obj->getActiveSheet()->setCellValue('D3', 'PLG');
		$obj->getActiveSheet()->setCellValue('E3', 'DIBACA');
		$obj->getActiveSheet()->setCellValue('F3', 'BLM DIBACA');
		$obj->getActiveSheet()->setCellValue('G3', 'GPS');
		$obj->getActiveSheet()->setCellValue('H3', 'NON GPS');
		$obj->getActiveSheet()->setCellValue('I3', 'LBKB');
		$obj->getActiveSheet()->setCellValue('J3', 'FOTO');
		$obj->getActiveSheet()->setCellValue('K3', 'NON FOTO');
		$obj->getActiveSheet()->setCellValue('L3', 'GPS GESER');
		$obj->getActiveSheet()->getStyle('A3:L3')->getFont()->setBold(true);
		
		for ($i = 0, $j = 4; $i < count($data); $i++, $j++) {
			$obj->getActiveSheet()->setCellValue('A' . $j, ($i + 1));
			$obj->getActiveSheet()->setCellValue('B' . $j, $data[$i]['rbm']);
			$obj->getActiveSheet()->setCellValue('C' . $j, $data[$i]['petugas']);
			$obj->getActiveSheet()->setCellValue('D' . $j, $data[$i]['plg']);
			$obj->getActiveSheet()->setCellValue('E' . $j, $data[$i]['terbaca']);
			$obj->getActiveSheet()->setCellValue('F' . $j, $data[$i]['blm_terbaca']);
			$obj->getActiveSheet()->setCellValue('G' . $j, $data[$i]['gps']);
			$obj->getActiveSheet()->setCellValue('H' . $j, $data[$i]['non_gps']);
			$obj->getActiveSheet()->setCellValue('I' . $j, $data[$i]['lbkb']);
			$obj->getActiveSheet()->setCellValue('J' . $j, $data[$i]['foto']);
			$obj->getActiveSheet()->setCellValue('K' . $j, $data[$i]['non_foto']);
			$obj->getActiveSheet()->setCellValue('L' . $j, '0');
		}
		
		$obj->getActiveSheet()->getStyle('A3:L' . ($j - 1))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		
		$obj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$obj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
		
		$objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"rekapcatat-{$namaunit}.xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}
	
	/**
	 * Analisa berdasarkan RBM
	 */
	public function analisa_rbm($data) {
		$get = $this->prepare_get(array('unit'));
		extract($get);
		$unit = intval($unit);
		
		// cari unit
		$run = $this->db->query("SELECT `NAMA_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$namaunit = $run->NAMA_UNIT;
		
		include_once('lib/phpexcel/PHPExcel.php');
		$obj = new \PHPExcel();
		$obj->getProperties()->setCreator('Sistem Analisa Baca Meter Madura')
							 ->setLastModifiedBy('Sistem Analisa Baca Meter Madura')
							 ->setTitle("Rekap Pelanggan")
							 ->setSubject('Rekap Pelanggan')
							 ->setDescription('Data Rekap Pelanggan')
							 ->setKeywords('rekap', 'pelanggan', 'rbm')
							 ->setCategory('Laporan');
		$obj->setActiveSheetIndex(0);
		$obj->getDefaultStyle()->getFont()->setName('Arial');
		$obj->getDefaultStyle()->getFont()->setSize(9);
		
		$obj->getActiveSheet()->setCellValue('A1', "DATA REKAP PELANGGAN PER RBM UNIT " . strtoupper($namaunit));
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);
		
		$obj->getActiveSheet()->setCellValue('A3', 'NO');
		$obj->getActiveSheet()->setCellValue('B3', 'RBM');
		$obj->getActiveSheet()->setCellValue('C3', 'TOTAL PLG');
		$obj->getActiveSheet()->setCellValue('D3', 'STAND LALU');
		$obj->getActiveSheet()->setCellValue('E3', 'STAND INI');
		$obj->getActiveSheet()->setCellValue('F3', 'PMK LALU');
		$obj->getActiveSheet()->setCellValue('G3', 'PMK INI');
		$obj->getActiveSheet()->getStyle('A3:L3')->getFont()->setBold(true);
		
		for ($i = 0, $j = 4, $row = 4; $i < count($data['data']); $i++, $j++) {
			$crow = $data['data'][$i];
			$obj->getActiveSheet()->setCellValue('A' . $j, ($i + 1));
			$obj->getActiveSheet()->setCellValue('B' . $j, $crow['rbm']);
			$obj->getActiveSheet()->setCellValue('C' . $j, $crow['plg']);
			$obj->getActiveSheet()->setCellValue('D' . $j, $crow['st_lalu']);
			$obj->getActiveSheet()->setCellValue('E' . $j, $crow['st_ini']);
			$obj->getActiveSheet()->setCellValue('F' . $j, $crow['kwh_lalu']);
			$obj->getActiveSheet()->setCellValue('G' . $j, $crow['kwh_ini']);
		}
		$obj->getActiveSheet()->setCellValue('A' . $j, 'TOTAL');
		$obj->getActiveSheet()->setCellValue('C' . $j, $data['total']['plg']);
		$obj->getActiveSheet()->setCellValue('D' . $j, $data['total']['st_lalu']);
		$obj->getActiveSheet()->setCellValue('E' . $j, $data['total']['st_ini']);
		$obj->getActiveSheet()->setCellValue('F' . $j, $data['total']['kwh_lalu']);
		$obj->getActiveSheet()->setCellValue('G' . $j, $data['total']['kwh_ini']);
		
		$obj->getActiveSheet()->getStyle('A3:G' . $j)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		
		$obj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$obj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
		
		$objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"rekappelangganrbm-{$namaunit}.xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}
	
	/**
	 * Analisa Tarif
	 */
	public function analisa_tarif($data) {
		$get = $this->prepare_get(array('unit'));
		extract($get);
		$unit = intval($unit);
		
		// cari unit
		$run = $this->db->query("SELECT `NAMA_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$namaunit = $run->NAMA_UNIT;
		
		include_once('lib/phpexcel/PHPExcel.php');
		$obj = new \PHPExcel();
		$obj->getProperties()->setCreator('Sistem Analisa Baca Meter Madura')
							 ->setLastModifiedBy('Sistem Analisa Baca Meter Madura')
							 ->setTitle("Rekap Pelanggan")
							 ->setSubject('Rekap Pelanggan')
							 ->setDescription('Data Rekap Pelanggan')
							 ->setKeywords('rekap', 'pelanggan', 'tarif')
							 ->setCategory('Laporan');
		$obj->setActiveSheetIndex(0);
		$obj->getDefaultStyle()->getFont()->setName('Arial');
		$obj->getDefaultStyle()->getFont()->setSize(9);
		
		$obj->getActiveSheet()->setCellValue('A1', "DATA REKAP PELANGGAN PER TARIF UNIT " . strtoupper($namaunit));
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);
		
		$obj->getActiveSheet()->setCellValue('A3', 'NO');
		$obj->getActiveSheet()->setCellValue('B3', 'TARIF');
		$obj->getActiveSheet()->setCellValue('C3', 'DAYA');
		$obj->getActiveSheet()->setCellValue('D3', 'TOTAL PLG');
		$obj->getActiveSheet()->setCellValue('E3', 'STAND LALU');
		$obj->getActiveSheet()->setCellValue('F3', 'STAND INI');
		$obj->getActiveSheet()->setCellValue('G3', 'PMK LALU');
		$obj->getActiveSheet()->setCellValue('H3', 'PMK INI');
		$obj->getActiveSheet()->getStyle('A3:H3')->getFont()->setBold(true);
		
		for ($i = 0, $j = 4, $row = 4; $i < count($data); $i++, $j++) {
			$crow = $data[$i];
			$obj->getActiveSheet()->setCellValue('A' . $j, ($i + 1));
			$obj->getActiveSheet()->setCellValue('B' . $j, $crow['tarif']);
			$obj->getActiveSheet()->setCellValue('C' . $j, $crow['daya']);
			$obj->getActiveSheet()->setCellValue('D' . $j, $crow['plg']);
			$obj->getActiveSheet()->setCellValue('E' . $j, $crow['st_lalu']);
			$obj->getActiveSheet()->setCellValue('F' . $j, $crow['kwh_ini']);
			$obj->getActiveSheet()->setCellValue('G' . $j, $crow['kwh_lalu']);
			$obj->getActiveSheet()->setCellValue('H' . $j, $crow['kwh_ini']);
		}
		
		$obj->getActiveSheet()->getStyle('A3:H' . ($j - 1))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		
		$obj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$obj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
		
		$objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"rekappelanggantarif-{$namaunit}.xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}
	
	/**
	 * Untuk laporan tusbung
	 */
	public function tusbung($r) {
		$get = $this->prepare_get(array('unit', 'blth'));
		extract($get);
		$unit = intval($unit);
		$blth = intval($blth);
		
		// cari unit
		$run = $this->db->query("SELECT `NAMA_UNIT` FROM `unit` WHERE `ID_UNIT` = '$unit'", TRUE);
		$nmunit = $run->NAMA_UNIT;
		
		// bulan tahun
		$run = $this->db->query("SELECT `NAMA_BLTH` FROM `blth` WHERE `ID_BLTH` = '$blth'", TRUE);
		$bln = substr($run->NAMA_BLTH, 0, 2);
		$thn = substr($run->NAMA_BLTH, 2, 4);
		$nmblth = datedb_to_tanggal($thn . '-' . $bln . '-' . '01', 'F Y');
		
		include_once('lib/phpexcel/PHPExcel.php');
		$obj = new \PHPExcel();
		$obj->getProperties()->setCreator('Sistem Analisa Baca Meter Madura')
							 ->setLastModifiedBy('Sistem Analisa Baca Meter Madura')
							 ->setTitle("Laporan Kinerja Tusbung")
							 ->setSubject('Laporan')
							 ->setDescription('Laporan Kinerja Tusbung')
							 ->setKeywords('laporan', 'kinerja', 'tusbung')
							 ->setCategory('Laporan');
		$obj->setActiveSheetIndex(0);
		$obj->getDefaultStyle()->getFont()->setName('Calibri');
		$obj->getDefaultStyle()->getFont()->setSize(11);
		
		$obj->getActiveSheet()->setCellValue('A3', 'Laporan Kinerja Pemutusan');
		$obj->getActiveSheet()->setCellValue('A4', "Zona ... Unit $nmunit");
		$obj->getActiveSheet()->setCellValue('A5', "Bulan: $nmblth");
		$obj->getActiveSheet()->getStyle('A1:A5')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A1:A5')->getFont()->setSize(12);
		$obj->getActiveSheet()->mergeCells('A3:P3');
		$obj->getActiveSheet()->mergeCells('A4:P4');
		$obj->getActiveSheet()->mergeCells('A5:P5');
		$obj->getActiveSheet()->getStyle('A3:A5')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		$obj->getActiveSheet()->setCellValue('A7', 'No');
		$obj->getActiveSheet()->setCellValue('B7', 'Tgl Cetak');
		$obj->getActiveSheet()->setCellValue('C7', 'Tul VI-01 Dicetak');
		$obj->getActiveSheet()->setCellValue('E7', 'Tul VI-01 Dilunasi');
		$obj->getActiveSheet()->setCellValue('G7', 'Tul VI-01 Diputus');
		$obj->getActiveSheet()->setCellValue('I7', 'Tul VI-01 Diputus Lunas');
		$obj->getActiveSheet()->setCellValue('K7', 'Sambung');
		$obj->getActiveSheet()->setCellValue('M7', 'Tul VI-01 Tidak Lunas');
		$obj->getActiveSheet()->setCellValue('O7', 'Rasio Pelunasan %');
		$obj->getActiveSheet()->setCellValue('C8', 'Lembar');
		$obj->getActiveSheet()->setCellValue('E8', 'Lembar');
		$obj->getActiveSheet()->setCellValue('G8', 'Lembar');
		$obj->getActiveSheet()->setCellValue('I8', 'Lembar');
		$obj->getActiveSheet()->setCellValue('K8', 'Lembar');
		$obj->getActiveSheet()->setCellValue('M8', 'Lembar');
		$obj->getActiveSheet()->setCellValue('O8', 'Lembar');
		$obj->getActiveSheet()->setCellValue('D8', 'Tagihan');
		$obj->getActiveSheet()->setCellValue('F8', 'Tagihan');
		$obj->getActiveSheet()->setCellValue('H8', 'Tagihan');
		$obj->getActiveSheet()->setCellValue('J8', 'Tagihan');
		$obj->getActiveSheet()->setCellValue('L8', 'Tagihan');
		$obj->getActiveSheet()->setCellValue('N8', 'Tagihan');
		$obj->getActiveSheet()->setCellValue('P8', 'Tagihan');
		$obj->getActiveSheet()->mergeCells('A7:A8');
		$obj->getActiveSheet()->mergeCells('B7:B8');
		$obj->getActiveSheet()->mergeCells('C7:D7');
		$obj->getActiveSheet()->mergeCells('E7:F7');
		$obj->getActiveSheet()->mergeCells('G7:H7');
		$obj->getActiveSheet()->mergeCells('I7:J7');
		$obj->getActiveSheet()->mergeCells('K7:L7');
		$obj->getActiveSheet()->mergeCells('M7:N7');
		$obj->getActiveSheet()->mergeCells('O7:P7');
		$obj->getActiveSheet()->getStyle('A7:P8')->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A7:P8')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		$obj->getActiveSheet()->getColumnDimension('A')->setWidth(4);
		$obj->getActiveSheet()->getColumnDimension('B')->setWidth(11);
		for ($i = 'C'; $i <= 'P'; $i++) $obj->getActiveSheet()->getColumnDimension($i)->setWidth(12);
		
		$data = $r['data'];
		$total = $r['total'];
		for ($i = 0, $crow = 9; $i < count($data); $i++, $crow++) {
			$d = $data[$i];
			$obj->getActiveSheet()->setCellValue('A' . $crow, ($i + 1));
			$obj->getActiveSheet()->setCellValue('B' . $crow, $d['tgl']);
			$obj->getActiveSheet()->setCellValue('B' . $crow, $d['tgl']);
			$obj->getActiveSheet()->setCellValue('C' . $crow, $d['cetak_lembar']);
			$obj->getActiveSheet()->setCellValueExplicit('D' . $crow, $d['cetak_tagihan'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue('E' . $crow, $d['lunas_lembar']);
			$obj->getActiveSheet()->setCellValueExplicit('F' . $crow, $d['lunas_tagihan'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue('G' . $crow, $d['p_lembar']);
			$obj->getActiveSheet()->setCellValueExplicit('H' . $crow, $d['p_tagihan'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue('I' . $crow, $d['pl_lembar']);
			$obj->getActiveSheet()->setCellValueExplicit('J' . $crow, $d['pl_tagihan'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue('K' . $crow, $d['sambung_lembar']);
			$obj->getActiveSheet()->setCellValueExplicit('L' . $crow, $d['sambung_tagihan'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValue('M' . $crow, $d['tlunas_lembar']);
			$obj->getActiveSheet()->setCellValueExplicit('N' . $crow, $d['tlunas_tagihan'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValueExplicit('O' . $crow, $d['rasio_lembar'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$obj->getActiveSheet()->setCellValueExplicit('P' . $crow, $d['rasio_tagihan'], \PHPExcel_Cell_DataType::TYPE_STRING);
		}
		
		$obj->getActiveSheet()->setCellValue('A' . $crow, 'Jumlah');
		$obj->getActiveSheet()->mergeCells('A'. $crow .':B' . $crow);
		$obj->getActiveSheet()->setCellValue('C' . $crow, $total['cetak'][0]);
		$obj->getActiveSheet()->setCellValueExplicit('D' . $crow, $total['cetak'][1], \PHPExcel_Cell_DataType::TYPE_STRING);
		$obj->getActiveSheet()->setCellValue('E' . $crow, $total['lunas'][0]);
		$obj->getActiveSheet()->setCellValueExplicit('F' . $crow, $total['lunas'][1], \PHPExcel_Cell_DataType::TYPE_STRING);
		$obj->getActiveSheet()->setCellValue('G' . $crow, $total['p'][0]);
		$obj->getActiveSheet()->setCellValueExplicit('H' . $crow, $total['p'][1], \PHPExcel_Cell_DataType::TYPE_STRING);
		$obj->getActiveSheet()->setCellValue('I' . $crow, $total['pl'][0]);
		$obj->getActiveSheet()->setCellValueExplicit('J' . $crow, $total['pl'][1], \PHPExcel_Cell_DataType::TYPE_STRING);
		$obj->getActiveSheet()->setCellValue('K' . $crow, $total['sambung'][0]);
		$obj->getActiveSheet()->setCellValueExplicit('L' . $crow, $total['sambung'][1], \PHPExcel_Cell_DataType::TYPE_STRING);
		$obj->getActiveSheet()->setCellValue('M' . $crow, $total['tlunas'][0]);
		$obj->getActiveSheet()->setCellValueExplicit('N' . $crow, $total['tlunas'][1], \PHPExcel_Cell_DataType::TYPE_STRING);
		$obj->getActiveSheet()->setCellValueExplicit('O' . $crow, $total['rasio'][0], \PHPExcel_Cell_DataType::TYPE_STRING);
		$obj->getActiveSheet()->setCellValueExplicit('P' . $crow, $total['rasio'][1], \PHPExcel_Cell_DataType::TYPE_STRING);
		$obj->getActiveSheet()->getStyle('A' . $crow . ':P' . $crow)->getFont()->setBold(true);
		$obj->getActiveSheet()->getStyle('A7:P' . $crow)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		
		$obj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$obj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
		
		$objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"laporan-tusbung-{$nmunit}-{$bln}{$thn}.xlsx\"");
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}
}