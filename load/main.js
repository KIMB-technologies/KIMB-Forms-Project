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

		$("input[type=text]").each((k,v) => {
			$( v ).val( data["inputsText"][k] );
		});
		$("input[type=number]").each((k,v) => {
			$( v ).val( data["inputsNum"][k] );
		});
		$("textarea").each((k,v) => {
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
