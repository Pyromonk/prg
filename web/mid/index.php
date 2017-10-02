<?php
if(!session_id()) { session_start(); }
mt_srand(11235813*round(microtime(true)));
require_once 'midi-class.php';
$p=$_POST;
$save_dir = UPLOAD_DIR.'/sess_'.session_id().'/';
//clean up, remove folders belonging to expired sessions
$sessions = array();
if($handle = opendir(session_save_path())) { //get current sessions
	while(($f = readdir($handle)) !== false) { if($f!='.' && $f!='..') { $sessions[] = $f; } }
	closedir($handle);
}
if($handle = opendir(UPLOAD_DIR.'/')) {
	while(($dir = readdir($handle)) !== false) { //remove upload folders that don't have corresponding session folders
		$tmp = __DIR__.'/'.UPLOAD_DIR.'/'.$dir; //get full path to current directory
		if(is_dir($tmp) && $dir!='.' && $dir!='..' && !in_array($dir, $sessions)) { rm($tmp); }
	}
	closedir($handle);
}
if(!is_dir(UPLOAD_DIR)) { mkdir(UPLOAD_DIR); }
if(!is_dir($save_dir)) { mkdir($save_dir); }
if(isset($_FILES['upload'])) { //save upload
	try {
		if(!isset($_FILES['upload']['error']) || is_array($_FILES['upload']['error'])) { //undefined/multiple files/$_FILES corruption attack check
			throw new RuntimeException('Invalid parameters.');
		}
		switch($_FILES['upload']['error']) { //check $_FILES['upload']['error'] value
			case UPLOAD_ERR_OK: break;
			case UPLOAD_ERR_NO_FILE: throw new RuntimeException('No file sent.');
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE: throw new RuntimeException('Exceeded filesize limit ('.round(UPLOAD_MAX_SIZE/1048576, 2).' MB).');
			default: throw new RuntimeException('Unknown errors.');
		}
		if($_FILES['upload']['size'] > UPLOAD_MAX_SIZE) { //check filesize here
			throw new RuntimeException('Exceeded filesize limit.');
		}
		$finfo = new finfo(FILEINFO_MIME_TYPE); //check MIME type
		$ext = array_search($finfo->file($_FILES['upload']['tmp_name']), array('mid' => 'audio/midi'), true);
		if($ext === false) {
			throw new RuntimeException('Invalid file format.');
		}
		$file = $save_dir.md5(((String)mt_rand()).session_id().sha1_file($_FILES['upload']['tmp_name'])).'.'.$ext; //that's what I call overkill
		if(!move_uploaded_file($_FILES['upload']['tmp_name'], $file)) {
			throw new RuntimeException('Failed to upload file.');
		} else {
			@chmod($file, 0666);
			$result = 'File successfully uploaded.';
		}
	} catch(RuntimeException $e) {
		$result = $e->getMessage();
	}
} elseif(isset($p['file'])) {
	$file = $p['file'];
}

function rm($dir) { //remove a directory
	$handle = opendir($dir);
	while(($file = readdir($handle)) !== false) { if($file!='.' && $file!='..') { unlink("$dir/$file"); } }
	closedir($handle);
	rmdir($dir);
}
?>
<!DOCTYPE html>
<html lang="en-gb">
<head>
	<meta charset="utf-8">
	<meta name="description" content="У меня проблемы с фантазией">
	<meta name="keywords" content="Мизогиния, Катехон">
	<link rel="icon" href="../img/fav.ico">
	<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Merriweather:400,400i,700,700i&amp;subset=cyrillic,cyrillic-ext,latin-ext"> 
	<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i&amp;subset=cyrillic,cyrillic-ext,latin-ext">
	<link rel="stylesheet" type="text/css" href="styles.css">
	<script type="text/javascript" src="scripts.js"></script>
	<title>MIDI Manipulation</title>
