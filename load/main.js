/** 
 * KIMB-Forms-Project
 * https://github.com/KIMB-technologies/KIMB-Forms-Project
 * 
 * (c) 2018 - 2020 KIMB-technologies 
 * https://github.com/KIMB-technologies/
 * 
 * released under the terms of GNU Public License Version 3
 * https://www.gnu.org/licenses/gpl-3.0.txt
 */

(function (){ // load marked and style custom
	var markRend = new marked.Renderer();
	markRend.heading = function (text, level) {
		return '<h' + (level + 2) + '>' + text + '</h' + (level + 2) + '>';
	}
	markRend.link = function (href, title, text) {
		if( typeof title !== "string" ){
			title = 'Open external link.'
		}
		return '<a href="' + href + '" title="' + title + '" target="_blank">' + text + '</a>';
	}
	marked.setOptions({
		renderer: markRend,
		gfm: true,
		tables: false,
	});
})();

/**
 * Markdown Parsing
 * @param {*string} toParse the markdown string
 * @return the output as html
 */
function md_parser(toParse){
	return marked(toParse);
}

/**
 * Switch on Template
 */
$( function (){
	template_main();
	
	if(typeof template_name !== "undefined"){
		switch(template_name){
			case "new":
				template_new();
				break;
			case "start":
				template_start();
				break;
			case "poll":
				template_poll();
				break;
			case "admin":
				template_admin();
				break;
			case "submissionquery":
				template_submissionquery();
				break;
			default:
				break;
		}
	}

	if( typeof on_site_loaded === "function" ){ // after init callback?
		on_site_loaded();
	}
});

/**
 * Do for each Template
 */
function template_main(){
	$(document).tooltip();
	if( $( ".collapseContent" ).length > 0 ){
		$( ".collapseContent .card-header" ).click( function() {
			var el = $(this).parent().find(".card-text");
			if( el.css('display') == 'none' ){
				el.show("blind", 250);
			}
			else{
				el.hide("blind", 250);
			}
		});
		$( ".collapseContent .card-text" ).hide("blind", 1);
	}
	$("div.parseasmarkdown").each( (k,v) =>{
		$( v ).html( md_parser( $( v ).text().trim() ) );
	});

	$( "div#languagebuttons button" ).click(function (){
		window.location.href = $(this).attr('linkdest');
	});
}

function template_new(){
	function personMeetingUpdate(){
		if( $("input[name=formtype]:checked").val() === "meeting" ){
			$( ".persononly" ).hide();
		}
		else{
			$( ".persononly" ).show();
		}
	}
	$( "input[name=formtype]" ).change( personMeetingUpdate );

	function weitererTermin() {
		$("div#add-more-here").append(
			'<div class="row align-items-start">'
			+ $( "#examplecontainer" ).html()
			+ '</div>'
		);
		personMeetingUpdate();
		$("div.laufindex" ).last().text( ++laufindex );
		$("input[type=text], input[type=number], textarea").unbind("change").change(save);
	}
	$("button#new-weiterer").click( weitererTermin );

	function loadSaved(){
		var data = JSON.parse( localStorage.getItem( "newPollData" ) );
		while( data.lauf > laufindex ){
			weitererTermin();
		}

		$("input[name=formtype][value="+ data.formtype +"]").prop('checked', true)
		personMeetingUpdate();

		$("input[type=text]:not(.nolocalsave)").each((k,v) => {
			$( v ).val( data["inputsText"][k] );
		});
		$("input[type=number]:not(.nolocalsave)").each((k,v) => {
			$( v ).val( data["inputsNum"][k] );
		});
		$("textarea:not(.nolocalsave)").each((k,v) => {
			$( v ).val( data["textAr"][k] );
		});
	}
	if( localStorage.hasOwnProperty("newPollData") ){
		loadSaved();
	}

	function save(){
		var data = {
			"inputsText" : [],
			"inputsNum" : [],
			"textAr" : [],
			"lauf" : laufindex,
			"formtype" : $("input[name=formtype]:checked").val()
		};
		$("input[type=text]:not(.nolocalsave)").each((k,v) => {
			data["inputsText"][k] = $( v ).val();
		});
		$("input[type=number]:not(.nolocalsave)").each((k,v) => {
			data["inputsNum"][k] = $( v ).val();
		});
		$("textarea:not(.nolocalsave)").each((k,v) => {
			data["textAr"][k] = $( v ).val();
		});
		localStorage.setItem( "newPollData", JSON.stringify( data ) );
	}
	$("input[type=text], input[type=number], textarea").change(save);

	function easyInput(){
		$("div#easyinputdialog").removeClass('d-none');
		$( "div#easyinputdialog" ).dialog({
			resizable: true,
			height: "auto",
			width:  Math.min($(window).width(), 600),
			modal: true,
			buttons: [
				{
					text: "OK",
					icon: "ui-icon-check",
					click: function() {
						//load form input and 
						var type = $("select#valuetype").val();
						var values = $("textarea#value").val();
						values = values.split( /\r|\n/ );
						while( values.length > laufindex ){
							weitererTermin();
						}
						$("input[name='" + type + "[]'], textarea[name='" + type + "[]']").each( (k,v) => {
							$(v).val( values[k] );
						});
						// close dialog
						$("div#easyinputdialog").addClass('d-none');
						$( this ).dialog( "close" );
					}
				}
			]
		});
	}
	$("button#easyinput").click(easyInput);
}

