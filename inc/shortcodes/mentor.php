<?php

/*
 * Output mentors and mentor
 * -------------------------
 */

/* Mentors shortcode */

function pma_mentors_shortcode( $atts ) {
	$atts = shortcode_atts( [
        'posts_per_page' => 10,
		'load_more' => 10,
		'only_rows' => false,
		'query_args' => []
	], $atts, 'mentors' );

    extract( $atts );

    $output = '';
    $archive = true;
	$only_rows = $only_rows === 'true' ? true : false;

    global $wp_query;

    if( !pma_check_wp_query_vars( $wp_query, 'post_type', 'mentor' ) ) {
        $args = [
            'post_type' => 'mentor',
            'posts_per_page' => $posts_per_page
        ];

		if( is_array( $query_args ) && count( $query_args ) > 0 ) {
			// merge query_args with args
			$args = array_replace_recursive( $args, $query_args );
		}

        $q = new WP_Query( $args );

        $archive = false;
    } else {
        $q = $wp_query;
    }

    if( $q->have_posts() ) {
        $output = '<div class="l-flex l-pad-h-' . ( $archive ? 'xl' : 'lg' ) .' l-pad-v-container u-fig --xl-b --wrap">';

        while( $q->have_posts() ) {
            $q->the_post();

            $id = get_the_ID();
            $url = get_the_permalink();
            $name = get_the_title();
            $artists = get_field( 'artists', $id );

            $featured_img = get_the_post_thumbnail( $id, 'medium', [
                'class' => 'o-aspect-ratio__media u-object-cover'
            ] );

            if( $featured_img ) {
                $fig_classes = 'o-aspect-ratio --circle';
                $featured_img =
                    "<div class='l-pad-h__item'>" .
                        "<div class='l-w-150'>" .
                            "<a class='u-transform-scale-fig' href='$url'><figure class='$fig_classes'>$featured_img</figure></a>" .
                        "</div>" .
                    "</div>";
            }

            $meta =
                "<div class='l-pad-h__item u-fade-out-links'>" .
                    "<h3 class='u-color-primary-light u-m-0 l-pad-v-sm-b lg'>" .
                        "<a href='$url'>$name</a>" .
                    "</h3>" .
                    ( $artists ? "<div class='u-text'>$artists</div>" : '' ) .
                "</div>";

            $output .=
                "<div class='l-50 u-flex-grow-1 l-pad-v-xl-b l-pad-h__item --max'>" .
                    "<div class='l-flex l-pad-h-md --align-center'>" .
                        $featured_img .
                        $meta .
                    "</div>" .
                "</div>";
        }

        $output .= '</div>';

        wp_reset_postdata();
    }

	return $output;
}
add_shortcode( 'mentors', 'pma_mentors_shortcode' );

/* Mentor shortcode */

function pma_mentor_shortcode( $atts ) {
	$atts = shortcode_atts( [
        'social' => true
	], $atts, 'mentor' );

    extract( $atts );

    $social = $social === 'false' ? false : true;

    $output = '';

    $id = get_the_ID();
    $name = get_the_title();
    $artists = get_field( 'artists', $id );

    $featured_img = get_the_post_thumbnail( $id, 'large', [
        'class' => 'o-aspect-ratio__media u-object-cover'
    ] );

    if( $featured_img ) {
        $fig_classes = 'o-aspect-ratio --circle';
        $featured_img = "<figure class='$fig_classes'>$featured_img</figure>";
    }

    $social = '';
        /*"<div class='l-pad-v-xl-t'>" .
            "<div class='l-flex l-pad-h-sm --wrap --justify-center --align-center'>" .
                "<div class='l-pad-h__item'>" .
                    "<a class='o-social u-color-primary-light fusion-facebook fusion-icon-facebook' href='' rel='noopener noreferrer'>" .
                        "<div class='u-visually-hidden'>Facebook</div>" .
                    "</a>" .
                "</div>" .
                "<div class='l-pad-h__item'>" .
                    "<a class='o-social u-color-primary-light fusion-twitter fusion-icon-twitter' href='' rel='noopener noreferrer'>" .
                        "<div class='u-visually-hidden'>Twitter</div>" .
                    "</a>" .
                "</div>" .
                "<div class='l-pad-h__item'>" .
                    "<a class='o-social u-color-primary-light fusion-linkedin fusion-icon-linkedin' href='' rel='noopener noreferrer'>" .
                        "<div class='u-visually-hidden'>Linkedin</div>" .
                    "</a>" .
                "</div>" .
            "</div>" .
        "</div>";*/

    $left =
        "<div class='l-w-300 l-pad-h__item l-pad-v-lg-b u-fig u-transform-y-900'>" .
            $featured_img .
            $social .
        "</div>";

    $content =
        "<div class='l-pad-h__item'>" .
            "<div class='l-pad-v-xl-b u-text-center-900'>" .
                "<h1 class='u-m-0 l-pad-v-md-b'>$name</h1>" .
                 ( $artists ? "<h3 class='u-m-0 u-fw-light'>$artists</h3>" : '' ) .
            "</div>" .
            do_shortcode( get_the_content() ) .
        "</div>";

    $output =
        "<div class='l-flex l-flex-wrap-900 l-align-start l-pad-h-lg l-pad-v-xxxl-b'>" .
            $left .
            $content .
        "</div>";

    $courses = do_shortcode( '[courses mentor_id="' . $id . '" ]' );

    if( $courses ) {
        $output .=
            "<div class='l-pad-v-xxxl-t l-pad-v-xxl-b u-b-top'>" .
                "<h2 class='sm u-m-0 l-pad-v-xl-b u-text-align-center'>Courses</h2>" .
                $courses .
            "</div>";
    }

    return $output;
}
add_shortcode( 'mentor', 'pma_mentor_shortcode' );
