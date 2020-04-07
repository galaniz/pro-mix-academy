<?php

/*
 * Output single mentor
 * --------------------
 */

/* Mentor shortcode */

function pma_mentor_shortcode( $atts ) {
    $output = '';

    $id = get_the_ID();
    $name = get_the_title();
    $artists = get_field( 'artists', $id );
	$social = get_field( 'social', $id );

	/* Featured image */

    $featured_img = get_the_post_thumbnail( $id, 'large', [
        'class' => 'o-aspect-ratio__media u-object-cover'
    ] );

    if( $featured_img ) {
        $fig_classes = 'o-aspect-ratio --circle o-gray__fig';
        $featured_img = "<figure class='$fig_classes'>$featured_img</figure>";
    }

	/* Social links */

	$social_output = '';

	if( $social ) {
		$social_array = pma_get_assoc_array( $social );

		if( $social_array ) {
			$social_output .=
				"<div class='l-pad-v-xl-t'>" .
					"<div class='l-pad-h-sm l-pad-v-container --xs l-flex --wrap --justify-center --align-center'>";

			foreach( $social_array as $s => $link ) {
				$s_lower = strtolower( $s );

				$social_output .=
					"<div class='l-pad-h__item l-pad-v-xs'>" .
						"<a class='o-social u-color-primary-light fusion-$s_lower fusion-icon-$s_lower' href='$link' rel='noopener noreferrer'>" .
							"<div class='u-visually-hidden'>$s</div>" .
						"</a>" .
					"</div>";
			}

			$social_output .=
					"</div>" .
				"</div>";
		}
	}

    $left =
        "<div class='l-pad-h__item l-pad-v-lg-b u-fig l-w-300 u-thumb-up u-flex-shrink-0'>" .
			"<div class='o-gray --gray-40'>$featured_img</div>" .
            $social_output .
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

    $courses = do_shortcode( '[get-posts type="course" posts_per_page="3" mentor_id="' . $id . '" ]' );

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
