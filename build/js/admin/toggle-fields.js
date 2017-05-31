( function( $ ) {
	
	$( document ).ready( function() {
		
		$( '#radio-show-meta' ).addClass( 'hidden' );
		
		if ( $( '#radio-show-meta' ).length > 0 ) {
			
			$( '#taxonomy-tribe_events_cat input[type="checkbox"]' ).each( function( index, checkbox ) {
				
				if ( $( checkbox ).closest( '.selectit' ).text().trim() == 'Radio Show' ) {
				
					if ( $( checkbox ).prop( 'checked' ) ) {
						$( '#radio-show-meta' ).removeClass( 'hidden' );
					}
					else {
						$( '#radio-show-meta' ).addClass( 'hidden' );
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
					}
					
				}
				
			} );
			
		}
		
	} );
	
} )( jQuery );