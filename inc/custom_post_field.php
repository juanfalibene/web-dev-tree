<?php

// Register Custom Post Field
function webdevtree_add_meta_box() {
    add_meta_box(
        'external_link_meta_box',
        __('External Link', 'web-dev-tree'),
        'webdevtree_render_meta_box',
        'resource',
        'normal',
        'default'
    );
}

add_action('add_meta_boxes', 'webdevtree_add_meta_box');

// Render Custom Post Field
function webdevtree_render_meta_box( $post ) {
    $external_link = get_post_meta($post->ID, '_external_link', true);

    // HTML output
    echo '<label for="external_link">' . __('External Link', 'web-dev-tree') . '</label>';
    echo '<input type="text" id="external_link" name="external_link" value="' . esc_url($external_link) . '" size="25" />';
}

// Save Custom Post Field
function webdevtree_save_meta_box( $post_id ) {
    if (array_key_exists('external_link', $_POST)) {
        update_post_meta(
            $post_id,
            '_external_link',
            esc_url_raw(
                $_POST['external_link']
            )
        );
    }
}

add_action('save_post', 'webdevtree_save_meta_box');
