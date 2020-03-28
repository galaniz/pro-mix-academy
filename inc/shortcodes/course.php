<?php

/*
 * Output courses and course meta
 * ------------------------------
 */

/* Courses shortcode */

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

/* Course Meta shortcode */

function pma_course_meta_shortcode( $atts ) {
	$output = '';

	$share = do_shortcode( '[fusion_sharing]' );

	return $output;
}
add_shortcode( 'course_meta', 'pma_course_meta_shortcode' );
