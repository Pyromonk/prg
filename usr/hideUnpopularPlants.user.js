// ==UserScript==
// @name             Hide Unpopular Plants [GW]
// @description      Скрывает непопулярные растения на ферме.
// @include			 http://*ganjawars.ru/ferma.php*
// @version          1.02
// ==/UserScript==

(function() {

if(!/ganjawars\.ru\/ferma\.php/.test(location.href) || !document.querySelector('img[src*="/fav-"][src$=".png"]')) { return false; }

var i, l, t, x;
var frm = document.querySelector('form[action*="/ferma.php"]');
var hdn = slc(frm, 'input[type="hidden"]');
var rad = slc(frm, 'input[type="radio"]');
var lbl = slc(frm, 'label');
var fav = slc(frm, 'a[href*="/ferma.php"][href*="&fav="]');
var rip = frm.textContent.match(/Время созревания: \d+ минут.?/gm);
var rew = frm.textContent.match(/Премия за урожай: \$\d+, \+\d+\.\d+ опыта/gm);

frm.innerHTML = '';
hdn.forEach(function(x) { apc(frm, x); });
for(i=0, l=lbl.length; i<l; i++) {
	if(fav[i].querySelector('img[src$="fav-grn.png"]')) {
		apc(frm, rad[i]);
		apc(frm, lbl[i]);
		frm.innerHTML += '&nbsp';
		apc(frm, fav[i]);
		t = '<br>&bull;&nbsp;'+rip[i]+'<br>&bull;&nbsp;'+rew[i].replace(/\$\d+/, '<font color="#990000"><b>$&</b></font>')+'<br><br>';
		frm.innerHTML += t;
	} else {
		break;
	}
}

function apc(plc, kid) { plc.appendChild(kid); }

function slc(plc, sel) { return [].slice.call(plc.querySelectorAll(sel)); }

})();
