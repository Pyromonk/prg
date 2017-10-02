// ==UserScript==
// @name             Thread Eviscerator [GW]
// @description      Вы всё ещё кипятите?
// @include			 http://*.ganjawars.ru/threads.php?fid=*
// @version          1.24
// ==/UserScript==

(function() {

if(location.href.indexOf('ganjawars.ru/threads.php?fid') == -1) { return false; }
var root = typeof unsafeWindow != 'undefined' ? unsafeWindow : window;
if(!areYouMod()) { return false; }

var to = 3; //тайм-аут, штоле, в секундах, снижать не рекомендую
var table = document.querySelectorAll('table[cellpadding="5"]')[1];
var i, boxes, temp;
var l = table.rows.length;

for (i=0; i<l; i++) {
	table.rows[i].cells[1].firstChild.innerHTML += ' <input type="checkbox" />';
	table.rows[i].cells[1].setAttribute('onclick', '');
}
table.rows[0].cells[1].innerHTML += '<img title="Удалить отмеченные" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAYAAAA71pVKAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3QQQCxAHHx48HQAAAGBJREFUKM/t0rEJgEAMRuFPEXQOF7QUJ3AQl7IVHEIrbc5G9BQOrHwQfgJ5SRPumTCEvKQ49T2WUDNG1GhRheqO4ewkb57J7i63WCNiGdu6oXnIqPwmQS6BX/5STnrPJHYx5Rb8vHzIPQAAAABJRU5ErkJggg==" style="cursor:pointer" />';
bindEvent(root.document.querySelector('input[type="checkbox"]'), 'click', function() {
	boxes = root.document.querySelectorAll('input[type="checkbox"]'); l = boxes.length;
	for(i=1; i<l; i++) { boxes[i].checked = boxes[0].checked; }
});
bindEvent(root.document.querySelector('img[title*="Удалить отмеченные"]'), 'click', function() {
	if(confirm('Ви таки уверены?')) { deleteThreads(1, table.rows.length); }
});

function ajaxQuery(url, method, param, async, onsuccess, onfailure) {
	var xmlHttpRequest = new XMLHttpRequest();
	if(async == true) {
		xmlHttpRequest.onreadystatechange = function () {
			if (xmlHttpRequest.readyState == 4 && xmlHttpRequest.status == 200 && typeof onsuccess != 'undefined') { onsuccess(xmlHttpRequest); }
			else if (xmlHttpRequest.readyState == 4 && xmlHttpRequest.status != 200 && typeof onfailure != 'undefined') { onfailure(xmlHttpRequest); }
		}
	}
	xmlHttpRequest.open(method, url, async);
	if(method == 'POST') { xmlHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); }
	xmlHttpRequest.send(param);
	if(async == false) {
		if (xmlHttpRequest.status == 200 && typeof onsuccess != 'undefined') { onsuccess(xmlHttpRequest); }
		else if (xmlHttpRequest.status != 200 && typeof onfailure != 'undefined') { onfailure(xmlHttpRequest); }
	}
}

function areYouMod() {
	var bln; temp = root.document.createElement('div');
	ajaxQuery('http://www.ganjawars.ru/threads-new.php?fid=' + location.href.substr(40), 'GET', null, false, function(req) {
		temp.innerHTML = req.responseText;
		if (typeof temp.querySelector('a') == 'undefined') { alert('204 No Content'); return false; }
		if (temp.textContent.indexOf('Прикрепить тему') != -1) { bln = true; } else { bln = false; }
	}, function() { alert('Что-то поломалось,\nвызывайте проктолога.'); });
	return bln;
}

function bindEvent(element, event, callback) {
	if(!element) { return; }
	if(element.addEventListener) {
		if (event.substr(0, 2) == 'on') { event = event.substr(2); }
		element.addEventListener(event, callback, false);
	} else if(element.attachEvent) {
		if (event.substr(0, 2) != 'on') { event = 'on' + event; }
		element.attachEvent(event, callback, false);
	}
	return;
}

function deleteThreads(itrtr, finish) {
	if(itrtr == finish) { return; }
	if(table.rows[itrtr].querySelector('input').checked) {
		setTimeout(function() {
			ajaxQuery(table.rows[itrtr].cells[0].firstChild.href, 'GET', null, true, function(req) {
				temp.innerHTML = req.responseText;
				if (typeof temp.querySelector('a') == 'undefined') { alert('204 No Content'); return false; }
				setTimeout(function() {
					ajaxQuery(temp.querySelector('a[href*="del"]').href, 'GET', null, true, function() {
						table.rows[itrtr].style.display = 'none';
						deleteThreads(++itrtr, finish);
					}, function() { alert('Что-то поломалось,\nвызывайте проктолога.'); });
				}, 1000*getRandomInt(to-1, to+1));
			}, function() { alert('Что-то поломалось,\nвызывайте проктолога.'); });
		}, 1000*getRandomInt(to-1, to+1));
	} else {
		deleteThreads(++itrtr, finish);
	}
}

function getRandomInt(a, b) { return Math.floor(Math.random()*(b-a+1))+a; }

})();
