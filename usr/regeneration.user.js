// ==UserScript==
// @name             Regeneration [GW]
// @description      Информация о здоровье.
// @include          http://*ganjawars.ru/me/*
// @version          3.18
// ==/UserScript==

(function() {

if(location.href.indexOf('ganjawars.ru/me/') == -1) { return false; }
if(typeof localStorage == 'undefined') {
    alert('Ваш браузер не поддерживает localStorage.\nСкачайте Opera 10.60, Firefox 3.6.8, Chrome 5 или эти же браузеры более новых версий.');
	return false;
}

var root = typeof unsafeWindow != 'undefined' ? unsafeWindow : window;

if(localStorage.getItem('regenState') == null) { localStorage.setItem('regenState', 'b'); }

var state = localStorage.getItem('regenState');
var speed = parseFloat(document.querySelector('script').innerHTML.match(/var hp_speed=(.*);/i)[1]);
var health = document.querySelector('font[color="green"]').parentNode;
var cur = parseFloat(document.querySelector('#hpdiv').textContent);
var max = parseInt(health.innerHTML.match(/\/&nbsp;(\d+)/)[1]);
var percent, sec, date, split, timeZone, stateNow;
var holder = document.createElement('span');

holder.style.color = 'black';
holder.innerHTML = '<br> &nbsp;&raquo; <span style="font-weight: bold">Выздоровление:</span> <span id="regen"></span>';
health.insertBefore(holder, health.querySelector('br'));

var regen = root.document.querySelector('#regen');

if((cur == max) && (state == 'c')) { regen.innerHTML = '100%'; return; }
update();

function update() {
	cur += speed;
	if(cur > max) { cur = max; }
	percent = (cur / max * 100);
	percent = percent > 100 ? 100 : percent;
	regen.innerHTML = Math.floor(percent) + '%';
	state = percent < 80 ? 'a' : state;
	stateNow = percent < 80 ? 'a' : (percent < 100 ? 'b' : 'c');
	if((percent >= 80) && (stateNow != state)) {
		alert((stateNow == 'b' ? '80' : '100') + '% здоровья восстановлено');
		state = stateNow;
	}
	if(percent < 100) {
		sec = Math.floor(((max * (percent < 80 ? 0.8 : 1)) - cur) / speed);
		date = new Date(sec * 1000);
		split = date.getSeconds() < 10 ? ':0' : ':';
		timeZone = Math.floor(date.getTimezoneOffset() / 60);
		regen.innerHTML += ', <span style="font-weight: bold">' + ((date.getHours() + timeZone) > 0 ? date.getHours() + timeZone + ':' : '') + date.getMinutes() + split + date.getSeconds() + '</span> (до ' + (percent < 80 ? 80 : 100) + '%)';
	}
	localStorage.setItem('regenState', state);
	setTimeout(function() { update(); }, 1000);
}

})();
