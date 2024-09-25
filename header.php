<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
</head>

<body <?php body_class(); ?>>
    <header class="main-header">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo">
            <h1 class="title" rel="home">
                <?php bloginfo('name'); ?>
        </a></h1>
        <p class="description"><?php bloginfo('description'); ?></p>
        <nav class="main-nav">
            <?php
            wp_nav_menu([
                'theme_location' => 'main-menu',
                'container' => 'ul',
                'menu_class' => 'nav-links',
            ]);
            ?>
        </nav>
    </header>

    <main class="main-container"></main>