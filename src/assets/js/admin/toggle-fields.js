( function( $ ) {
	
	$( document ).ready( function() {
		
		var $eventOptions = $( '#tribe_events_event_options > h2 span' ),
			eventOptionsText = $eventOptions.text();
		
		$( '#radio-show-meta' ).addClass( 'hidden' );
		$( '#gscr-live-radio-show' ).addClass( 'hidden' );
		
		if ( $( '#radio-show-meta' ).length > 0 ) {
			
			// Hide/Show on Load
			$( '#taxonomy-tribe_events_cat input[type="checkbox"]' ).each( function( index, checkbox ) {
				
				if ( $( checkbox ).closest( '.selectit' ).text().trim() == 'Radio Show' ) {
				
					if ( $( checkbox ).prop( 'checked' ) ) {
						
						$eventOptions.html( gscrCPTRadioShows.eventOptionsText );
						$( '#radio-show-meta' ).removeClass( 'hidden' );
						
						if ( $( '#radio-show-meta input[name="_rbm_radio_show_live"]' ).is( ':checked' ) ) {
							$( '#gscr-live-radio-show' ).removeClass( 'hidden' );
						}
						
					}
					
				}
				
			} );
			
			// Show/Hide Select Field depending on if Encore is checked
			$( '#taxonomy-tribe_events_cat input[type="checkbox"]' ).on( 'change', function( event ) {
				
				if ( $( this ).closest( '.selectit' ).text().trim() == 'Radio Show' ) {
				
					if ( $( this ).prop( 'checked' ) ) {
						$( '#radio-show-meta' ).removeClass( 'hidden' );
						$eventOptions.html( gscrCPTRadioShows.eventOptionsText );
					}
					else {
						$eventOptions.html( eventOptionsText );
						$( '#radio-show-meta' ).addClass( 'hidden' );
						$( '#gscr-live-radio-show' ).addClass( 'hidden' );
					}
					
				}
				
			} );
			
			// Only a problem for user accounts other than my own. No idea why
			$( '#gscr-live-radio-show' ).removeAttr( 'style' ).removeClass( 'hide-if-js' );
			
			// Hide/show Live Radio Show options if applicable
			$( '#radio-show-meta input[name="_rbm_radio_show_live"]' ).on( 'change', function( event ) {
				
				if ( $( this ).prop( 'checked' ) ) {
					$( '#gscr-live-radio-show' ).removeClass( 'hidden' );
				}
				else {
					$( '#gscr-live-radio-show' ).addClass( 'hidden' );
				}
				
			} );
			
		}
		
	} );
	
} )( jQuery );