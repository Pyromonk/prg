// ==UserScript==
// @name             Suit Numbers [GW]
// @description      Нумеруем иски.
// @include			 http://*ganjawars.ru/isks.php?sid=*&st=*&period=*
// @version          1.04
// ==/UserScript==

(function() {

if(!/ganjawars\.ru\/isks\.php\?sid=\d+&st=\d+&period=\d+/.test(location.href)) { return false; }

var pnm = document.querySelector('a[href*="/isks.php?sid="] font[color="red"]') ? Number(document.querySelector('a[href*="/isks.php?sid="] font[color="red"]').innerHTML) : 1;
var tbl = document.querySelector('nobr a[href*="/isk.php?isk_id="]').parentNode.parentNode.parentNode.parentNode;

tbl.querySelector('td').colSpan = 8;
[].forEach.call(tbl.querySelectorAll('tr'), function(x, i) { if(x.querySelector('a[href*="/isk.php?isk_id="]')) {
	x.querySelector('td nobr').innerHTML = '&nbsp;'+Math.round(i-51+50*pnm)+'.'+x.querySelector('td nobr').innerHTML;
} });

})();
