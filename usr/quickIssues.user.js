// ==UserScript==
// @name             Quick Issues [NS]
// @description      Dump your memory right away.
// @include			 *nationstates.net/page=*_dilemma/dilemma=*
// @version          1.27
// ==/UserScript==

(function() {

var lh = location.href;
if(!/nationstates\.net\/page=\w+?_dilemma\/dilemma=/.test(lh)) { return false; }

var btnDismiss = document.querySelector('button[name="choice--1"]');
var papers = document.querySelector('#dilemmapapers');
if(btnDismiss && !/error/.test(btnDismiss.parentNode.className)) {
	var issue = lh.match(/\d+$/)[0];
	var nation = document.querySelector('[class$="nation"] a[href*="nation="]').href.match(/nation=(\w+)/)[1];
	var strData = localStorage.getItem('strIssueData');
	var Data = strData ? JSON.parse(strData) : {};
	var Issues = Data.hasOwnProperty(nation) ? Data[nation] : {};
	var choice = Issues.hasOwnProperty(issue) ? Issues[issue] : '';
	var gear = document.createElement('img');
	var settings = document.createElement('div');
	var txtSettings = document.createElement('textarea');
	var btnSave = createButton('Save', 'button big icon approve', save);
	var btnClose = createButton('Close', 'button big icon remove', display);
	var answer = document.querySelector('button[name="choice-'+choice+'"]');
	if(choice != '' && answer) { answer.style.border = '4px solid #009900'; }
	[].forEach.call(document.querySelectorAll('button[name^="choice-"]'), function(x) {
		x.addEventListener('click', function() {
			answer = x.name.match(/choice-(.+)/)[1];
			if(choice != answer) {
				Issues[issue] = answer;
				Data[nation] = Issues;
				localStorage.setItem('strIssueData', JSON.stringify(Data));
				strData = localStorage.getItem('strIssueData');
			}
		}, false);
	});
	gear.src = '/images/trophies/gdp-10.png';
	gear.title = 'Quick Issues Settings';
	gear.style.cssText = 'border: 0; cursor: pointer; float: left;';
	txtSettings.value = !strData ? '{"'+nation+'":{}}' : strData;
	txtSettings.style.cssText = 'min-width: 92%; max-width: 98%; height: 500px; margin-bottom: 8px;';
	settings.appendChild(txtSettings);
	settings.appendChild(btnSave);
	settings.appendChild(btnClose);
	settings.style.cssText = 'width: 48%; position: fixed; left: 15%; top: 15%; background-color: #d0eed0; border: 4px solid #009900; border-radius: 4px; padding: 8px; opacity: 0.9; z-index: 1; box-shadow: 8px 8px 4px rgba(50, 50, 50, 0.5); display: none;';
	document.body.appendChild(settings);
	btnDismiss.parentNode.insertBefore(gear, btnDismiss);
	gear.addEventListener('click', display, false);
} else if(papers) {
	var details = document.querySelector('#toggleissuedetail button.active');
	document.querySelector('#panel').style.minHeight = '610px';
	papers.style.display = 'none';
	papers.previousElementSibling.style.display = 'none';
	if(details) {
		details.className = 'button';
		details.nextElementSibling.className = 'button active';
		[].forEach.call(document.querySelectorAll('.wc-detail'), function(x) { x.style.display = 'inline-flex'; });
	}
} else {
	return false;
}

function createButton(value, strClass, callback) {
	var btn = document.createElement('button');
	btn.innerHTML = value;
	btn.className = strClass;
	btn.addEventListener('click', callback, false);
	return btn;
}

function display() { settings.style.display = settings.style.display == 'none' ? 'block' : 'none'; }

function save() {
	localStorage.setItem('strIssueData', txtSettings.value);
	display();
	location.reload();
}

})();
