<section class="blog-section">
    <?php
    echo 'IS ARCHIVE';
    echo '<article class="post-card ' . get_post_type() . '">';
    echo '<a href="' . get_permalink() . '">';
    echo '<h2 class="post-title">' . get_the_title() . '</h2>';
    echo '</a>';
    echo '<p>' . the_content() . '</p>';
    echo '</article>';
    ?>
</section>