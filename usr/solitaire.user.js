// ==UserScript==
// @name             Solitaire [NS]
// @description      I am not going to play Cookie Clicker when I play NationStates.
// @include			 *nationstates.net/page=deck*
// @version          2.04
// ==/UserScript==

(function() {

var lh = location.href;
if(!/nationstates\.net\/page=deck/.test(lh) || /\/nation=/.test(lh)) { return false; }
if(!document.querySelector('.deckcard-info')) { return false; }
if(!/\/card=\d+/.test(lh)) {
	[].forEach.call(document.querySelectorAll('.deckcard-info'), function(v) { v.classList.add('show'); });
}
var sp = document.querySelector('.auction-self-price');
if(sp) {
	if(sp.classList.contains('auction-self-unmatched')) {
		alert('Someone outbid you!');
	} else {
		setTimeout(function() { location.reload(); }, getRandomInt(5000, 59000));
	}
}

function getRandomInt(a, b) { return Math.floor(Math.random()*(b-a+1))+a; }

})();
