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

/* Add ppp fields to reading settings */

function pma_admin_setup() {

    /* Show rating for courses */

    $rating_name = 'pma_course_show_rating';

    register_setting(
        'reading',
        $rating_name,
        [
            'type' => 'boolean'
        ]
    );

    add_settings_field(
        $rating_name . '_field', // id
        'Course pages display rating', // title
        function() use ( $rating_name ) {
            $checked = get_option( $rating_name, '' );

            if( $checked )
                $checked = $checked ? ' checked' : '';

            echo
                "<div>" .
                    "<input type='checkbox' name='$rating_name' id='$rating_name' value='1'$checked>" .
                "</div>";
        }, // callback
        'reading', // page
        'default', // section
        [] // args
    );

    /* Posts per page options */

    $fields = [
        [
            'title' => 'Course page shows at most',
            'name' => 'pma_course_ppp'
        ],
        [
            'title' => 'Course page loads at most (ajax)',
            'name' => 'pma_course_ppp_ajax'
        ],
        [
            'title' => 'Mentor page shows at most',
            'name' => 'pma_mentor_ppp'
        ],
        [
            'title' => 'Mentor page loads at most (ajax)',
            'name' => 'pma_mentor_ppp_ajax'
        ]
    ];

    foreach( $fields as $field ) {
        $name = $field['name'];

        register_setting(
    		'reading',
    		$name,
            [
                'type' => 'number'
            ]
	    );

        add_settings_field(
            $field['name'] . '_field', // id
            $field['title'], // title
            function() use ( $name ) {
                $value = get_option( $name, '' );

                echo
                    "<div>" .
                        "<input type='number' name='$name' id='$name' value='$value' step='1' min='1' class='small-text'> posts" .
                    "</div>";
            }, // callback
            'reading', // page
            'default', // section
            [] // args
        );
    }
}
add_action( 'admin_init', 'pma_admin_setup' );