function template_start(){
	$("button#newlos").click(function(){
		window.location.href = template_data["los"];
	});
	$("button#polllos").click(function(){
		var id = $("input#pollid").val();
		window.location.href = template_data["poll"].replace('<poll>', id);
	});
	$("button#adminlos").click(function(){
		var code = $("input#admincode").val();
		window.location.href = template_data["admin"].replace('<admin>', code);
	});
	$("input#pollid, input#admincode").keypress(function(e) {
		if(e.which == 13) { // Enter
			$( "button#" + ( $(this).attr("id") == "pollid" ? "polllos" : "adminlos" ) ).click();
		}
	});
}

function template_poll(){
	function loadSaved(){
		var data = JSON.parse( localStorage.getItem( "pollPollData" ) );
		$("input[name=name]").val( data.username );
		$("input[name=email]").val( data.usermail );

		if( localStorage.hasOwnProperty("pollPollDateData") ){
			var wahl = JSON.parse( localStorage.getItem( "pollPollDateData" ) );
			if( wahl.hasOwnProperty( pollid ) ){
				$( "input.terminwahl" ).each( (k, v) => {
					$(v).prop('checked', wahl[pollid][k]);
				});
				Object.keys(wahl[pollid]).forEach(function(k) {
					if( !$.isNumeric(k) ){
						if( typeof wahl[pollid][k] === "string" ){
							$("input.othersave[name="+ k +"]").val(wahl[pollid][k]);
						}
						else{
							$("input.othersave[name="+ k +"]").prop('checked', wahl[pollid][k]);
						}		
					}
				});
			}
		}
	}
	if( localStorage.hasOwnProperty("pollPollData") ){
		loadSaved();
	}

	function loadSubmittedPolls(){
		var json = JSON.parse(localStorage.getItem('pollsubmissons'));
		json = json[pollid] || null;
		if( json !== null ){
			$( "input.terminwahl" ).each( (k,e) => {
				var id = parseInt($(e).attr('name').replace(/[^0-9]/g,''));
				if(json.hasOwnProperty(id)) {
					$('button.deletemy[termid="' + $(e).attr('name') + '"]').parent().removeClass('d-none');	
				}
			});

			$('button.deletemy').click(function () {
				var id = parseInt($(this).attr('termid').replace(/[^0-9]/g,''));
				$.post( deletesubmissonapi, { terminid : id, code : json[id] }, (data) => {
					if( data == 'ok' ){
						poll_submissions_delete_code_used(pollid, id);
						$(this).removeClass('btn-light');
						$(this).addClass('btn-success');
						$(this).prepend('&#x2714; ');
						window.location.reload();
					}
					else{
						$(this).removeClass('btn-light');
						$(this).addClass('btn-danger');
						$(this).prepend('&#x2718; ');
					}
				});
			});
		}
	}
	if( localStorage.hasOwnProperty("pollsubmissons") ){
		loadSubmittedPolls();
	}

	function save(){
		var data = {
			"username" : $("input[name=name]").val(),
			"usermail" : $("input[name=email]").val(),
		};
		localStorage.setItem( "pollPollData", JSON.stringify( data ) );

		var wahl = localStorage.hasOwnProperty("pollPollDateData") ? JSON.parse( localStorage.getItem( "pollPollDateData" ) ) : {};
		wahl[pollid] = {};
		$( "input.terminwahl" ).each( (k, v) => {
			wahl[pollid][k] = $(v).prop('checked');
		});
		$("input.othersave").each( (k, v) => {
			var name = $(v).attr('name');
			if( $(v).attr('type') == 'text' ){
				wahl[pollid][name] = $(v).val();
			}
			else{
				wahl[pollid][name] = $(v).prop('checked');
			}
		});
		localStorage.setItem( "pollPollDateData", JSON.stringify( wahl ) );
	}
	$("input[type=text], input[type=email], input.terminwahl, input.othersave").change(save);
}

