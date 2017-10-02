<?php
/* Based on MIDI Class v1.7.3 by Valentin Schmidt (fluxus@freenet.de).
   The original library along with examples can be found here: http://valentin.dasdeck.com/midi/
   MIDI messages information: http://www.mobilefish.com/tutorials/midi/midi_quickguide_specification.html */

require_once 'aes-class.php';

//-- Constants --//
define('UPLOAD_DIR', 'sav'); //uploads and sessions directory
define('UPLOAD_MAX_SIZE', 1048576); //maximum upload size in bytes, default - 1048576 (1 MB)

class Midi {

//-- Properties --//
protected $tracks; //array of tracks, where each track is array of message strings
protected $timebase; //timebase = ticks per frame (quarter note)
protected $tempo; //tempo as integer (0 for unknown)
protected $tempoMsgNum; //position of tempo event in track 0
protected $type; //MIDI file type (0, 1 or 2)
protected $rangeDrums; //minimum and maximum drum "notes" (used for transposition)
protected $rangeNotes; //minimum and maximum instrument notes (used for transposition)

//-- Public methods --//
function __construct() { //constructor
	$this->rangeDrums = array(min(array_keys($this->getDrumset())), max(array_keys($this->getDrumset())));
	$this->rangeNotes = array(min(array_keys($this->getNoteList())), max(array_keys($this->getNoteList())));
}

function open($timebase=480) { //creates (or resets to) new empty MIDI song
	$this->tempo = 0; //125000 = 120 bpm
	$this->timebase = $timebase;
	$this->tracks = array();
}

function setTempo($tempo) { //sets tempo by replacing set tempo msg in track 0 (or adding new track 0)
	$tempo = round($tempo);
	if(isset($this->tempoMsgNum)) {
		$this->tracks[0][$this->tempoMsgNum] = "0 Tempo $tempo";
	} else {
		$tempoTrack = array('0 TimeSig 4/4 24 8', "0 Tempo $tempo", '0 Meta TrkEnd');
		array_unshift($this->tracks, $tempoTrack);
		$this->tempoMsgNum = 1;
	}
	$this->tempo = $tempo;
}

function getTempo() { //returns tempo (0 if not set)
	return $this->tempo;
}

function setBpm($bpm) { //sets tempo corresponding to given bpm
	$tempo = round(60000000/$bpm);
	$this->setTempo($tempo);
}

function getBpm() { //returns bpm corresponding to tempo
	return ($this->tempo!=0) ? (int)(60000000/$this->tempo) : 0;
}

function setTimebase($tb) { //sets timebase
	$this->timebase = $tb;
}

function getTimebase() { //returns timebase
	return $this->timebase;
}

function newTrack() { //adds new track, returns new track count
	array_push($this->tracks, array());
	return count($this->tracks)-1;
}

function getTrack($tn) { //returns track $tn as array of msg strings
	return $this->tracks[$tn];
}

function getMsgCount($tn) { //returns number of messages of track $tn
	return count($this->tracks[$tn]);
}

function addMsg($tn, $msgStr, $ttype=0) { //adds $msgStr to end of track $tn, $ttype (time type) is either 0 (absolute) or 1 (delta)
	$track = $this->tracks[$tn];
	if($ttype==1){
		$last = $this->_getTime($track[count($track)-1]);
		$msg = explode(' ', $msgStr);
		$dt = (int)$msg[0];
		$msg[0] = $last + $dt;
		$msgStr = implode(' ', $msg);
	}
	$track[] = $msgStr;
	$this->tracks[$tn] = $track;
}

function insertMsg($tn, $msgStr) { //adds message at adequate position of track $n (slower than addMsg)
	$time = $this->_getTime($msgStr);
	$track = $this->tracks[$tn];
	$mc = count($track);
	for($i=0; $i<$mc; $i++){
		$t = $this->_getTime($track[$i]);
		if($t>=$time) { break; }
	}
	array_splice($this->tracks[$tn], $i, 0, $msgStr);
}

function getMsg($tn, $mn) { //returns message number $mn of track $tn
	return $this->tracks[$tn][$mn];
}

function deleteMsg($tn, $mn) { //deletes message number $mn of track $tn
	array_splice($this->tracks[$tn], $mn, 1);
}

function deleteTrack($tn) { //deletes track $tn
	array_splice($this->tracks, $tn, 1);
	return count($this->tracks);
}

function getTrackCount() { //returns number of tracks
	return count($this->tracks);
}

function soloTrack($tn) { //deletes all tracks except track $tn (and $track 0 which contains tempo info)
	if($tn==0) {
		$this->tracks = array($this->tracks[0]);
	} else {
		$this->tracks = array($this->tracks[0], $this->tracks[$tn]);
	}
}

function transpose($dn, $di) { //transposes song by $dn half tone steps, $di - ignore drums
	$tc = count($this->tracks);
	for($i=0; $i<$tc; $i++) { $this->transposeTrack($i, $dn, $di); }
}

function transposeTrack($tn, $dn, $di) { //transposes track $tn by $dn half tone steps, $di - ignore drums
	$track = $this->tracks[$tn];
	$mc = count($track);
	$notes = array();
	$idt = 0; //is this a drum track?
	$maxn = 127; //maximum note value allowed
	for($i=0; $i<$mc; $i++) {
		$msg = explode(' ', $track[$i]);
		if($msg[1]=='On' || $msg[1]=='Off') {
			$idt = $msg[2]=='ch=10' ? 1 : 0;
			if($di && $idt) {
				if($this->type) { break; }
				continue; //accounting for Type 0 files
			}
			$notes = $idt ? $this->rangeDrums : $this->rangeNotes;
			$maxn = $notes[1]-$notes[0];
			eval("\$".$msg[3].';'); //$n - note
			$n = ($n-$notes[0]+$dn)%($maxn+1);
			$n = $n<0 ? $maxn+1+$n : $n;
			$n += $notes[0];
			$msg[3] = "n=$n";
			$track[$i] = join(' ', $msg);
		}
	}
	$this->tracks[$tn] = $track;
}

function normalise($idt, $snm, $key=0) { //normalises song to fit a particular scale, $idt - is this a drum track, $snm - scale number, $key - tonic
	$tc = count($this->tracks);
	for($i=0; $i<$tc; $i++) { $this->normaliseTrack($i, $idt, $snm, $key); }
}

function normaliseTrack($tn, $idt, $snm, $key=0) { //normalises track $tn, $idt - is this a drum track, $snm - scale number, $key - tonic
	$track = $this->tracks[$tn];
	$mc = count($track);
	$scale = $this->_getNormalisedArray($idt, $snm, $key);
	for($i=0; $i<$mc; $i++) {
		$msg = explode(' ', $track[$i]);
		if($msg[1]=='On' || $msg[1]=='Off') {
			$chn = $msg[2]=='ch=10' ? 1 : 0;
			if(($idt && !$chn) || (!$idt && $chn)) {
				if($this->type) { break; }
				continue; //accounting for Type 0 files
			}
			eval("\$".$msg[3].';'); //$n - note
			$n = $scale[$n];
			$msg[3] = "n=$n";
			$track[$i] = join(' ', $msg);
		}
	}
	$this->tracks[$tn] = $track;
}

function nonLinearise($pw, $bn, $bv, $bsb, $bsr, $bmc) { //pushes the file through certain steps within the AES algorithm; $pw - password, $bn - affect notes, $bv - affect velocities, $bsb - turn on subBytes, $bsr - turn on shiftRows, $bmc - turn on mixColumns
	$tc = count($this->tracks);
	for($i=0; $i<$tc; $i++) { $this->nonLineariseTrack($i, $pw, $bn, $bv, $bsb, $bsr, $bmc); }
}

function nonLineariseTrack($tn, $pw, $bn, $bv, $bsb, $bsr, $bmc) { //pushes track $tn through certain steps within the AES algorithm; $bn - affect notes, $bv - affect velocities, $bsb - turn on subBytes, $bsr - turn on shiftRows, $bmc - turn on mixColumns
	$track = $this->tracks[$tn];
	$mc = count($track);
	$ons = array();
	$offs = array();
	$vels = array();
	$nfs = array();
	for($i=0; $i<$mc; $i++) {
		$msg = explode(' ', $track[$i]);
		if($msg[1]=='On' || $msg[1]=='Off') {
			eval("\$".$msg[2].';'); //$ch - channel, to account for Type 0 files
			eval("\$".$msg[3].';'); //$n - note
			eval("\$".$msg[4].';'); //$v - velocity
			if($bn) {
				if($msg[1]=='Off' || ($msg[1]=='On' && $v===0)) { //some synthesizers send the Off event as an On event with a velocity of 0
					$offs[$ch][] = _getBytes($n, 1); //pack('C', ...) can be used for this purpose, but this library already had _getBytes, so that's what I used
				} elseif($msg[1]=='On') {
					$ons[$ch][] = _getBytes($n, 1);
				}
			}
			if($bv) {
				if($v) { $vels[] = _getBytes($v, 1); } //best not to screw with the velocity of events that have a velocity of 0
			}
		}
	}
	if($bn) {
		foreach($ons as $channel => $notes) {
			$nfs[$channel] = array_merge(unpack('C*', Aes::encrypt(implode('', (count($notes)>=count($offs[$channel]) ? $notes : $offs[$channel])), $pw, 256, $bsb, $bsr, $bmc)));
			unset($ons[$channel]);
			$ons[$channel] = 0;
			unset($offs[$channel]);
			$offs[$channel] = 0;
		}
	}
	if($bv) {
		$vels = array_merge(unpack('C*', Aes::encrypt(implode('', $vels), $pw, 256, $bsb, $bsr, $bmc)));
	}
	for($i=0; $i<$mc; $i++) {
		$msg = explode(' ', $track[$i]);
		if($msg[1]=='On' || $msg[1]=='Off') {
			eval("\$".$msg[2].';'); //$ch - channel, to account for Type 0 files
			eval("\$".$msg[3].';'); //$n - note
			eval("\$".$msg[4].';'); //$v - velocity
			if($bn) {
				if($msg[1]=='Off' || ($msg[1]=='On' && $v===0)) { //some synthesizers send the Off event as an On event with a velocity of 0
					$n = $nfs[$ch][$offs[$ch]];
					$offs[$ch] += 1;
				} elseif($msg[1]=='On') {
					$n = $nfs[$ch][$ons[$ch]];
					$ons[$ch] += 1;
				}
				$notes = $ch==10 ? $this->rangeDrums : $this->rangeNotes;
				$maxn = $notes[1]-$notes[0];
				$n = ($n-$notes[0])%($maxn+1);
				$n = $n<0 ? $maxn+1+$n : $n;
				$n += $notes[0];
				$msg[3] = "n=$n";
			}
			if($bv) {
				if($v) { //best not to screw with the velocity of events that have a velocity of 0
					$v = $vels[$i];
					$v %= 128;
					$v = $v===0 ? 1 : $v;
				}
				$msg[4] = "v=$v";
			}
			$track[$i] = join(' ', $msg);
		}
	}
	$this->tracks[$tn] = $track;
}

function fitOctaves($mno, $mxo) { //fit notes between octaves $mno and $mxo while preserving the pitch contour
	$tc = count($this->tracks);
	for($i=0; $i<$tc; $i++) { $this->fitOctavesTrack($i, $mno, $mxo); }
}

function fitOctavesTrack($tn, $mno, $mxo) { //fit notes between octaves $mno and $mxo while preserving the pitch contour for track number $tn
	$track = $this->tracks[$tn];
	$mc = count($track);
	$foct = 0; //first octave index
	$loct = 10; //last octave index
	$octaves = range($foct, $loct);
	foreach($octaves as &$octave) {
		if($octave<$mno) {
			$delta = $octave-$foct;
			$octave = $mno+$delta;
			if($octave>$mxo) { $octave = $mxo; }
		} elseif($octave>$mxo) {
			$delta = $loct-$octave;
			$octave = $mxo-$delta;
			if($octave<$mno) { $octave = $mno; }
		}
	}
	unset($octave);
	for($i=0; $i<$mc; $i++) {
		$msg = explode(' ', $track[$i]);
		if($msg[1]=='On' || $msg[1]=='Off') {
			if($msg[2]=='ch=10') {
				if($this->type) { break; }
				continue; //accounting for Type 0 files
			}
			eval("\$".$msg[3].';'); //$n - note
			$o = (int)($n/12); //octave number
			$s = $n%12; //how many semitones away from the tonic
			$n = 12*$octaves[$o]+$s;
			$msg[3] = "n=$n";
			$track[$i] = join(' ', $msg);
		}
	}
	$this->tracks[$tn] = $track;
}

function importTxt($txt) { //import whole MIDI song as text (mf2t-format)
	$txt = trim($txt);
	$txt = preg_replace('%(*BSR_ANYCRLF)\R%', "\n", $txt);
	$txt .= "\n";
	$headerStr = strtok($txt, "\n");
	$header = explode(' ', $headerStr); //"MFile $type $tc $timebase"
	$this->type = $header[1];
	$this->timebase = $header[3];
	$this->tempo = 0;
	$trackStrings = explode("MTrk\n", $txt);
	array_shift($trackStrings);
	$tracks = array();
	foreach($trackStrings as $trackStr) {
		$track = explode("\n", $trackStr);
		array_pop($track);
		array_pop($track);
		if($track[0]=='TimestampType=Delta') { //delta
			array_shift($track);
			$track = _delta2Absolute($track);
		}
		$tracks[] = $track;
	}
	$this->tracks = $tracks;
	$this->_findTempo();
}

function importTrackTxt($txt, $tn) { //imports track as text (mf2t-format)
	$txt = trim($txt);
	$txt = preg_replace('%(*BSR_ANYCRLF)\R%', "\n", $txt);
	$track = explode("\n", $txt);
	if($track[0]=='MTrk') { array_shift($track); }
	if($track[count($track)-1]=='TrkEnd') { array_pop($track); }
	if($track[0]=='TimestampType=Delta') { //delta
		array_shift($track);
		$track = _delta2Absolute($track);
	}
	$tn = isset($tn) ? $tn : count($this->tracks);
	$this->tracks[$tn] = $track;
	if($tn==0) { $this->_findTempo(); }
}

function getTxt($ttype=0) { //returns MIDI song as text, $ttype (time type) is either 0 (absolute) or 1 (delta)
	$timebase = $this->timebase;
	$tracks = $this->tracks;
	$tc = count($tracks);
	$type = $tc>1 ? 1 : 0;
	$str = "MFile $type $tc $timebase\n";
	for($i=0; $i<$tc; $i++) { $str .= $this->getTrackTxt($i, $ttype); }
	return $str;
}

function getTrackTxt($tn, $ttype=0) { //returns track as text, $ttype (time type) is either 0 (absolute) or 1 (delta)
	$track = $this->tracks[$tn];
	$str = "MTrk\n";
	if($ttype==1) { //time as delta
		$str .= "TimestampType=Delta\n";
		$last = 0;
		foreach($track as $msgStr) {
			$msg = explode(' ', $msgStr);
			$t = (int)$msg[0];
			$msg[0] = $t - $last;
			$str .= implode(' ',$msg)."\n";
			$last = $t;
		}
	} else {
		foreach($track as $msg) { $str .= $msg."\n"; }
	}
	$str .= "TrkEnd\n";
	return $str;
}

function gotDrums() { //returns true if file has at least 1 drum track, false otherwise or if an error occurred
	return preg_match('% ch=10 n=%mi', $this->getTxt());
}

function gotNotes() { //returns true if file has at least 1 non-drum track, false otherwise or if an error occurred
	return preg_match('% ch=(?!10)\d+ n=%mi', $this->getTxt());
}

function importMid($smf_path) { //imports Standard MIDI File (type 0 or 1 [and RMID]); if optional parameter $tn set, only track $tn is imported
	$SMF = fopen($smf_path, 'rb'); //standard MIDI File, type 0 or 1
	$song = fread($SMF, filesize($smf_path));
	fclose($SMF);
	if(strpos($song, 'MThd')>0) { $song = substr($song, strpos($song, 'MThd')); } //get rid of RMID header
	$header = substr($song, 0, 14);
	if(substr($header,0,8)!="MThd\0\0\0\6") { _err('Wrong MIDI-header.'); }
	$type = ord($header[9]);
	if($type>1) { _err('Only SMF Type 0 and 1 are supported.'); }
	$timebase = ord($header[12])*256 + ord($header[13]);
	$this->type = $type;
	$this->timebase = $timebase;
	$this->tempo = 0; //maybe (hopefully!) overwritten by _parseTrack
	$trackStrings = explode('MTrk', $song);
	array_shift($trackStrings);
	$tracks = array();
	$tsc = count($trackStrings);
	if(func_num_args()>1) {
		$tn = func_get_arg(1);
		if($tn>=$tsc) { _err('SMF has less tracks than $tn.'); }
		$tracks[] = $this->_parseTrack($trackStrings[$tn], $tn);
	} else {
		for($i=0; $i<$tsc; $i++) { $tracks[] = $this->_parseTrack($trackStrings[$i], $i); }
	}
	$this->tracks = $tracks;
}

function getMid() { //returns binary MIDI string
	$tracks = $this->tracks;
	$tc = count($tracks);
	$type = $tc>1 ? 1 : 0;
	$midStr = "MThd\0\0\0\6\0".chr($type)._getBytes($tc, 2)._getBytes($this->timebase, 2);
	for($i=0; $i<$tc; $i++) {
		$track = $tracks[$i];
		$mc = count($track);
		$time = 0;
		$midStr .= "MTrk";
		$trackStart = strlen($midStr);
		$last = '';
		for($j=0; $j<$mc; $j++) {
			$line = $track[$j];
			$t = $this->_getTime($line);
			$dt = $t - $time;
			$time = $t;
			$midStr .= _writeVarLen($dt);
			//repetition, same event, same channel, omit first byte (smaller file size)
			$str = $this->_getMsgStr($line);
			$start = ord($str[0]);
			if($start>=0x80 && $start<=0xEF && $start==$last) { $str = substr($str, 1); }
			$last = $start;
			$midStr .= $str;
		}
		$trackLen = strlen($midStr) - $trackStart;
		$midStr = substr($midStr, 0, $trackStart)._getBytes($trackLen, 4).substr($midStr, $trackStart);
	}
	return $midStr;
}

function saveMidFile($mid_path) { //saves MIDI song as Standard MIDI File
	if(count($this->tracks)<1) { _err('This MIDI song has no tracks.'); }
	$SMF = fopen($mid_path, 'wb'); //SMF
	fwrite($SMF, $this->getMid());
	fclose($SMF);
}

function downloadMidFile($output, $file=false) { //starts download of Standard MIDI File, either from memory or from the server's filesystem
	ob_start('ob_gzhandler'); //for compressed output
	$mime_type = 'application/octetstream'; //force download
	header('Content-Type: '.$mime_type);
	header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Content-Disposition: attachment; filename="'.$output.'"');
	header('Pragma: no-cache');
	if($file) {
		$d=fopen($file, 'rb');
		fpassthru($d);
		@fclose($d);
	} else {
		echo $this->getMid();
	}
	exit();
}

//-- Public utilities --//
function getInstrumentList() { //returns a list of standard instrument names
	return array('Piano', 'Bright Piano', 'Electric Grand', 'Honky Tonk Piano', 'Electric Piano 1', 'Electric Piano 2', 'Harpsichord', 'Clavinet', 'Celesta', 'Glockenspiel', 'Music Box', 'Vibraphone', 'Marimba', 'Xylophone', 'Tubular Bell', 'Dulcimer', 'Hammond Organ', 'Perc Organ', 'Rock Organ', 'Church Organ', 'Reed Organ', 'Accordion', 'Harmonica', 'Tango Accordion', 'Nylon Str Guitar', 'Steel String Guitar', 'Jazz Electric Gtr', 'Clean Guitar', 'Muted Guitar', 'Overdrive Guitar', 'Distortion Guitar', 'Guitar Harmonics', 'Acoustic Bass', 'Fingered Bass', 'Picked Bass', 'Fretless Bass', 'Slap Bass 1', 'Slap Bass 2', 'Syn Bass 1', 'Syn Bass 2', 'Violin', 'Viola', 'Cello', 'Contrabass', 'Tremolo Strings', 'Pizzicato Strings', 'Orchestral Harp', 'Timpani', 'Ensemble Strings', 'Slow Strings', 'Synth Strings 1', 'Synth Strings 2', 'Choir Aahs', 'Voice Oohs', 'Syn Choir', 'Orchestra Hit', 'Trumpet', 'Trombone', 'Tuba', 'Muted Trumpet', 'French Horn', 'Brass Ensemble', 'Syn Brass 1', 'Syn Brass 2', 'Soprano Sax', 'Alto Sax', 'Tenor Sax', 'Baritone Sax', 'Oboe', 'English Horn', 'Bassoon', 'Clarinet', 'Piccolo', 'Flute', 'Recorder', 'Pan Flute', 'Bottle Blow', 'Shakuhachi', 'Whistle', 'Ocarina', 'Syn Square Wave', 'Syn Saw Wave', 'Syn Calliope', 'Syn Chiff', 'Syn Charang', 'Syn Voice', 'Syn Fifths Saw', 'Syn Brass and Lead', 'Fantasia', 'Warm Pad', 'Polysynth', 'Space Vox', 'Bowed Glass', 'Metal Pad', 'Halo Pad', 'Sweep Pad', 'Ice Rain', 'Soundtrack', 'Crystal', 'Atmosphere', 'Brightness', 'Goblins', 'Echo Drops', 'Sci Fi', 'Sitar', 'Banjo', 'Shamisen', 'Koto', 'Kalimba', 'Bag Pipe', 'Fiddle', 'Shanai', 'Tinkle Bell', 'Agogo', 'Steel Drums', 'Woodblock', 'Taiko Drum', 'Melodic Tom', 'Syn Drum', 'Reverse Cymbal', 'Guitar Fret Noise', 'Breath Noise', 'Seashore', 'Bird', 'Telephone', 'Helicopter', 'Applause', 'Gunshot');
}

function getDrumset() { //returns a list of drum kit "notes"
	return array(
	27=>'High Q',
	28=>'Slap',
	29=>'Scratch Push',
	30=>'Scratch Pull',
	31=>'Sticks',
	32=>'Square Click',
	33=>'Metronome Click',
	34=>'Metronome Bell',
	35=>'Bass Drum 1',
	36=>'Bass Drum 2',
	37=>'Side Stick',
	38=>'Acoustic Snare',
	39=>'Hand Clap',
	40=>'Electric Snare',
	41=>'Low Floor Tom',
	42=>'Closed Hi-Hat',
	43=>'High Floor Tom',
	44=>'Pedal Hi-Hat',
	45=>'Low Tom',
	46=>'Open Hi-Hat',
	47=>'Low Mid Tom',
	48=>'High Mid Tom',
	49=>'Crash Cymbal 1',
	50=>'High Tom',
	51=>'Ride Cymbal 1',
	52=>'Chinese Cymbal',
	53=>'Ride Bell',
	54=>'Tambourine',
	55=>'Splash Cymbal',
	56=>'Cowbell',
	57=>'Crash Cymbal 2',
	58=>'Vibraslap',
	59=>'Ride Cymbal 2',
	60=>'High Bongo',
	61=>'Low Bongo',
	62=>'Mute High Conga',
	63=>'Open High Conga',
	64=>'Low Conga',
	65=>'High Timbale',
	66=>'Low Timbale',
	67=>'High Agogo',
	68=>'Low Agogo',
	69=>'Cabase',
	70=>'Maracas',
	71=>'Short Whistle',
	72=>'Long Whistle',
	73=>'Short Guiro',
	74=>'Long Guiro',
	75=>'Claves',
	76=>'High Wood Block',
	77=>'Low Wood Block',
	78=>'Mute Cuica',
	79=>'Open Cuica',
	80=>'Mute Triangle',
	81=>'Open Triangle',
	82=>'Shaker',
	83=>'Jingle Bell',
	84=>'Bell Tree',
	85=>'Castanets',
	86=>'Mute Surdo',
	87=>'Open Surdo'
	);
}

function getDrumkitList() { //returns a list of standard drum kit names
	return array(
	1   => 'Dry',
	9   => 'Room',
	19  => 'Power',
	25  => 'Electronic',
	33  => 'Jazz',
	41  => 'Brush',
	57  => 'SFX',
	128 => 'Default'
	);
}

function getNoteList() { //returns a list of note names
	//note 69 (A5) = A440, note 60 (C5) = Middle C
	return array(
	//Do          Re           Mi    Fa           Sol          La           Ti
	'C0', 'Cs0', 'D0', 'Ds0', 'E0', 'F0', 'Fs0', 'G0', 'Gs0', 'A0', 'As0', 'B0',
	'C1', 'Cs1', 'D1', 'Ds1', 'E1', 'F1', 'Fs1', 'G1', 'Gs1', 'A1', 'As1', 'B1',
	'C2', 'Cs2', 'D2', 'Ds2', 'E2', 'F2', 'Fs2', 'G2', 'Gs2', 'A2', 'As2', 'B2',
	'C3', 'Cs3', 'D3', 'Ds3', 'E3', 'F3', 'Fs3', 'G3', 'Gs3', 'A3', 'As3', 'B3',
	'C4', 'Cs4', 'D4', 'Ds4', 'E4', 'F4', 'Fs4', 'G4', 'Gs4', 'A4', 'As4', 'B4',
	'C5', 'Cs5', 'D5', 'Ds5', 'E5', 'F5', 'Fs5', 'G5', 'Gs5', 'A5', 'As5', 'B5',
	'C6', 'Cs6', 'D6', 'Ds6', 'E6', 'F6', 'Fs6', 'G6', 'Gs6', 'A6', 'As6', 'B6',
	'C7', 'Cs7', 'D7', 'Ds7', 'E7', 'F7', 'Fs7', 'G7', 'Gs7', 'A7', 'As7', 'B7',
	'C8', 'Cs8', 'D8', 'Ds8', 'E8', 'F8', 'Fs8', 'G8', 'Gs8', 'A8', 'As8', 'B8',
	'C9', 'Cs9', 'D9', 'Ds9', 'E9', 'F9', 'Fs9', 'G9', 'Gs9', 'A9', 'As9', 'B9',
	'C10','Cs10','D10','Ds10','E10','F10','Fs10','G10'
	);
}

//-- Private methods --//
function _getTime($msgStr) { //returns time code of message string
	return (int)strtok($msgStr, ' ');
}

function _getMsgStr($line) { //returns binary code for message string
	$msg = explode(' ', $line);
	switch($msg[1]) {
		case 'PrCh': //0x0C
			eval("\$".$msg[2].';'); //chan
			eval("\$".$msg[3].';'); //prog
			return chr(0xC0+$ch-1).chr($p);
			break;
		case 'On': //0x09
			eval("\$".$msg[2].';'); //chan
			eval("\$".$msg[3].';'); //note
			eval("\$".$msg[4].';'); //vel
			return chr(0x90+$ch-1).chr($n).chr($v);
			break;
		case 'Off': //0x08
			eval("\$".$msg[2].';'); //chan
			eval("\$".$msg[3].';'); //note
			eval("\$".$msg[4].';'); //vel
			return chr(0x80+$ch-1).chr($n).chr($v);
			break;
		case 'PoPr': //0x0A = PolyPressure
			eval("\$".$msg[2].';'); //chan
			eval("\$".$msg[3].';'); //note
			eval("\$".$msg[4].';'); //val
			return chr(0xA0+$ch-1).chr($n).chr($v);
			break;
		case 'Par': //0x0B = ControllerChange
			eval("\$".$msg[2].';'); //chan
			eval("\$".$msg[3].';'); //controller
			eval("\$".$msg[4].';'); //val
			return chr(0xB0+$ch-1).chr($c).chr($v);
			break;
		case 'ChPr': //0x0D = ChannelPressure
			eval("\$".$msg[2].';'); //chan
			eval("\$".$msg[3].';'); //val
			return chr(0xD0+$ch-1).chr($v);
			break;
		case 'Pb': //0x0E = PitchBend
			eval("\$".$msg[2].';'); //chan
			eval("\$".$msg[3].';'); //val (2 Bytes!)
			$a = $v & 0x7f; //Bits 0..6
			$b = ($v >> 7) & 0x7f; //Bits 7..13
			return chr(0xE0+$ch-1).chr($a).chr($b);
			break;
		//Meta events
		case 'Seqnr': //0x00 = SequenceNumber; this event bears no meaning for type 0 and 1 MIDI files
			$num = chr($msg[2]);
			if($msg[2]>255) { _err("Code broken around SequenceNumber event."); }
			return "\xFF\x00\x02\x00$num";
			break;
		case 'Meta':
			$type = $msg[2];
			switch($type){
				case 'Text': //0x01: Meta Text
				case 'Copyright': //0x02: Meta Copyright
				case 'TrkName': //0x03: Meta TrackName (SeqName?)
				case 'InstrName': //0x04: Meta InstrumentName
				case 'Lyric': //0x05: Meta Lyrics
				case 'Marker': //0x06: Meta Marker; for a format 1 MIDI file, Marker Meta events should only occur within the first MTrk chunk
				case 'Cue': //0x07: Meta Cue
					$texttypes = array('Text', 'Copyright', 'TrkName', 'InstrName', 'Lyric', 'Marker', 'Cue');
					$byte = chr(array_search($type, $texttypes)+1);
					$start = strpos($line, '"')+1;
					$end = strrpos($line, '"');
					$txt = substr($line, $start, $end-$start);
					//To do: $len could also be more than one byte (variable length, see Sequence/Track name specification)
					$len = chr(strlen($txt));
					if($len>127) { _err('This class does not support variable-length Meta Cue events yet.'); }
					return "\xFF$byte$len$txt";
					break;
				case 'TrkEnd': //0x2F
					return "\xFF\x2F\x00";
					break;
				case '0x20': //0x20 = ChannelPrefix
					$v = chr($msg[3]);
					return "\xFF\x20\x01$v";
					break;
				case '0x21': //0x21 = ChannelPrefixOrPort
					$v = chr($msg[3]);
					return "\xFF\x21\x01$v";
					break;
				default:
					_err("Unknown Meta event: $type.");
					exit();
			}
			break;
		case 'Tempo': //0x51
			$tempo = _getBytes((int)$msg[2], 3);
			return "\xFF\x51\x03$tempo";
			break;
		case 'SMPTE': //0x54 = SMPTE offset
			$h = chr($msg[2]);
			$m = chr($msg[3]);
			$s = chr($msg[4]);
			$f = chr($msg[5]);
			$fh = chr($msg[6]);
			return "\xFF\x54\x05$h$m$s$f$fh";
			break;
		case 'TimeSig': //0x58
			$zt = explode('/', $msg[2]);
			$z = chr($zt[0]);
			$t = chr(log($zt[1])/log(2));
			$mc = chr($msg[3]);
			$c = chr($msg[4]);
			return "\xFF\x58\x04$z$t$mc$c";
			break;
		case 'KeySig': //0x59
			$vz = chr($msg[2]);
			$g = chr(($msg[3]=='major') ? 0 : 1);
			return "\xFF\x59\x02$vz$g";
			break;
		case 'SeqSpec': //0x7F = Sequencer-specific data (for example, 0 SeqSpec 00 00 41)
			$cnt = count($msg)-2;
			$data = '';
			for($i=0; $i<$cnt; $i++) { $data.=_hex2bin($msg[$i+2]); }
			//To do: $len can be more than one byte
			$len = chr(strlen($data));
			if($len>127) { _err('This class does not support variable-length sequencer-specific data yet.'); }
			return "\xFF\x7F$len$data";
			break;
		case 'SysEx': //0xF0 = SysEx
			$start = strpos($line, 'f0');
			$end = strrpos($line, 'f7');
			$data = substr($line, $start+3, $end-$start-1);
			$data = _hex2bin(str_replace(' ', '', $data));
			$len = chr(strlen($data));
			return "\xF0$len".$data;
			break;
		default:
			_err('Unknown event: '.$msg[1]);
			exit();
	}
}

function _parseTrack($binStr, $tn) { //converts binary track string to track (list of msg strings)
	$trackLen = strlen($binStr);
	$p = 4;
	$time = 0;
	$track = array();
	while($p<$trackLen) {
		//timedelta
		$dt = _readVarLen($binStr, $p);
		$time += $dt;
		$byte = ord($binStr[$p]);
		$high = $byte >> 4;
		$low = $byte - $high*16;
		switch($high) {
			case 0x0C: //PrCh = ProgramChange
				$chan = $low+1;
				$prog = ord($binStr[$p+1]);
				$last = 'PrCh';
				$track[] = "$time PrCh ch=$chan p=$prog";
				$p+=2;
				break;
			case 0x09: //On
				$chan = $low+1;
				$note = ord($binStr[$p+1]);
				$vel = ord($binStr[$p+2]);
				$last = 'On';
				$track[] = "$time On ch=$chan n=$note v=$vel";
				$p+=3;
				break;
			case 0x08: //Off
				$chan = $low+1;
				$note = ord($binStr[$p+1]);
				$vel = ord($binStr[$p+2]);
				$last = 'Off';
				$track[] = "$time Off ch=$chan n=$note v=$vel";
				$p+=3;
				break;
			case 0x0A: //PoPr = PolyPressure
				$chan = $low+1;
				$note = ord($binStr[$p+1]);
				$val = ord($binStr[$p+2]);
				$last = 'PoPr';
				$track[] = "$time PoPr ch=$chan n=$note v=$val";
				$p+=3;
				break;
			case 0x0B: //Par = ControllerChange
				$chan = $low+1;
				$c = ord($binStr[$p+1]);
				$val = ord($binStr[$p+2]);
				$last = 'Par';
				$track[] = "$time Par ch=$chan c=$c v=$val";
				$p+=3;
				break;
			case 0x0D: //ChPr = ChannelPressure
				$chan = $low+1;
				$val = ord($binStr[$p+1]);
				$last = 'ChPr';
				$track[] = "$time ChPr ch=$chan v=$val";
				$p+=2;
				break;
			case 0x0E: //Pb = PitchBend
				$chan = $low+1;
				$val = (ord($binStr[$p+1]) & 0x7F) | (((ord($binStr[$p+2])) & 0x7F) << 7);
				$last = 'Pb';
				$track[] = "$time Pb ch=$chan v=$val";
				$p+=3;
				break;
			default:
				switch($byte) {
					case 0xFF: //Meta
						$meta = ord($binStr[$p+1]);
						switch($meta) {
							case 0x00: //sequence_number
								$tmp = ord($binStr[$p+2]);
								if($tmp==0x00) {
									$num = $tn;
									$p+=3;
								} else {
									$num= 1;
									$p+=5;
								}
								$track[] = "$time Seqnr $num";
								break;
							case 0x01: //Meta Text
							case 0x02: //Meta Copyright
							case 0x03: //Meta TrackName (SequenceName?)
							case 0x04: //Meta InstrumentName
							case 0x05: //Meta Lyrics
							case 0x06: //Meta Marker
							case 0x07: //Meta Cue
								$texttypes = array('Text','Copyright','TrkName','InstrName','Lyric','Marker','Cue');
								$type = $texttypes[$meta-1];
								$p+=2;
								$len = _readVarLen($binStr, $p);
								if(($len+$p) > $trackLen) {
									_err("Meta $type has a corrupt variable length field ($len) [track: $tn dt: $dt].");
								}
								$txt = substr($binStr, $p, $len);
								$track[] = "$time Meta $type \"$txt\"";
								$p+=$len;
								break;
							case 0x20: //ChannelPrefix
								$chan = ord($binStr[$p+3]);
								if($chan<10) { $chan = '0'.$chan; } //pad channel number by a 0 if it's less than 10
								$track[] = "$time Meta 0x20 $chan";
								$p+=4;
								break;
							case 0x21: //ChannelPrefixOrPort
								$chan = ord($binStr[$p+3]);
								if($chan<10) { $chan = '0'.$chan; } //pad channel number by a 0 if it's less than 10
								$track[] = "$time Meta 0x21 $chan";
								$p+=4;
								break;
							case 0x2F: //Meta TrkEnd
								$track[] = "$time Meta TrkEnd";
								return $track; //ignore rest
								break;
							case 0x51: //Tempo
								$tempo = ord($binStr[$p+3])*256*256 + ord($binStr[$p+4])*256 + ord($binStr[$p+5]);
								$track[] = "$time Tempo $tempo";
								if($tn==0 && $time==0) {
									$this->tempo = $tempo;
									$this->tempoMsgNum = count($track)-1;
								}
								$p+=6;
								break;
							case 0x54: //SMPTE offset
								$h = ord($binStr[$p+3]);
								$m = ord($binStr[$p+4]);
								$s = ord($binStr[$p+5]);
								$f = ord($binStr[$p+6]);
								$fh = ord($binStr[$p+7]);
								$track[] = "$time SMPTE $h $m $s $f $fh";
								$p+=8;
								break;
							case 0x58: //TimeSig
								$z = ord($binStr[$p+3]);
								$t = pow(2,ord($binStr[$p+4]));
								$mc = ord($binStr[$p+5]);
								$c = ord($binStr[$p+6]);
								$track[] = "$time TimeSig $z/$t $mc $c";
								$p+=7;
								break;
							case 0x59: //KeySig
								$vz = ord($binStr[$p+3]);
								$g = ord($binStr[$p+4])==0 ? 'major' : 'minor';
								$track[] = "$time KeySig $vz $g";
								$p+=5;
								break;
							case 0x7F: //Sequencer specific data (string or hexString?)
								$p+=2;
								$len = _readVarLen($binStr, $p);
								if(($len+$p) > $trackLen) {
									_err("SeqSpec has a corrupt variable length field ($len) [track: $tn dt: $dt].");
								}
								$p-=3;
								$data='';
								for($i=0; $i<$len; $i++) { $data .= ' '.sprintf("%02x", ord($binStr[$p+3+$i])); }
								$track[] = "$time SeqSpec$data";
								$p+=$len+3;
								break;
							default:
								$metacode = sprintf("%02x", ord($binStr[$p+1]));
								$p+=2;
								$len = _readVarLen($binStr, $p);
								if(($len+$p) > $trackLen) {
									_err("Meta $metacode has a corrupt variable length field ($len) [track: $tn dt: $dt].");
								}
								$p-=3;
								$data='';
								for($i=0; $i<$len; $i++) { $data .= ' '.sprintf("%02x", ord($binStr[$p+3+$i])); }
								$track[] = "$time Meta 0x$metacode $data";
								$p+=$len+3;
								break;
						} //switch ($meta)
						break; //end Meta
					case 0xF0: //SysEx
						$p+=1;
						$len = _readVarLen($binStr, $p);
						if(($len+$p) > $trackLen) {
							_err("SysEx has a corrupt variable length field ($len) [track: $tn dt: $dt p: $p].");
						}
						$str = 'f0';
						for($i=0;$i<$len;$i++) { $str .= ' '.sprintf("%02x", ord($binStr[$p+$i])); }
						$track[] = "$time SysEx $str";
						$p+=$len;
						break;
					default: //Repetition of last event?
						switch($last) {
							case 'On':
							case 'Off':
								$note = ord($binStr[$p]);
								$vel = ord($binStr[$p+1]);
								$track[] = "$time $last ch=$chan n=$note v=$vel";
								$p+=2;
								break;
							case 'PrCh':
								$prog = ord($binStr[$p]);
								$track[] = "$time PrCh ch=$chan p=$prog";
								$p+=1;
								break;
							case 'PoPr':
								$note = ord($binStr[$p+1]);
								$val = ord($binStr[$p+2]);
								$track[] = "$time PoPr ch=$chan n=$note v=$val";
								$p+=2;
								break;
							case 'ChPr':
								$val = ord($binStr[$p]);
								$track[] = "$time ChPr ch=$chan v=$val";
								$p+=1;
								break;
							case 'Par':
								$c = ord($binStr[$p]);
								$val = ord($binStr[$p+1]);
								$track[] = "$time Par ch=$chan c=$c v=$val";
								$p+=2;
								break;
							case 'Pb':
								$val = (ord($binStr[$p]) & 0x7F) | ((ord($binStr[$p+1]) & 0x7F)<<7);
								$track[] = "$time Pb ch=$chan v=$val";
								$p+=2;
								break;
							default:
								_err("Unknown repetition: $last.");
						} //switch ($last)
				} //switch ($byte)
		} //switch ($high)
	} //while
	return $track;
}

function _findTempo() { //search track 0 for set tempo msg
	$track = $this->tracks[0];
	$mc = count($track);
	for($i=0; $i<$mc; $i++) {
		$msg = explode(' ', $track[$i]);
		if((int)$msg[0]>0) { break; }
		if($msg[1]=='Tempo') {
			$this->tempo = $msg[2];
			$this->tempoMsgNum = $i;
			break;
		}
	}
}

function _getNormalisedArray($idt, $snm, $key) { //$idt - is this a drum track, $snm - scale number, $key - tonic
	$scale = $this->_getScale($idt, $snm, $key);
	$notes = $idt ? $this->rangeDrums : $this->rangeNotes;
	$result = array();
	$result = array_fill($notes[0], $notes[1]-$notes[0]+1, $notes[0]);
	array_walk($result, function(&$v, $k, $d) {
		if($k<$d[0][0] || $k>=$d[0][$d[1]-1]) {
			$m = round(($d[0][0]-$d[2][0]+$d[2][1]-$d[0][$d[1]-1]+1)/2);
			if($k<$d[0][0]) {
				$t = $d[0][0]-$k;
				$v = $t<=$m ? $d[0][0] : $d[0][$d[1]-1];
			} else {
				$t = $k-$d[0][$d[1]-1];
				$v = $t<$m ? $d[0][$d[1]-1] : $d[0][0];
			}
		} else {
			for($i=0, $l=$d[1]-1; $i<$l; $i++) {
				if($k>=$d[0][$i] && $k<$d[0][$i+1]) {
					$m = round(($d[0][$i+1]-$d[0][$i])/2);
					$t = $k-$d[0][$i];
					$v = $t<$m ? $d[0][$i] : $d[0][$i+1];
					break;
				}
			}
		}
	}, array($scale, count($scale), $notes));
	return $result;
}

function _getScale($idt, $snm, $key) { //$idt - is this a drum track, $snm - scale number, $key - tonic
	$scale = array();
	if($idt) {
		switch($snm) {
			case 0: //all sounds present in a common drum kit
				$scale = array(35, 36, 37, 38, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 55, 57, 59);
				break;
			case 1: //previous set minus unusual sounds like "Side Stick"
				$scale = array(35, 36, 38, 40, 42, 44, 45, 46, 47, 48, 49, 50, 51, 52, 55, 57, 59);
				break;
			case 2: //previous set minus some tom-toms and duplicates like "Crash Cymbal 2"
				$scale = array(36, 40, 42, 44, 46, 47, 48, 49, 51, 52, 55);
				break;
		}
	} else {
		//$key - 'C', 'C#/Db', 'D', 'D#/Eb', 'E', 'F', 'F#/Gb', 'G', 'G#/Ab', 'A', 'A#/Bb', 'B', where 'C' has an index of 0, 'C#/Db' has an index of 1, etc.
		$mask = array(); //maybe a string would make more sense?
		$notes = $this->rangeNotes;
		switch($snm) { //R - root note, W - whole tone (2 semitones), H - half a tone (1 semitone)
			case 0: //natural major
				$mask = array(1, 0, 1, 0, 1, 1, 0, 1, 0, 1, 0, 1); //R, W, W, H, W, W, W, H
				break;
			case 1: //natural minor
				$mask = array(1, 0, 1, 1, 0, 1, 0, 1, 1, 0, 1, 0); //R, W, H, W, W, H, W, W
				break;
			case 2: //harmonic minor
				$mask = array(1, 0, 1, 1, 0, 1, 0, 1, 1, 0, 0, 1); //R, W, H, W, W, H, W+H, H
				break;
			case 3: //pentatonic major
				$mask = array(1, 0, 1, 0, 1, 0, 0, 1, 0, 1, 0, 0); //R, W, W, W+H, W, W+H
				break;
			case 4: //pentatonic minor
				$mask = array(1, 0, 0, 1, 0, 1, 0, 1, 0, 0, 1, 0); //R, W+H, W, W, W+H, W
				break;
			case 5: //blues hexatonic major
				$mask = array(1, 0, 1, 1, 1, 0, 0, 1, 0, 1, 0, 0); //R, W, H, H, W+H, W, W+H
				break;
			case 6: //blues hexatonic minor
				$mask = array(1, 0, 0, 1, 0, 1, 1, 1, 0, 0, 1, 0); //R, W+H, W, H, H, W+H, W
				break;
		}
		$mask = _array_circular_shift($mask, $key); //transpose scale by the key
		for($i=$notes[0], $l=$notes[1]+1; $i<$l; $i++) {
			$note = $i%12; //note number within the octave, 0-11
			if(!$mask[$note]) { continue; } //if not within the scale, continue
			$scale[] = $i;
		}
	}
	return $scale;
}

} //End class

