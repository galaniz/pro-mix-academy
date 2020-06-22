<?php

/*
 * Get posts shortcode
 * -------------------
 */

function pma_get_posts_shortcode( $atts ) {
    $atts = shortcode_atts( [
        'type' => 'course',
        'posts_per_page' => 10,
        'query_args' => [],
        'return_array' => false,
        'layout' => '',
        'gradient' => false,
        'category_slug' => '',
        'mentor_id' => 0, // course single
        'meta_key' => '',
        'meta_value' => '',
        'meta_type' => 'string',
        'show_content' => true,
        'horizontal' => true,
        'like_archive' => false,
        'ids' => '' // comma separated list of ids
    ], $atts, 'get-posts' );

    extract( $atts );

    // filter variables by type
    $posts_per_page = intval( $posts_per_page );
    $mentor_id = intval( $mentor_id );
    $gradient = filter_var( $gradient, FILTER_VALIDATE_BOOLEAN );
    $return_array = filter_var( $return_array, FILTER_VALIDATE_BOOLEAN );
    $show_content = filter_var( $show_content, FILTER_VALIDATE_BOOLEAN );
    $horizontal = filter_var( $horizontal, FILTER_VALIDATE_BOOLEAN );
    $like_archive = filter_var( $like_archive, FILTER_VALIDATE_BOOLEAN );

    if( $meta_type == 'int' || $meta_type == 'int-array' )
        $meta_value == intval( $meta_value );

    if( $meta_type == 'string' || $meta_type == 'string-array' ) 
        $meta_value = strval( $meta_value );

    $output = '';
    $total = 0;
    $row_count = 0;

    $archive = is_post_type_archive( $type );

    if( $type === 'course' && is_tax( 'course_category' ) )
        $archive = true;

    if( $like_archive ) {
        $posts_per_page = pma_get_ppp( $type );
        $archive = true;
    }

    // for courses
    $show_rating = (bool) get_option( 'pma_course_show_rating', '' );

    /* Layout */

    if( !$layout ) {
        if( $type === 'course' )
            $layout = 'cards_v';

        if( $type === 'mentor' )
            $layout = 'cards_h';

        if( $type === 'testimonial' )
            $layout = 'cards_flex';
    }

    /* Process query / args */

    global $wp_query;

    $q_condition = !pma_check_wp_query_vars( $wp_query, 'post_type', $type );

    if( $type === 'course' )
        $q_condition = $q_condition && !pma_check_wp_query_vars( $wp_query, 'taxonomy', 'course_category' );

    if( $q_condition ) {
        $args = [
            'post_type' => $type,
            'posts_per_page' => $posts_per_page
        ];

        if( $ids ) {
            $post_ids = explode( ',', $ids );

            $post_ids = array_map( function( $v ) {
                return (int) $v;
            }, $post_ids );

            $args['post__in'] = $post_ids;
        }

        if( !isset( $args['meta_query'] ) )
            $args['meta_query'] = [];

        if( $type === 'course' ) {
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
                $args['meta_query'][] = [
                    'key' => 'mentor',
                    'value' => "$mentor_id",
                    'compare' => 'LIKE'
                ];
            }

            /*
            // homepage meta
            if( is_front_page() ) {
                $args['meta_query'][] = [
                    'key' => 'homepage',
                    'value' => '1',
                    'compare' => 'LIKE'
                ];
                
                if( !$ignore_homepage_order ) {
                    $args['meta_key'] = 'homepage_order';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                }
            }
            */
        }

        // general meta
        if( !$return_array && $meta_value && $meta_key ) {
            $compare = '=';

            if( $meta_type == 'int-array' || $meta_type == 'string-array' )
                $compare = 'LIKE';

            $args['meta_query'][] = [
                'key' => $meta_key,
                'value' => $meta_value,
                'compare' => $compare
            ];

            pma_additional_script_data( 'pma_load_posts_query_static', [
                'meta_query' => [
                    [
                        'key' => $meta_key,
                        'value' => $meta_value,
                        'compare' => $compare
                    ]
                ]
            ] ); 
        }

        if( is_array( $query_args ) && count( $query_args ) > 0 ) {
            // merge query_args with args
            $args = array_replace_recursive( $args, $query_args );
        }   

        error_log( print_r( $args, true ) );

        $q = new WP_Query( $args );
    } else {
        $q = $wp_query;
    }

    /* The Loop */

    if( $q->have_posts() ) {
        $total = $q->found_posts;
        $row_count = $q->post_count;

        while( $q->have_posts() ) {
            $q->the_post();

            if( $layout === 'table' )
                $output .= pma_render_table_content( [
                    'type' => $type,
                    'show_rating' => $show_rating
                ] );

            if( $layout === 'cards_h' )
                $output .= pma_render_card_h( [
                    'subtext' => $type === 'mentor' ? get_field( 'artists', get_the_ID() ) : ''
                ] );

            if( $layout === 'cards_v' ) {
                $output .= pma_render_card_v( [
                    'gradient' => $gradient
                ] );
            }

            if( $layout === 'cards_flex' )
                $output .= pma_render_card_flex( [
                    'horizontal' => $horizontal,
                    'show_content' => $show_content
                ] );
        }

        if( !$return_array ) {
            if( $layout === 'table' ) {
                $labels = [];

                if( $type === 'course' )
                    $labels = [
                        [
                            'value' => 'Image',
                            'hide' => true
                        ],
                        [
                            'value' => 'Course'
                        ],
                        [
                            'value' => 'Mentor(s)'
                        ],
                        [
                            'value' => 'Price'
                        ],
                        [
                            'value' => 'Rating'
                        ],
                        [
                            'value' => 'Link',
                            'hide' => true
                        ]
                    ];

                $output = pma_render_table( [
                    'type' => $type,
                    'labels' => $labels
                ], $output );
            }

            if( $layout == 'cards_h' )
                $output = pma_render_cards_h( [
                    'padding' => $archive ? 'xl' : 'lg'
                ], $output );

            if( $layout == 'cards_v' ) {
                $output = pma_render_cards_v( [
                    'gradient' => $gradient
                ], $output );
            }

            if( $layout == 'cards_flex' )
                $output = pma_render_cards_flex( [
                    'horizontal' => $horizontal
                ], $output );

            /* Load more for archives */

            if( $archive ) {
                $next_posts_link = pma_get_next_posts_link();

                $tag = 'button';
                $hide_load_more = false;
                $href = '';

                $ppp = pma_get_ppp( $type );
                $ajax_ppp = pma_get_ppp( $type, true );

                if( $total <= 1 ) {
                    $hide_load_more = true;
                } else {
                    if( $like_archive ) {
                        if( $total <= $ppp )
                            $hide_load_more = true;
                    } else {
                        if( !$next_posts_link )
                            $hide_load_more = true;
                    }
                }

                if( $hide_load_more )
                    $hide_load_more = ' style="display:none;"';

                if( $next_posts_link ) {
                    $tag = 'a';
                    $href = " href='$next_posts_link'";
                } else {
                    $href = " type='button'";
                }

                $output .=
                    '<div class="js-no-results l-pad-v-t u-p-0" style="display:none;">' .
                        '<p>Sorry looks like nothing was found.</p>' .
                    '</div>' .
                    "<div class='l-pad-v-x" . ( $layout === 'cards_h' ? 'x' : '' ) . "l-t u-text-align-center u-position-relative" . ( $type === 'course' ? ' u-b-top' : '' ) . "'$hide_load_more>" .
                        "<$tag class='o-button js-load-more u-color-background-light'$href data-type='$type'" . ( $ajax_ppp ? " data-ajax-posts-per-page='$ajax_ppp'" : '' ) . " data-posts-per-page='$ppp' data-total='$total' data-insert-selector='.js-insert'>" .
                            "<div class='o-subtext u-height-100 l-flex --align-center'>" .
                                __( 'Load More' ) .
                            "</div>" .
                            "<div class='o-loader --hide'>" .
                                "<div class='o-loader__icon --sm'></div>" .
                            "</div>" .
                        "</$tag>" .
                    "</div>";
            }
        }

        wp_reset_postdata();
    }

    if( $return_array ) {
        return [
            'row_count' => $row_count,
            'output' => $output,
            'total' => $total
        ];
    } else {
        return $output;
    }
}
add_shortcode( 'get-posts', 'pma_get_posts_shortcode' );
