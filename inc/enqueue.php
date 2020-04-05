<?php

/*
 * Enqueue styles and scripts
 * --------------------------
 */

function pma_enqueue_styles() {
    wp_enqueue_style(
        'pma-style',
        get_stylesheet_directory_uri() . '/style.css'
    );

    wp_register_script(
        'pma-script',
        get_stylesheet_directory_uri() . '/assets/public/js/pma.js',
        [],
        NULL,
        true
    );

    wp_localize_script( 'pma-script', 'pma', [
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ] );

    wp_enqueue_script( 'pma-script' );
}
add_action( 'get_footer', 'pma_enqueue_styles' );
