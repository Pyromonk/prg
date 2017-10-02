// ==UserScript==
// @name             Advanced Syndicate List [GW]
// @description      Неудобно мне среди более чем ста синдикатов свои деньги искать и угадывать, откуда меня выгнали.
// @include			 http://*ganjawars.ru/syndicates.php*
// @version          2.09
// ==/UserScript==

(function() {

if(!/ganjawars\.ru\/syndicates\.php/.test(location.href)) { return false; }
if(typeof localStorage == 'undefined') {
    alert('Ваш браузер не поддерживает localStorage.\nСкачайте Opera 10.60, Firefox 3.6.8, Chrome 5 или эти же браузеры более новых версий.');
	return false;
}

var i, l, s, t;
var sn = [].slice.call(document.querySelectorAll('table[class="wb"][width="600"] a[href*="/syndicate.php?id="]'));
sn.forEach(function(value, index, slf) { slf[index] = parseInt(value.href.match(/id=(\d+)/)[1]); });
var sl = localStorage.getItem('hsm') == null ? JSON.stringify(sn) : localStorage.getItem('hsm');
var na = [];

s = document.querySelectorAll('select[name="sid"] option');
if(s) { for(i=0, l=s.length; i<l; i++) { if(!/\/ \$0$/.test(s[i].innerHTML) && !/-- выберите --/.test(s[i].innerHTML)) {
	//s[i].style.backgroundColor = '#edd9e3';
	s[i].innerHTML = '--->        '+s[i].innerHTML+'</center>';
} } }

t = document.querySelector('table[class="wb"][width="600"] td');
t.innerHTML += '<br>';
if(sl == JSON.stringify(sn)) { t.innerHTML += 'Пока что Вас ниоткуда не выгоняли'; ls(); return; }
t.innerHTML += 'С последнего посещения Вас успели выгнать из:<br>';
sl = JSON.parse(sl);
for(i=0, l=sl.length; i<l; i++) { if(sn.indexOf(sl[i]) == -1) { na.push(sl[i]); } }
for(i=0, l=na.length; i<l; i++) { t.innerHTML += '<a href="http://www.ganjawars.ru/syndicate.php?id='+na[i]+'">#'+na[i]+'</a>'; if(i != l-1) { t.innerHTML += ', '; } }
ls();

function ls() { localStorage.setItem('hsm', JSON.stringify(sn)); }

})();
