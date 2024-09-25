<section class="single-post">
    <article class="single-post-content">
        <?php
        echo 'IS SINGLE POST';
        echo '<h2>' . get_the_title() . '</h2>';
        echo '<p>' . the_content() . '</p>';
        ?>
    </article>
</section>