<?php
/* This function has not been written by me, I have found it in one of ndn's posts on StackOverflow.
   http://stackoverflow.com/questions/34881790/split-string-into-sentences-using-regex */
function sentence_split($text) {
	$before_regexes = array('/(?:(?:[\'\"„][\.!?…][\'\"”]\s)|(?:[^\.]\s[A-Z]\.\s)|(?:\b(?:St|Gen|Hon|Vol|Prof|Dr|Mr|Ms|Mrs|[JS]r|Col|Maj|Brig|Sgt|Capt|Cmnd|Sen|Rev|Rep|Revd)\.\s)|(?:\b(?:St|Gen|Hon|Prof|Dr|Mr|Ms|Mrs|[JS]r|Col|Maj|Brig|Sgt|Capt|Cmnd|Sen|Rev|Rep|Revd)\.\s[A-Z]\.\s)|(?:\bApr\.\s)|(?:\bAug\.\s)|(?:\bBros\.\s)|(?:\bCo\.\s)|(?:\bCorp\.\s)|(?:\bDec\.\s)|(?:\bDist\.\s)|(?:\bFeb\.\s)|(?:\bInc\.\s)|(?:\bJan\.\s)|(?:\bJul\.\s)|(?:\bJun\.\s)|(?:\bMar\.\s)|(?:\bNov\.\s)|(?:\bOct\.\s)|(?:\bPh\.?D\.\s)|(?:\bSept?\.\s)|(?:\b\p{Lu}\.\p{Lu}\.\s)|(?:\b\p{Lu}\.\s\p{Lu}\.\s)|(?:\bcf\.\s)|(?:\be\.g\.\s)|(?:\besp\.\s)|(?:\bet\b\s\bal\.\s)|(?:\bvs\.\s)|(?:\p{Ps}[!?]+\p{Pe} ))\Z/mu', '/(?:(?:[\.\s]\p{L}{1,2}\.\s))\Z/mu', '/(?:(?:[\[\(]*\.\.\.[\]\)]* ))\Z/mu', '/(?:(?:\b(?:pp|[Vv]iz|i\.?\s*e|[Vvol]|[Rr]col|maj|Lt|[Ff]ig|[Ff]igs|[Vv]iz|[Vv]ols|[Aa]pprox|[Ii]ncl|Pres|[Dd]ept|min|max|[Gg]ovt|lb|ft|c\.?\s*f|vs)\.\s))\Z/mu', '/(?:(?:\b[Ee]tc\.\s))\Z/mu', '/(?:(?:[\.!?…]+\p{Pe} )|(?:[\[\(]*…[\]\)]* ))\Z/mu', '/(?:(?:\b\p{L}\.))\Z/mu', '/(?:(?:\b\p{L}\.\s))\Z/mu', '/(?:(?:\b[Ff]igs?\.\s)|(?:\b[nN]o\.\s))\Z/mu', '/(?:(?:[\"”\']\s*))\Z/mu', '/(?:(?:[\.!?…][\x{00BB}\x{2019}\x{201D}\x{203A}\"\'\p{Pe}\x{0002}]*\s)|(?:\r?\n))\Z/mu', '/(?:(?:[\.!?…][\'\"\x{00BB}\x{2019}\x{201D}\x{203A}\p{Pe}\x{0002}]*))\Z/mu', '/(?:(?:\s\p{L}[\.!?…]\s))\Z/mu');
	$after_regexes = array('/\A(?:)/mu', '/\A(?:[\p{N}\p{Ll}])/mu', '/\A(?:[^\p{Lu}])/mu', '/\A(?:[^\p{Lu}]|I)/mu', '/\A(?:[^p{Lu}])/mu', '/\A(?:\p{Ll})/mu', '/\A(?:\p{L}\.)/mu', '/\A(?:\p{L}\.\s)/mu', '/\A(?:\p{N})/mu', '/\A(?:\s*\p{Ll})/mu', '/\A(?:)/mu', '/\A(?:\p{Lu}[^\p{Lu}])/mu', '/\A(?:\p{Lu}\p{Ll})/mu');
	$is_sentence_boundary = array(false, false, false, false, false, false, false, false, false, false, true, true, true);
	$count = 13;
	$sentences = array();
	$sentence = '';
	$before = '';
	$after = substr($text, 0, 10);
	$text = substr($text, 10);
	while($text != '') {
		for($i = 0; $i < $count; $i++) {
			if(preg_match($before_regexes[$i], $before) && preg_match($after_regexes[$i], $after)) {
				if($is_sentence_boundary[$i]) { array_push($sentences, $sentence); $sentence = ''; }
				break;
			}
		}
		$first_from_text = $text[0];
		$text = substr($text, 1);
		$first_from_after = $after[0];
		$after = substr($after, 1);
		$before .= $first_from_after;
		$sentence .= $first_from_after;
		$after .= $first_from_text;
	}
	if($sentence != '' && $after != '') { array_push($sentences, $sentence.$after); }
	return $sentences;
}
?>
