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

 /* Cards shortcode */

 function pma_courses_shortcode( $atts ) {
 	$atts = shortcode_atts( [
         'posts_per_page' => 10,
		 'load_more' => 10,
		 'only_rows' => false
 	], $atts, 'courses' );

     extract( $atts );

     $output = '';
	 $archive = true;
	 $only_rows = $only_rows === 'true' ? true : false;

     global $wp_query;

     if( $wp_query->query_vars['post_type'] !== 'course' ) {
         $args = [
             'post_type' => 'course',
             'posts_per_page' => $posts_per_page
         ];

         $q = new WP_Query( $args );

         $archive = false;
     } else {
         $q = $wp_query;
     }

     if( $q->have_posts() ) {
		 if( !$only_rows ) {
			 $output .=
			 	'<table class="o-table">' .
					'<thead>' .
						'<tr>' .
							"<th><div class='u-visually-hidden'>Image</div></th>" .
							"<th>Course</th>" .
							"<th>Mentor(s)</th>" .
							"<th>Price</th>" .
							"<th><div class='u-visually-hidden'>Link</div></th>" .
						'</tr>' .
					'</thead>' .
					'<tbody>';
		 }

         while( $q->have_posts() ) {
             $q->the_post();

             $id = get_the_ID();
             $url = get_the_permalink();
             $title = get_the_title();
			 $excerpt = pma_get_excerpt( $id );
			 $mentors = get_field( 'mentor', $id );
			 $price = (int) get_field( 'price', $id );
			 $price = '&dollar;' . $price;

			 $featured_img = '';
			 $mentors_output = '';

			 $output .=
			 	'<tr>' .
					"<td>$featured_img</td>" .
					"<td>" .
						"<h4>$title</h4>" .
						"<p class='u-text-sm o-clamp --l-2'>$excerpt</p>" .
					"</td>" .
					"<td>$mentors_output</td>" .
					"<td>$price</td>" .
					"<td><a class='o-button u-color-background-light o-subtext-sm --sm' href='$url'>" . __( 'Learn More' ) . "</a></td>" .
				'</tr>';
         }

		 if( !$only_rows ) {
			 $output .=
			 		'</tbody>' .
			 	'</table>';
		 }

         wp_reset_postdata();
     }

 	return $output;
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
                'class' => 'o-aspect-ratio__media ' . ( $gradient ? 'u-object-contain' : 'u-object-cover u-opacity-30' )
            ] );

            if( $featured_img ) {
                $fig_classes = 'o-aspect-ratio l-max-h-400 --p-65 ' . ( $gradient ? 'o-gray__item' : 'u-op-70' );
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

	// mentor meta
	if( is_array( $mentors ) ) {
		$get_mentor_img = count( $mentors ) === 1;
		$output .= '<div class="l-pad-h__item l-pad-v-md-b u-color-primary-light u-fade-out-links">';
		$m = [];

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
							"<div class='l-pad-h__item'>" .
								"<a href='$mentor_url'>" .
									"<div class='l-w-50'>" .
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
	}

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
