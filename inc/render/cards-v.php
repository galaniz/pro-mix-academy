<?php

/*
 * Output for vertical cards
 * -------------------------
 */

 /* Output for outer cards */

 function pma_render_cards_v( $args = [], $content ) {
     $args = array_merge( [
         'gradient' => false,
     ], $args );

     extract( $args );

     return
         '<div class="l-flex l-pad-h-m' . ( $gradient ? 'd' : '' ) . ' l-pad-v-container u-fig --xl-b --wrap js-insert">' .
             $content .
         "</div>";
 }

 /* Output for single card */

 function pma_render_card_v( $args = [] ) {
     $args = array_merge( [
         'gradient' => false,
     ], $args );

     extract( $args );

     $id = get_the_ID();
     $title = get_the_title( $id );
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
         $mentor_name = get_the_title( $mentor );
         $mentor_url = get_the_permalink( $mentor );

         $meta =
             "<h3 class='u-m-0 l-pad-v-xs-b'><a href='$url'>$title</a></h3>" .
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

     $item_classes = 'l-33 --max l-pad-h__item l-flex-equal l-pad-v-xl-b' . ( $gradient ? ' o-gray --gray-100' : '' );

     return
         "<div class='$item_classes'>" .
             $featured_img .
             $meta .
         "</div>";
 }
