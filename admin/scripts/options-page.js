// display htaccess writing notices to user when user decides an option that writes to htaccess
jQuery( document ).ready( function () {
	
	jQuery( "#ahr-redirect-static-resources" ).change( ahr_write_htaccess_rules );	
	jQuery( "#ahr-redirection-type" ).change( function(){
		if ( jQuery("#ahr-redirect-static-resources").val() !== 'none')
			ahr_write_htaccess_rules();
	});	
	
});

// backup htaccess for the first time
jQuery('form').submit(function(e){
	
	if ( ahrHtaccessBacked !== true ){
		e.preventDefault();
		jQuery("#ahr-modal").show();
		jQuery.post(ajaxurl, {action: "backup_htaccess"}, function(response) {
			
			var text = "";
			if (response === 'true')
				text = ".htaccess file was successfully backed up, changes are being saved please wait...";
			
			else
				text = ".htaccess file backup failed, you can still save changes and work as expected. you are encouraged to backup your htaccess manually.";
				
			jQuery("#ahr-modal-loader").hide();
			jQuery("#ahr-modal-p").text(text);
			
			ahrHtaccessBacked = true;
			if ( response === "true" )
				jQuery('form').submit();
			
		});
	}
});

// close the modal when x is clicked
jQuery("#ahr-modal-close").click(function(){
	jQuery("#ahr-modal").hide();
});

// generate htaccess writing notices for user
function ahr_write_htaccess_rules() {
	var redirectionStatus = jQuery("#ahr-redirect-static-resources").val();
	var redirectionType = jQuery("#ahr-redirection-type").val();
	var httpsStatus = redirectionStatus === "https" ? "off" : "on";
	
	var writtenRules = `
	# Begin Advanced https redirection ${ahrAnchorNumber}<br>
	&ltIfModule mod_rewrite.c&gt <br>
	RewriteEngine On <br>
	RewriteCond %{HTTPS} <span style="color:red;">${httpsStatus}</span> <br> 
	RewriteCond %{REQUEST_FILENAME} -f <br>
	RewriteCond %{REQUEST_FILENAME} !\.php$ <br> 
	RewriteRule .* <span style="color:red;">${redirectionStatus}</span>://%{HTTP_HOST}%{REQUEST_URI} [L,<span style="color:red;">R=${redirectionType}</span>]  <br>
	&lt/IfModule&gt <br>
	# End Advanced https redirection ${ahrAnchorNumber}
	`;
	
	// if we can't write to htaccess it would be useless to show what we are going to write
	if ( jQuery("#ahr-htaccess-errors").css("display") === 'none' ) {
		if ( redirectionStatus === "http" || redirectionStatus === "https") {
			jQuery("#ahr-write-link").css("display", "inline");
			jQuery("#ahr-htaccess-written-rules")[0].innerHTML = writtenRules;
			jQuery("#ahr-htaccess-rules").show();
		}
			
		if ( redirectionStatus === "none" ) {
			jQuery("#ahr-htaccess-rules").hide();
		}
	}	
}