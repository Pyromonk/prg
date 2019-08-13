<?
/**
 * A simple PHP bot that chooses random responses to issues for a given nation.
 * 2017-2019
 * Version 2.26
 *
 * @param xAutologin - an encrypted version of your nation's password
 * @param xPin - a unique session identifier
 * @param apiVersion - the currently utilised version of NationStates' API
 * @param nationName - the name of the nation being mutilated (lowercase, URL-encoded)
 * @param nationMain - the full, properly capitalised name of your main nation, required for not breaking site rules
 * @param email - where the script will send error reports if something spicy happens
 *
 * No warranty of any form is offered. Use at your own risk.
 * For additional information, refer to NationStates' API: https://www.nationstates.net/pages/api.html
 */

$xAutologin = '';
$xPin = '';
$apiVersion = '9';
$nationName = '';
$nationMain = '';
$email = '';
//libxml_use_internal_errors(true);//--debug
$nationName = mb_strtolower(preg_replace('%\s%', '_', $nationName));
$base = 'https://www.nationstates.net/cgi-bin/api.cgi';
$uData = array('X-Autologin: '.$xAutologin, 'X-Pin: '.$xPin);
$nation = simplexml_load_string(mb_convert_case(getPage($base.'?v='.$apiVersion.'&nation='.$nationName.'&q=issues+unread', $uData, 0), MB_CASE_LOWER, 'UTF-8'));
if($nation === false) {
	reportProblems('XML read failure.');
	//echo 'Errors:<br>'.var_dump(libxml_get_errors()).'<br><br>';//--debug
	die('Something went terribly wrong.');
}
foreach($nation->issues->issue as $issue) {
	$options = array();
	$id = (string)$issue['id'];
	foreach($issue->option as $option) { $options[] = (string)$option['id']; }
	$choice = $options[mt_rand(0, count($options)-1)];
	$pData = array('v'=>$apiVersion, 'nation'=>$nationName, 'c'=>'issue', 'issue'=>$id, 'option'=>$choice);
	getPage($base, $uData, $pData);
}
$stats = '<b>Notices</b>:&nbsp;<span style="color: #ff0000;">'.$nation->unread->notices.'</span><br>';
$stats .= '<b>Issues</b>:&nbsp;<span style="color: #009900;">'.$nation->unread->issues.'</span><br>';
$stats .= '<b>Telegrams</b>:&nbsp;<span style="color: #0000ff;">'.$nation->unread->telegrams.'</span>';
echo $stats;

function getPage($url, $udt, $pst) {
	global $nationMain;
	$headers = array();
	$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8';
	$headers[] = 'Accept-Language: en-GB,en;q=0.5';
	$headers[] = 'Accept-Encoding: deflate';
	$headers[] = 'User-Agent: NSBot, current user - '.$nationMain;
	$headers = array_merge($headers, $udt);
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_REFERER, 'https://www.nationstates.net/nation='.rawurlencode(mb_strtolower($nationMain)));
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	if($pst) {
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $pst);
	}
	$get_page = @curl_exec($curl);
	curl_close($curl);
	if(!$get_page) { return false; }
	return $get_page;
}

function reportProblems($txt) {
	global $email, $nationName;
	$addressee = "$email";
	$subject = "NationStates";
	$message = "I need an oilcan, pronto: $txt";
	$headers = "From: $nationName@nationstates.net\r\n";
	$headers .= "Reply-To: $nationName@nationstates.net";
	mail($addressee, $subject, $message, $headers);
}
?>
