/**
 * Admin JavaScript for Disable Abilities API
 *
 * Handles showing/hiding selective options based on disable mode
 *
 * @package Disable_Abilities_API
 */

( function( $ ) {
    'use strict';

    /**
     * Toggle visibility of selective options based on selected mode
     */
    function toggleSelectiveOptions() {
        var selectedMode = $( 'input[name="ayudawp_daa_settings[disable_mode]"]:checked' ).val();
        var $selectiveSection = $( '.ayudawp-daa-selective-section' );
        var $selectiveOptions = $( '.ayudawp-daa-selective-options' );

        if ( selectedMode === 'selective' ) {
            $selectiveSection.slideDown( 200 );
            $selectiveOptions.slideDown( 200 );
        } else {
            $selectiveSection.slideUp( 200 );
            $selectiveOptions.slideUp( 200 );
        }
    }

    /**
     * Initialize on document ready
     */
    $( document ).ready( function() {
        // Initial state
        toggleSelectiveOptions();

        // Listen for changes
        $( 'input[name="ayudawp_daa_settings[disable_mode]"]' ).on( 'change', toggleSelectiveOptions );
    } );

} )( jQuery );
