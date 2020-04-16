<?php

/*
 * Functions
 * ---------
 */

/* Utility / helper functions */

require get_stylesheet_directory() . '/inc/utils.php';

/* Render markup ( used in get-posts shortcode ) */

require get_stylesheet_directory() . '/inc/render/stars.php';
require get_stylesheet_directory() . '/inc/render/cards-h.php';
require get_stylesheet_directory() . '/inc/render/cards-v.php';
require get_stylesheet_directory() . '/inc/render/cards-flex.php';
require get_stylesheet_directory() . '/inc/render/table.php';

/* Front end filters */

require get_stylesheet_directory() . '/inc/filters.php';

/* Enqueue styles and scripts */

require get_stylesheet_directory() . '/inc/enqueue.php';

/* Ajax hooks */

require get_stylesheet_directory() . '/inc/ajax.php';

/* Admin customizations */

require get_stylesheet_directory() . '/inc/admin.php';

/* Avada hooks */

require get_stylesheet_directory() . '/inc/avada.php';

/* Shortcodes */

require get_stylesheet_directory() . '/inc/shortcodes/course.php';
require get_stylesheet_directory() . '/inc/shortcodes/mentor.php';
require get_stylesheet_directory() . '/inc/shortcodes/get-posts.php';
