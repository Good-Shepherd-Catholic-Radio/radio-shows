( function( $ ) {

    // Populated on Ready
    var days_selected = {};

    var $post_ids_to_delete = $( '[name="rbm_cpts_radio_show_occurrences_to_delete"]' );

    $( document ).on( 'ready', function() {

        $( '.fieldhelpers-field-select[data-fieldhelpers-name="days_of_the_week"] select' ).each( function( index, element ) {

            days_selected[ $( element ).attr( 'id' ) ] = $( element ).val();

        } );

    } );

    /**
     * Assign which Post IDs to delete on deselecting a Day
     * 
     * @since 2.0.0
     * @return void
     */
    $( document ).on( 'change', '.fieldhelpers-field-select[data-fieldhelpers-name="days_of_the_week"] select', function( event ) {

        if ( typeof days_selected[ $( this ).attr( 'id' ) ] == "undefined" ) {

            // If we it is a new Row, we don't know the difference
            days_selected[ $( this ).attr( 'id' ) ] = $( this ).val();
            return;

        }

        var currentValue = $( this ).val();
        
        currentValue = ( currentValue == null ) ? [] : currentValue;

        // Now we know which one has been removed
        var difference = days_selected[ $( this ).attr( 'id' ) ].filter( function( value ) {
             return currentValue.indexOf( value ) < 0;
        } );

        // Nothing removed, bail
        if ( difference.length == 0 ) return;

        var $row = $( this ).closest( '.fieldhelpers-field-repeater-content' );

        var $post_ids = $row.find( '.fieldhelpers-field-hidden[data-fieldhelpers-name="post_ids"] input' ),
            post_ids = $post_ids.val().trim();

        post_ids = ( post_ids.length == 0 ) ? {} : JSON.parse( post_ids );

        var $post_ids_to_delete = $row.find( '.fieldhelpers-field-hidden[data-fieldhelpers-name="post_ids_to_delete"] input' ),
            post_ids_to_delete = $post_ids_to_delete.val().trim();

        post_ids_to_delete = ( post_ids_to_delete.length == 0 ) ? {} : JSON.parse( post_ids_to_delete );

        for ( var index in difference ) {

            if ( typeof post_ids[ difference[ index ] ] !== 'undefined' && 
                post_ids[ difference[ index ] ] !== null ) {

                post_ids_to_delete[ difference[ index ] ] = post_ids[ difference[ index ] ];

                delete post_ids[ difference[ index ] ];

            }

        }

        // Reassign Post IDs field only Post IDs that should continue to exist
        $post_ids.val( JSON.stringify( post_ids ) );

        // Assign the Post IDs we're now deleting
        $post_ids_to_delete.val( JSON.stringify( post_ids_to_delete ) );

        days_selected[ $( this ).attr( 'id' ) ] = $( this ).val();

    } );

    /**
     * Re-add Post IDs if a day is removed and then re-added
     * 
     * @since 2.0.0
     * @return void
     */
    $( document ).on( 'change', '.fieldhelpers-field-select[data-fieldhelpers-name="days_of_the_week"] select', function( event ) {

        if ( typeof days_selected[ $( this ).attr( 'id' ) ] == "undefined" ) {

            // If we it is a new Row, we don't know the difference
            days_selected[ $( this ).attr( 'id' ) ] = $( this ).val();
            return;

        }

        var currentValue = $( this ).val(),
            id = $( this ).attr( 'id' );
        
        currentValue = ( currentValue == null ) ? [] : currentValue;

        // Now we know which one has been added
        var difference = currentValue.filter( function( value ) {
             return days_selected[ id ].indexOf( value ) < 0;
        } );

        // Nothing added, bail
        if ( difference.length == 0 ) {
            // Assign the current Value to our global variable
            days_selected[ id ] = currentValue;
            return;
        }

        var $row = $( this ).closest( '.fieldhelpers-field-repeater-content' );

        var $post_ids = $row.find( '.fieldhelpers-field-hidden[data-fieldhelpers-name="post_ids"] input' ),
            post_ids = $post_ids.val().trim();

        post_ids = ( post_ids.length == 0 ) ? {} : JSON.parse( post_ids );

        var $post_ids_to_delete = $row.find( '.fieldhelpers-field-hidden[data-fieldhelpers-name="post_ids_to_delete"] input' ),
            post_ids_to_delete = $post_ids_to_delete.val().trim();

        post_ids_to_delete = ( post_ids_to_delete.length == 0 ) ? {} : JSON.parse( post_ids_to_delete );

        for ( var index in difference ) {

            if ( typeof post_ids_to_delete[ difference[ index ] ] !== 'undefined' && 
                post_ids_to_delete[ difference[ index ] ] !== null ) {

                post_ids[ difference[ index ] ] = post_ids_to_delete[ difference[ index ] ];

                delete post_ids_to_delete[ difference[ index ] ];

            }

        }

        // Reassign Post IDs field to include the re-added Post ID
        $post_ids.val( JSON.stringify( post_ids ) );

        // Remove the Post ID from the Post IDs to be deleted
        $post_ids_to_delete.val( JSON.stringify( post_ids_to_delete ) );

        days_selected[ id ] = currentValue;

    } );

} )( jQuery );