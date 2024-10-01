<footer class="main-footer">
  <a href="<?php echo esc_url(get_theme_mod('footer_link', 'https://juanfalibene.com')); ?>" target="_blank" rel="noopener noreferrer">
    <img src="<?php echo esc_url(get_theme_mod('footer_image', get_template_directory_uri() . '/images/juanfalibene_profile.svg')); ?>" class="img-profile" />
    <?php echo esc_html(get_theme_mod('footer_link_name', 'juanfalibene.com')); ?>
  </a>
</footer>
</main>
<?php wp_footer(); ?>
</body>
</html>