//-- Additional utilities --//
function _hex2bin($hex_str) { //hexstr to binstr
	$bin_str = '';
	for($i=0; $i<strlen($hex_str); $i+=2) { $bin_str .= chr(hexdec(substr($hex_str, $i, 2))); }
	return $bin_str;
}

function _getBytes($n, $len) { //int to bytes (length $len)
	$str = '';
	for($i=$len-1; $i>=0; $i--) { $str .= chr(floor($n/pow(256, $i))); }
	return $str;
}

function _readVarLen($str, &$pos) { //variable length string to int (+repositioning)
	if(($value = ord($str[$pos++])) & 0x80) {
		$value &= 0x7F;
		do {
			$value = ($value << 7) + (($c = ord($str[$pos++])) & 0x7F);
		} while($c & 0x80);
	}
	return($value);
}

function _writeVarLen($value) { //int to variable length string
	$buf = $value & 0x7F;
	$str = '';
	while($value >>= 7) {
		$buf <<= 8;
		$buf |= (($value & 0x7F) | 0x80);
	}
	while(true) {
		$str .= chr($buf%256);
		if($buf & 0x80) { $buf >>= 8; } else { break; }
	}
	return $str;
}

function _delta2Absolute($track) { //converts all delta times in track to absolute times
	$mc = count($track);
	$last = 0;
	for($i=0; $i<$mc; $i++) {
		$msg = explode(' ',$track[$i]);
		$t = $last + (int)$msg[0];
		$msg[0] = $t;
		$track[$i] = implode(' ',$msg);
		$last = $t;
	}
	return $track;
}

function _array_circular_shift($array, $steps=1) { //circularly shift $array elements by a certain number of $steps
	$l = count($array);
	if($steps===0 || $l===0) { return $array; }
	$steps %= $l;
	$steps *= -1;
	return array_merge(array_slice($array, $steps), array_slice($array, 0, $steps));
}

function _err($str) { //error message
	if((int)phpversion()>=5) {
		eval('throw new RuntimeException($str);'); //throws php5-exceptions, the main script can deal with these errors
	} else {
		die('>>> '.$str.'!');
	}
}
?>
