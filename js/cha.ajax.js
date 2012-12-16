
jQuery(document).ready( function($) {

// **************************************************************
//  store new parent
// **************************************************************

$('select#comment_parent').change(function () {

	// remove any existing messages
	$('#wpbody div#message').remove();

	var comment	= $('div#poststuff').find('input[name="comment_ID"]').val();
	var post_id	= $('div#poststuff').find('input[name="comment_post_ID"]').val();

	var parent	= $(this).val();

	var data = {
		action:		'save_parent',
		parent:		parent,
		comment:	comment,
		postID:		post_id
	};

	jQuery.post(ajaxurl, data, function(response) {

		var obj;
		try {
			obj = jQuery.parseJSON(response);
		}
		catch(e) {
			$('table.comment_xtra').after('<div id="message" class="error below-h2"><p><strong>' + chaL10n.errorMessage + '</strong></p></div>');
			$('div#message').delay(3000).fadeOut('slow');
			}

		if(obj.success === true) {
			$('table.comment_xtra').after('<div id="message" class="updated below-h2"><p>' + obj.message + '</p></div>');
			$('div#message').delay(3000).fadeOut('slow');
		}
		else {
			$('table.comment_xtra').after('<div id="message" class="error below-h2"><p>' + chaL10n.errorMessage + '</p></div>');
			$('div#message').delay(3000).fadeOut('slow');
			}
		});

	});

//********************************************************
// you're still here? it's over. go home.
//********************************************************
});
