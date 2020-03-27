<?php

/*
 * Actions and filters
 * -------------------
 */


/* Add nav classes */

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


/* Enqueue styles and scripts */

function pma_enqueue_styles() {
    wp_enqueue_style(
        'pma-style',
        get_stylesheet_directory_uri() . '/style.css'
    );

    wp_enqueue_script(
        'pma-script',
        get_stylesheet_directory_uri() . '/assets/public/js/pma.js',
        [],
        NULL,
        true
    );
}
add_action( 'get_footer', 'pma_enqueue_styles' );


/* Hide portfolio cpt */

function pma_remove_menu_items() {
    remove_menu_page( 'edit.php?post_type=avada_portfolio' );
}
add_action( 'admin_menu', 'pma_remove_menu_items' );


/* Load mentors on course backend */

function pma_acf_load_mentors( $field ) {
    $mentors = get_posts( [
        'post_type' => 'mentor',
        'numberposts' => -1
    ] );

    $m = [];

    if( $mentors ) {
        foreach( $mentors as $mentor ) {
           $m[$mentor->ID] = $mentor->post_title;
        }
    }

    if( $m ) {
        $field['choices'] = $m;
    }

    return $field;
}
add_filter( 'acf/load_field/name=mentor', 'pma_acf_load_mentors' );


/* Courses output shortcode */

function pma_courses_shortcode( $atts ) {
	$atts = shortcode_atts( [
		'gradient' => false,
		'category_slug' => '',
        'posts_per_page' => 3,
        'mentor_id' => 0
	], $atts, 'courses' );

    extract( $atts );

    $output = '';

    $args = [
        'post_type' => 'course',
        'posts_per_page' => $posts_per_page
    ];

    if( $category_slug ) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'course_category',
                'field' => 'slug',
                'terms' => $category_slug
            ]
        ];
    }

    if( $mentor_id ) {
        $args['meta_query'] = [
            [
                'key' => 'mentor',
                'value' => "$mentor_id",
                'compare' => 'LIKE'
            ]
        ];
    }

    // homepage meta
    if( is_front_page() ) {
        $args['meta_query'] = [
            [
                'key' => 'homepage',
                'value' => '1',
                'compare' => 'LIKE'
            ]
        ];

        $args['meta_key'] = 'homepage_order';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'ASC';
    }

    $q = new WP_Query( $args );

    if( $q->have_posts() ) {
        $output = '<div class="l-flex l-pad-h-md l-pad-v-container u-fig --xl-b --wrap">';

        while( $q->have_posts() ) {
            $q->the_post();

            $id = get_the_ID();
            $url = get_the_permalink();

            $featured_img = get_the_post_thumbnail( $id, 'large', [
                'class' => 'o-aspect-ratio__media ' . ( $gradient ? 'u-object-contain' : 'u-object-cover u-opacity-30' )
            ] );

            if( $featured_img ) {
                $fig_classes = 'o-aspect-ratio --p-65 ' . ( $gradient ? 'o-gray__item' : 'u-op-70' );
                $featured_img = "<a class='u-transform-scale" . ( $gradient ? '-fig' : '' ) . "' href='$url'><figure class='$fig_classes'>$featured_img</figure></a>";
            }

            $meta = '';

            $mentor = get_field( 'mentor', $id );
            $mentor = (int) $mentor ? $mentor[0] : 0;

            if( $mentor ) {
                $course_title = get_the_title( $id );
                $mentor_name = get_the_title( $mentor );
                $mentor_url = get_the_permalink( $mentor );

                $meta =
                    "<h3 class='u-m-0 l-pad-v-xs-b'><a href='$url'>$course_title</a></h3>" .
                    "<a class='u-color-primary-light' href='$mentor_url'>$mentor_name</a>";

                if( $gradient ) {
                    $mentor_img = get_the_post_thumbnail( $mentor, 'post-thumbnail', [
                        'class' => 'o-aspect-ratio__media u-object-cover'
                    ] );

                    if( $mentor_img )
                        $mentor_img = "<a href='$mentor_url'><figure class='l-w-70 o-aspect-ratio --circle'>$mentor_img</figure></a>";

                    $meta =
                        "<div class='l-flex l-pad-v-sm-t l-pad-h-sm --align-center --justify-center'>" .
                            "<div class='l-pad-h__item'>" .
                                $mentor_img .
                            "</div>" .
                            "<div class='l-pad-h__item u-fade-out-links'>" .
                                $meta .
                            "</div>" .
                        "</div>";
                } else {
                    $meta =
                        "<div class='l-pad-h u-m-0 u-text-align-center l-flex-equal'>" .
                            "<div class='u-bg-light l-pad-h__item l-pad-v l-flex-equal'>" .
                                "<div class='u-m-auto u-fade-out-links'>" .
                                    $meta .
                                "</div>" .
                            "</div>" .
                        "</div>";
                }
            }

            $item_classes = 'l-33 l-pad-h__item l-flex-equal l-pad-v-xl-b' . ( $gradient ? ' o-gray' : '' ) . ' --max';

            $output .=
                "<div class='$item_classes'>" .
                    $featured_img .
                    $meta .
                "</div>";
        }

        $output .= '</div>';

        wp_reset_postdata();
    }

	return $output;
}
add_shortcode( 'courses', 'pma_courses_shortcode' );


/* Mentors output shortcode */

function pma_mentors_shortcode( $atts ) {
	$atts = shortcode_atts( [
        'posts_per_page' => 10
	], $atts, 'mentors' );

    extract( $atts );

    $output = '';
    $archive = true;

    global $wp_query;

    if( $wp_query->query_vars['post_type'] !== 'mentor' ) {
        $args = [
            'post_type' => 'mentor',
            'posts_per_page' => $posts_per_page
        ];

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


/* Testimonials output shortcode */

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


/* Mentor output shortcode */

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
