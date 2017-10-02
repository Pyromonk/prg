function calc(e) {
	var dlt, rgx, tmp;
	var bln = 1;
	var Num = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
	var tbl = document.querySelector('#out');
	var val = document.querySelector('#inp [type="text"]').value;
	var Prm = []; //prime
	var Rtr = []; //retrograde
	var Inv = []; //inversion
	var Rin = []; //retrograde inversion
	var Inr = []; //inverse retrograde
	e.preventDefault();
	Num.forEach(function(x) {
		rgx = new RegExp('(^| )'+x+'( |$)', 'g');
		if(!rgx.test(val)) { bln = 0; }
	});
	if(!bln) { alert('Every integer should be present.'); return bln; }
	Prm = val.trim().split(/\s+/);
	if(Prm.length > 12) { alert('There cannot be more than 12 numbers in the input.'); return 0; }
	Prm.forEach(function(x, i, a) { a[i] = +x; });
	Rtr = Prm.slice().reverse();
	Prm.forEach(function(x, i, a) {
		if(!i) {
			tmp = x;
		} else {
			dlt = x-a[0];
			tmp = a[0]-dlt;
			tmp = tmp<0 ? 12+tmp : (tmp>=12 ? tmp%12 : tmp);
		}
		Inv.push(tmp);
	});
	Rin = Inv.slice().reverse();
	Rtr.forEach(function(x, i, a) {
		if(!i) {
			tmp = x;
		} else {
			dlt = x-a[0];
			tmp = a[0]-dlt;
			tmp = tmp<0 ? 12+tmp : (tmp>=12 ? tmp%12 : tmp);
		}
		Inr.push(tmp);
	});
	document.querySelector('#inp').style.display = 'none';
	tmp = '<tr><th>Prime</th><td>'+Prm.join('</td><td>')+'</td></tr>';
	tmp += '<tr><th>Retrograde</th><td>'+Rtr.join('</td><td>')+'</td></tr>';
	tmp += '<tr><th>Inversion</th><td>'+Inv.join('</td><td>')+'</td></tr>';
	tmp += '<tr><th>Retrograde Inversion</th><td>'+Rin.join('</td><td>')+'</td></tr>';
	tmp += '<tr><th>Inverse Retrograde</th><td>'+Inr.join('</td><td>')+'</td></tr>';
	tbl.innerHTML = tmp;
	tbl.style.display = 'table';
}
