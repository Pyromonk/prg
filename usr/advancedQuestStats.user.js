// ==UserScript==
// @name             Advanced Quest Stats [GW]
// @description      Статистика взятых квестов.
// @include          http://*ganjawars.ru/npc.php?id=*
// @version          2.62
// ==/UserScript==

(function() {

if(location.href.indexOf('ganjawars.ru/npc.php') == -1) { return false; }
if(typeof localStorage == 'undefined') {
    alert('Ваш браузер не поддерживает localStorage.\nСкачайте Opera 10.60, Firefox 3.6.8, Chrome 5 или эти же браузеры более новых версий.');
	return false;
}

var root = typeof unsafeWindow != 'undefined' ? unsafeWindow : window;
var tables = root.document.getElementsByTagName('table');
var QuestStats; var strQuestStats; var NPC = parseFloat(getNPC());
if(localStorage.getItem('questStats') == null) {
	/* 0 - статистика по всем NPC; 1-20 - статистика по каждому отдельному NPC.
	0 - имя; 1 - статус с NPC; 2 - выполнено; 3 - отказов; 4 - провалено; 5-16 - число квестов каждого типа */
	QuestStats = {0: {0: 'Все вместе', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  1: {0: 'Smokie Canablez', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  2: {0: 'Hempy Trown', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  3: {0: 'Rusty Reefer', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  4: {0: 'Kenny Buzz', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  5: {0: 'Yoshinori Watanabe', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  6: {0: 'Donnie Ray', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  7: {0: 'Rony James', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  8: {0: 'Ricardo Gonzalez', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  9: {0: 'Tommy Morales', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  10: {0: 'Inamoto Kanushi', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  11: {0: 'Tony Brandino', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  12: {0: 'John Moretti', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  16: {0: 'Takeshi Yamagata', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  17: {0: 'Michael Doyle', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  18: {0: 'Alfonso Morales', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  19: {0: 'Roy Fatico', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0},
				  20: {0: 'Giovanni Greco', 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0, 13: 0, 14: 0, 15: 0, 16: 0}};
	strQuestStats = JSON.stringify(QuestStats);
	localStorage.setItem('questStats', strQuestStats);
}
QuestStats = JSON.parse(localStorage.getItem('questStats'));
QuestStats[NPC][1] = getNPCStatus(); QuestStats[NPC][2] = getCompletedNum();
QuestStats[NPC][3] = getRejectedNum(); QuestStats[NPC][4] = getFailedNum();
updateAllStats();

main();

function main() {

// Настройки
var expQuests = 0; //0 - выключить квесты на боевой опыт, 1 - включить
var lightVersion = 0; //0 - выключить кнопку нападения на NPC, 1 - включить
// ---------

var strAvailable = 'Вы находитесь в одном секторе';
var strUnavailable = 'Вы находитесь в другом секторе';
var strDumbFool = 'не хочет с Вами разговаривать';
var strGoodBye = 'Завершить диалог';
/* 0 - пистолеты, 1 - гранаты, 2 - автоматы, 3 - пулемёты, 4 - дробовики, 5 - снайперки, 6 - опыт за опыт, 7 - опыт за предмет из гос. магазина,
8 - эконом за лут, 9 - трезвость, 10 - эконом за предмет определённой прочности, 11 - уличные */
var QuestTypes = {0: 'владения пистолетами',
				  1: 'владения гранатометами',
				  2: 'у моего тепловизора села батарея',
				  3: 'нам очень нужен уран',
				  4: 'отпускать без гостинцев',
				  5: 'можешь посидеть со мной в укрытии',
				  6: 'нам нужны толковые люди',
				  7: 'У моего друга скоро день рождения',
				  8: 'Outland',
				  9: 'А мы тут культурно отдыхаем',
				  10: 'Требуется именно такая прочность',
				  11: 'бонус умений на пару часов'};
var guts = document.getElementsByTagName('body')[0].innerHTML;
var conversationBox = tables[10].rows[1].cells[0];
var holder = document.createElement('div'); var tblStats = getStatsTable(); var txtStats = getStatsText();
var imgSvd = tables[6].rows[1].cells[0].getElementsByTagName('img')[0]; imgSvd.style.cursor = 'pointer'; imgSvd.title = 'Посмотреть статистику';
imgSvd.addEventListener('click', function() { holder.style.display = 'block'; }, false);
// Красота требует жертв :(
holder.setAttribute('style', 'width: 700px; position: absolute; top: 15%; left: 25%; background-color: #ecf8ec; border: 8px solid #d0eed0; -moz-border-radius: 16px; border-radius: 16px; padding: 15px; opacity: 0.9; z-index: 1; -moz-box-shadow: 10px 10px 5px rgba(50, 50, 50, 0.5); box-shadow: 10px 10px 5px rgba(50, 50, 50, 0.5); display: none');
holder.appendChild(tblStats);
var btnStats = createButton('Статистика', '#a6f1a6', '5px', '5px 3px');
with(btnStats.style) { position = 'relative'; left = '10px'; top = '5px'; }
var btnEdit = createButton('Редактировать', '#a6f1a6', '5px', '5px 3px');
with(btnEdit.style) { position = 'relative'; left = '5px'; top = '5px'; }
var btnClose = createButton('Закрыть', '#a6f1a6', '5px', '5px 3px');
with(btnClose.style) { position = 'relative'; left = '10px'; top = '5px'; }
btnClose.addEventListener('click', function() { holder.style.display = 'none'; }, false);
holder.appendChild(btnEdit); holder.appendChild(btnClose);
btnStats.addEventListener('click', function() { holder.replaceChild(tblStats, txtStats);holder.replaceChild(btnEdit, btnStats); }, false);
btnEdit.addEventListener('click', function() { holder.replaceChild(txtStats, tblStats); holder.replaceChild(btnStats,btnEdit); }, false);
document.getElementsByTagName('body')[0].appendChild(holder);

conversationBox.style.textAlign = 'center';
conversationBox.style.padding = '10px';

// Код специально писал через жопу, чтобы не тырили, да
if(lightVersion == 0 && guts.indexOf(strAvailable) != -1) {
	conversationBox.innerHTML = '';
	var btnTalk = createButton('Разговаривать', '#d2a0ba', '5px', '5px 3px');
	btnTalk.addEventListener('click', function() { root.location.href = 'http://www.ganjawars.ru/npc.php?id=' + getNPC() + '&talk=1'; }, false);
	conversationBox.appendChild(btnTalk);
}
else if(lightVersion == 0 && guts.indexOf(strUnavailable) != -1) { conversationBox.innerHTML = 'Переместитесь в нужный сектор.'; }
else if(lightVersion == 0 && guts.indexOf(strDumbFool) != -1) { conversationBox.innerHTML = 'Неразговорчивый попался.'; }
else if(lightVersion == 0 && guts.indexOf(strGoodBye) != -1) {
	conversationBox.style.padding = '0px';
	tables[11].rows[1].cells[1].innerHTML = '';
	var btnGoodBye = createButton('До свидания', '#d2a0ba', '5px', '5px 3px');
	btnGoodBye.addEventListener('click', function() { root.location.href = 'http://www.ganjawars.ru/npc.php?id=' + getNPC(); }, false);
	tables[11].rows[1].cells[1].appendChild(btnGoodBye);
}
else {
	if(lightVersion == 0) {
		conversationBox.style.padding = '0px';
		tables[11].rows[1].cells[1].innerHTML = ''; tables[11].rows[2].cells[0].innerHTML = '';
		var btnYes = createButton('Да', '#4dbf4d', '5px', '5px 3px'); btnYes.style.width = '80px';
		var btnNo = createButton('Нет', '#ff7575', '5px', '5px 3px'); btnNo.style.width = '80px';
		btnYes.addEventListener('click', function() { root.location.href = 'http://www.ganjawars.ru/npc.php?id=' + getNPC() + 
							'&talk=1&action_submit=yes'; }, false);
		btnNo.addEventListener('click', function() { root.location.href = 'http://www.ganjawars.ru/npc.php?id=' + getNPC() + 
							'&talk=1&action_submit=no'; }, false);
		if(expQuests == 0 && (guts.indexOf(QuestTypes[6]) != -1 || guts.indexOf(QuestTypes[7]) != -1)) { btnYes.disabled = true; }
		tables[11].rows[1].cells[1].appendChild(btnYes); tables[11].rows[1].cells[1].appendChild(btnNo);
	}
	for(var i = 0; i < 12; i++) { if(guts.indexOf(QuestTypes[i]) != -1) { QuestStats[NPC][i+5]++; QuestStats[0][i+5]++; break; } }
}

strQuestStats = JSON.stringify(QuestStats);
localStorage.setItem('questStats', strQuestStats);

}

function createButton(value, bgColour, margin, padding) {
	var btn = document.createElement('input');
	btn.type = 'button';
	btn.value = value;
	btn.style.backgroundColor = bgColour;
	btn.style.margin = margin;
	btn.style.padding = padding;
	btn.style.cursor = 'pointer';
	return btn;
}

function getStatsTable() {
	var tbl = document.createElement('table'); tbl.className = 'wb';
	with(tbl.style) { width = '700px'; textAlign = 'center'; margin = '0px auto'; }
	tbl.innerHTML = '<tr bgcolor="#d0eed0"><td class="wb"> </td><td class="wb">Статус</td>' +
	'<td class="wb">Выполнено</td><td class="wb">Отказов</td><td class="wb">Провалено</td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/wargroup/skill_combat_pistols.gif" title="Умение пистолетами" /></td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/wargroup/skill_combat_explosives.gif" title="Умение гранатами" /></td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/wargroup/skill_combat_auto.gif" title="Умение автоматами" /></td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/wargroup/skill_combat_heavy.gif" title="Умение пулемётами" /></td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/wargroup/skill_combat_sgun.gif" title="Умение дробовиками" /></td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/wargroup/skill_combat_snipe.gif" title="Умение снайперскими винтовками" /></td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/home/cashlog.gif" title="Опыт за опыт" /></td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/home/friends.gif" title="Опыт за предмет" /></td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/home/market.gif" title="Эконом за лут" /></td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/home/farm.gif" title="Трезвость" /></td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/home/shop.gif" title="Эконом за предмет" /></td>' +
	'<td class="wb"><img src="http://images.ganjawars.ru/i/home/bank.gif" title="Уличные" /></td></tr>';
	var questStatsSum; var questTypeSum;
	for(var i = 1; i < 13; i++) {
		questStatsSum = QuestStats[i][2] + QuestStats[i][3] + QuestStats[i][4];
		questTypeSum = QuestStats[i][5] + QuestStats[i][6] + QuestStats[i][7] + QuestStats[i][8] + QuestStats[i][9] + QuestStats[i][10] + 
					   QuestStats[i][11] + QuestStats[i][12] + QuestStats[i][13] + QuestStats[i][14] + QuestStats[i][15] + QuestStats[i][16];
		if(questStatsSum == 0) { questStatsSum = 1; } if(questTypeSum == 0) { questTypeSum = 1; }
		tbl.innerHTML += '<tr><td class="wb">' + QuestStats[i][0] + '</td><td class="wb">' + QuestStats[i][1] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][2]/questStatsSum).toFixed(2) + '%">' + QuestStats[i][2] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][3]/questStatsSum).toFixed(2) + '%">' + QuestStats[i][3] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][4]/questStatsSum).toFixed(2) + '%">' + QuestStats[i][4] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][5]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][5] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][6]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][6] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][7]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][7] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][8]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][8] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][9]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][9] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][10]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][10] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][11]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][11] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][12]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][12] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][13]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][13] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][14]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][14] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][15]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][15] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][16]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][16] +
						 '</td></tr>';
	}
	for(var i = 16; i < 21; i++) {
		questStatsSum = QuestStats[i][2] + QuestStats[i][3] + QuestStats[i][4];
		questTypeSum = QuestStats[i][5] + QuestStats[i][6] + QuestStats[i][7] + QuestStats[i][8] + QuestStats[i][9] + QuestStats[i][10] + 
					   QuestStats[i][11] + QuestStats[i][12] + QuestStats[i][13] + QuestStats[i][14] + QuestStats[i][15] + QuestStats[i][16];
		if(questStatsSum == 0) { questStatsSum = 1; } if(questTypeSum == 0) { questTypeSum = 1; }
		tbl.innerHTML += '<tr><td class="wb">' + QuestStats[i][0] + '</td><td class="wb">' + QuestStats[i][1] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][2]/questStatsSum).toFixed(2) + '%">' + QuestStats[i][2] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][3]/questStatsSum).toFixed(2) + '%">' + QuestStats[i][3] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][4]/questStatsSum).toFixed(2) + '%">' + QuestStats[i][4] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][5]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][5] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][6]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][6] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][7]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][7] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][8]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][8] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][9]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][9] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][10]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][10] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][11]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][11] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][12]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][12] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][13]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][13] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][14]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][14] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][15]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][15] +
						 '</td><td class="wb" title="' + (100*QuestStats[i][16]/questTypeSum).toFixed(2) + '%">' + QuestStats[i][16] +
						 '</td></tr>';
	}
	questStatsSum = QuestStats[0][2] + QuestStats[0][3] + QuestStats[0][4];
	questTypeSum = QuestStats[0][5] + QuestStats[0][6] + QuestStats[0][7] + QuestStats[0][8] + QuestStats[0][9] + QuestStats[0][10] + 
					   QuestStats[0][11] + QuestStats[0][12] + QuestStats[0][13] + QuestStats[0][14] + QuestStats[0][15] + QuestStats[0][16];
	if(questStatsSum == 0) { questStatsSum = 1; } if(questTypeSum == 0) { questTypeSum = 1; } // Тоже криво как-то... Три раза одно и то же
	tbl.innerHTML += '<tr bgcolor="#dee3de"><td class="wb">' + QuestStats[0][0] + '</td><td class="wb">' + QuestStats[0][1] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][2]/questStatsSum).toFixed(2) + '%">' + QuestStats[0][2] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][3]/questStatsSum).toFixed(2) + '%">' + QuestStats[0][3] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][4]/questStatsSum).toFixed(2) + '%">' + QuestStats[0][4] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][5]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][5] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][6]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][6] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][7]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][7] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][8]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][8] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][9]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][9] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][10]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][10] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][11]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][11] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][12]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][12] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][13]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][13] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][14]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][14] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][15]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][15] +
					 '</td><td class="wb" title="' + (100*QuestStats[0][16]/questTypeSum).toFixed(2) + '%">' + QuestStats[0][16] +
					 '</td></tr>';
	return tbl;
}

