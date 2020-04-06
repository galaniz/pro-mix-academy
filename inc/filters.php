<?php

/*
 * Front end filters
 * -----------------
 */

/* Customize query args */

function pma_pre_get_posts( $query ) {
    if( !is_admin() && $query->is_main_query() ) {
        if( is_home() || is_category() || is_archive() ) {
            $ppp = pma_get_ppp();

            if( $ppp )
                $query->set( 'posts_per_page', $ppp );
            }

        if( is_tax() || is_post_type_archive() ) {
            $post_type = $query->get( 'post_type' );
            $ppp = pma_get_ppp( $post_type );

            if( $ppp )
                $query->set( 'posts_per_page', $ppp );
        }
    }
}
add_action( 'pre_get_posts', 'pma_pre_get_posts' );

/* Add classes to main nav */

function pma_nav_link_class( $atts, $item, $args ) {
    $classes = $atts['class'] ?? '';

    if( 'main_navigation' === $args->theme_location ) {
        $classes .= " o-subtext";

        if( in_array( 'pma-button', $item->classes ) ) {
            $classes .= " o-button --sm --outline";
        } else {
            $classes .= " --l";
        }

        if( in_array( 'primary-light', $item->classes ) )
            $classes .= " u-color-primary-light";
    } else {
        $classes .= " o-subtext-sm --l";
    }

    $atts['class'] = $classes;

    return $atts;
}
add_filter( 'nav_menu_link_attributes' , 'pma_nav_link_class', 10, 3 );

/* Add classes to body */

add_filter( 'body_class', function( $classes ) {
    if( is_front_page() )
        $classes[] = 'is-front';

    return $classes;
} );

/* Prevent Contact Form 7 wrapping inputs in p tags */

add_filter( 'wpcf7_autop_or_not', '__return_false' );

/* Filter single term titles */

add_filter( 'single_term_title', function( $term_name ) {
    if( is_tax( 'course_category' ) ) {
        $queried_object = get_queried_object();

        if( $queried_object ) {
            $tax = get_taxonomy( $queried_object->taxonomy );
            $term_name = sprintf( __( '%1$s &ndash; %2$s' ), $tax->labels->singular_name, $term_name );
        }
    }

    return $term_name;
}, 10, 1 );
