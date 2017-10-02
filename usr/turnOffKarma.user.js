// ==UserScript==
// @name             Turn Off Karma [GW]
// @description      Приказ свыше, чо.
// @include			 http://*ganjawars.ru/info.php?id=*
// @version          1.02
// ==/UserScript==

(function() {

if(!/ganjawars\.ru\/info\.php\?id=/.test(location.href)) { return false; }
var t = document.querySelector('a[title^="Отправить Ваш голос:"]');
if(t) { t.parentNode.parentNode.parentNode.style.display = 'none'; }

})();