function getStatsText() {
	var tbl = document.createElement('table'); tbl.className = 'wb';
	with(tbl.style) { width = '700px'; margin = '0px auto'; }
	var row = document.createElement('tr');
	var data = document.createElement('td'); data.rowSpan = '2';
	var txt = document.createElement('textarea'); with(txt.style) { width = '350px'; height = '200px'; }
	txt.value = JSON.stringify(QuestStats);
	data.appendChild(txt); row.appendChild(data);
	data = document.createElement('td'); data.style.padding = '5px';
	data.innerHTML = 'При очистке временных файлов браузера, можно случайно удалить и данные скрипта.<br />'
					 + 'Используйте этот функционал для восстановления утерянных данных.<br />'
					 + 'Так же стоит отметить, что Вы, конечно, можете тут сохранить любое говно, какое душе угодно, но не удивляйтесь, '
					 + 'если из-за этого что-то перестанет работать.';
	row.appendChild(data); tbl.appendChild(row);
	row = document.createElement('tr'); data = document.createElement('td');
	with(data.style) { padding = '5px'; textAlign = 'center'; }
	var btn = createButton('Сохранить', '#ff7575', '5px', '5px 3px');
	btn.addEventListener('click', function() { localStorage.setItem('questStats', txt.value); location.reload(); }, false);
	data.appendChild(btn); row.appendChild(data); tbl.appendChild(row);
	return tbl;
}

function getNPC() {
	var npcNum = /npc.php\?id=(\d*)/.exec(root.location.href);
	return npcNum[1];
}

function getNPCStatus() {
	var bees = tables[8].getElementsByTagName('b');
	return parseFloat(bees[0].innerHTML);
}

function getCompletedNum() {
	var bees = tables[9].getElementsByTagName('b');
	return parseFloat(bees[0].innerHTML);
}

function getRejectedNum() {
	var bees = tables[8].getElementsByTagName('b');
	return parseFloat(bees[1].innerHTML);
}

function getFailedNum() {
	var bees = tables[9].getElementsByTagName('b');
	return parseFloat(bees[1].innerHTML);
}

function updateAllStats() {
	for(var i = 1; i < 17; i++) {
		QuestStats[0][i] = 0;
		for(var j = 1; j < 13; j++) { QuestStats[0][i] += QuestStats[j][i]; }
		for(var j = 16; j < 21; j++) { QuestStats[0][i] += QuestStats[j][i]; }
	}
	QuestStats[0][1] = QuestStats[0][1].toFixed(2);
}

})();
