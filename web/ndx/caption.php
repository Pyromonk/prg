<?php
$vwl = array('а', 'е', 'ё', 'и', 'о', 'у', 'ы', 'э', 'ю', 'я');
$urc = array('ж', 'й', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ь');
$rpc = array('б', 'в', 'г', 'д', 'з', 'к', 'л', 'м', 'н', 'п', 'р', 'с', 'т'); //Русская Православная Церковь
$ztn = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
$ttf = array('a', 'b', 'c', 'd', 'e', 'f');
//-- debug_begin --
echo '<pre>';
for($k=0; $k<100; $k++) {
	$clr = '';
	$cpn = '';
	$rnd = 0;
	genWord();
}
echo '</pre>';
function genWord() {
	global $clr;
	global $cpn;
	global $rnd;
	global $vwl;
	global $urc;
	global $rpc;
	global $ztn;
	global $ttf;
	global $k;
	//-- debug_end --
	if(!mb_regex_encoding('utf-8')) { die('А-а-а!'); }; //выведет 1 - установление кодировки прошло удачно
	if(!mb_internal_encoding('utf-8')) { die('Да что ж такое-то...'); }; //также выведет 1 (на всякий случай, чтобы внять все вопросы)
	for($i=0; rzo()<1/pow($i+1, 1/6); $i++) {
		$rnd = mt_rand(0, 32);
		$res = intval(preg_match('/[^аеёиоуыэюя]{3}/u', mb_substr($cpn, -3)));
		if($res) {
			$cpn .= $vwl[$rnd%10];
		} else if(intval(preg_match('/[аеёиоуыэюя]{2}/u', mb_substr($cpn, -2)))) {
			$rnd %= 23;
			$cpn .= $rnd<10 ? $urc[$rnd] : $rpc[$rnd-10];
		} else if(in_array(mb_substr($cpn, -1), $urc)) {
			$rnd %= 23;
			$cpn .= $rnd<10 ? $vwl[$rnd] : $rpc[$rnd-10];
		} else {
			$cpn .= $rnd<10 ? $vwl[$rnd] : ($rnd<20 ? $urc[$rnd-10] : $rpc[$rnd-20]);
		}
		//-- debug_begin --
		echo $cpn.' : ';
		//-- debug_end --
	}
	if(!preg_match('/[аеёиоуыэюя]/u', $cpn)) {
		$rnd = mt_rand(0, mb_strlen($cpn));
		$cpn = mb_substr($cpn, 0, $rnd).$vwl[mt_rand(0, 9)].($rnd==mb_strlen($cpn) ? '' : mb_substr($cpn, $rnd));
	}
	for($i=0; $i<6; $i++) {
		$rnd = mt_rand(0, 1);
		$clr .= !$rnd || ($i==4 && !preg_match('/\d/', $clr)) ? $ztn[mt_rand(0, 9)] : $ttf[mt_rand(0, 5)];
	}
	echo '<span id="word_'.$k.'"><span style="color: #'.$clr.'; font-size: 200%;">'.$cpn.'</span></span>';
	//-- debug_begin --
	echo '<br>';
}
//-- debug_end --
 
function rzo() { return (float)(mt_rand()/mt_getrandmax()); }
?>
