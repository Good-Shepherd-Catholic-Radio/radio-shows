( function( $ ) {
	
	$( document ).ready( function() {
		
		if ( $( '#radio-show-meta' ).length > 0 ) {
			
			// Show/Hide Select Field depending on if Encore is checked
			$( 'p.radio-show-encore.rbm-field-checkbox input[type="checkbox"]' ).on( 'change', function( event ) {
				
				if ( $( this ).prop( 'checked' ) ) {
					$( '.radio-show-original.rbm-field-select' ).removeClass( 'hidden' ).find( 'select' ).attr( 'required', true );
				}
				else {
					$( '.radio-show-original.rbm-field-select' ).addClass( 'hidden' ).find( 'select' ).attr( 'required', false );
				}
				
			} );
			
			// Toggle Live/Encore so that they both can't be checked at once
			$( 'p.radio-show-encore.rbm-field-checkbox input[type="checkbox"], p.radio-show-live.rbm-field-checkbox input[type="checkbox"]' ).on( 'change', function( event ) {
				
				if ( $( this ).prop( 'checked' ) ) {
					
					var toggle = '';
					if ( event.currentTarget.name.indexOf( 'live' ) > 0 ) {
						toggle = 'encore';
					}
					else {
						toggle = 'live';
					}
					
					$( 'p.radio-show-' + toggle + '.rbm-field-checkbox input[type="checkbox"]' ).prop( 'checked', false ).trigger( 'change' );
					
				}
				
			} );
			
		}
		
	} );
	
} )( jQuery );