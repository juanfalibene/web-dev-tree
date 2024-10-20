<?php

// Register menus
function webdevtree_register_menus() {
    register_nav_menu( 'main-menu', __( 'Main Menu', 'webdevtree') );
}
add_action('after_setup_theme', 'webdevtree_register_menus');
