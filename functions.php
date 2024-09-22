<?php



// Enqueue styles and scripts
function webdevtree_enqueue_scripts() {
    wp_enqueue_style('webdevtree-style', get_stylesheet_uri(), [], '1.0.0'); // Esta línea ya incluye style.css
    wp_enqueue_script('webdevtree-script', get_template_directory_uri() . '/assets/js/script.js', ['jquery'], '1.0.0', true);
}

add_action('wp_enqueue_scripts', 'webdevtree_enqueue_scripts');
