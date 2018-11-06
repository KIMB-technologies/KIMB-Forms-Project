$( function (){
	$(document).tooltip();

	template_main();

	switch(template_name){
		case "new":
			template_new();
			break;
		default:
			break;
	}
});

function template_main(){
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

	$("button#new-weiterer").click( function () {
		$("div#add-more-here").append(
			'<div class="row align-items-start">'
			+ $( "#examplecontainer" ).html()
			+ '</div>'
		);
		personMeetingUpdate();
		$("div.laufindex" ).last().text( ++laufindex );
	});
}
