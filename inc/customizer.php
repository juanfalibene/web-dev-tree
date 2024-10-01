<?php
function webdevtree_customize_register($wp_customize) {
    // Footer Settings
    $wp_customize->add_section('footer_settings', array(
        'title' => __('Footer Settings', 'web-dev-tree'),
        'priority' => 30,
    ));

    // Link settings
    $wp_customize->add_setting('footer_link', array(
        'default' => 'https://juanfalibene.com',
        'sanitize_callback' => 'esc_url',
    ));

    // Link control
    $wp_customize->add_control('footer_link', array(
        'label' => __('Footer Link URL', 'web-dev-tree'),
        'section' => 'footer_settings',
        'type' => 'url',
    ));

    // Link name
    $wp_customize->add_setting('footer_link_name', array(
        'default' => 'juanfalibene.com',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    // Link name control
    $wp_customize->add_control('footer_link_name', array(
        'label' => __('Footer Link Name', 'web-dev-tree'),
        'section' => 'footer_settings',
        'type' => 'text',
    ));

    // Image settings
    $wp_customize->add_setting('footer_image', array(
        'default' => get_template_directory_uri() . '/images/juanfalibene_profile.svg',
        'sanitize_callback' => 'esc_url',
    ));

    // Image control
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'footer_image', array(
        'label' => __('Footer Image', 'web-dev-tree'),
        'section' => 'footer_settings',
        'settings' => 'footer_image',
    )));
}
add_action('customize_register', 'webdevtree_customize_register');