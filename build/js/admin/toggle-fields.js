( function( $ ) {
	
	$( document ).ready( function() {
		
		$( '#radio-show-meta' ).addClass( 'hidden' );
		$( '#gscr-live-radio-show' ).addClass( 'hidden' );
		
		if ( $( '#radio-show-meta' ).length > 0 ) {
			
			// Hide/Show on Load
			$( '#taxonomy-tribe_events_cat input[type="checkbox"]' ).each( function( index, checkbox ) {
				
				if ( $( checkbox ).closest( '.selectit' ).text().trim() == 'Radio Show' ) {
				
					if ( $( checkbox ).prop( 'checked' ) ) {
						
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
					}
					else {
						$( '#radio-show-meta' ).addClass( 'hidden' );
						$( '#gscr-live-radio-show' ).addClass( 'hidden' );
					}
					
				}
				
			} );
			
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