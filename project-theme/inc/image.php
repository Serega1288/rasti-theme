<?php
if ( function_exists( 'add_image_size' ) ) {
//    add_image_size( 'new1', 579, 327, true );
//    add_image_size( 'new2', 372, 215, true );
//    add_image_size( 'new3', 1170, 400, true );
//    add_image_size( 'new4', 410, 235, true );
    // add_image_size( 'photo3', 380, 440, true );
}

// Disable WordPress responsive images srcset
add_filter('wp_calculate_image_srcset', '__return_false');

// Disable sizes attribute
add_filter('wp_calculate_image_sizes', '__return_false');

// Remove srcset and sizes from image tags
add_filter('wp_get_attachment_image_attributes', function ($attr) {
    if (isset($attr['srcset'])) {
        unset($attr['srcset']);
    }

    if (isset($attr['sizes'])) {
        unset($attr['sizes']);
    }

    return $attr;
}, 10, 1);