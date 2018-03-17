//Based on https://github.com/brianhouse/bjorklund/blob/master/__init__.py

function calc(e) {

var stp = Math.round(Number(document.querySelector('#stp').value));
var pls = Math.round(Number(document.querySelector('#pls').value));
var rsl = '';
e.preventDefault();
if(stp<pls) { alert('The 1st number should be greater or equal to the 2nd.'); return false; }
rsl = bjorklund(stp, pls).join('&nbsp;');
document.querySelector('#out').innerHTML = rsl;

function bjorklund(steps, pulses) {
	var divisor = steps-pulses;
	var level;
	var remainder = [];
	var count = [];
	var pattern = [];
	var o = 0;
	remainder[0] = pulses;
	for(level=0; remainder[level]>1; level++) {
		count[level] = Math.floor(divisor/remainder[level]);
		remainder[level+1] = divisor%remainder[level];
		divisor = remainder[level];
	}
	count[level] = divisor;
	build(level);
	o = pattern.indexOf(1);
	pattern = (pattern.slice(o)).concat(pattern.slice(0, o)); //0_o
	return pattern;
	
	function build(level) {
		if(level == -1) {
			pattern.push(0);
		} else if(level == -2) {
			pattern.push(1);
		} else {
			for(var i=0; i<count[level]; i++) { build(level-1); }
			if(remainder[level] != 0) { build(level-2); }
		}
	}
}

}
