<?php
if($_SERVER['REQUEST_METHOD']!='POST' || !preg_match('%subdomain\.domain\.extension%', $_SERVER['HTTP_REFERER'])) { die('You sly little fox!'); }
extract($_REQUEST, EXTR_PREFIX_SAME, 'r_');
$rsl = strtolower($rsl);
$cus = strip_tags($rsl); //storing to compare later on
$wrd = compact('nun', 'vrb', 'adj', 'adv');
foreach($wrd as $k => $v) {
	if($v!='' && !preg_match('%^Pick one%', $v)) { $v = explode(', ', $v); shuffle($v); }
	if(is_array($v)) { $rsl = preg_replace_callback('%<span class="wrd '.$k.'">.+?<\/span>%', function() use(&$v) { return array_shift($v); }, $rsl); }
}
$rsl = strtolower(preg_replace('%<span class="wrd">(.+?)<\/span>%', "$1", $rsl));
echo '<i class="nte">Similarity between original text and result: '.getSimilarity($cus, $rsl).'%.</i><br><br>';
if($sbm=='ptr') { //poetry mode selected
	require_once 'syllable.php';
	$num = (int)$num;
	$rsl = trim(preg_replace(array('%\d%m', '%[^-\'\w]%im', '%\s+%m'), array('', ' ', ' '), $rsl));
	if($num>0) {
		$xpr = explode(' ', $rsl);
		$msk = ''; //contains a numeric representation of input text
		foreach($xpr as $x) { $msk .= syllableCount($x)>1 ? (string)syllableCount($x) : '0'; }
		$rsl = '';
		$wgt = 0; //word weight
		$brk = 0; //counter for when to break the line
		for($i=0, $l=count($xpr); $i<$l; $i++) {
			$rsl .= ' '.$xpr[$i];
			$wgt = $msk[$i]=='0' ? 0 : (int)$msk[$i];
			$wgt = $wgt ? 1+(($wgt-1)*0.5) : 0.5; //should hyphenated words be treated differently?
			$brk += $wgt;
			if($brk>=$num) {
				if($i!=$l-1) { $rsl .= '<br>'; }
				$brk = 0;
			}
			if($i==$l-1 && $brk!=0) { $rsl .= '<br><i class="nte">The last line lacks '.($num-$brk).' weight (unstressed syllable = 0.5 weight, stressed syllable = 1 weight).</i>'; }
		}
		trim($rsl);
	}
}
echo $rsl;

function getSimilarity($s1, $s2) { //from one of the comments at http://php.net/manual/en/function.similar-text.php
	$l1 = strlen($s1);
	$l2 = strlen($s2);
	$mxl = max($l1, $l2);
	$s = $i = $j = 0;
	while($i<$l1 && isset($s2[$j])) {
		if($s1[$i]==$s2[$j]) {
			$s++;
			$i++;
			$j++;
		} else if($l1<$l2) {
			$l1++;
			$j++;
		} else if($l1>$l2) {
			$i++;
			$l1--;
		} else {
			$i++;
			$j++;
		}
	}
	return 100*round($s/$mxl, 2); //another option: http://php.net/manual/en/function.levenshtein.php
}
?>