function template_admin(){
	function deletePollSubmiss( type ){
		$( type == 'all' ? "div#deletepoll div.pollentire" : "div#deletepoll div.pollsubm" ).removeClass('d-none');
		$( type == 'all' ? "div#deletepoll div.pollsubm" : "div#deletepoll div.pollentire" ).addClass('d-none');
		$( "div#deletepoll" ).removeClass('d-none');
		$( "div#deletepoll" ).dialog({
			resizable: false,
			height: "auto",
			width: Math.min($(window).width(), 400),
			modal: true,
			buttons: [
				{
					text: "OK",
					icon: "ui-icon-check",
					click: function() {
						window.location.href = type == 'all' ? template_data.delallurl : template_data.delsuburl;
						$( this ).dialog( "close" );
					},
				},
				{
					text: "Cancel",
					icon: "ui-icon-close",
					click: function() {
						$( this ).dialog( "close" );
					},
				}
			]
		});
	}
	$("button#deleteerg").click( () => deletePollSubmiss( 'subm' )  );
	$("button#deleteall").click( () => deletePollSubmiss( 'all' )  );
	
	function refreshView( id ){
		window.location.href = template_data.polladmin + ( typeof id !== "undefined" ? '#' + id : '');
		window.location.reload();
	}

	function changeUmfrageMeta(){
		$( "div#editpoll" ).removeClass('d-none');
		$( "div#editpoll div.alert" ).addClass('d-none');
		$( "div#editpoll input.pollname" ).val( template_data.meta[0] );
		$( "div#editpoll textarea.description" ).val( template_data.meta[1] );
		$( "div#editpoll" ).dialog({
			resizable: true,
			height: "auto",
			width:  Math.min($(window).width(), 600),
			modal: true,
			buttons: [
				{
					text: "Save",
					icon: "ui-icon-disk",
					click: function() {
						$( "div#editpoll input.pollname" ).prop('disabled', true)
						$( "div#editpoll textarea.description" ).prop('disabled', true)
						$.post( template_data.editurl,
							{ "name" : $( "div#editpoll input.pollname" ).val(), "desc" : $( "div#editpoll textarea.description" ).val() },
							function (data){
								if( data == 'ok' ) {
									$( "div#editpoll div.alert" ).addClass('d-none');
									refreshView();
								}
								else{
									$( "div#editpoll div.alert" ).removeClass('d-none');
									$( "div#editpoll input.pollname" ).prop('disabled', false);
									$( "div#editpoll textarea.description" ).prop('disabled', false);
								}
							});
					},
				}
			]
		});
	}
	$("button#umfreditbutton").click( changeUmfrageMeta );

	function changeUmfrageTermin( terminid ){
		if( terminid == 'addadate'){
			template_data.terminmeta['addadate'] = ['', template_data.polltype == 'person' ? '' : false ,''];
		}
		$( "div#editdate" ).removeClass('d-none');
		$( "div#editdate div.alert" ).addClass('d-none');
		$( "div#editdate input.datename" ).val( template_data.terminmeta[terminid][0] );
		if(  template_data.terminmeta[terminid][1] === false ){
			$( "div#editdate input.personlim" ).addClass('d-none');
		}
		else{
			$( "div#editdate input.personlim" ).val( template_data.terminmeta[terminid][1] );
		}
		$( "div#editdate textarea.notes" ).val( template_data.terminmeta[terminid][2] );
		$( "div#editdate" ).dialog({
			resizable: true,
			height: "auto",
			width: Math.min($(window).width(), 600),
			modal: true,
			buttons: [
				{
					text: "Save",
					icon: "ui-icon-disk",
					click: function() {
						$( "div#editdate input.datename" ).prop('disabled', true)
						$( "div#editdate input.personlim" ).prop('disabled', true)
						$( "div#editdate textarea.notes" ).prop('disabled', true)
						
						$.post( template_data.editurl,
							{
								"name" : $( "div#editdate input.datename" ).val(),
								"termin" : terminid,
								"hinw"  : $( "div#editdate textarea.notes" ).val(),
								"anz"  : $( "div#editdate input.personlim" ).val(),
							},
							function (data){
								if( data == 'ok' ) {
									$( "div#editdate div.alert" ).addClass('d-none');
									refreshView( terminid );
								}
								else{
									$( "div#editdate div.alert" ).removeClass('d-none');
									$( "div#editdate input.datename" ).prop('disabled', false);
									$( "div#editdate input.personlim" ).prop('disabled', false);
									$( "div#editdate textarea.notes" ).prop('disabled',  false);
								}
							});
					},
				}
			]
		});
	}
	$("button.editbutton").click( function (){ changeUmfrageTermin( $(this).attr( 'id' ) ) } );

	function swapTermine(){
		$( "div#swapdate" ).removeClass('d-none');
		$( "div#swapdate div.alert" ).addClass('d-none');
		var opts = '';
		$.each( template_data.terminmeta, (k,v) =>{
			opts += '<option value="' + k + '">' + v[0] + '</option>';
		});
		$( "div#swapdate #swapA" ).html(opts);
		$( "div#swapdate #swapB" ).html(opts);
		$( "div#swapdate" ).dialog({
			resizable: true,
			height: "auto",
			width: Math.min($(window).width(), 600),
			modal: true,
			buttons: [
				{
					text: "Go",
					icon: "ui-icon-arrowthick-2-e-w",
					click: function() {
						$( "div#swapdate #swapA" ).prop('disabled', true);
						$( "div#swapdate #swapB" ).prop('disabled', true);
						var swapA = $( "div#swapdate #swapA" ).val();
						var swapB = $( "div#swapdate #swapB" ).val();
						$.post( template_data.editurl,
							{
								"name" : template_data.terminmeta[swapA][0],
								"termin" : swapB,
								"hinw"  : template_data.terminmeta[swapA][2],
								"anz"  : template_data.terminmeta[swapA][1],
							},
							function (data){
								if( data == 'ok' ) {
									$.post( template_data.editurl,
										{
											"name" : template_data.terminmeta[swapB][0],
											"termin" : swapA,
											"hinw"  : template_data.terminmeta[swapB][2],
											"anz"  : template_data.terminmeta[swapB][1],
										},
										function (data){
											if( data == 'ok' ) {
												$( "div#swapdate div.alert" ).addClass('d-none');
												refreshView('swapbutton');
											}
											else{
												$( "div#swapdate div.alert" ).removeClass('d-none');
												$( "div#swapdate #swapA" ).prop('disabled', false)
												$( "div#swapdate #swapB" ).prop('disabled', false)
											}
										});
								}
								else{
									$( "div#swapdate div.alert" ).removeClass('d-none');
									$( "div#swapdate #swapA" ).prop('disabled', false)
									$( "div#swapdate #swapB" ).prop('disabled', false)
								}
							});
					}
				}
			]
		});
	}
	$("button#swapbutton").click( swapTermine );
	if( !template_data.submissempty ){
		$("button#swapbutton").prop('disabled', true);
	}

	function saveEMailList(){
		$( "button#notifmailsSave" ).prop('disabled', true);
		$.post( template_data.editurl,
			{
				"maillist" : $("input#notifmailsList").val()
			},
			function (data){
				if( data == 'ok' ){
					$("button#notifmailsSave").removeClass('btn-light');
					$("button#notifmailsSave").addClass('btn-success');
					$("button#notifmailsSave").prepend('&#x2714; ');
					refreshView('notifmailsList');
				}
				else{
					$("button#notifmailsSave").removeClass('btn-light');
					$("button#notifmailsSave").addClass('btn-danger');
					$("button#notifmailsSave").prepend('&#x2718; ');
					$( "button#notifmailsSave" ).prop('disabled', false)
				}
			}
		);
	}
	$("button#notifmailsSave").click( saveEMailList );

	function saveAdditionalInputs(){
		var f = [];
		$("ul#listofadditionals li.additionals-element").each( (k,v) => {
			var data = $(v).attr('additionals-data');
			data = data.split(',');
			f.push({
				'type' : data[0],
				'require' : data[1] == 'true',
				'text' : $(v).find("span.additionals-name").text()
			});
		});
		
		$( "button#saveadditionalinputs" ).prop('disabled', true);
		$.post( template_data.editurl,
			{
				"additionals" : {
					"empty" : f.length == 0,
					"data" : f
				}
			},
			function (data){
				if( data == 'ok' ){
					$("button#saveadditionalinputs").removeClass('btn-light');
					$("button#saveadditionalinputs").addClass('btn-success');
					$("button#saveadditionalinputs").prepend('&#x2714; ');
					refreshView('saveadditionalinputs');
				}
				else{
					$("button#saveadditionalinputs").removeClass('btn-light');
					$("button#saveadditionalinputs").addClass('btn-danger');
					$("button#saveadditionalinputs").prepend('&#x2718; ');
					$( "button#saveadditionalinputs" ).prop('disabled', false)
				}
			}
		);
	}
	function addAdditionalInput(){
		var type = $("select#additionals-type").val();
		var require = $("select#additionals-req").val() == "req";
		var text = $("input#additionals-text").val();

		if( text == "" ){
			return;
		}

		$("ul#listofadditionals").append('<li class="list-group-item additionals-element" additionals-data="'
			+ type +','+ (require ? 'true' : 'false') +'"><span class="ui-icon ui-icon-'
			+ (type == 'text' ? 'pencil' : 'check') + '"></span> <span class="additionals-name">'
			+ text + '</span>' + (require ? ' *' : ' (optional)') 
			+ '<span class="ui-icon ui-icon-trash additionals-delete"></span></li>'
		);

		$("ul#listofadditionals li span.additionals-delete").unbind('click').click(removeAdditionalInput);
	}
	function removeAdditionalInput(){
		$(this).parent().remove();
	}
	$("button#saveadditionalinputs").click(saveAdditionalInputs);
	$("button#addadditionalinputs").click(addAdditionalInput)
	if( !template_data.submissempty ){
		$(".additionalscreate").css("display", "none");
		$("ul#listofadditionals li span.additionals-delete").css("display", "none");
		$("button#saveadditionalinputs").prop('disabled', true);
	}
	else {
		$("ul#listofadditionals li span.additionals-delete").click(removeAdditionalInput);
		$( "ul#listofadditionals" ).sortable({ items: "> li.additionals-element" });
	}

	function deleteSingleEntry(){
		var codes = $(this).attr('subcode').split(',');
		const changecolor = (newcol) => $(this).css('background-image').replace(/^(url\(".*\/ui\-icons_)[0-9a-f]{6}(_256x240\.png"\))$/, '$1' + newcol + '$2'); 

		$( "div#delsingle" ).removeClass('d-none');
		$( "div#delsingle" ).dialog({
			resizable: true,
			height: "auto",
			width: Math.min($(window).width(), 600),
			modal: true,
			buttons: [
				{
					text: "OK",
					icon: "ui-icon-check",
					class: "delsingle-buttons",
					click: () => {
						$("button.delsingle-buttons").button({disabled: true});
						$.post( template_data.delsinglesub, { terminid : parseInt(codes[0]), code : codes[1] }, (data) => {
							$("button.delsingle-buttons").button({disabled: false});
							if( data == 'ok' ){
								$(this).parent().remove();
							}
							else{
								$(this).css('background-image', changecolor('cc0000') );
							}
							$( "div#delsingle" ).dialog( "close" );
						});
					},
				},
				{
					text: "Cancel",
					icon: "ui-icon-close",
					class: "delsingle-buttons",
					click: function() {
						$( this ).dialog( "close" );
					},
				}
			]
		});
	}
	$("span.delsinglesub").click(deleteSingleEntry);
}

