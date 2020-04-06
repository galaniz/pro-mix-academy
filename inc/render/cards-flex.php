<?php

/*
 * Output for cards
 * ----------------
 */

/* Output for outer cards */

function pma_render_cards_flex( $args = [], $content ) {
    $args = array_merge( [
        'horizontal' => true
    ], $args );

    extract( $args );

    $container_classes = 'u-fig u-overflow-hidden l-pad-v-container --' . ( $horizontal ? 'xxl' : 'lg' ) . '-b';

    if( $horizontal )
        $container_classes .= ' l-pad-h l-flex l-flex-wrap-900 js-insert';

    return "<div class='$container_classes'>$content</div>";
}

/* Output for outer cards */

function pma_render_card_flex( $args = [] ) {
    $args = array_merge( [
        'horizontal' => true,
        'show_content' => true
    ], $args );

    extract( $args );

    $output = '';

    $id = get_the_ID();
    $name = get_the_title();
    $title = get_field( 'subtitle', $id );
    $content = get_the_content();

    if( $content ) {
        if( substr( $content, 0, 3 ) !== '<p>' )
            $content = "<p>$content</p>";
    }

    $featured_img = get_the_post_thumbnail( $id, 'medium', [
        'class' => 'o-aspect-ratio__media u-object-cover'
    ] );

    if( $featured_img ) {
        $fig_classes = 'o-aspect-ratio --circle o-gray --gray-30';
        $featured_img =
            "<div class='l-pad-v-b l-w-150" . ( !$horizontal ? ' --fixed l-pad-h__item u-m-b-auto' : ' --lg u-m-auto' ) . "'>" .
                "<figure class='$fig_classes'>$featured_img</figure>" .
            "</div>";
    }

    $meta =
        "<h3 class='u-color-primary-light u-m-0 l-pad-v-xs-b'>$name</h3>" .
        ( $title ? "<div class='u-text-sm'>$title</div>" : "" );

    if( !$horizontal )
        $meta =
            "<div class='l-flex" . ( $show_content && $content ? ' l-w-150-meta' : '' ) . "'>" .
                "<div>" .
                    $meta .
                "</div>" .
            "</div>";

    if( $show_content && $content )
        $meta .= '<div class="l-pad-v-md-t u-text u-p-0' . ( !$horizontal ? ' l-w-150-text' : '' ) . '">' . $content . "</div>";

    if( !$horizontal ) {
        $output .=
            "<div class='l-flex l-pad-v-lg-b l-pad-h-md l-pad-v-container --b" . ( !$show_content || !$content ? ' --align-center' : '' ) . "'>" .
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

    return $output;
}
