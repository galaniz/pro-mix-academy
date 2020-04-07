<?php

/*
 * Output for horizontal cards
 * ---------------------------
 */

/* Output for outer cards */

function pma_render_cards_h( $args = [], $content ) {
    $args = array_merge( [
        'padding' => 'lg',
    ], $args );

    extract( $args );

    return
        "<div class='u-overflow-hidden'>" .
            "<div class='l-flex --wrap l-pad-h-$padding l-pad-v-container --m u-fig u-b-half js-insert'>" .
                $content .
            "</div>" .
        "</div>";
}

/* Output for single card */

function pma_render_card_h( $args = [] ) {
    $args = array_merge( [
        'subtext' => '',
    ], $args );

    extract( $args );

    $id = get_the_ID();
    $url = get_the_permalink();
    $title = get_the_title();

    $featured_img = get_the_post_thumbnail( $id, 'medium', [
        'class' => 'o-aspect-ratio__media u-object-cover'
    ] );

    if( $featured_img ) {
        $fig_classes = 'o-aspect-ratio --circle o-gray__fig';
        $featured_img =
            "<div class='l-pad-h__item l-w-150 u-m-b-auto o-gray --gray-30'>" .
                "<a class='u-transform-scale-fig' href='$url'><figure class='$fig_classes'>$featured_img</figure></a>" .
            "</div>";
    }

    $meta =
        "<div class='l-pad-h__item u-fade-out-links'>" .
            "<h3 class='u-color-primary-light u-m-0 l-pad-v-xs-b lg'>" .
                "<a href='$url'>$title</a>" .
            "</h3>" .
            ( $subtext ? "<div class='u-text'>$subtext</div>" : '' ) .
        "</div>";

    return
        "<div class='l-50 u-flex-grow-1 l-pad-v-m l-pad-h__item --max'>" .
            "<div class='l-flex l-pad-h-md --align-center'>" .
                $featured_img .
                $meta .
            "</div>" .
        "</div>";
}

/* Output for single small card */

function pma_render_card_h_sm( $args = [] ) {
    $args = array_merge( [
        'mentors' => [],
        'classes' => '',
        'img_item_class' => '',
        'width' => 50
    ], $args );

    extract( $args );

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
                                    "<div class='l-w-$width o-gray --gray-30'>" .
                                        "<figure class='o-aspect-ratio --circle o-gray__fig'>$mentor_img</figure>" .
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
