// ==UserScript==
// @name             My Property Wrap [GW]
// @description      Хватит уже ломать эту строчку.
// @include          http://*ganjawars.ru/*
// @version          1.17
// ==/UserScript==

(function() {

var lh = location.href;
var t;

if(/\/me\//.test(lh)) {
	t = document.querySelector('a[href*="/info.realty.php?id="] font[color="#000099"]');
	if(t) { t.parentNode.parentNode.parentNode.style.whiteSpace = 'nowrap'; }
	t = document.querySelector('img[src*="/wargroup/skill_combat_pistols"]').parentNode.parentNode;
	t.innerHTML = t.innerHTML.replace('&nbsp;', '&nbsp;');
	[].forEach.call(document.querySelectorAll('table[id^="note_"]'), function(x) { if(!/\/object\.php\?id=\d+/.test(x.innerHTML)) { x.style.display = 'none'; } });
} else if(/\/market\.php/.test(lh)) {
	[].forEach.call(document.querySelectorAll('select[style*="width"]'), function(x) { x.style.width = '80%'; });
} else if(/\/sms-read\.php/.test(lh)) {
	t = document.querySelector('[style*="/img/sms-ny.jpg"]');
	if(t) { t.style.backgroundImage = 'none'; }
} else {
	return false;
}

})();
