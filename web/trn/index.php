<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="У меня проблемы с фантазией">
	<meta name="keywords" content="Мизогиния, Катехон">
	<link rel="icon" href="img/fav.ico">
	<title>Translation</title>
	<style>
		form :not([type="submit"]) { display: block; }
		textarea { min-width: 1000px; min-height: 250px; }
	</style>
</head>
<body>
<? /* Чтобы опять не забыть: я написал эту страницу,
	когда надо было предложения оригинала и перевода писать друг за другом, чтобы было видно */
if(!$_POST['eng'] || !$_POST['rus']) {
?>
	<form method="POST" action="?">
		<label for="eng">English:</label>
		<textarea id="eng" name="eng"></textarea>
		<label for="rus">Russian:</label>
		<textarea id="rus" name="rus"></textarea>
		<input type="Submit">
	</form>
<?
} else {
	$eng = $_POST['eng']; $rus = $_POST['rus']; $rtn = '';
	if(mb_regex_encoding('utf-8') && mb_internal_encoding('utf-8')) {
		$eng = preg_split("/(\r\n?|\n)+/mu", trim($eng)); //(?<!(?: |^)(?:Mr|Mrs|Ms|Dr|St|\p{Lu}{1}))([’”\'"»]?)([.!?]+)\s+(\p{Lu}+)
		$rus = preg_split("/(\r\n?|\n)+/mu", trim($rus));
		for($i=0, $l=count($eng); $i<$l; $i++) { $rtn .= $eng[$i]."\n".$rus[$i]."\n\n"; }
	}
?>
	<textarea readonly><?=trim($rtn)?></textarea>
<?
}
?>
</body>
</html>
