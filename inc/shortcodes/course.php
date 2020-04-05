<?php

/*
 * Output courses and course meta
 * ------------------------------
 */

 /* Helpers */

 function pma_get_excerpt( $id = 0, $length = 20 ) {
	 if( !$id )
	 	return;

	 $excerpt = get_the_excerpt( $id );

	 if( !$excerpt ) {
		 $post = get_post( $id );
		 $content = $post->post_content;
		 $content = apply_filters( 'the_content', $content );
		 $content = str_replace(']]>', ']]&gt;', $content );
		 $excerpt = wp_trim_words( $content, $length );
	 }

	 return $excerpt;
 }

 function pma_format_mentors( $mentors = [], $classes = '', $img_item_class = '', $width = 50 ) {
 	if( is_array( $mentors ) ) {
 		$get_mentor_img = count( $mentors ) === 1;
 		$output = '<div class="u-color-primary-light u-fade-out-links' . ( $classes ? " $classes" : '' ) . '">';
 		$m = [];

		if( $img_item_class )
			$img_item_class = " $img_item_class";

 		foreach( $mentors as $mentor ) {
 			$mentor_name = get_the_title( $mentor );
 			$mentor_url = get_the_permalink( $mentor );
 			$mentor_link = "<a class='u-color-primary-light' href='$mentor_url'>$mentor_name</a>";

 			if( $get_mentor_img ) {
 				$mentor_img = get_the_post_thumbnail( $mentor, 'post-thumbnail', [
 					'class' => 'o-aspect-ratio__media u-object-cover'
 				] );

 				if( $mentor_img ) {
 					$output .=
 						"<div class='l-flex l-pad-h-xs --align-center'>" .
 							"<div class='l-pad-h__item$img_item_class'>" .
 								"<a href='$mentor_url'>" .
 									"<div class='l-w-$width'>" .
 										"<figure class='o-aspect-ratio --circle'>$mentor_img</figure>" .
 									"</div>" .
 								"</a>" .
 							"</div>" .
 							"<div class='l-pad-h__item'>" .
 								$mentor_link .
 							"</div>" .
 						"</div>";
 				}
 			} else {
 				$m[] = $mentor_link;
 			}
 		}

 		if( !$get_mentor_img )
 			$output .= implode( ', ', $m );

 		$output .= '</div>';

 		return $output;
 	} else {
 		return '';
 	}
 }


 /* Courses table shortcode */

 function pma_courses_shortcode( $atts ) {
 	$atts = shortcode_atts( [
         'posts_per_page' => 10,
		 'load_more' => 10,
		 'only_rows' => false,
         'show_rating' => false,
         'query_args' => [],
         'return_array' => false
 	], $atts, 'courses' );

     extract( $atts );

     $output = '';
     $total = 0;
     $row_count = 0;
	 $archive = true;

	 $only_rows = $only_rows === 'true' ? true : false;
     $show_rating = $show_rating === 'true' ? true : false;
     $return_array = $return_array === 'true' ? true : false;

     global $wp_query;

     if( !pma_check_wp_query_vars( $wp_query, 'post_type', 'course' ) && !pma_check_wp_query_vars( $wp_query, 'taxonomy', 'course_category' ) ) {
        $args = [
            'post_type' => 'course',
            'posts_per_page' => $posts_per_page
        ];

        if( is_array( $query_args ) && count( $query_args ) > 0 ) {
            // merge query_args with args
            $args = array_replace_recursive( $args, $query_args );
        }

        if( isset( $args['s'] ) && function_exists( 'relevanssi_do_query' ) ) {
            $q = new WP_Query();
            $q->parse_query( $args );
            $blah = relevanssi_do_query( $q );

            error_log( print_r( $args, true ) );
            error_log( print_r( $blah, true ) );
        } else {
            $q = new WP_Query( $args );
        }

        $archive = false;
    } else {
        $q = $wp_query;
    }

     if( $q->have_posts() ) {
         $total = $q->found_posts;
         $row_count = $q->post_count;

		 if( !$only_rows ) {
			 $output .=
			 	'<table class="o-table u-fig" data-collapse="false" data-courses="true">' .
					'<thead>' .
						'<tr>' .
							"<th><div class='u-visually-hidden'>Image</div></th>" .
							"<th>Course</th>" .
							"<th>Mentor(s)</th>" .
							"<th>Price</th>" .
							( $show_rating ? "<th>Rating</th>" : "" ) .
							"<th><div class='u-visually-hidden'>Link</div></th>" .
						'</tr>' .
					'</thead>' .
					'<tbody class="js-insert">';
		 }

         while( $q->have_posts() ) {
             $q->the_post();

             $id = get_the_ID();
             $url = get_the_permalink();
             $title = get_the_title();
			 $excerpt = pma_get_excerpt( $id );
			 $gradient = (bool) get_field( 'gradient', $id );
			 $mentors = get_field( 'mentor', $id );
             $rating = (int) get_field( 'rating', $id );
			 $price = (int) get_field( 'price', $id );
			 $price = '&dollar;' . $price;

			 /* Featured image */

			 $featured_img = '';

			 $featured_img = get_the_post_thumbnail( $id, 'medium', [
                 'class' => 'o-aspect-ratio__media ' . ( $gradient ? 'u-object-contain' : 'u-object-cover' )
             ] );

			 if( $featured_img ) {
				 $fig_classes = 'o-aspect-ratio u-height-100 u-op-70 --p-65' . ( $gradient ? ' o-gray__item' : '' );
				 $featured_img =
				 	"<a class='u-width-100 u-display-block o-table__thumb' href='$url'>" .
						"<figure class='$fig_classes'>$featured_img</figure>" .
					"</a>";
			 }

			 /* Mentors */

			 $mentors_output = pma_format_mentors( $mentors, '', 'o-table__collapse' );

             /* Rating output */

             $rating_output = '';

             if( $show_rating ) {
                 $stars = "&starf;&starf;&starf;&starf;&starf;";
                 $percent = ( $rating / 5 ) * 100;

                 $rating_output =
                    "<td class='o-table__stars u-text-sm'>" .
                        "<div class='o-stars'>" .
                            "<div class='o-stars__bg'>$stars</div>" .
                            "<div class='o-stars__fg u-color-primary-light' style='width:$percent%'>$stars</div>" .
                        "</div>" .
                    "</td>";
             }

			 $output .=
			 	'<tr>' .
					"<td class='u-fade-out-links'>$featured_img</td>" .
					"<td class='o-table__pl o-table__pb u-fade-out-links u-p-0'>" .
						"<div class='o-table__desc'>" .
							( $excerpt ? "<div class='l-pad-v-sm-b'>" : '' ) .
								"<h4 class='o-table__title u-m-0 o-clamp --l-2'>" .
									"<a class='u-color-background-light' href='$url'>$title</a>" .
								"</h4>" .
							( $excerpt ? "</div>" : '' ) .
							( $excerpt ? "<p class='o-table__text u-text-sm o-table__collapse o-clamp --l-2'>$excerpt</p>" : '' ) .
						"</div>" .
					"</td>" .
					"<td class='o-table__pl o-table__pb o-table__meta u-text'>$mentors_output</td>" .
					"<td class='o-table__pl u-text'><div>$price</div></td>" .
                    $rating_output .
					"<td class='o-table__collapse u-text-align-right'>" .
						"<a class='o-button u-color-background-light u-nowrap o-subtext-sm --sm' href='$url'>" . __( 'Learn More' ) . "</a>" .
					"</td>" .
				'</tr>';
         }

        if( !$only_rows ) {
            $output .=
                    '</tbody>' .
                '</table>';

            $next_posts_link = pma_get_next_posts_link();

            $tag = 'button';
            $hide_load_more = false;
            $href = '';

            if( $total <= 1 ) {
                $hide_load_more = true;
            } else {
                if( !$next_posts_link )
                    $hide_load_more = true;
            }

            if( $hide_load_more )
                $hide_load_more = ' style="display:none;"';

            if( $next_posts_link ) {
                $tag = 'a';
                $href = " href='$next_posts_link'";
            } else {
                $href = " type='button'";
            }

            $ppp = 3;
            $ajax_ppp = 3;

            $output .=
                '<div class="js-no-results u-p-0" style="display:none;">' .
                    '<p>Sorry looks like nothing was found.</p>' .
                '</div>' .
                "<div class='l-pad-v-xl-t u-text-align-center u-b-top u-position-relative'$hide_load_more>" .
                    "<$tag class='o-button js-load-more u-color-background-light'$href data-type='course'" . ( $ajax_ppp ? " data-ajax-posts-per-page='$ajax_ppp'" : '' ) . " data-posts-per-page='$ppp' data-total='$total' data-insert-selector='.js-insert'>" .
                        "<div class='o-subtext u-height-100 l-flex --align-center'>" .
                            "Load More" .
                        "</div>" .
                        "<div class='o-loader --hide'>" .
                            "<div class='o-loader__icon --sm'></div>" .
                        "</div>" .
                    "</$tag>" .
                "</div>";
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
 add_shortcode( 'courses', 'pma_courses_shortcode' );

/* Courses cards shortcode */

function pma_courses_cards_shortcode( $atts ) {
	$atts = shortcode_atts( [
		'gradient' => false,
		'category_slug' => '',
        'posts_per_page' => 3,
        'mentor_id' => 0
	], $atts, 'courses_cards' );

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
        $output = '<div class="l-flex l-pad-h-m' . ( $gradient ? 'd' : '' ) . ' l-pad-v-container u-fig --xl-b --wrap">';

        while( $q->have_posts() ) {
            $q->the_post();

            $id = get_the_ID();
            $url = get_the_permalink();

            $featured_img = get_the_post_thumbnail( $id, 'large', [
                'class' => 'o-aspect-ratio__media ' . ( $gradient ? 'u-object-contain' : 'u-object-cover' )
            ] );

            if( $featured_img ) {
                $fig_classes = 'o-aspect-ratio u-op-70 l-max-h-400 --p-65 ' . ( $gradient ? 'o-gray__item' : '' );
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
add_shortcode( 'courses_cards', 'pma_courses_cards_shortcode' );

/* Course meta shortcode */

function pma_course_meta_shortcode( $atts ) {
	$output = '<div class="l-pad-h-md u-fig l-pad-v-container l-flex --wrap --align-center --justify-center --md-b">';

    $id = get_the_ID();

	// meta
	$mentors = get_field( 'mentor', $id );
	$duration = (int) get_field( 'duration', $id );
	$price = (int) get_field( 'price', $id );
	$buy_link = get_field( 'buy_link', $id );
    $rating = (int) get_field( 'rating', $id );

	// mentor meta
	$output .= pma_format_mentors( $mentors, 'l-pad-h__item l-pad-v-md-b' );

	// duration meta
	if( $duration ) {
		$time_icon = '<i class="fa-clock far"></i>';

		if( $duration >= 60 ) {
			$duration = date( 'g\h i', mktime( 0, $duration ) ) . 'min';
		} else {
			$duration = date( 'i', mktime( 0, $duration ) ) . 'min';
		}

		$output .=
			'<div class="l-pad-h__item l-pad-v-md-b l-flex --align-center">' .
				"<div class='u-time'>$time_icon</div>" .
				"<div>$duration</div>" .
			'</div>';
	}

	// price meta
	if( $price ) {
		$price = '&dollar;' . $price;
		$buy_now = '';

		if( $buy_link ) {
			$buy_now =
				"<div class='l-pad-h__item'>" .
					"<a class='o-button o-subtext' href='$buy_link'>" . __( 'Buy Now' ) . "</a>" .
				"</div>";
		}

		$output .=
			'<div class="l-pad-h__item l-pad-v-md-b">' .
				"<div class='l-pad-h-sm l-flex --align-center'>" .
					"<div class='l-pad-h__item'>" .
						"<h3 class='lg u-m-0'>$price</h3>" .
					"</div>" .
					$buy_now .
				"</div>" .
			'</div>';
	}

	// share links
	$output .=
		"<div class='l-pad-h__item l-pad-v-md-b'>" .
			do_shortcode( '[fusion_sharing tagline="Share" class="u-m-0"]' ) .
		"</div>";

	$output .= '</div>';

	return $output;
}
add_shortcode( 'course_meta', 'pma_course_meta_shortcode' );

/* Course mentors shortcode */

function pma_course_mentors_shortcode( $atts ) {
	$output = "<div class='l-pad-v-container u-fig --xl-b'>";

	// mentors
	$mentors = get_field( 'mentor', get_the_ID() );

	if( is_array( $mentors ) ) {
		$order = 0;

		foreach( $mentors as $mentor ) {
			$name = get_the_title( $mentor );
			$url = get_the_permalink( $mentor );
			$img = get_the_post_thumbnail( $mentor, 'large' );
			$excerpt = pma_get_excerpt( $mentor, 50 );

			if( $img ) {
				$img =
					"<a class='l-flex-equal u-transform-scale' href='$url'>" .
						"<figure class='l-flex-equal u-overflow-hidden'>$img</figure>" .
					"</a>";
			}

			$media =
				"<div class='l-50 l-pad-h__item l-col__media --max-h --contain'>" .
					"<div class='l-col__inner'>" .
						"<div class='l-col__content l-flex-equal'>" .
							$img .
						"</div>" .
					"</div>" .
				"</div>";

			$text =
				"<div class='l-50 l-pad-h__item l-col__text u-fade-out-links'>" .
					"<div class='l-col__inner'>" .
						"<div class='l-col__content l-flex-equal'>" .
							"<h3 class='lg'><a class='u-color-primary-light' href='$url'>$name</a></h3>" .
							( $excerpt ? "<p>$excerpt</p>" : "" ) .
							"<a class='o-subtext u-color-background-light' href='$url'>Learn More</a>" .
						"</div>" .
					"</div>" .
				"</div>";

			$output .=
				"<div class='l-col l-pad-v-xl-b l-pad-h-xl l-flex --wrap --align-center'>" .
					( !$order ? $media : $text ) .
					( !$order ? $text : $media ) .
				"</div>";

			$order = !$order;
		}
	}

	$output .= '</div>';

	return $output;
}
add_shortcode( 'course_mentors', 'pma_course_mentors_shortcode' );

/* Courses filters */

function pma_courses_filters_shortcode( $atts ) {
    $output = '';
    $load_posts_query = [];

    $tax = 'course_category';

    $cat = get_terms( [
        'taxonomy' => $tax,
        'hide_empty' => true
    ] );

    if( $cat ) {
        $any_checked = false;

        $cat = array_map( function( $c ) use( &$any_checked, $tax ) {
            $checked = is_tax( $tax, $c->term_id );

            if( $checked )
                $any_checked = true;

            return [
                'checked' => $checked,
                'value' => $c->term_id,
                'label' => $c->name
            ];
        }, $cat );

        array_unshift( $cat, [
            'checked' => !$any_checked ? true : false,
            'value' => 'null',
            'label' => 'All'
        ] );

        foreach( $cat as $c ) {
            $id = 'pma_course_cat_' . uniqid();
            $label = $c['label'];
            $value = $c['value'];
            $checked = $c['checked'] ? ' checked' : '';

            $output .=
                "<div class='o-button-radio l-pad-h__item l-pad-v-md-b'>" .
                    "<label>" .
                        "<input class='u-hide-input js-load-more-filter' type='radio' id='$id' name='pma_course_cat' value='$value'$checked>" .
                        "<div class='o-subtext o-button --outline --sm --radio'>$label</div>" .
                    "</label>" .
                "</div>";
        }

        $load_posts_query['pma_course_cat'] = [
            'tax_query' => [
                [
                    'taxonomy' => $tax,
                    'terms' => '%value:int'
                ]
            ]
        ];
    }

    $search_id = uniqid( 'pma_search_' );
    $load_posts_query[$search_id] = [
        's' => '%value'
    ];

    $output .=
        "<div class='l-pad-h__item l-pad-v-md-b'>" .
            "<button class='c-search-trigger u-color-background-light l-flex --align-center --justify-center' type='button' aria-expanded='false' aria-controls='js-search'>" .
                "<div>" .
                    "<div class='u-visually-hidden'>Toggle search input</div>" .
                    "<i class='fas fa-search'></i>" .
                    "<i class='fas fa-times'></i>" .
                "</div>" .
            "</button>" .
        "</div>" .
        "<div class='c-search u-flex-grow-1 l-pad-h__item l-pad-v-md-b' id='js-search' data-open='false'>" .
            "<div class='u-position-relative'>" .
                "<label for='$search_id'>" .
                    "<span class='u-visually-hidden'>" . __( 'Search for:' ) . "</span>" .
                "</label>" .
                "<input class='c-search__input u-color-background-light js-load-more-filter' id='$search_id' type='search' name='pma_course_search' placeholder='Search' data-submit-selector='.c-search__submit'>" .
                "<button class='c-search__submit u-color-background-light' type='button'>" .
                    "<div class='u-visually-hidden'>Submit search query</div>" .
                    "<i class='fas fa-search'></i>" .
                "</button>" .
            "</div>" .
        "</div>";

    $output =
        "<div class='u-position-relative'>" .
            "<form class='js-load-more-filters l-pad-v-container --b'>" .
                "<div class='l-pad-v-b'>" .
                    "<div class='l-pad-h-xs l-pad-v-container --md-b l-flex --align-center --wrap'>" .
                        $output .
                        '<div class="l-pad-h__item l-pad-v-md-b js-no-results" style="display:none;">' .
                            '<button class="js-no-results__button o-button o-subtext" type="button">Reset</button>' .
                        '</div>' .
                    "</div>" .
                "</div>" .
            "</form>" .
            "<div class='js-load-more-filters-loader o-loader --hide'>" .
                "<div class='o-loader__icon'></div>" .
            "</div>" .
        "</div>";

    pma_additional_script_data( 'pma_load_posts_query', $load_posts_query );

    return $output;
}
add_shortcode( 'courses_filters', 'pma_courses_filters_shortcode' );
