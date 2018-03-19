// listen to the ajaxSuccess event, if it was for adding a tax then reset the ahr-redirect select element
jQuery( document ).ajaxSuccess( function( event, xhr, settings ) {

	if ( settings.data.indexOf( "action=add-tag" ) >= 0) {
	
		var selectElm = document.getElementById( "ahr-redirect" );
		selectElm.selectedIndex = 0;
	}
	
});