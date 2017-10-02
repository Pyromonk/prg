function disableInputs(chk) {
	cid = chk.id;
	fld = chk.parentNode.parentNode;
	[].forEach.call(fld.querySelectorAll('input'), function(x) { if(x.id!=cid) { x.disabled = !chk.checked; } });
	[].forEach.call(fld.querySelectorAll('select'), function(x) { x.disabled = !chk.checked; });
}

function showHelp() {
	var hlp = document.querySelector('#help');
	if(hlp) {
		hlp.addEventListener('click', function(e) {
			if(e.target.id=='help') { hlp.style.display = 'none'; }
		}, false);
		hlp.style.display = 'block';
	}
}
