// ==UserScript==
// @name             Synd and Warlog [GW] 
// @description      Ссылка на синдикат ведёт на онлайн; ссылка на лог боя переворачивает его.
// @include          http://*ganjawars.ru/*
// @version          1.02
// ==/UserScript==

(function() {

if(!/ganjawars\.ru/.test(location.href)) { return false; }

if(!/\/syndicate\.php\?id=\d+/.test(location.href)) {
	[].forEach.call(document.querySelectorAll('a[href*="/syndicate.php?id="]'), function(x) { if(/id=\d+$/.test(x.href)) { x.href += '&page=online'; } });
}
[].forEach.call(document.querySelectorAll('a[href*="/warlog.php?bid="]'), function(x) { if(/bid=\d+$/.test(x.href)) { x.href += '&rev=1'; } });

})();
