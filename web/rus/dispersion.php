<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="Собираем данные о великом и могучем">
	<meta name="keywords" content="Правописание, Ил, Смерть">
	<link rel="icon" href="../img/fav.ico">
	<link rel="stylesheet" type="text/css" href="styles.css">
	<title>Дисперсия</title>
</head>
<body>
	<form method="POST" id="d" action="dispersion.php"><fieldset>
	<?php
	if($l) {
		if($REQUEST_METHOD != 'POST') {
			echo 'Ишь ты какой хитрый!';
		} else if(strtolower($l) != 'login' || md5($p) != 'pw_md5') {
			echo 'Попробуйте ввести правильные логин и пароль.';
		} else {
			$_SESSION['a'] = 1;
		}
	}
	if(((strtolower($l) == 'login' && md5($p) == 'pw_md5') || $a) && mb_regex_encoding('utf-8') && mb_internal_encoding('utf-8')) {
		$t = preg_match('/\w+/imu', $t) ? $t : 0;
		if($t) { $t = mb_strtolower(trim(preg_replace(array('/\d/mu', '/\W/imu', '/\s+/mu'), array('', ' ', ' '), $t))); }
		?><textarea name="t" id="t"<?=$t ? ' readonly="readonly"' : ''?> maxlength="4096"><?=$t ? $t : ''?></textarea><?php
		if(!$t) { ?><input type="submit" value="Submit"><?php }
	} else { ?>
		<label for="l">Login:&emsp;</label><input type="text" name="l" id="l"><br>
		<label for="p">Password:&emsp;</label><input type="password" name="p" id="p"><br>
		<input type="submit" value="Submit">
	<?php } ?>
	</fieldset></form>
</body>
</html>
