<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>CSV Form Test</title></head>
<body>
<?php
if(isset($fn)) {
	$fn = $_GET['fn'];
	$nn = $_GET['nn'] ? $_GET['nn'] : null;
	ini_set('auto_detect_line_endings', true);
	$t = '<div>';
	$fp = file_get_contents('ksdb.csv');
	$lines = str_getcsv($fp, PHP_EOL);
	if($fn || $nn) {
		foreach($lines as $line) {
			$l = preg_split('/,(?!\s+)/', $line);
			if($fn && (strtolower($l[0]) != strtolower($fn))) { continue; }
			if($nn && (strtolower($l[1]) != strtolower($nn))) { continue; }
			$t .= 'Dear '.htmlspecialchars($l[0]).',<br><br>';
			$t .= 'Your parcel tracking number is '.$l[5].'.<br>';
			$t .= 'Current status is: '.($l[6] == '' ? 'unknown' : $l[6]).'.<br>';
			$t .= 'Shipped to '.htmlspecialchars($l[2]).'.<br>';
			$cc = array('US');
			if(in_array($l[2], $cc)) {
				switch ($l[2]) {
					case 'US': $t .= '<a href="https://tools.usps.com/go/TrackConfirmAction?tLabels='.$l[5].'">Track your package here</a>'; break;
					//case '': t .= ''; break;
				}
			} else {
				$t += 'You can track your package with your local carrier.';
			}
			break;
		}
	}
	if($t == '<div>') { $t .= 'Record not found. Please check spelling.<br><a href="index.php">Return to form</a>'; }
	$t .= '</div>';
	echo $t;
} else { ?>
	<form method="GET">
		<label for="fn">Name: </label><input type="text" name="fn" id="fn"><br>
		<label for="nn">Nickname: </label><input type="text" name="nn" id="nn"><br>
		<input type="submit" action="index.php">
	</form>
<?php } ?>
</body>
</html>
