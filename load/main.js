$( function (){
	$(document).tooltip();

	if( typeof template_name === "undefined" ){
		var template_name = '';
	}
	switch(template_name){
		case "new":
			template_new();
			break;
		case '':
			break;
	}
});

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