function template_submissionquery(){
	const item = (t) => '<li class="list-group-item ">'+ t +'</li>';
	const ahref = (l,n) => '<a href="' + l + '" target="_blank">' + n + '</a>';

	var json = JSON.parse(localStorage.getItem('pollsubmissons')) || {};
	
	if( json.hasOwnProperty(template_data.pollid) ){
		// list of entries
		var h = '';
		Object.keys(json[template_data.pollid]).forEach(function(k) {
			if( template_data.termine.hasOwnProperty( k ) ){
				h += item( template_data.termine[k][0] + ' (' + template_data.termine[k][1] + ')' );
			}
		});
		$("ul.entriesSamePollDelcode").append(h);
	}

	// list of 
	var h = '';
	Object.keys(json).forEach(function(pollid) {
		if( template_data.pollid != pollid ){
			h += item( ahref( template_data.polllink.replace('<poll>', pollid ), pollid ) );
		}
	});
	$("ul.entriesOtherPollDelcode").append(h);
}

/**
 * Global functions
 */
// add a new poll submission delete code
function poll_submissions_delete_code(pollid, values, code){
	if( !values.length > 0 ){
		return; // nothing to add
	}
	var json = JSON.parse(localStorage.getItem('pollsubmissons')) || {};
	if( !json.hasOwnProperty(pollid) ){
		json[pollid] = {};
	}
	values.forEach( (v) => {
		json[pollid][v] = code;
	});
	localStorage.setItem('pollsubmissons', JSON.stringify(json));
}
// remove a used poll submission delete code
function poll_submissions_delete_code_used(pollid, value){
	var json = JSON.parse(localStorage.getItem('pollsubmissons')) || {};
	if( !json.hasOwnProperty(pollid) ){
		return; // nothing remove
	}
	delete json[pollid][value];
	localStorage.setItem('pollsubmissons', JSON.stringify(json));
}
// delete checked options for a poll
function delete_wahl_for_poll(pollid){
	var wahl = localStorage.hasOwnProperty("pollPollDateData") ? JSON.parse( localStorage.getItem( "pollPollDateData" ) ) : {};
	var neu = {};
	Object.keys(wahl[pollid]).forEach(function(k) {
		if( !$.isNumeric(k) ){ 
			neu[k] = wahl[pollid][k];
		}
	});
	wahl[pollid] = neu;
	localStorage.setItem( "pollPollDateData", JSON.stringify( wahl ) );
}