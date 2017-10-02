// ==UserScript==
// @name             Laboratory Assistant [NS]
// @description      Tells you when to empty the test tube.
// @include			 *nationstates.net/*page=nukes*
// @version          1.20
// ==/UserScript==

(function() {

var prd = document.querySelector('a.nukestat-production');
var cmn = document.querySelector('a.nukestat-incoming');
var mnt = document.querySelector('[class$="nation"] a[href*="nation="]').href.match(/nation=(\w+)/)[1] == document.querySelector('h1.nukeh1 a.nlink').href.match(/nation=(\w+)/)[1] ? 1 : 0;
var lhr = location.href;
if(!/nationstates\.net.*\/page=nukes/.test(lhr) || !prd || !cmn || !mnt) { return false; }
setTimeout(function() { location.href = location.href; }, getRandomInt(50000, 70000));
prd = +prd.textContent.match(/\d+/)[0];
cmn = +cmn.textContent.match(/\d+/)[0];
document.title = prd>43 ? '[Pr.: '+prd+']' : 'Pr.: '+prd;
if(cmn>0) { document.title = '↓ '+document.title; }
if(/view=production/.test(lhr)) {
	var amn = 0;
	var prc = 1;
	var nms = [];
	[].forEach.call(document.querySelectorAll('button[name="convertproduction"]'), function(x) {
		nms = x.innerHTML.match(/\d+/g);
		amn = +nms[0];
		prc = +nms[1];
		x.innerHTML += '<br><span class="smalltext" style="color: #2d8659; font-weight: bold;">'+roundNumber(amn/prc, 2)+'</span>';
	});
}

function getRandomInt(a, b) { return Math.floor(Math.random()*(b-a+1))+a; }

function roundNumber(num, scale) {
	if(!(''+num).includes('e')) { return +(Math.round(num+'e+'+scale)+'e-'+scale); }
	var arr = (''+num).split('e');
	var sig = '';
	if(+arr[1]+scale > 0) { sig = '+'; }
	return +(Math.round(+arr[0]+'e'+sig+(+arr[1]+scale))+'e-'+scale);
}

})();
