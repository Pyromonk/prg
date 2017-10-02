// ==UserScript==
// @name             Family [GW]
// @description      Семейные узы никогда не были прозрачнее.
// @include			 http://*ganjawars.ru/info.php?id=*
// @version          1.12
// ==/UserScript==

(function() {

if(!/ganjawars\.ru\/info\.php\?id=/.test(location.href) || !/<b>Семья:<\/b>/.test(document.body.innerHTML)) { return false; }

var tbl = document.querySelector('table[border="0"] a[href*="/info.vote.php?id="]').parentNode.parentNode.parentNode.parentNode.parentNode.parentNode; //спасибо тебе, Илья
var fin = tbl.innerHTML.indexOf('Семья:</b>')+11;
var ban = /<br>.+<font color=/.test(tbl.innerHTML) ? tbl.innerHTML.search(/<br>.+<font color=/) : 0; //и ещё раз спасибо
var fml = ban ? tbl.innerHTML.substring(fin, ban) : tbl.innerHTML.substring(fin);
var Frr = [];
var a, b, n;

if(!/\(/.test(fml)) { return; }
fml = fml.replace(/ +/gm, ' '); //не поверите, но у них там множественные пробелы бывают
if(/,/.test(fml)) { Frr = fml.split(', '); } else { Frr.push(fml); }
Frr.forEach(function(x, i, y) {
	a = x.lastIndexOf(';')+1;
	b = x.lastIndexOf(' (') == -1 ? x.length : x.lastIndexOf(' (');
	n = x.substring(a, b);
	y[i] = x.substring(0, a)+' '+'<a href="http://www.ganjawars.ru/search.php?key='+n+'">'+n+'</a>'+(b == x.length ? '' : ' ')+x.substring(b);
});
fml = Frr.join(', ');
if(ban) { ban = tbl.innerHTML.substring(ban); }
tbl.innerHTML = tbl.innerHTML.substring(0, fin)+fml+(ban ? ban : '');

})();
