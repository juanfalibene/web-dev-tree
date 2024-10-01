<section class="single-post">
    <article class="single-post-content">
        <h2><?php echo get_the_title(); ?></h2>
        <p><?php echo the_content(); ?></p>
        <?php get_template_part('template-parts/pagination'); ?>
    </article>
</section>