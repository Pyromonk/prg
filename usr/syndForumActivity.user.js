// ==UserScript==
// @name             Syndicate Forum Activity [GW]
// @description      Проверка активности членов синдиката на форуме.
// @include			 http://*ganjawars.ru/threads.php?fid=1*
// @version          1.34
// ==/UserScript==

(function() {

if(!/ganjawars\.ru\/threads\.php\?fid=1\d{4}/.test(location.href)) { return false; }

var fnm = location.href.match(/ganjawars\.ru\/threads\.php\?fid=1(\d{4})/)[1];
var sgn = document.createElement('span');
var frm = document.createElement('span');
var tmp = document.createElement('div');
var to = 4; //тайм-аут между запросами, в секундах; уменьшать не рекомендую; официальные лица могут использовать -1
var DueDate, Members, MostPosts, MostThreads, NineInch, Threads, d, i, k, t, x;

with(sgn.style) { cursor = 'pointer'; marginLeft = '5px'; color = '#7d937d'; }
sgn.innerHTML = '[+]';
bindEvent(sgn, 'click', function() { frm.style.display = frm.style.display == 'none' ? 'inline' : 'none'; sgn.innerHTML = sgn.innerHTML == '[+]' ? '[&ndash;]' : '[+]'; });
frm.innerHTML = '<br /><input type="text" size="4" maxlength="4" id="sfat" value="0" style="margin-top: 10px;" />';
frm.innerHTML += '<input type="button" id="sfab" value="&raquo;" style="margin-left: 5px; padding: 0px 12px; cursor: pointer;" />';
with(frm.style) { marginLeft = '5px'; display = 'none'; }
bindEvent(frm.querySelector('#sfab'), 'click', function() { frm.querySelector('#sfab').style.display = 'none'; getMembers(); });
t = document.querySelector('a[href$="/forum.php"][style*="color"]').parentNode;
t.appendChild(sgn);
t.appendChild(frm);

function ajaxQuery(url, method, param, async, onsuccess, onfailure) {
	var xmlHttpRequest = new XMLHttpRequest();
	if (async == true) {
		xmlHttpRequest.onreadystatechange = function () {
			if (xmlHttpRequest.readyState == 4 && xmlHttpRequest.status == 200 && typeof onsuccess != 'undefined') { onsuccess(xmlHttpRequest); }
			else if (xmlHttpRequest.readyState == 4 && xmlHttpRequest.status != 200 && typeof onfailure != 'undefined') { onfailure(xmlHttpRequest); }
		}
	}
	if (method == 'POST') { xmlHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); }
	xmlHttpRequest.open(method, url, async);
	xmlHttpRequest.send(param);
	if (async == false) {
		if (xmlHttpRequest.status == 200 && typeof onsuccess != 'undefined') { onsuccess(xmlHttpRequest); }
		else if (xmlHttpRequest.status != 200 && typeof onfailure != 'undefined') { onfailure(xmlHttpRequest); }
	}
}

function avantGarde(nmb, uid, nmv) { //долго объяснять
	var cases = [(function() { return MostThreads; }), (function() { return MostPosts; }), (function() { return NineInch; })];
	t = nmb ? cases[nmb-1]() : cases[nmv-1]();
	if(nmb) {
		if(nmv > t[0][1]) { t.length = 0; }
		if(!t.length || nmv >= t[0][1]) { t.push([uid, nmv]); }
		return;
	}
	if(JSON.stringify(t).indexOf('["'+uid+'",') != -1) { return true; }
	return false;
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
}

function getDueDate() {
	var now = new Date();
	var ddt = new Date(now.getTime()+60000*(now.getTimezoneOffset()-1440*Number(frm.querySelector('#sfat').value)+180));
	var dds = ddt.getFullYear()+'-'+prependZero(ddt.getMonth()+1)+'-'+prependZero(ddt.getDate())+' ';
	dds += prependZero(ddt.getHours())+':'+prependZero(ddt.getMinutes())+':'+prependZero(ddt.getSeconds());
	return dds;
}

function getMembers() {
	Members = {};
	ajaxQuery('http://www.ganjawars.ru/syndicate.php?id='+Number(fnm)+'&page=members', 'GET', null, true, function(r) {
		tmp.innerHTML = r.responseText;
		[].forEach.call(tmp.querySelectorAll('table[width="600"]:not([bgcolor]) a[href*="/info.php?id="]'), function(cur) {
			Members[cur.href.match(/\d+/)[0]] = [cur.textContent, 0, 0, 0, '', ''];
		});
		getThreads(-1);
	}, function() { alert('Самое время рвать на себе волосы!'); });
}

function getRandomInt(a, b) { return Math.floor(Math.random() * (b - a + 1)) + a; }

function getThreads(itr, fin) {
	if(itr >= fin) {
		Threads = Threads.filter(function(val, ind, slf) { return slf.indexOf(val) == ind; });
		parseThreads(0, Threads.length, -1);
		frm.innerHTML += ', 0/'+Threads.length+' тем';
		return;
	}
	if(itr == -1) {
		Threads = [];
		DueDate = Number(frm.querySelector('#sfat').value) > 0 ? getDueDate() : 0;
		setTimeout(function() { ajaxQuery('http://www.ganjawars.ru/threads.php?fid=1'+fnm+'&page_id=1000000', 'GET', null, true, function(r) {
			tmp.innerHTML = r.responseText;
			t = tmp.querySelectorAll('table[cellpadding="8"] a[class="clr"]');
			t = t[t.length-1].textContent;
			frm.innerHTML += ' 0/'+t+' страниц'
			getThreads(0, t);
		}, function() { alert('Самое время рвать на себе волосы!'); }); }, 1000*getRandomInt(to-1, to+1));
		return;
	}
	setTimeout(function() { ajaxQuery('http://www.ganjawars.ru/threads.php?fid=1'+fnm+'&page_id='+itr, 'GET', null, true, function(r) {
		tmp.innerHTML = r.responseText;
		[].forEach.call(tmp.querySelectorAll('table[width="100%"][cellspacing="1"] td[valign="top"] a[href*="/messages.php?fid=1"]'), function(cur) {
			Threads.push(cur.href.replace(/&page_id=\w+/, ''));
		});
		frm.innerHTML = frm.innerHTML.replace(/\d+\/\d+/, (itr+1)+'/'+fin);
		getThreads(++itr, fin);
	}, function() { alert('Самое время рвать на себе волосы!'); }); }, 1000*getRandomInt(to-1, to+1));
}

function parseThreads(thc, thn, thp) { //thc - номер темы в Threads; thn - Threads.length; thp - итератор страницы, ограничен нулём
	if(thc >= thn) {
		MostPosts = [['0', 0]]; MostThreads = [['0', 0]]; NineInch = [['0', 0]];
		for(k in Members) { avantGarde(1, k, Members[k][1]); avantGarde(2, k, Members[k][2]); avantGarde(3, k, Members[k][3]); }
		printResults();
		return;
	}
	if(thp == -1) {
		setTimeout(function() { ajaxQuery(Threads[thc]+'&page_id=1000', 'GET', null, true, function(r) {
			tmp.innerHTML = r.responseText;
			t = tmp.querySelectorAll('table[cellpadding="8"] a[class="clr"]');
			t = t[t.length-1].textContent;
			parseThreads(thc, thn, Number(t)-1);
		}, function() { alert('Самое время рвать на себе волосы!'); }); }, 1000*getRandomInt(to-1, to+1));
		return;
	}
	setTimeout(function() { ajaxQuery(Threads[thc]+'&page_id='+thp, 'GET', null, true, function(r) {
		tmp.innerHTML = r.responseText;
		t = [].slice.call(tmp.querySelectorAll('td[id^="cella_"]'));
		for(i=t.length-1; i>=0; i--) {
			k = t[i].querySelector('a[href*="/info.php?id="]').href.match(/\d+/)[0];
			if(Members.hasOwnProperty(k)) {
				d = t[i].parentNode.innerHTML.match(/написано: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/)[1];
				x = t[i].parentNode.querySelector('a[href*="/messages.php?fid=1'+fnm+'"]');
				if(DueDate && DueDate > d) { thp = 0; break; }
				x.textContent == '1' ? Members[k][1] += 1 : Members[k][2] += 1;
				if(!t[i].parentNode.querySelector('i')) { Members[k][3] += t[i].parentNode.querySelector('table[cellpadding="5"]').textContent.length; }
				if(Members[k][4] == '' || Members[k][5] < d) { Members[k][4] = x.href; Members[k][5] = d; }
			}
		}
		parseThreads((thp-1 < 0 ? ++thc : thc), thn, --thp);
		frm.innerHTML = frm.innerHTML.replace(/\d+\/\d+ тем/, thc+'/'+thn+' тем');
	}, function() { alert('Самое время рвать на себе волосы!'); }); }, 1000*getRandomInt(to-1, to+1));
}

function prependZero(num) { //самая полезная функция в этом королевстве
	return (num < 10 ? '0'+num : num);
}

function printResults() {
	t = '<tr bgcolor="#d0eed0" style="text-align: center; font-weight: bold">';
	t += '<td class="wb">#</td><td class="wb">Персонаж</td><td class="wb">Темы</td><td class="wb">Сообщения</td><td class="wb">Буквы</td><td class="wb">Последнее</td></tr>';
	i = 0;
	for(k in Members) {
		t += '<tr bgcolor="'+(i%2 == 0 ? '#ffffff' : '#e8f6e8')+'"><td class="wb">'+(i+1)+'</td><td class="wb"><a href="/info.php?id='+k+'" style="color: ';
		t += Members[k][1]+Members[k][2] == 0 ? '#f60000' : (avantGarde(0, k, 1) || avantGarde(0, k, 2) || avantGarde(0, k, 3) ? '#0000aa': '');
		t += '">'+Members[k][0]+'</a></td><td class="wb"'+(avantGarde(0, k, 1) ? ' style="font-style: oblique"' : '')+'>'+Members[k][1]+'</td>';
		t += '<td class="wb"'+(avantGarde(0, k, 2) ? ' style="font-style: oblique"' : '')+'>'+Members[k][2]+'</td>';
		t += '<td class="wb"'+(avantGarde(0, k, 3) ? ' style="font-style: oblique"' : '')+'>'+Members[k][3]+'</td>';
		t += '<td class="wb"><a href="'+Members[k][4]+'">'+Members[k][5]+'</a></td></tr>';
		i++;
	}
	t += '</table>';
	tmp = document.querySelector('table[width="100%"][cellspacing="1"]');
	tmp.cellSpacing = 0;
	with(tmp.style) { width = '50%'; margin = '5px 25%'; borderCollapse = 'collapse'; }
	tmp.innerHTML = t;
}

})();
