<?php

/*
 * Admin customizations
 * --------------------
 */

 /* Hide portfolio cpt */

 function pma_remove_menu_items() {
     remove_menu_page( 'edit.php?post_type=avada_portfolio' );
 }
 add_action( 'admin_menu', 'pma_remove_menu_items' );

 /* Load mentors on course backend */

 function pma_acf_load_mentors( $field ) {
     $mentors = get_posts( [
         'post_type' => 'mentor',
         'numberposts' => -1
     ] );

     $m = [];

     if( $mentors ) {
         foreach( $mentors as $mentor ) {
            $m[$mentor->ID] = $mentor->post_title;
         }
     }

     if( $m ) {
         $field['choices'] = $m;
     }

     return $field;
 }
 add_filter( 'acf/load_field/name=mentor', 'pma_acf_load_mentors' );
