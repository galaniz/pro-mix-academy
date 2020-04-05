<?php

/*
 * Avada hooks
 * -----------
 */

/* Add price and buy now links after course */

add_action( 'avada_after_content', function() {
    if( !is_singular( 'course' ) )
        return;

    $id = get_the_ID();
    $price = (int) get_field( 'price', $id );
	$buy_link = get_field( 'buy_link', $id );

    if( !$price || !$buy_link )
        return;

    $price = '&dollar;' . $price;

    echo
        "<div class='l-pad-v-xxl u-width-100 u-overflow-hidden'>" .
            "<div class='l-max-700 u-m-auto'>" .
                "<div class='l-pad-h-sm l-flex --align-center --justify-center'>" .
                    "<div class='l-pad-h__item'>" .
                        "<h3 class='lg u-m-0'>$price</h3>" .
                    "</div>" .
                    "<div class='l-pad-h__item'>" .
                        "<a class='o-button u-color-background-light o-subtext' href='$buy_link'>" . __( 'Buy Now' ) . "</a>" .
                    "</div>" .
                "</div>" .
            "</div>" .
        "</div>" .
        "<div class='c-cta u-fade-out-links'>" .
            "<a class='c-cta__link u-text-m u-color-background-light o-subtext-lg l-flex --align-center --justify-center' href='$buy_link'>" .
                "<div>" . __( "Buy Now for $price" ) . "</div>" .
            "</a>" .
        "</div>";
} );

/* Add course taxonomy */

add_filter( 'fusion_tax_meta_allowed_screens', function( $taxonomies ) {
    $taxonomies[] = 'course_category';
    return $taxonomies;
} );
