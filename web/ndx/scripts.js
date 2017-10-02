$(function() { getCaption(); });

function getCaption() {
	$.ajax({ url: 'caption.php', dataType: 'html' }).done(function(d) {
		$('#caption').html($(d).find('#word_'+getRandomInt(0, 99)).html());
		setTimeout(getCaption, 1000*getRandomInt(4, 7));
	});
}

function getRandomInt(a, b) { return Math.floor(Math.random()*(b-a+1))+a; }

function lastMod() { document.querySelector('#info').innerHTML = (new Date(document.lastModified)).toISOString().replace(/t/gi, ' ').replace(/\.\d+z/gi, ' (UTC)'); }
