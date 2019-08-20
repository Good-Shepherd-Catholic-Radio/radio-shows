( function( $ ) {

    $( document ).on( 'ready', function() {

        var $repeater = rbmFHgetFieldObject( 'rbm_cpts_radio_show_times', 'rbm_cpts', 'repeater' );

        $repeater.repeater.on( 'repeater-before-delete-item', function( event, $item ) {

            let post_id = parseInt( $item.find( '[data-fieldhelpers-name="post_id"]' ).val() );

            if ( post_id.length > 0 && post_id !== 0 ) {

                let post_ids = $( '[name="radio_show_occurrences_to_delete"]' ).val().trim();

                post_ids = post_ids.split( ',' );
                post_ids.push( post_id );

                $( '[name="radio_show_occurrences_to_delete"]' ).val( post_ids.join( ',' ) );

            }

        } );

    } );

} )( jQuery );