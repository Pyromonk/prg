<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$l = ''; //your bot's nation name
$p = ''; //password
$v = '9'; //API version
$l = mb_strtolower(preg_replace('%\s%', '_', $l));
login($l, $p, $v);

function login($lgn, $psw, $vrs) {
	$headers = array();
	$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8';
	$headers[] = 'Accept-Language: en-GB,en;q=0.5';
	$headers[] = 'Accept-Encoding: deflate';
	$headers[] = 'User-Agent: NSBot'; //include main nation name or contact information to not break the site's rules
	$headers[] = 'X-Password: '.$psw;
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, 'https://www.nationstates.net/cgi-bin/api.cgi?v='.$vrs.'&nation='.$lgn.'&q=unread');
	curl_setopt($curl, CURLOPT_COOKIESESSION, TRUE);
	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	$get_page = @curl_exec($curl);
	if(!$get_page) {
		echo 'Error retrieving data.<br>';
		echo('cURL error: '.curl_errno($curl).': '.curl_error($curl));
		return false;
	}
	$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
	curl_close($curl);
	//echo substr($get_page, 0, $header_size); //this will echo returned headers, including X-Autologin and X-Pin
	return true;
}
?>
