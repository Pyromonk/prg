// ==UserScript==
// @name             Show Rank Tooltip [GW]
// @description      Краткое описание каждого погона.
// @include          http://*ganjawars.ru/info.php?id=*
// @include          http://*ganjawars.ru/syndicate.php?id=*&page=online
// @include          http://*ganjawars.ru/syndicate.php?id=*&page=members
// @version          1.28
// ==/UserScript==

(function() {

if(location.href.indexOf('ganjawars.ru/info') == -1 && location.href.indexOf('ganjawars.ru/synd') == -1) { return false; }

var root = typeof unsafeWindow != 'undefined' ? unsafeWindow : window;
var Ranks = {'0':'<b style="color:#770000">Private</b><br /><hr />Минимальный синдикатный уровень: <b>0</b>',
			'1':'<b style="color:#770000">Lieutenant</b><br /><hr />Минимальный синдикатный уровень: <b>15</b><br /><hr />Бонусы: +4% тепловизора, +2 меткости, +2 выносливости, +1 бонус сапера',
			'2':'<b style="color:#770000">Captain</b><br /><hr />Минимальный синдикатный уровень: <b>18</b><br /><hr />Бонусы: +4% маскировки, +6% тепловизора, +4 меткости, +2 здоровья, +2 бонуса сапера, +1 бонус устойчивости',
			'3':'<b style="color:#770000">Major</b><br /><hr />Минимальный синдикатный уровень: <b>24</b><br /><hr />Бонусы: +6% маскировки, +8% тепловизора, +4 меткости, +4 выносливости, +2 здоровья, +3 бонуса сапера, +1 бонус опыта',
			'4':'<b style="color:#770000">Colonel</b><br /><hr />Минимальный синдикатный уровень: <b>30</b><br /><hr />Бонусы: +9% маскировки, +9% тепловизора, +6 меткости, +6 выносливости, +4 здоровья, +4 бонуса сапера, +1 бонус второго шага',
			'5':'<b style="color:#770000">Brigadier</b><br /><hr />Минимальный синдикатный уровень: <b>34</b><br /><hr />Бонусы: +9% маскировки, +10% тепловизора, +6 меткости, +6 выносливости, +6 здоровья, +5 бонуса сапера, +1 бонус второго шага, +1 бонус ярости, +1 крепкий орешек',
			'6':'<b style="color:#770000">Major General</b><br /><hr />Минимальный синдикатный уровень: <b>37</b><br /><hr />Бонусы: +11% маскировки, +11% тепловизора, +7 меткости, +7 выносливости, +7 здоровья, +5 бонуса сапера, +2 бонус второго шага, +2 бонус ярости, +1 крепкий орешек, +1 бонус снайпера, +1 бонус Маклауда',
			'7':'<b style="color:#770000">Lieutenant General</b><br /><hr />Минимальный синдикатный уровень: <b>40</b><br /><hr />Бонусы: +12% маскировки, +11% тепловизора, +8 меткости, +7 выносливости, +8 здоровья, +5 бонуса сапера, +1 бонус второго шага, +2 бонус ярости, +2 крепкий орешек, +2 бонус снайпера, +1 бонус Маклауда',
			'8':'<b style="color:#770000">Colonel General</b><br /><hr />Минимальный синдикатный уровень: <b>42</b><br /><hr />Бонусы: +13% маскировки, +12% тепловизора, +9 меткости, +8 выносливости, +8 здоровья, +5 бонуса сапера, +1 бонус второго шага, +2 бонус ярости, +3 крепкий орешек, +3 бонус снайпера, +1 бонус Маклауда',
			'9':'<b style="color:#770000">Syndicate General</b><br /><hr />Минимальный синдикатный уровень: <b>45</b><br /><hr />Бонусы: +14% маскировки, +14% тепловизора, +10 меткости, +10 выносливости, +10 здоровья, +5 бонуса сапера, +2 бонус второго шага, +2 бонус ярости, +4 крепкий орешек, +4 бонус снайпера'}

createTooltip();addEvents();
			
function BID(id) { return root.document.getElementById(id); }
function BTN(name) { return root.document.getElementsByTagName(name); }
function DCE(elem) { return document.createElement(elem); }

function addEvents() {
	var images = BTN('img');
	for(var i = 0, l = images.length; i < l; i++) {
		if(images[i].src.indexOf('http://images.ganjawars.ru/img/rank') != -1) {
			var rankNum = /^http:\/\/images\.ganjawars\.ru\/img\/rank(\d+)\.gif/.exec(images[i].src)[1];
			var text = Ranks[rankNum];
			bindEvent(images[i], 'mousemove', (function(text_) { return function(event) { showTooltip(event, text_); } } )(text));
			bindEvent(images[i], 'mouseout', hideTooltip);
		}
	}
}

function createTooltip() {
	var table = DCE('table');
		table.id = 'toolTip';
		table.setAttribute('style', 'background: #ffffff; position: absolute; width: 400px; visibility: hidden; z-index: 1; border: 1px solid black; opacity: 0.9');
		table.innerHTML = '<tr><td valign="top"></td></tr>';
	document.body.appendChild(table);
}

function showTooltip(event, text) {
	var tooltip = BID('toolTip');
	tooltip.style.left = (event.clientX + 20) + 'px';
	var height = document.compatMode == 'CSS1Compat' && !window.opera ? document.documentElement.clientHeight : document.body.clientHeight;
	if(event.clientY > height - 140) { tooltip.style.top  = (getBodyScrollTop() + (event.clientY - 120)) + 'px'; }
	else { tooltip.style.top  = (getBodyScrollTop() + (event.clientY + 10)) + 'px'; }
	tooltip.style.visibility = 'visible';
	if(text != tooltip.rows[0].cells[0].innerHTML) { tooltip.rows[0].cells[0].innerHTML = text; }
}

function hideTooltip() { BID('toolTip').style.visibility = 'hidden'; }

function getBodyScrollTop() { return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop); }

function bindEvent(element, event, callback) {
	if(!element) { return; }
	if(element.addEventListener) {
		if (event.substr(0, 2) == 'on') { event = event.substr(2); }
		element.addEventListener(event, callback, false);
	} else if(element.attachEvent) {
		if(event.substr(0, 2) != 'on') { event = 'on' + event; }
		element.attachEvent(event, callback, false);
	}
	return;
}

})();
