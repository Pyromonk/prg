<?php
/* This is not my code. I've modified his:
   https://github.com/DaveChild/Text-Statistics/blob/master/src/DaveChild/TextStatistics */

/**
 * Returns the number of syllables in the word.
 * Based in part on Greg Fast's Perl module Lingua::EN::Syllables
 * @param string $strWord - Word to be measured
 * @return int
 */
function syllableCount($strWord) { //removed some checks and the encoding option because I have javascript dealing with most of it
	// Specific common exceptions that don't follow the rule set below are handled individually
	// Common reasons we need to override some words: trailing 'e' is pronounced; portmanteaus
	$arrProblemWords = array('abalone' => 4, 'abare' => 3, 'abed' => 2, 'abruzzese' => 4, 'abbruzzese' => 4, 'aborigine' => 5, 'acreage' => 3, 'adame' => 3, 'adieu' => 2, 'adobe' => 3, 'anemone' => 4, 'apache' => 3, 'aphrodite' => 4, 'apostrophe' => 4, 'ariadne' => 4, 'cafe' => 2, 'calliope' => 4, 'catastrophe' => 4, 'chile' => 2, 'chloe' => 2, 'circe' => 2, 'coyote' => 3, 'epitome' => 4, 'forever' => 3, 'gethsemane' => 4, 'guacamole' => 4, 'hyperbole' => 4, 'jesse' => 2, 'jukebox' => 2, 'karate' => 3, 'machete' => 3, 'maybe' => 2, 'people' => 2, 'recipe' => 3, 'sesame' => 3, 'shoreline' => 2, 'simile' => 3, 'syncope' => 3, 'tamale' => 3, 'yosemite' => 4, 'daphne' => 2, 'eurydice' => 4, 'euterpe' => 3,'hermione' => 4, 'penelope' => 4, 'persephone' => 4, 'phoebe' => 2, 'zoe' => 2);
	// These syllables would be counted as two but should be one
	$arrSubSyllables = array('cia(l|$)', 'tia', 'cius', 'cious', '[^aeiou]giu', '[aeiouy][^aeiouy]ion', 'iou', 'sia$', 'eous$', '[oa]gue$', '.[^aeiuoycgltdb]{2,}ed$', '.ely$', '^jua', 'uai', 'eau', '[aeiouy](b|c|ch|d|dg|f|g|gh|gn|k|l|ll|lv|m|mm|n|nc|ng|nn|p|r|rc|rn|rs|rv|s|sc|sk|sl|squ|ss|st|t|th|v|y|z)e$', '[aeiouy](b|c|ch|dg|f|g|gh|gn|k|l|lch|ll|lv|m|mm|n|nc|ng|nch|nn|p|r|rc|rn|rs|rv|s|sc|sk|sl|squ|ss|th|v|y|z)ed$', '[aeiouy](b|ch|d|f|gh|gn|k|l|lch|ll|lv|m|mm|n|nch|nn|p|r|rn|rs|rv|s|sc|sk|sl|squ|st|t|th|v|y)es$', '^busi$');
	// These syllables would be counted as one but should be two
	$arrAddSyllables = array('([^s]|^)ia', 'riet', 'dien', 'iu', 'io', 'eo($|[b-df-hj-np-tv-z])', 'ii', '[ou]a$', '[aeiouym]bl$', '[aeiou]{3}', '[aeiou]y[aeiou]', '^mc', 'ism$', 'asm$', 'thm$', '([^aeiouy])\1l$', '[^l]lien', '^coa[dglx].', '[^gq]ua[^auieo]', 'dnt$', 'uity$', '[^aeiouy]ie(r|st|t)$', 'eings?$', '[aeiouy]sh?e[rsd]$', 'iell', 'dea$', 'real', '[^aeiou]y[ae]', 'gean$', 'uen');
	// Single syllable prefixes and suffixes
	$arrAffix = array('`^un`', '`^fore`', '`^ware`', '`^none?`', '`^out`', '`^post`', '`^sub`', '`^pre`', '`^pro`', '`^dis`', '`^side`', '`ly$`', '`less$`', '`some$`', '`ful$`', '`ers?$`', '`ness$`', '`cians?$`', '`ments?$`', '`ettes?$`', '`villes?$`', '`ships?$`', '`sides?$`', '`ports?$`', '`shires?$`', '`tion(ed)?$`');
	// Double syllable prefixes and suffixes
	$arrDoubleAffix = array('`^above`', '`^ant[ie]`', '`^counter`', '`^hyper`', '`^afore`', '`^agri`', '`^in[ft]ra`', '`^inter`', '`^over`', '`^semi`', '`^ultra`', '`^under`', '`^extra`', '`^dia`', '`^micro`', '`^mega`', '`^kilo`', '`^pico`', '`^nano`', '`^macro`', '`berry$`', '`woman$`', '`women$`');
	// Triple syllable prefixes and suffixes
	$arrTripleAffix = array('`ology$`', '`ologist$`', '`onomy$`', '`onomist$`');

	// Trim whitespace
	$strWord = trim($strWord);
	if(!strlen($strWord)) { return 0; }
	// Check for problem words
	$singularWord = getSingular($strWord); // Try singular first
	if($singularWord != $strWord) { if(isset($arrProblemWords[$singularWord])) { return $arrProblemWords[$singularWord]; } }
	if(isset($arrProblemWords[$strWord])) { return $arrProblemWords[$strWord]; }
	// Remove prefixes and suffixes and count how many were taken
	$strWord = preg_replace($arrAffix, '', $strWord, -1, $intAffixCount);
	$strWord = preg_replace($arrDoubleAffix, '', $strWord, -1, $intDoubleAffixCount);
	$strWord = preg_replace($arrTripleAffix, '', $strWord, -1, $intTripleAffixCount);
	// Break the word into parts and count them
	$arrWordParts = preg_split('`[^aeiouy]+`', $strWord);
	$intWordPartCount = 0;
	foreach($arrWordParts as $strWordPart) { if($strWordPart<>'') { $intWordPartCount++; } }
	// Some syllables do not follow normal rules - check for them
	$intSyllableCount = $intWordPartCount+$intAffixCount+2*$intDoubleAffixCount+3*$intTripleAffixCount;
	foreach($arrSubSyllables as $strSyllable) { $intSyllableCount -= preg_match('`'.$strSyllable.'`', $strWord); }
	foreach($arrAddSyllables as $strSyllable) { $intSyllableCount += preg_match('`'.$strSyllable.'`', $strWord); }
	$intSyllableCount = $intSyllableCount==0 ? 1 : $intSyllableCount;
	return $intSyllableCount;
}

