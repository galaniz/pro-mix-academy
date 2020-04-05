<?php

/*
 * Functions
 * ---------
 */




 /* Filter single term titles */

 add_filter( 'single_term_title', function( $term_name ) {
     if( is_tax( 'course_category' ) ) {
         $queried_object = get_queried_object();

         if( $queried_object ) {
             $tax = get_taxonomy( $queried_object->taxonomy );
             $term_name = sprintf( __( '%1$s &ndash; %2$s' ), $tax->labels->singular_name, $term_name );
         }
     }

     return $term_name;
 }, 10, 1 );



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


/* Enqueue styles and scripts */

require get_stylesheet_directory() . '/inc/enqueue.php';

/* Filter classes */

require get_stylesheet_directory() . '/inc/filter-classes.php';

/* Admin customizations */

require get_stylesheet_directory() . '/inc/admin.php';

/* Avada hooks */

require get_stylesheet_directory() . '/inc/avada.php';

/* Shortcodes */

require get_stylesheet_directory() . '/inc/shortcodes/course.php';
require get_stylesheet_directory() . '/inc/shortcodes/mentor.php';
require get_stylesheet_directory() . '/inc/shortcodes/testimonial.php';






/*
 * Get more posts for posts and custom post types.
 *
 * @pass int $offset
 * @pass string $type
 * @pass int $posts_per_page Required.
     * @pass array $query_args_static
 * @pass array $query_args
 * @pass array $filters
 * @echo string json containing output
 */

function pma_get_posts() {
    try {
        $offset = (int) $_POST['offset'] ?? 0;
        $type = $_POST['type'] ?? 'post';
        $posts_per_page = (int) $_POST['ppp'] ?? 0;

        if( !$posts_per_page )
            throw new Exception( 'No limit' );

        $args = [
            'offset' => $offset,
            'post_type' => $type,
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page
        ];

        if( isset( $_POST['query_args_static'] ) ) {
            $query_args_static = $_POST['query_args_static'];

            if( $query_args_static && is_array( $query_args_static ) )
                $args = array_replace_recursive( $query_args_static, $args );
        }

        if( isset( $_POST['query_args'] ) && isset( $_POST['filters'] ) ) {
            $filters = $_POST['filters'];
            $query_args = $_POST['query_args'];
            $processed_args = [];

            foreach( $filters as $id => $arr ) {
                if( isset( $query_args[$id] ) ) {
                    $add_to_query_args = true;

                    // replace placeholders like %value with value
                    array_walk_recursive(
                        $query_args[$id],
                        function( &$v ) use ( $arr, &$add_to_query_args ) {
                            $actual_v = $arr['value'];

                            if( $actual_v == 'null' )
                                $add_to_query_args = false;

                            if( $v == '%value' )
                                $v = $actual_v;

                            if( $v == '%value:int' )
                                $v = (int) $actual_v;

                            if( $v == '%operator' && isset( $arr['operator'] ) )
                                $v = $arr['operator'];
                        }
                    );

                    if( $add_to_query_args )
                        $processed_args = array_merge_recursive( $query_args[$id], $processed_args );
                }
            }

            $args = array_merge_recursive( $processed_args, $args );
        }

        $output = '';

        if( $type === 'course' ) {
            $output = pma_courses_shortcode( [
                'only_rows' => 'true',
                'show_rating' => 'true',
                'query_args' => $args,
                'return_array' => 'true'
            ] );
        } else {
            $output = pma_mentors_shortcode( [
                'only_rows' => 'true',
                'query_args' => $args,
                'return_array' => 'true'
            ] );
        }

        echo json_encode( $output );

        exit;
    } catch( Exception $e ) {
        echo $e->getMessage();
        header( http_response_code( 500 ) );
        exit;
    }
}





add_action( 'wp_ajax_nopriv_pma_get_posts', 'pma_get_posts' );
add_action( 'wp_ajax_pma_get_posts', 'pma_get_posts' );



function blah( $value, $field_name, $post_id ) {
    if( $field_name === 'mentor' && get_post_type( $post_id ) === 'course' ) {
        $value = $value[0];

        $output = [];

        foreach( $value as $v ) {
            $output[] = get_the_title( (int) $v );
        }

        $value = implode( ', ', $output );
    }

    if( $field_name === 'artists' && get_post_type( $post_id ) === 'mentor' ) {
        $value = $value[0];
    }

    return $value;
}
add_filter( 'relevanssi_custom_field_value', 'blah', 20, 3 );

/* Filter acf field values for search */

// add_filter( 'relevanssi_acf_field_value', 'blah', 10, 3 );





add_filter('relevanssi_content_to_index', 'beautymed_add_product', 10, 2);

function beautymed_add_product($content, $post) {

    if( get_post_type( $post ) === 'course' ) {
        $mentor = get_field( 'mentor' );

        $output = [];

        foreach( $mentor as $m ) {
            $output[] = get_the_title( (int) $m );
        }

        $content .= implode( ', ', $output );

        error_log( print_r( $output, true ) );
    }

    if( get_post_type( $post ) === 'mentor' ) {
        $content .= get_field( 'artists' );
    }

    return $content;
}
