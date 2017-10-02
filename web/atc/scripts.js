$(function() {

var clc = $('.clc');
var num = $('[name="num"]');
var opt = ['nun', 'vrb', 'adj', 'adv'];
var otp = [];
var rgt = $('#rgt');
var rsl = $('#rsl');
var sbc = $('.sbm');
var sbm = $('[type="button"][value="Submit"]');
var sbt = $('[type="button"][value="Save"]');
var txt = $('#txt');
sbt.click(function() {
	otp = translateText();
	if(otp[1]>1) {
		rsl.html(otp[0]);
		sbt.hide();
		sbc.show();
		txt.toggle();
		rsl.toggle();
		num.attr('max', otp[2]);
		num.val(num.attr('value')); //this is to fight browser caching
		if(Number(num.val())>otp[2]) { num.val(otp[2]); }
		rgt.children('p').toggle();
		rgt.find('div:not([class="clc"])').toggle();
		$('#nun').prop('checked', true); //browser caching as well
		$('.cls:first-child').show();
		$('#nun, #vrb, #adj, #adv').change(function(e) { showCollection(e); });
		$('.wrd').click(function(e) { styleWord(e); });
	} else {
		alert('Working with less than two words makes no sense.');
	}
});
sbm.click(function() {
	if($('span.nun').length>1 || $('span.vrb').length>1 || $('span.adj').length>1 || $('span.adv').length>1) {
		sbc.hide();
		$.ajax('constructor.php', { beforeSend: function() { if(/^Pick one/gm.test($(clc[0]).text())) { $(clc[0]).text(''); } rsl.text('The data has been sent. Please wait.'); }, data: { adj: $(clc[2]).text(), adv: $(clc[3]).text(), num: num.val(), nun: $(clc[0]).text(), rsl: rsl.html(), sbm: $('input[name="sbm"]:checked').val(), vrb: $(clc[1]).text() }, error: function() { alert('Something went wrong.'); }, method: 'POST', success: function(d) { rsl.html(d); } });
	} else {
		alert('At least one of the groups has to have two or more words in it.');
	}
});

function showCollection(e) { //I know I could probably just use $(this) if I were lazy
	var trg = $(e.target);
	var tid = trg.is('[type="radio"]') ? trg.attr('id') : trg.attr('class');
	clc.filter(':visible').hide();
	$(clc[opt.indexOf(tid)]).show(); //.eq() could be used
}

function styleWord(e) {
	var rdc = $('[name="pos"]:checked').attr('id');
	var cnd = opt.filter(function(x) { return x!=rdc; });
	var wrd = $(e.target);
	if(!wrd.is('.'+cnd.join(', .'))) {
		wrd.toggleClass(rdc);
		var hlw = $('span.'+rdc);
		$(clc[opt.indexOf(rdc)]).text(hlw.length>0 ? hlw.map(function(i, x) { return $(x).text(); }).get().join(', ') : '');
	} else {
		alert('A word can only belong to one group at a time.');
	}
}
 
function translateText() {
	var msw = 0; //a counter for multisyllabic words
	var vol = 0; //how many vowel groups in a word
	var tel = 0; //trailing 'e' length
	var trl = 0; //final length of trn
	/* Explaining the one-liner:
	 * 1) take input value, replace unneeded characters
	 * 2) if a punctuation mark is not followed by whitespace - add it
	 * 3) if a punctuation mark is preceded by whitespace - remove it
	 * 4) remove consecutive occurences of the same punctuation mark
	 * 5) trim whitespace at beginning and end
	 * 6) split at whitespace
	 * 7) remove fragments with no letters or consisting entirely of hyphens/apostrophes
	 * 8) trim unnecessary apostrophes and hyphens from each fragment
	 * 9) remove empty fragments
	 * 10) insert a span tag around the word part of each fragment
	 */
	var trn = txt.val().replace(/[^-a-z'\s,.!?:;]/gim, '').replace(/[,.!?:;](?!\s)/gm, '$& ').replace(/\s+([,.!?:;])/gm, '$1').replace(/([-',.!?:;])(?=\1)/gim, '').trim().split(/\s+/).map(function(x) { if(!/[-a-z']/gi.test(x) || /^[-']+$/g.test(x)) { x = ''; } return x.replace(/^'(.+)'$/, '$1').replace(/^'/, '').replace(/^-|-$/g, ''); }).filter(function(x) { return x!=''; }).map(function(x) { vol = /[aeiouy]+/gi.test(x) ? x.match(/[aeiouy]+/gi).length : 0; tel = /e(?=[-']|$)/i.test(x) ? x.match(/e(?=[-']|$)/i).length : 0; msw += vol-tel>1 ? 1 : 0; return x.replace(/([-a-z']+)([,.!?:;]*)/gi, '<span class="wrd">$1</span>$2'); });
	trl = trn.length;
	return [trn.join(' ').trim(), trl, Math.floor(msw/2)]; //return translated text, how many words in it, maximum multisyllabic words per line for poetry mode
}

});
