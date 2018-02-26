main();

function main() {
	if(document.readyState == 'complete') {
		Array.prototype.clean = (function() {
			return function(deleteValue) {
				for(var i=0, l=this.length; i<l; i++) {
					if(this[i] == deleteValue) {         
						this.splice(i, 1);
						i--;
					}
				}
				return this;
			}
		})();
		Array.prototype.rotate = (function() {
			var push = Array.prototype.push;
			var splice = Array.prototype.splice;
			return function(count) {
				var len = this.length >>> 0; //convert to unsigned int
				var count = count >> 0; //convert to int
				count = ((count%len)+len)%len; //convert count to value in range [0, len)
				push.apply(this, splice.call(this, 0, count));
				return this;
			};
		})();
		var key, scale, Notes, Roots;
		var Boxes = [].slice.call(document.querySelectorAll('#n [type="checkbox"]'), 0);
		var Names = [].map.call(document.querySelectorAll('#n label'), function(x) { return x.textContent; });
		showScale();
		[].forEach.call(document.querySelectorAll('select'), function(x) { x.addEventListener('change', showScale, false); });
		Boxes.forEach(function(x) { x.addEventListener('change', updateNotes, false); });
		
		function getChordList(seq, root) {
			var Chords = ['', '', '', '', '', '', '', '', '', '', '', '', ''];
			var rsl = '';
			if(seq[3]) {
				if(seq[6]) {
					Chords[3] = 'Diminished'+getChordNoteList([3, 6], root);
					if(seq[10]) { Chords[8] = 'Half-diminished'+getChordNoteList([3, 6, 10], root); }
				}
				if(seq[7]) {
					Chords[1] = 'Minor'+getChordNoteList([3, 7], root);
					if(seq[9]) { Chords[6] = 'Minor 6th'+getChordNoteList([3, 7, 9], root); }
					if(seq[10]) {
						Chords[10] = 'Minor 7th'+getChordNoteList([3, 7, 10], root);
						if(seq[14]) { Chords[12] = 'Minor 9th'+getChordNoteList([3, 7, 10, 14], root); }
					}
				}
			}
			if(seq[4]) {
				if(seq[7]) {
					Chords[0] = 'Major'+getChordNoteList([4, 7], root);
					if(seq[9]) { Chords[5] = 'Major 6th'+getChordNoteList([4, 7, 9], root); }
					if(seq[10]) { Chords[7] = 'Dominant 7th'+getChordNoteList([4, 7, 10], root); }
					if(seq[11]) {
						Chords[9] = 'Major 7th'+getChordNoteList([4, 7, 11], root);
						if(seq[14]) { Chords[11] = 'Major 9th'+getChordNoteList([4, 7, 11, 14], root); }
					}
				}
				if(seq[8]) { Chords[4] = 'Augmented'+getChordNoteList([4, 8], root); }
			}
			if(seq[5] && seq[7]) { Chords[2] = 'Suspended 4th'+getChordNoteList([5, 7], root); }
			Chords.clean('');
			rsl = Chords.length ? '&bull;&nbsp;'+Chords.clean('').join('<br>&bull;&nbsp;') : '&mdash;';
			return rsl;
		}
		function getChordNoteList(distArr, root) {
			var out = '&nbsp;('+Names[root];
			for(var i=0, l=distArr.length; i<l; i++) { out += '-'+Names[(root+distArr[i])%12]; }
			return out+')';
		}
		function getOptionNumber(id) { return Number(document.querySelector('#'+id+' option:checked').value.match(/\d+/)[0]); }
		function showScale() {
			var Masks = [
				[1, 0, 1, 0, 1, 1, 0, 1, 0, 1, 0, 1], //Natural Major
				[1, 0, 1, 1, 0, 1, 0, 1, 1, 0, 1, 0], //Natural Minor
				[1, 0, 1, 1, 0, 1, 0, 1, 1, 0, 0, 1], //Harmonic Minor
				[1, 0, 1, 0, 1, 0, 0, 1, 0, 1, 0, 0], //Pentatonic Major
				[1, 0, 0, 1, 0, 1, 0, 1, 0, 0, 1, 0], //Pentatonic Minor
				[1, 0, 1, 1, 1, 0, 0, 1, 0, 1, 0, 0], //Blues Hexatonic Major
				[1, 0, 0, 1, 0, 1, 1, 1, 0, 0, 1, 0]  //Blues Hexatonic Minor
			];
			key = getOptionNumber('k');
			scale = getOptionNumber('s');
			Notes = Masks[scale].rotate(-key);
			Boxes.forEach(function(x, i) { x.checked = Notes[i]; });
			updateChords(key);
		}
		function updateChords(key) {
			var count = 0; //count scale degree
			var mask = '';
			var note = '';
			var result = '';
			Roots = Notes.rotate(key); //back to C
			mask = Roots.join('');
			Roots = Roots.concat(Roots.concat(Roots)); //triple octave for 9th chords, I know this is a lazy way out
			for(var i=0, l=Names.length; i<l; i++) {
				if(Number(mask.charAt(i))>0) {
					++count;
					if(count>1) { result += '<br>'; }
					note = Names[(i+key)%12];
					result += '<h3 class="fwbl">'+count+' ('+note+')'+'</h3>';
					result += getChordList(Roots, Names.indexOf(note));
				}
				Roots.rotate(1);
			}
			document.querySelector('#chrd').innerHTML = result;
		}
		function updateNotes() { //to update value of Notes on checkbox state change
			Notes = Boxes.map(function(x) { return x.checked ? 1 : 0; });
			key = getOptionNumber('k')
			updateChords(key);
		}
	} else {
		setTimeout(function() { main(); }, 1000);
	}
}
