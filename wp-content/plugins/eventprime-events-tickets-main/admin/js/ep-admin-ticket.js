jQuery( function($){
	// on upload button click
	$( 'body' ).on( 'click', '.ep-ticket-logo-upload', function( event ){
		event.preventDefault();
		const button = $(this);
		const imageId = button.next().next().val();
		
		const customUploader = wp.media({
			title: 'Insert image',
			library : {
				type : 'image'
			},
			button: {
				text: 'Use this image'
			},
			multiple: false
		}).on( 'select', function() {
			const attachment = customUploader.state().get( 'selection' ).first().toJSON();
			button.removeClass( 'button' ).html( '<img src="' + attachment.url + '">');
			button.next().show();
			button.next().next().val( attachment.id );
                        
			$('#ep-ticket-logo').attr('src',attachment.url);
		});
		customUploader.on( 'open', function() {

			if( imageId ) {
			  const selection = customUploader.state().get( 'selection' );
			  attachment = wp.media.attachment( imageId );
			  attachment.fetch();
			  selection.add( attachment ? [attachment] : [] );
			}
			
		});
		customUploader.open();
	
	});
	// on remove button click
	$( 'body' ).on( 'click', '.ep-ticket-logo-remove', function( event ){
		event.preventDefault();
		const button = $(this);
		button.next().val( '' ); // emptying the hidden field
		button.hide().prev().addClass( 'button' ).html( 'Upload image' ); // replace the image with text
		$('#ep-ticket-logo').attr('src','');
	});
        
        
});

function ticketFontColorChange(){
    var FontColor = jQuery('#ep-ticket-font-color').val();
    jQuery('.ep-font-color').css('color',FontColor);
}
function ticketBackgroundColorChange(){
    var BGColor = jQuery('#ep-ticket-background-color').val();
    jQuery('.ep-ticket-preview').css('background',BGColor);
}
function ticketBorderColorChange(){
    var BRColor = jQuery('#ep-ticket-border-color').val();
    jQuery('.ep-ticket-left-section').css('border-right-color',BRColor);
}

function ticketFontChange(){
    var Font = jQuery('#ep-ticket-font').val();
    jQuery('.ep-ticket-preview').css('font-family',Font);
}
