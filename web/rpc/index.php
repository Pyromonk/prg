<?php
	extract($_REQUEST, EXTR_PREFIX_SAME, 'r_');
	if($s && $REQUEST_METHOD != 'POST') {
		die('You sly little fox!');
	} else if($t && mb_regex_encoding('utf-8') && mb_internal_encoding('utf-8')) {
		require_once 'splitter.php';
		$n = (int)$n;
		$n = $n<1 ? 1 : $n;
		$t = strlen($t)>65535 ? substr($t, 0, 65535) : $t;
		$t = array_filter(array_map('trim', sentence_split(html_entity_decode($t))), function($x) { return $x !== ''; });
		shuffle($t);
		$t = array_slice($t, 0, $n);
		$t = implode("\n", $t);
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="Creating random paragraphs, one text at a time">
	<meta name="keywords" content="Русская, Православая, Церковь">
	<link rel="icon" href="../img/fav.ico">
	<link rel="stylesheet" type="text/css" href="styles.css">
	<title>Random Paragraph Creator</title>
</head>
<body>
	<form action="" method="POST">
		<textarea name="t" maxlength="65535"<?=$s ? ' readonly="readonly">'.$t : ' placeholder="Input text. Select number of sentences to output. Submit.">';?></textarea><br>
		<input name="n" type="number" value="7"><br>
		<input name="s" type="submit" value="Submit"<?=$s ? ' disabled="disabled"' : '';?>>
	</form>
</body>
</html>
