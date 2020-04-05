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
