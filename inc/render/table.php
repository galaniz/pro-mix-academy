<?php

/*
 * Output for table
 * ----------------
 */

/* Output for outer table */

function pma_render_table( $args = [], $content = '' ) {
    $args = array_merge( [
        'labels' => [],
        'type' => 'course'
    ], $args );

    extract( $args );

    $headings = '';

    foreach( $labels as $label ) {
        $value = $label['value'];

        if( isset( $label['hide'] ) )
            $value = "<div class='u-visually-hidden'>$value</div>";

        $headings .= "<th>$value</th>";
    }

    return
        "<table class='o-table u-fig' data-collapse='false' data-$type='true'>" .
            '<thead>' .
                '<tr>' .
                    $headings .
                '</tr>' .
            '</thead>' .
            '<tbody class="js-insert">' .
                $content .
            '</tbody>' .
        '</table>';
}

/* Output for table rows */

function pma_render_table_content( $args = [] ) {
    $args = array_merge( [
        'type' => 'course',
        'show_rating' => false
    ], $args );

    extract( $args );

    $id = get_the_ID();
    $url = get_the_permalink();
    $title = get_the_title();
    $excerpt = pma_get_excerpt( $id );
    $gradient = (bool) get_field( 'gradient', $id );
    $additional_cells = '';

    /* Featured image */

    $featured_img = '';

    $featured_img = get_the_post_thumbnail( $id, 'medium', [
        'class' => 'o-aspect-ratio__media ' . ( $gradient ? 'u-object-contain' : 'u-object-cover' )
    ] );

    if( $featured_img ) {
        $fig_classes = 'o-aspect-ratio o-gray__fig u-height-100 u-op-70 --p-65' . ( $gradient ? ' o-gray__item' : '' );
        $featured_img =
            "<a class='u-width-100 u-display-block o-table__thumb o-gray --gray-30' href='$url'>" .
                "<figure class='$fig_classes'>$featured_img</figure>" .
            "</a>";
    }

    /* Course specific cells */

    if( $type === 'course' || $type === 'hit_the_road_music' ) {
        $mentors = get_field( 'mentor', $id );
        $rating = (float) get_field( 'rating', $id );
        $price = (int) get_field( 'price', $id );
        $price_display = str_replace( ['<p>', '</p>'], '', get_field( 'price_display', $id ) );
        $price = $price_display ? $price_display : '&dollar;' . $price;

        /* Mentors */

        if( $mentors ) {
            $mentors_output = pma_render_card_h_sm( [
                'mentors' => $mentors,
                'img_item_class' => 'o-table__collapse',
                'width' => 50
            ] );

            if( $mentors_output )
                $additional_cells .= "<td class='o-table__pl o-table__pb o-table__meta u-text'>$mentors_output</td>";
        }

        $additional_cells .= "<td class='o-table__pl u-text'><div>$price</div></td>";

        /* Rating output */

        if( $show_rating ) {
            $additional_cells .=
                "<td class='o-table__stars u-text-sm'>" .
                    pma_render_stars( $rating ) .
                "</td>";
        }
    }

    return
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
            $additional_cells .
            "<td class='o-table__collapse u-text-align-right'>" .
                "<a class='o-button u-color-background-light u-nowrap o-subtext-sm --sm --no-scale' href='$url'>" .
                    "<span class='o-button__text'>" . __( 'Learn More' ) . "</span>" .
                "</a>" .
            "</td>" .
        '</tr>';
}
