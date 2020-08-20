<?php

/*
 * Output course meta, filters...
 * ------------------------------
 */

/* Course meta shortcode */

function pma_course_meta_shortcode( $atts ) {
	$output = '<div class="l-pad-h-md u-fig l-pad-v-container l-flex --wrap --align-center --justify-center --md-b">';

    $id = get_the_ID();

	// meta
	$mentors = get_field( 'mentor', $id );
	$duration = (int) get_field( 'duration', $id );
	$price = (int) get_field( 'price', $id );
	$price_display = str_replace( ['<p>', '</p>'], '', get_field( 'price_display', $id ) );
	$buy_link = get_field( 'buy_link', $id );
	$show_rating = (bool) get_option( 'pma_course_show_rating', '' );

	// mentor meta
	$output .= pma_render_card_h_sm( [
		'mentors' => $mentors,
		'classes' => 'l-pad-h__item l-pad-v-md-b'
	] );

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
		$price = $price_display ? $price_display : '&dollar;' . $price;
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

	// rating
	if( $show_rating ) {
		$output .=
			'<div class="l-pad-h__item l-pad-v-md-b">' .
				pma_render_stars( (float) get_field( 'rating', $id ) ) .
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
add_shortcode( 'course-meta', 'pma_course_meta_shortcode' );

/* Course mentors shortcode */

function pma_course_mentors_shortcode( $atts ) {
	$atts = shortcode_atts( [
		'contain' => 'true'
	], $atts, 'course-mentors' );

	extract( $atts );

	$contain = filter_var( $contain, FILTER_VALIDATE_BOOLEAN );

	$output = "<div class='l-pad-v-container u-fig --xl-b'>";

	// mentors
	$mentors = get_field( 'mentor', get_the_ID() );

	if( is_array( $mentors ) ) {
		$order = 0;

		foreach( $mentors as $mentor ) {
			$name = get_the_title( $mentor );
			$url = get_the_permalink( $mentor );
			$excerpt = pma_get_excerpt( $mentor, 50 );
			$img = (int) get_field( 'landscape_featured_image', $mentor );

			if( $img ) {
				$img = wp_get_attachment_image( $img, 'large' );
			} else {
				$img = get_the_post_thumbnail( $mentor, 'large' );
			}

			if( $img ) {
				$img =
					"<a class='l-flex-equal u-transform-scale o-gray --gray-30' href='$url'>" .
						"<figure class='l-flex-equal u-overflow-hidden o-gray__fig'>$img</figure>" .
					"</a>";
			}

			$contain_class = $contain ? " --contain" : '';

			$media =
				"<div class='l-50 l-pad-h__item l-col__media --max-h$contain_class'>" .
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
add_shortcode( 'course-mentors', 'pma_course_mentors_shortcode' );

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
            "<div class='js-load-more-filters-loader o-loader --hide'>" .
                "<div class='o-loader__icon'></div>" .
            "</div>" .
            "<form class='o-loader-before js-load-more-filters l-pad-v-container --b'>" .
                "<div class='l-pad-v-b l-pad-v-lg-t'>" .
                    "<div class='l-pad-h-xs l-pad-v-container --md-b l-flex --align-center --wrap'>" .
                        $output .
                        '<div class="l-pad-h__item l-pad-v-md-b js-no-results" style="display:none;">' .
                            '<button class="js-no-results__button o-button o-subtext" type="button">Reset</button>' .
                        '</div>' .
                    "</div>" .
                "</div>" .
            "</form>" .
        "</div>";

    pma_additional_script_data( 'pma_load_posts_query', $load_posts_query );

    return $output;
}
add_shortcode( 'courses-filters', 'pma_courses_filters_shortcode' );
