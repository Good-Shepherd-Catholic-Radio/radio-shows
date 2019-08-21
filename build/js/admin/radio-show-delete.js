( function( $ ) {

    $( document ).on( 'ready', function() {

        var $repeater = rbmFHgetFieldObject( 'rbm_cpts_radio_show_times', 'rbm_cpts', 'repeater' );

        $repeater.repeater.on( 'repeater-before-delete-item', function( event, $item ) {

            let post_id = parseInt( $item.find( '[data-fieldhelpers-name="post_id"] input' ).val() );

            if ( post_id > 0 ) {

                let post_ids = $( '[name="rbm_cpts_radio_show_occurrences_to_delete"]' ).val().trim();

                post_ids = post_ids.split( ',' ).filter( Boolean );
                post_ids.push( post_id );

                $( '[name="rbm_cpts_radio_show_occurrences_to_delete"]' ).val( post_ids.join( ',' ) );

            }

        } );

    } );

} )( jQuery );