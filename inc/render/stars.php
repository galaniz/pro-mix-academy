<?php

/*
 * Output for stars
 * ----------------
 */

function pma_render_stars( $rating = 0 ) {
    $stars = "&starf;&starf;&starf;&starf;&starf;";
    $percent = ( $rating / 5 ) * 100;

    return
        "<div class='o-stars'>" .
            "<div class='o-stars__bg'>$stars</div>" .
            "<div class='o-stars__fg u-color-primary-light' style='width:$percent%'>$stars</div>" .
        "</div>";
}
