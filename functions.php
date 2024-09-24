<?php

// Theme setup
function webdevtree_setup() {
    // Language support packs
    load_theme_textdomain('webdevtree', get_template_directory() . '/languages');
    // Add support for HTML5 elements
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
    // Add support for automatic feed links
    add_theme_support('automatic-feed-links');
    // Add support for title tag
    add_theme_support('title-tag');
    // Add support for post thumbnails
    add_theme_support('post-thumbnails');
    // Define content width
    $GLOBALS['content_width'] = 1440;
    // Body background image settings
    $background_settings = [
        'default_color' => '#f1f1f1',
    ];
    add_theme_support('custom-background', $background_settings);
}
// Theme setup: Add Hook
add_action('after_setup_theme', 'webdevtree_setup');

// Enqueue styles and scripts
function webdevtree_enqueue_scripts() {
    wp_enqueue_style('webdevtree-style', get_stylesheet_uri(), [], '1.0.0'); // Esta línea ya incluye style.css
    wp_enqueue_script('webdevtree-script', get_template_directory_uri() . '/assets/js/script.js', ['jquery'], '1.0.0', true);
}

add_action('wp_enqueue_scripts', 'webdevtree_enqueue_scripts');

// Theme body class
function webdevtree_body_class( $classes ) {
    $classes[] = 'webdevtree';
    return $classes;
}
// Add specific CSS class by filter
add_filter('body_class', 'webdevtree_body_class','');

require_once 'inc/menus.php';
