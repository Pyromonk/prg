// ==UserScript==
// @name             Fishing [GW]
// @description      Упрощение глупого занятия.
// @include			 http://*ganjawars.ru/*
// @version          1.33
// ==/UserScript==

(function() {

if(!/ganjawars\.ru\//.test(location.href) || /log(in|out)\.php/.test(location.href)) { return false; }
if(!/fshn=.+?(;|$)/.test(document.cookie)) { document.cookie = 'fshn=-1; domain=.ganjawars.ru; path=/'; }
var toc = 60; var tof = 10; var snd = 22; //тайм-аут проверки возможности рыбачить; тайм-аут обновления удочки; номер голоса клюющей рыбы
var rod = document.querySelector('form[action*="/object-specops.php"] input[type="submit"]');
var bob = document.querySelector('table[cellpadding="10"] img[src$="/q-new/water_F.gif"]') || document.querySelector('table[cellpadding="10"] img[src$="/q-new/water_R.gif"]');
var dtm, lrt, ndt, now;
fish(); update();

function fish() {
	if(!bob) { return; }
	if(rod && rod.value=='Достать рыбу') {
		playSound(snd); //замените на alert, у кого на работе нет звука
	} else {
		setTimeout(function() { location.href = document.querySelector('div[align="center"] a[href*="/walk.p.php?"]').href; }, 1000*getRandomInt(tof-1, tof+1));
	}
}

function getRandomInt(a, b) { return Math.floor((b-a+1)*Math.random())+a; }

function playSound(sId) {
	if(sId<1 || sId>30) { return false; }
	var root = window || unsafeWindow;
	var t = root.document.querySelector('#ac'); var x;
	if(!t) { t = document.createElement('div'); t.id = 'ac'; t.style.visibility = 'hidden'; document.body.appendChild(t); }
	x = '<embed flashvars="soundPath=http://ganjawars.ru/sounds/'+sId+'.mp3" allowscriptaccess="always" quality="high"';
	x += ' src="http://images.ganjawars.ru/i/play.swf" type="application/x-shockwave-flash">';
	t.innerHTML = x;
}

function update() {
	dtm = document.cookie.match(/fshn=(.+?)(?:;|$)/)[1];
	lrt = Number(dtm.match(/\d+$/)[0]);
	ndt = new Date();
	dtm = Number(dtm.match(/\d+/)[0]);
	ndt = ndt.getTime();
	if(ndt>=dtm+2400000 && !lrt) {
		document.cookie = 'fshn='+dtm+'-1; domain=.ganjawars.ru; path=/';
		alert('Можно рыбачить!');
	} else if(/\/walk\.p\.php/.test(location.href)) {
		if(rod && rod.value=='Достать рыбу') { rod.addEventListener('click', function() {
			now = new Date();
			now = now.getTime();
			document.cookie = 'fshn='+now+'-0; domain=.ganjawars.ru; path=/';
		}, false); }
	} else {
		setTimeout(function() { update(); }, 1000*getRandomInt(toc-1, toc+1));
	}
}

})();
