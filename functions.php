<?php

/*
 * Functions
 * ---------
 */

/* Enqueue styles and scripts */

require get_stylesheet_directory() . '/inc/enqueue.php';

/* Nav customizations */

require get_stylesheet_directory() . '/inc/nav.php';

/* Admin customizations */

require get_stylesheet_directory() . '/inc/admin.php';

/* Shortcodes */

require get_stylesheet_directory() . '/inc/shortcodes/course.php';
require get_stylesheet_directory() . '/inc/shortcodes/mentor.php';
require get_stylesheet_directory() . '/inc/shortcodes/testimonial.php';
