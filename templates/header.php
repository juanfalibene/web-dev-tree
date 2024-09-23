<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
</head>

<body <?php body_class(); ?>>
    <header>
      <h1 class="title" rel="home"><?php bloginfo('name'); ?></a></h1>
      <p class="description"><?php bloginfo('description'); ?></p>
      <nav>
        <ul class="nav-links">
          <li class="nav-link"><a href="#">Read more</a></li>
          <li class="nav-link"><a href="#">GitHub</a></li>
          <li class="nav-link">
            <a href="#">Add Link</a>
          </li>
        </ul>
      </nav>
    </header>

    <main class="container"></main>