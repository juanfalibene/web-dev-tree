<?php
get_header();

$external_link = get_post_meta(get_the_ID(), '_external_link', true);
$categories = get_the_term_list(get_the_ID(), 'category_resource', '', ', ');
?>

<section class="single-post resource-landing">
    <article class="single-post-content">
        <?php if ($categories) : ?>
            <div class="resource-category-badge">
                <?php echo $categories; ?>
            </div>
        <?php endif; ?>

        <h2><?php echo get_the_title(); ?></h2>
        
        <div class="resource-summary">
            <?php if (has_excerpt()) : ?>
                <p><?php echo get_the_excerpt(); ?></p>
            <?php else : ?>
                <p><?php echo wp_trim_words(get_the_content(), 30); ?></p>
            <?php endif; ?>
        </div>

        <?php if ($external_link) : ?>
            <div class="redirect-notice">
                <p>Redirecting to external resource in <span id="countdown">5</span> seconds...</p>
                <a href="<?php echo esc_url($external_link); ?>" class="button resource-button">Visit Resource Now →</a>
            </div>

            <script>
                (function() {
                    var count = 5;
                    var countdownElement = document.getElementById('countdown');
                    var targetUrl = "<?php echo esc_url($external_link); ?>";

                    var timer = setInterval(function() {
                        count--;
                        if (countdownElement) countdownElement.textContent = count;
                        if (count <= 0) {
                            clearInterval(timer);
                            window.location.href = targetUrl;
                        }
                    }, 1000);
                })();
            </script>
        <?php else : ?>
            <div class="resource-content">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>

        <?php get_template_part('template-parts/pagination'); ?>
    </article>
</section>

<?php
get_footer();
?>
