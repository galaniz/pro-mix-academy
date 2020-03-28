<?php

/*
 * Output testimonials
 * -------------------
 */

/* Testimonials shortcode */

function pma_testimonials_shortcode( $atts ) {
	$atts = shortcode_atts( [
        'posts_per_page' => 3,
        'show_quote' => true,
        'horizontal' => true
	], $atts, 'testimonials' );

    extract( $atts );

    $horizontal = $horizontal === 'false' ? false : true;
    $show_quote = $show_quote === 'false' ? false : true;

    $output = '';

    $args = [
        'post_type' => 'testimonial',
        'posts_per_page' => $posts_per_page
    ];

    $q = new WP_Query( $args );

    if( $q->have_posts() ) {
        $container_classes = 'u-fig l-pad-v-container --xxl-b';

        if( $horizontal )
            $container_classes .= ' l-pad-h l-flex l-flex-wrap-900';

        $output = "<div class='$container_classes'>";

        while( $q->have_posts() ) {
            $q->the_post();

            $id = get_the_ID();
            $name = get_the_title();
            $title = get_field( 'subtitle', $id );
            $quote = get_the_content();

            $featured_img = get_the_post_thumbnail( $id, 'medium', [
                'class' => 'o-aspect-ratio__media u-object-cover'
            ] );

            if( $featured_img ) {
                $fig_classes = 'o-aspect-ratio --circle';
                $featured_img =
                    "<div class='l-pad-v-b" . ( !$horizontal ? ' l-pad-h__item ' : '' ) . "'>" .
                        "<div class='l-w-150 u-m-auto'>" .
                            "<figure class='$fig_classes'>$featured_img</figure>" .
                        "</div>" .
                    "</div>";
            }

            $meta =
                "<h3 class='u-color-primary-light u-m-0 l-pad-v-xs-b'>$name</h3>" .
                ( $title ? "<div class='u-text-sm'>$title</div>" : "" );

            if( !$horizontal )
                $meta =
                    "<div class='l-flex" . ( $show_quote && $quote ? ' l-w-150-meta' : '' ) . "'>" .
                        "<div>" .
                            $meta .
                        "</div>" .
                    "</div>";

            if( $show_quote && $quote )
                $meta .= '<div class="l-pad-v-md-t u-text u-p-0' . ( !$horizontal ? ' l-w-150-text' : '' ) . '">' . $quote . "</div>";

            if( !$horizontal ) {
                $output .=
                    "<div class='l-flex l-pad-v-xl-b l-pad-h-md l-pad-v-container --b" . ( !$show_quote || !$quote ? ' --align-center' : '' ) . "'>" .
                        $featured_img .
                        "<div class='l-pad-v-b l-pad-h__item'>" .
                            $meta .
                        "</div>" .
                    "</div>";
            } else {
                $output .=
                    "<div class='l-basis-100 l-max-600 l-pad-h__item l-pad-v-xxl-b u-text-align-center'>" .
                        $featured_img .
                        $meta .
                    "</div>";
            }
        }

        $output .= '</div>';

        wp_reset_postdata();
    }

	return $output;
}
add_shortcode( 'testimonials', 'pma_testimonials_shortcode' );
