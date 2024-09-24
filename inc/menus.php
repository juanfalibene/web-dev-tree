<?php

// Register menus
function webdevtree_register_menus() {
    register_nav_menu( 'main-menu', __( 'Main Menu', 'webdevtree') );
    /*register_nav_menu( 'footer-menu', __( 'Footer Menu', 'webdevtree') );*/
}
add_action('after_setup_theme', 'webdevtree_register_menus');
