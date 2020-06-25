<?php

/*
 * Utility / helper functions
 * --------------------------
 */

/* Get posts per page from options */

function pma_get_ppp( $post_type = 'post', $ajax = false ) {
    if( !$post_type )
        return 0;

    $suffix = $ajax ? '_ajax' : '';

    $ppp = $post_type == 'post' ? (int) get_option( 'posts_per_page' ) : (int) get_option( "pma_" . $post_type . "_ppp" . $suffix );

    if( !$ppp )
        $ppp = 10;

    return $ppp;
}

/* Get excerpt with short code expansion */

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

/* For load more button get next pagination link */

function pma_get_next_posts_link() {
    global $wp_query;
    global $paged;

    if( empty( $paged ) )
        $paged = 1;

    if( $paged === 0 )
        $paged = 1;

    $paged++;

    $total_pages = $wp_query->max_num_pages;

    if( $paged > $total_pages )
        return false;

    return get_pagenum_link( $paged );
}

/* Check query vars on query object */

function pma_check_wp_query_vars( $q, $prop, $val ) {
    if( !isset( $q ) )
       return false;

   if( empty( $q ) )
       return false;

   if( !property_exists( $q, 'query_vars' ) )
       return false;

   if( !isset( $q->query_vars[$prop] ) )
       return false;

   if( $q->query_vars[$prop] != $val )
       return false;

   return true;
}

/* String to associative array */

function pma_get_assoc_array( $str, $index = "\n", $sep = ' : ' ) {
    if( !$str )
        return [];

    $arr = explode( $index, $str );

    if( is_string( $arr ) )
        $arr = [$str];

    $assoc = [];

    foreach( $arr as $a ) {
        $aa = explode( $sep, $a );

        if( isset( $aa[1] ) ) {
            $assoc[$aa[0]] = $aa[1];
        } else {
            $assoc[] = $aa[0];
        }
    }

    return $assoc;
}

/*
 * Pass data to front end not passed with localize script.
 *
 * @param string/boolean $name Required.
 * @param array $data Required.
 * @param boolean $admin
 */

function pma_additional_script_data( $name = false, $data = [], $admin = false, $head = false ) {
   $action = $admin ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts';

   if( $head )
       $action = $admin ? 'admin_head' : 'wp_head';

   add_action( $action,
       function() use ( $name, $data ) {
           if( !$name || !$data )
               return;

           $var = 'data_' . uniqid(); ?>

           <script type="text/javascript">
               (function () {
                   var <?php echo $var; ?> = <?php echo json_encode( $data ); ?>;

                   if( window.hasOwnProperty( '<?php echo $name; ?>' ) ) {
                       // merge existing object with new data
                       for( var key in <?php echo $var; ?> ) {
                           window['<?php echo $name; ?>'][key] = <?php echo $var; ?>[key];
                       }
                   } else {
                       window['<?php echo $name; ?>'] = <?php echo $var; ?>;
                   }
               })();
           </script>

       <?php }
   );
}
