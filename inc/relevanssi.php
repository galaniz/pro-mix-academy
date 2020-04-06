<?php

/*
 * Customize Relevanssi plugin
 * ---------------------------
 */

/* Exclude testimonials from index ( not being respected in settings... ) */

function pma_block_index( $block, $post_id ) {
    if( get_post_type( $post_id ) === 'testimonial' ) {
        $block = true;
    }

    return $block;
}
add_filter( 'relevanssi_do_not_index', 'pma_block_index', 10, 2 );

/* Pass custom field values as strings ( does not seem to affect search results... ) */

function pma_custom_field_value( $value, $field_name, $post_id ) {
    if( $field_name === 'mentor' && get_post_type( $post_id ) === 'course' ) {
        $value = $value[0];

        $output = [];

        foreach( $value as $v ) {
            $output[] = get_the_title( (int) $v );
        }

        $value = implode( ', ', $output );
    }

    if( $field_name === 'artists' && get_post_type( $post_id ) === 'mentor' ) {
        $value = $value[0];
    }

    return $value;
}
add_filter( 'relevanssi_custom_field_value', 'pma_custom_field_value', 10, 3 );

/* Add custom field data to indexed content */

function pma_append_to_index_content( $content, $post ) {
    if( get_post_type( $post ) === 'course' ) {
        $mentor = get_field( 'mentor' );

        $output = [];

        foreach( $mentor as $m ) {
            $output[] = get_the_title( (int) $m );
        }

        $content .= implode( ', ', $output );
    }

    if( get_post_type( $post ) === 'mentor' ) {
        $content .= get_field( 'artists' );
    }

    return $content;
}
add_filter( 'relevanssi_content_to_index', 'pma_append_to_index_content', 10, 2 );
