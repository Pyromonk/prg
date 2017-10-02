<?
$cookie = '__cfduid=get-your-own; pin=tasty; autologin=cookie;';
//$rands=array();//--debug
if(preg_match_all('/a href="\/page=show_dilemma\/dilemma=(\d+)"/', getPage('https://www.nationstates.net/page=dilemmas', $cookie, 0), $dilemmas)) {
	//echo 'Dilemmas: '; print_r($dilemmas); echo '<br>';//--debug
	if(count($dilemmas[1])>4) { reportProblems('too many issues to deal with.'); die(); }
	foreach($dilemmas[1] as $num) {
		if(preg_match_all('/type="submit" name="choice-(\d+)/', getPage('https://www.nationstates.net/page=show_dilemma/dilemma='.$num, $cookie, 0), $choices)) {
			//echo 'Choices: '; print_r($choices); echo'<br>';//--debug
			$choice = array();
			foreach($choices[1] as $val) { $choice[] = $val; }
			$rand = mt_rand(0, count($choice)-1);
			$send = array();
			$send['choice-'.$choice[$rand]] = 1;
			//$rands[]=$rand;//--debug
			getPage('https://www.nationstates.net/page=enact_dilemma/dilemma='.$num, $cookie, $send);
		} else {
			reportProblems('there are no choices to pick from.');
		}
	}
} else {
	reportProblems('no dilemmas to consider.');
}
//echo 'Rands: '; print_r($rands);//--debug

function getPage($url, $udt, $pst) {
	$headers = array();
	$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8';
	$headers[] = 'Accept-Language: en-GB,en;q=0.5';
	$headers[] = 'Accept-Encoding: deflate';
	$headers[] = 'User-Agent: NSBot';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_REFERER, 'https://www.nationstates.net/nation=testlandia'); //change this to link to your nation
	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt($curl, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_COOKIESESSION, TRUE);
	curl_setopt($curl, CURLOPT_COOKIE, $udt);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	if($pst) {
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $pst);
	}
	$get_page = @curl_exec($curl);
	if(!$get_page) { return false; }
	curl_close($curl);
	return $get_page;
}

function reportProblems($txt) {
	$addressee = "name@domain.extension";
	$subject = "NationStates";
	$message = "I need an oilcan, pronto: ".$txt;
	$headers = array(); //again
	$headers = "From: testlandia@nationstates.net\r\n"; //change this line and the following one to reflect your bot's nation name
	$headers = "Reply-To: testlandia@nationstates.net";
	mail($addressee, $subject, $message, $headers);
}
?>
