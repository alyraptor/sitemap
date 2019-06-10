<!DOCTYPE html>
<html>
<head>
	<title>Site Map Tool</title>
	<link rel="stylesheet" type="text/css" href="progress.css">
</head>
<body>
	<p>Creating .xlsx File... (Please wait, this will take a long time.)</p>
</body>
</html>

<?php

$time_start = microtime(true);

// ========================
//  Includes
// ========================

require_once 'utility.php';
require_once 'scrape-automate.php';
require_once 'vendor/simple_html_dom.php';
require_once 'vendor/PHPExcel/Classes/PHPExcel.php';

// ========================
//  Initial Values
// ========================

$total_rows = 200; // How many rows are needed // TODO: Detect dynamically
$row_start = $row_number = 2; // First row to be edited
$col_start = 5; // First column to be edited
$callout_percent = 5; // How often our progress should be reported

// ========================
//  Initialize PHPExcel
// ========================

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('America/Chicago');
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$inputFileType = 'Excel2007';
$objReader = PHPExcel_IOFactory::createReader($inputFileType);

// Load Sitemap File
$inputFileName = getcwd().'/sitemap-comments.xlsx';
$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
$worksheetData = $objReader->listWorksheetInfo($inputFileName);
$totalRows = $worksheetData[0]['totalRows'];


// ========================
//  Functions
// ========================

function file_progress($row_number, $total_rows, $callout_percent) {
	// Echo file progress
	$percent = $row_number / $total_rows * 100;

	// Only echo rows that are divisible by the callout percentage
	if(fmod($percent, $callout_percent) == 0) {
		echo 'File progress: ' . $percent . '%<br>';

		// Display PHP output
		ob_flush(); 
		flush();
	}
}

function processing_time($time_start) {
	$time_end = microtime(true);
	$time['true'] = $time_end - $time_start;

	$hours = (int) ($time['true'] / 60 / 60); // Get hours int from time
	$hours = ($hours != 0 ? $hours . 'hr ' : '' );

	$minutes = (int) ($time['true'] / 60 % 60); // Get minutes modulo from time
	$minutes = ($minutes != 0 ? $minutes . 'min ' : '' );

	$seconds = (int) ($time['true'] % 60); // Get seconds modulo from time
	$seconds = $seconds . 'sec';

	$time['display'] = $hours . $minutes . $seconds;

	return $time;
}

function write_row($table_row, $objPHPExcel) {

	global $letter;
	global $col_start;

	$num = $objPHPExcel->getActiveSheet()->getCell('A'.$table_row)->getValue();
	$url = $objPHPExcel->getActiveSheet()->getCell('C'.$table_row)->getValue();

	if($num !== null) {

		$master_array = scrape_automate($url, $num);

		// ========================
		//  Write to File
		// ========================

		for ($i = 1; $i <= arr_length($master_array); $i++) {
			if($i != 1) {
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($table_row + 1); // Insert row after current
				$table_row++;
				
				$objPHPExcel->getActiveSheet()->getRowDimension($table_row)->setOutlineLevel(1); // Make new row hideable
				$objPHPExcel->getActiveSheet()->getRowDimension($table_row)->setVisible(false); // Hide new row under dropdown
			}

			$col_num = $col_start;
			foreach($master_array as $column) { // Write page array to PHPExcel Object

				$key = $i - 1; // Adjust for array starting at [0]
				if(isset($column[$key])) {
					$objPHPExcel->getActiveSheet()->setCellValue($letter[$col_num].$table_row, $column[$key]);
				}
				$col_num++;
			}
		}
	}
}


// ========================
//  Create File
// ========================

echo 'File progress: 0%<br>';

while($row_number < $total_rows){
	write_row($row_number, $objPHPExcel);

	file_progress($row_number, $total_rows, $callout_percent);

	$row_number++;
}

$new_filename = 'Dotcom Pages ' . date('Y-m-d His') . '.xlsx';

// Finalize and Save File
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objPHPExcel->getActiveSheet()->unfreezePane(); // Sometimes the new file is frozen
$objWriter->save($new_filename); // Date format 2017-08-16 111359
echo 'File activity complete.<br><br>';

$time = processing_time($time_start);
?>

<pre>
<?php
	echo 'Finished: ' . date('h:i:sa') . '<br />';
	echo 'Execution Time: ' . $time['display'] . ' (' . round($time['true'], 2) . 'sec)<br />';
	echo 'Memory Used: ' . round((memory_get_peak_usage(false)/1024/1024), 4) . ' MiB<br />'; // Get memory usage in Mebibytes. Change to 1000/1000 for Megabytes
	echo 'Filename: "' . $new_filename . '"';
?>
</pre>