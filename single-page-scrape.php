<?php

$time_start = microtime(true);

include "utility.php";
include "scrape-automate.php";
include "vendor/simple_html_dom.php";

//========================
// Initialize Variables
//========================

$url;
$num = '';
if(isset($_GET["url"])) {$url = $_GET["url"];}
if(isset($_GET["num"])) {$num = $_GET["num"];}
date_default_timezone_set('America/Chicago');

//========================
// Main Loop
//========================

if(isset($url)) {$master_array = scrape_automate($url, $num);}

//========================
// Write Table
//========================

if (isset($master_array)):
	$array_length;
	if(arr_length($master_array) == 0) {
		$array_length = 1;
	} else {
		$array_length = arr_length($master_array);
	}
?>

<table>
	<thead>
	<tr>
		<th><?php echo implode('</th><th>', array_keys($master_array)); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php for ($i = 1; $i <= $array_length; $i++) { ?>
		<tr>
			<?php foreach($master_array as $v) { ?>
			<td>
				<?php
					$key = $i - 1;
					if(isset($v[$key])) {
						echo $v[$key];
					}
				?>
			</td>
			<?php } ?>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php 

//========================
// Display Data on Page
//========================

?>
<pre>
<?php
	$time = microtime(true) - $time_start;
	echo 'Finished: ' . date('h:i:sa') . '<br />';
	echo 'Execution Time: ' . round($time, 2) . " seconds<br />";
	echo 'Memory Used: ' . round((memory_get_peak_usage(false)/1024/1024), 4) . ' MiB'; // Get memory usage in Mebibytes. Change to 1000/1000 for Megabytes
?>
</pre>

<pre>
<?php echo 'Longest Array Length: ' . arr_length($master_array) . '<br /><br />'; echo 'Final Array: '; print_r($master_array); ?>
</pre>
<?php endif; ?>