/**
 * Get the singular of the word passed in.
 * @param string $string - Word to singularise
 * @return string - Singularised word
 */
function getSingular($string) {
	$singular = array('/(quiz)zes$/i' => "$1", '/(matr)ices$/i' => "$1ix", '/(vert|ind)ices$/i' => "$1ex", '/^(ox)en$/i' => "$1", '/(alias)es$/i' => "$1", '/(octop|vir)i$/i' => "$1us", '/(cris|ax|test)es$/i' => "$1is", '/(shoe)s$/i' => "$1", '/(o)es$/i' => "$1", '/(bus)es$/i' => "$1", '/([m|l])ice$/i' => "$1ouse", '/(x|ch|ss|sh)es$/i' => "$1", '/(m)ovies$/i' => "$1ovie", '/(s)eries$/i' => "$1eries", '/([^aeiouy]|qu)ies$/i' => "$1y", '/([lr])ves$/i' => "$1f", '/(tive)s$/i' => "$1", '/(hive)s$/i' => "$1", '/(li|wi|kni)ves$/i' => "$1fe", '/(shea|loa|lea|thie)ves$/i' => "$1f", '/(^analy)ses$/i' => "$1sis", '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => "$1$2sis", '/([ti])a$/i' => "$1um", '/(n)ews$/i' => "$1ews", '/(h|bl)ouses$/i' => "$1ouse", '/(corpse)s$/i' => "$1", '/(us)es$/i' => "$1", '/s$/i' => "");
	$irregular = array('child' => 'children', 'foot' => 'feet', 'goose' => 'geese', 'man' => 'men', 'mouse' => 'mice', 'move' => 'moves', 'person' => 'people', 'sex' => 'sexes', 'tooth' => 'teeth');
	$uncountable = array('beef', 'bison', 'buffalo', 'carbon', 'chemistry', 'copper', 'geometry', 'gold', 'cs', 'css', 'deer', 'equipment', 'fish', 'furniture', 'information', 'mathematics', 'money', 'moose', 'nitrogen', 'oxygen', 'rice', 'series', 'sheep', 'species', 'surgery', 'traffic', 'water');

	// Save some time in case singular and plural are the same
	if(in_array(strtolower($string), $uncountable)) { return $string; }
	// Check to see if already singular and irregular
	foreach($irregular as $pattern => $result) {
		$_pattern = '/'.$pattern.'$/i';
		if(preg_match($_pattern, $string)) { return $string; }
	}
	// Check for irregular plural forms
	foreach($irregular as $result => $pattern) {
		$pattern = '/'.$pattern.'$/i';
		if(preg_match($pattern, $string)) { return preg_replace($pattern, $result, $string); }
	}
	// Check for matches using regular expressions
	foreach($singular as $pattern => $result) { if(preg_match($pattern, $string)) { return preg_replace($pattern, $result, $string); } }
	return $string;
}
?>
