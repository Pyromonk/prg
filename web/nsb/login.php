<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$l = 'login'; //your bot's nation name
$p = 'password';

login($l, $p);

function login($lgn, $psw) {
	global $cookie;
	
	$headers = array();
	$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8';
	$headers[] = 'Accept-Language: en-GB,en;q=0.5';
	$headers[] = 'Accept-Encoding: deflate';
	$headers[] = 'User-Agent: NSBot';
	
	$data = array();
	$data['logging_in'] = 1;
	$data['nation'] = $lgn;
	$data['password'] = $psw;
	$data['autologin'] = 'yes';
	$data['submit'] = 'Login';
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, 'https://www.nationstates.net/page=login');
	curl_setopt($curl, CURLOPT_COOKIESESSION, TRUE);
	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	$get_page = @curl_exec($curl);
	
	if(!$get_page) {
		echo 'Error retrieving data.<br>';
		echo('CURL ERROR: '.curl_errno($curl).': '.curl_error($curl));
		return false;
	}
	
	preg_match_all('/Set-Cookie: (\S+)/', $get_page, $matches);
	$cookie = implode($matches[1], ' ');
	//print_r($cookie);
	
	curl_close($curl);
	return true;
}
?>
