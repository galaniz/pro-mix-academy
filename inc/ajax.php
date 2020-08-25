<?php

/*
 * Ajax hooks
 * ----------
 */

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

function pma_ajax_get_posts() {
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

        $output = pma_get_posts_shortcode( [
            'type' => $type,
            'query_args' => $args,
            'return_array' => 'true',
            'layout' => ( $type === 'course' || $type === 'hit_the_road_music' ) ? 'table' : ''
        ] );

        echo json_encode( $output );

        exit;
    } catch( Exception $e ) {
        echo $e->getMessage();
        header( http_response_code( 500 ) );
        exit;
    }
}
add_action( 'wp_ajax_nopriv_pma_ajax_get_posts', 'pma_ajax_get_posts' );
add_action( 'wp_ajax_pma_ajax_get_posts', 'pma_ajax_get_posts' );
