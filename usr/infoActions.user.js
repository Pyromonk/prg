// ==UserScript==
// @name             Info Actions [GW]
// @description      Добавляем ссылок в меню взаимодействия с персонажем.
// @include			 http://*ganjawars.ru/info.php?id=*
// @version          1.05
// ==/UserScript==

(function() {

if(!/ganjawars\.ru\/info\.php\?id=/.test(location.href)) { return false; }

var id = location.href.match(/\d+/)[0];
var nn = convert(document.querySelector('#namespan b').textContent);
var tbl = document.querySelector('#actiondiv table');
var t;

document.querySelector('img[src$="/weapon.gif"]').style.paddingLeft = '6px';
addRow('friends', '/home.friends.php?addfriend='+nn, 'В друзья');
addRow('last_fight', '/home.friends.php?addfriend='+nn+'&blop=1', 'В чёрный список');
addRow('iski', '/isks.php?sid='+id+'&st=1&period=4', 'Иски');
addRow('market', '/info.rent.php?id='+id, 'Предметы в аренде');
addRow('wlog', 'http://ganjastats.ru/players/item/'+id, 'GanjaStats');

function addRow(img, hrf, lnk) {
	tbl.innerHTML += '<tr><td class="greengreenbg"><img src="http://images.ganjawars.ru/i/home/'+img+'.gif" width="12" height="10">&nbsp;<a href="'+hrf+'">'+lnk+'</a></td></tr>';
}

function convert(str) {
	var win = {1025: 168, 1105: 184, 8470: 185, 8482: 153};
	var ret = [].map.call(str, function(x) { t = x.charCodeAt(0); t = win[t] ? win[t] : (t > 1039 ? t-848 : t); return (t < 16 ? '%0' : '%') + t.toString(16); });
	return ret.join('');
}

})();
