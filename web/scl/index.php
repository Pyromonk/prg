<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="author" content="GoreGrindGeek">
	<meta name="description" content="У меня проблемы с фантазией">
	<meta name="keywords" content="Мизогиния, Катехон">
	<link rel="icon" href="img/fav.ico">
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans"> 
	<link rel="stylesheet" type="text/css" href="styles.css">
	<script type="text/javascript" src="scripts.js"></script>
	<title>Scales and Chords</title>
</head>
<body>
	<table>
		<?php
		$n = array('C', 'C#/Db', 'D', 'D#/Eb', 'E', 'F', 'F#/Gb', 'G', 'G#/Ab', 'A', 'A#/Bb', 'B');
		$s = array('Natural Major', 'Natural Minor', 'Harmonic Minor', 'Pentatonic Major', 'Pentatonic Minor', 'Blues Hexatonic Major', 'Blues Hexatonic Minor');
		?>
		<tr><td colspan="12">
			<select id="k"><?php for($i=0; $i<12; $i++) { echo '<option value="k'.$i.'">'.$n[$i].'</option>'; } ?></select>&emsp;<!--
			--><select id="s"><?php for($i=0, $l=count($s); $i<$l; $i++) { echo '<option value="s'.$i.'">'.$s[$i].'</option>'; } ?></select>
		</td></tr>
		<tr id="n">
		<?php
		for($i=0; $i<12; $i++) { echo '<td class="brdr cntr'.(strpos($n[$i], '#') ? ' shrp' : '').'"><label>'.$n[$i].'<br><input type="checkbox"></label></td>'; }
		?>
		</tr>
		<tr><td colspan="12" id="chrd"></td></tr>
	</table>
</body>
</html>