</head>
<body>
	<div id="wrapper">
		<div id="container">
			<div id="input">
				<h3>Upload File</h3>
				<form enctype="multipart/form-data" method="POST">
					<input type="hidden" name="MAX_FILE_SIZE" value="<?=UPLOAD_MAX_SIZE;?>">
					<input type="file" name="upload" accept=".mid, .midi, .smf, audio/midi"><br>
					<input type="submit" value="Send">
				</form>
				<?php
				if(isset($file)) {
					$midi = new Midi();
					$midi->importMid($file);
					if(!isset($_FILES['upload'])) {
						if(isset($p['achk'])) {
							$asbc = isset($p['asbc']) ? 1 : 0;
							$asrc = isset($p['asrc']) ? 1 : 0;
							$amcc = isset($p['amcc']) ? 1 : 0;
							$abvc = isset($p['abvc']) ? 1 : 0;
							$midi->nonLinearise(basename($file, '.mid'), 1, $abvc, $asbc, $asrc, $amcc);
						}
						if(isset($p['tchk'])) {
							$tnoo = (int)$p['tnoo'];
							$sign = $tnoo>0 ? 1 : -1;
							$tnoo = abs($tnoo)>4 ? $sign*4 : $sign*abs($tnoo);
							$tnos = (int)$p['tnos'];
							$sign = $tnos>0 ? 1 : -1;
							$tnos = abs($tnos)>11 ? $sign*11 : $sign*abs($tnos);
							$tidr = isset($p['tidr']) ? 1 : 0;
							$midi->transpose(12*$tnoo+$tnos, $tidr);
						}
						if(isset($p['echk'])) {
							$edmn = (int)$p['edmn'];
							$edmn = $edmn<2 ? 2 : ($edmn>10 ? 10 : $edmn);
							if($p['edom']=='d') {
								$midi->setTempo($midi->getTempo()/$edmn);
							} elseif($p['edom']=='m') {
								$midi->setTempo($midi->getTempo()*$edmn);
							}
						}
						if(isset($p['dchk'])) {
							$dsnm = (int)$p['dsnm'];
							$dsnm = $dsnm<0 ? 0 : ($dsnm>2 ? 2 : $dsnm);
							$midi->normalise(1, $dsnm);
						}
						if(isset($p['nchk'])) {
							$nsnm = (int)$p['nsnm'];
							$nsnm = $nsnm<0 ? 0 : ($nsnm>4 ? 4 : $nsnm);
							$nkey = (int)$p['nkey'];
							$nkey = $nkey<0 ? 0 : ($nkey>11 ? 11 : $nkey);
							$midi->normalise(0, $nsnm, $nkey);
						}
						if(isset($p['ochk'])) {
							$omnn = (int)$p['omnn'];
							$omnn = $omnn<1 ? 1 : ($omnn>4 ? 4 : $omnn);
							$omxn = (int)$p['omxn'];
							$omxn = $omxn<6 ? 6 : ($omxn>9 ? 9 : $omxn);
							$midi->fitOctaves($omnn, $omxn);
						}
						$midi->saveMidFile($file);
					}
				?>
				<form method="POST" id="main_form">
					<h3>Modify File</h3>
					<input type="hidden" name="file" value="<?=isset($file) ? $file : '';?>">
					<fieldset>
						<legend>
							<input type="checkbox" name="achk" id="achk" onclick="javascript: disableInputs(this);"<?=isset($p['achk']) ? ' checked' : '';?>>&nbsp;<label for="achk">Modulation</label>
						</legend>
						Apply functions from the <abbr title="Advanced Encryption Standard">AES</abbr> algorithm to the file:<br>
						<label><input type="checkbox" name="asbc"<?=isset($p['asbc']) ? ' checked' : '';?><?=isset($p['achk']) ? '' : ' disabled';?>>&nbsp;subBytes;</label><br>
						<label><input type="checkbox" name="asrc"<?=isset($p['asrc']) ? ' checked' : '';?><?=isset($p['achk']) ? '' : ' disabled';?>>&nbsp;shiftRows;</label><br>
						<label><input type="checkbox" name="amcc"<?=isset($p['amcc']) ? ' checked' : '';?><?=isset($p['achk']) ? '' : ' disabled';?>>&nbsp;mixColumns;</label><br>
						<label><input type="checkbox" name="abvc"<?=isset($p['abvc']) ? ' checked' : '';?><?=isset($p['achk']) ? '' : ' disabled';?>>&nbsp;affect note velocities.</label>
					</fieldset>
					<fieldset>
						<legend>
							<input type="checkbox" name="tchk" id="tchk" onclick="javascript: disableInputs(this);"<?=isset($p['tchk']) ? ' checked' : '';?>>&nbsp;<label for="tchk">Transposition</label>
						</legend>
						Transpose the file by <label><input type="number" name="tnoo" value="<?=isset($p['tnoo']) ? $p['tnoo'] : -1;?>" min="-4" max="4"<?=isset($p['tchk']) ? '' : ' disabled';?>> octaves</label> and <label><input type="number" name="tnos" value="<?=isset($p['tnos']) ? $p['tnos'] : 0;?>" min="-11" max="11"<?=isset($p['tchk']) ? '' : ' disabled';?>> semitones</label>.
						<?php if($midi->gotDrums()) { ?>
						<br>
						<label><input type="checkbox" name="tidr" checked<?=isset($p['tchk']) ? '' : ' disabled';?>>&nbsp;Ignore drums.</label>
						<?php } ?>
					</fieldset>
					<fieldset>
						<legend>
							<input type="checkbox" name="echk" id="echk" onclick="javascript: disableInputs(this);"<?=isset($p['echk']) ? ' checked' : '';?>>&nbsp;<label for="echk">Tempo</label>
						</legend>
						<label><input type="radio" name="edom" value="d"<?=isset($p['edom']) ? ($p['edom']=='d' ? ' checked' : '') : ' checked';?><?=isset($p['echk']) ? '' : ' disabled';?>>Divide</label> or <label><input type="radio" name="edom" value="m"<?=isset($p['edom']) ? ($p['edom']=='m' ? ' checked' : '') : '';?><?=isset($p['echk']) ? '' : ' disabled';?>>multiply</label> tempo by <input type="number" name="edmn" value="<?=isset($p['edmn']) ? $p['edmn'] : 2;?>" min="2" max="10"<?=isset($p['echk']) ? '' : ' disabled';?>>.
					</fieldset>
					<?php if($midi->gotDrums()) { ?>
					<fieldset>
						<legend>
							<input type="checkbox" name="dchk" id="dchk" onclick="javascript: disableInputs(this);"<?=isset($p['dchk']) ? ' checked' : '';?>>&nbsp;<label for="dchk">Drum Normalisation</label>
						</legend>
						Snap to:<br>
						<label><input type="radio" name="dsnm" value="0"<?=isset($p['dsnm']) ? ((int)$p['dsnm']===0 ? ' checked' : '') : ' checked';?><?=isset($p['dchk']) ? '' : ' disabled';?>>35, 36, 37, 38, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 55, 57, 59;</label><br>
						<label><input type="radio" name="dsnm" value="1"<?=isset($p['dsnm']) ? ((int)$p['dsnm']===1 ? ' checked' : '') : '';?><?=isset($p['dchk']) ? '' : ' disabled';?>>35, 36, 38, 40, 42, 44, 45, 46, 47, 48, 49, 50, 51, 52, 55, 57, 59;</label><br>
						<label><input type="radio" name="dsnm" value="2"<?=isset($p['dsnm']) ? ((int)$p['dsnm']===2 ? ' checked' : '') : '';?><?=isset($p['dchk']) ? '' : ' disabled';?>>36, 40, 42, 44, 46, 47, 48, 49, 51, 52, 55.</label>
					</fieldset>
					<?php } ?>
					<?php if($midi->gotNotes()) { ?>
					<fieldset>
						<legend>
							<input type="checkbox" name="nchk" id="nchk" onclick="javascript: disableInputs(this);"<?=isset($p['nchk']) ? ' checked' : '';?>>&nbsp;<label for="nchk">Note Normalisation</label>
						</legend>
						<label>Scale: <select name="nsnm"<?=isset($p['nchk']) ? '' : ' disabled';?>>
							<option value="0"<?=isset($p['nsnm']) ? ((int)$p['nsnm']===0 ? ' selected' : '') : ' selected';?>>Natural Major</option>
							<option value="1"<?=isset($p['nsnm']) ? ((int)$p['nsnm']===1 ? ' selected' : '') : '';?>>Natural Minor</option>
							<option value="2"<?=isset($p['nsnm']) ? ((int)$p['nsnm']===2 ? ' selected' : '') : '';?>>Harmonic Minor</option>
							<option value="3"<?=isset($p['nsnm']) ? ((int)$p['nsnm']===3 ? ' selected' : '') : '';?>>Pentatonic Major</option>
							<option value="4"<?=isset($p['nsnm']) ? ((int)$p['nsnm']===4 ? ' selected' : '') : '';?>>Pentatonic Minor</option>
							<option value="5"<?=isset($p['nsnm']) ? ((int)$p['nsnm']===5 ? ' selected' : '') : '';?>>Blues Hexatonic Major</option>
							<option value="6"<?=isset($p['nsnm']) ? ((int)$p['nsnm']===6 ? ' selected' : '') : '';?>>Blues Hexatonic Minor</option>
						</select></label>,
						<label>key: <select name="nkey"<?=isset($p['nchk']) ? '' : ' disabled';?>>
							<option value="0"<?=isset($p['nkey']) ? ((int)$p['nkey']===0 ? ' selected' : '') : ' selected';?>>C</option>
							<option value="1"<?=isset($p['nkey']) ? ((int)$p['nkey']===1 ? ' selected' : '') : '';?>>C#/Db</option>
							<option value="2"<?=isset($p['nkey']) ? ((int)$p['nkey']===2 ? ' selected' : '') : '';?>>D</option>
							<option value="3"<?=isset($p['nkey']) ? ((int)$p['nkey']===3 ? ' selected' : '') : '';?>>D#/Eb</option>
							<option value="4"<?=isset($p['nkey']) ? ((int)$p['nkey']===4 ? ' selected' : '') : '';?>>E</option>
							<option value="5"<?=isset($p['nkey']) ? ((int)$p['nkey']===5 ? ' selected' : '') : '';?>>F</option>
							<option value="6"<?=isset($p['nkey']) ? ((int)$p['nkey']===6 ? ' selected' : '') : '';?>>F#/Gb</option>
							<option value="7"<?=isset($p['nkey']) ? ((int)$p['nkey']===7 ? ' selected' : '') : '';?>>G</option>
							<option value="8"<?=isset($p['nkey']) ? ((int)$p['nkey']===8 ? ' selected' : '') : '';?>>G#/Ab</option>
							<option value="9"<?=isset($p['nkey']) ? ((int)$p['nkey']===9 ? ' selected' : '') : '';?>>A</option>
							<option value="10"<?=isset($p['nkey']) ? ((int)$p['nkey']===10 ? ' selected' : '') : '';?>>A#/Bb</option>
							<option value="11"<?=isset($p['nkey']) ? ((int)$p['nkey']===11 ? ' selected' : '') : '';?>>B</option>
						</select></label>.
					</fieldset>
					<fieldset>
						<legend>
							<input type="checkbox" name="ochk" id="ochk" onclick="javascript: disableInputs(this);"<?=isset($p['ochk']) ? ' checked' : '';?>>&nbsp;<label for="ochk">Octave Range</label>
						</legend>
						Limit notes to octaves between <input type="number" name="omnn" value="<?=isset($p['omnn']) ? $p['omnn'] : 1;?>" min="1" max="4"<?=isset($p['ochk']) ? '' : ' disabled';?>> and <input type="number" name="omxn" value="<?=isset($p['omxn']) ? $p['omxn'] : 6;?>" min="6" max="9"<?=isset($p['ochk']) ? '' : ' disabled';?>> inclusive.
					</fieldset>
					<?php } ?>
					<input type="submit" value="Modify">
				</form>
				<?php
				}
				?>
				<span id="question" onclick="javascript: showHelp();">?</span>
			</div>
			<div id="output">
			<?php
			if(isset($result)) { echo '<h3>Upload Result</h3><p>'.$result.'<p>'; }
			if(isset($file)) { echo '<h3>File Contents</h3><p><a href="'.urlencode($file).'" download="'.urlencode(basename($file)).'" target="_blank">'.urlencode(basename($file)).'</a></p><p>'.nl2br(trim($midi->getTxt()), false).'</p>'; }
			?>
			</div>
		</div>
		<div id="help"><div>
			<h3>About This Tool</h3>
			<p>
				Most notation programs and digital audio workstations these days allow importing and exporting data in MIDI (SMF) format.<br>
				This tool is meant to help musicians and composers improvise and construct music more efficiently.<br>
				Only files with MIME type of audio/midi are supported (their extension is normally .mid, .midi or .smf), and their size cannot exceed <?=round(UPLOAD_MAX_SIZE/1048576, 2);?> MB.<br>
				Please note that some of the functionality outlined below is dependent on what type of tracks your file contains. For example, if your file contains no drum tracks, drum normalisation will be unavailable.
			</p>
			<h3>Modulation</h3>
			<p>
				This is the main component of this tool, it allows applying the full <abbr title="Advanced Encryption Standard">AES</abbr> algorithm (<abbr title="Counter">CTR</abbr> mode) or its components to notes and velocities of a file (note lengths are left unchanged). This, coupled with octave range and note normalisation, makes it possible to create a composition with the same rhythmical structure but a different pitch contour.
			</p>
			<h3>Transposition</h3>
			<p>
				This section allows to change the pitch of all the notes by a certain number of semitones. An octave contains 12 semitones. The file can be transposed by up to 4 octaves and 11 semitones, up or down. 
			</p>
			<p>
				Channel 10 is reserved for drum kits in MIDI. Drum kits operate differently compared to other instruments: they are, effectively, soundbanks and not pitch sequences, which is why by default drums are ignored when transposing the notes in a file.
			</p>
			<h3>Tempo</h3>
			<p>
				Tempo is the speed at which the MIDI file is played.
			</p>
			<h3>Drum Normalisation</h3>
			<p>
				As has been mentioned above, drums in MIDI are more of a soundbank than a sequence of pitches, so normalising a drum track will simply "round" every note within it to the nearest value from the 3 available sets. The reason for that is quite simple: the "notes" available in these sets represent the instruments you would find in an average drum kit (bass drums, tom-toms, hi-hats, cymbals, but not, for example, maracas).<br>
				Please refer to MIDI format specifications if you are not sure what each number stands for.
			</p>
			<h3>Note Normalisation</h3>
			<p>
				This section allows you to align the notes of a file with the notes of a particular scale in a certain key.
			</p>
			<h3>Octave Range</h3>
			<p>
				Notes in MIDI can take values from 0 (C0) to 127 (G10), however, a real-life instrument can normally only play a portion of that range. This section is useful for limiting notes to certain octaves while also preserving the pitch contour.
			</p>
		</div></div>
	</div>
</body>
</html>
