/** 
 * KIMB-Forms-Project
 * https://github.com/KIMB-technologies/KIMB-Forms-Project
 * 
 * (c) 2018 KIMB-technologies 
 * https://github.com/KIMB-technologies/
 * 
 * released under the terms of GNU Public License Version 3
 * https://www.gnu.org/licenses/gpl-3.0.txt
 */

$( function (){
	template_main();

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
		default:
			break;
	}
});

function template_main(){
	$(document).tooltip();
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
	}
	if( localStorage.hasOwnProperty("pollPollData") ){
		loadSaved();
	}

	function save(){
		var data = {
			"username" : $("input[name=name]").val(),
			"usermail" : $("input[name=email]").val(),
		};
		localStorage.setItem( "pollPollData", JSON.stringify( data ) );
	}
	$("input[type=text], input[type=email]").change(save);
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
}
