<?php

// Register Custom Post Type "Resources"
function webdevtree_register_post_type() {
    $labels = [
        'name'                  => __('Resources', 'web-dev-tree'),
        'singular_name'         => __('Resource', 'web-dev-tree'),
        'menu_name'             => __('Resources', 'web-dev-tree'),
        'name_admin_bar'        => __('Resource', 'web-dev-tree'),
        'add_new'               => __('Add New', 'web-dev-tree'),
        'add_new_item'          => __('Add New Resource', 'web-dev-tree'), // Personaliza "Add New"
        'new_item'              => __('New Resource', 'web-dev-tree'),
        'edit_item'             => __('Edit Resource', 'web-dev-tree'),
        'view_item'             => __('View Resource', 'web-dev-tree'),
        'all_items'             => __('All Resources', 'web-dev-tree'),
        'search_items'          => __('Search Resources', 'web-dev-tree'),
        'not_found'             => __('No Resources found', 'web-dev-tree'),
        'not_found_in_trash'    => __('No Resources found in Trash', 'web-dev-tree'),
    ];

    $args = [
        'labels'                => $labels,
        'public'                => true,
        'has_archive'           => true,
        'supports'              => ['title', 'editor', 'custom-fields', 'excerpt'],
        'rewrite'               => ['slug' => 'resources'],
        'menu_icon'             => 'dashicons-admin-links',
        'show_in_rest'          => true, // For Gutenberg
    ];

    register_post_type('resource', $args);
}
add_action('init', 'webdevtree_register_post_type');

// Register Custom Taxonomy "Resource Category"
function webdevtree_register_taxonomy() {
    register_taxonomy('category_resource', 'resource', [
        'labels' => [
            'name' => __('Categories', 'web-dev-tree'),
            'singular_name' => __('Category', 'web-dev-tree'),
        ],
        'hierarchical' => true,
        'show_in_rest' => true, // Gutenberg
    ]);
}
add_action('init', 'webdevtree_register_taxonomy');