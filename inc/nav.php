<?php

/*
 * Add classes to main nav
 * -----------------------
 */

function pma_nav_link_class( $atts, $item, $args ) {
    $classes = $atts['class'] ?? '';

    if( 'main_navigation' === $args->theme_location ) {
        $classes .= " o-subtext --l";

        if( in_array( 'pma-button', $item->classes ) )
            $classes .= " o-button --sm --outline";

        if( in_array( 'primary-light', $item->classes ) )
            $classes .= " u-color-primary-light";
    } else {
        $classes .= " o-subtext-sm --l";
    }

    $atts['class'] = $classes;

    return $atts;
}
add_filter( 'nav_menu_link_attributes' , 'pma_nav_link_class', 10, 3 );
