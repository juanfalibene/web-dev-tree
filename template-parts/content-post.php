<article class="post-card <?php post_class(); ?>" id="post-<?php the_ID(); ?>">
    <header>
        <!-- Post thumbnail -->
        <?php if (has_post_thumbnail()): ?>
            <div class="featured-image">
                <!-- Page image -->
                <?php if (is_page()): ?>
                    <?php the_post_thumbnail('featured-page', array('alt' => get_the_title())); ?>
                    <!-- Featured image -->
                <?php else: ?>
                    <?php the_post_thumbnail('featured-post', array('alt' => get_the_title())); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <!-- Post categories -->
        <p class="post-categories"><?php the_category(' / '); ?></p>
        <!-- Post date -->
        <time class="post-updated" datetime="<?php the_modified_time('c'); ?>"><?php the_date(); ?></time>
        <!-- Post author -->
        <span class="post-by-author">
            <?php the_author_posts_link(); ?>
        </span>
        <!-- Post title -->
        <?php if (is_singular()): ?>
            <h2 class="post-title"><?php the_title(); ?></h2>
        <?php else: ?>
            <h3 class="post-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h3>
        <?php endif; ?>

    </header>
    <div class="entry-content">
        <!-- Post content con conditionals tags -->
        <?php if (is_singular()): ?>
            <?php the_content(); ?>
            <!-- Internal links -->
            <?php wp_link_pages(); ?>
        <?php else: ?>
            <?php /* Post excerpt on homepage; */ ?>
            <?php the_excerpt(); ?>
            <!-- Post read more link -->
            <p class="read-more"><a class="button-link"
                    href="<?php the_permalink(); ?>"><?php _e('Read more', 'web-dev-tree'); ?></a></p>
        <?php endif; ?>
    </div>
    <footer>
        <!-- Post tags -->
        <ul class="post-tags"><?php the_tags('<li>', '</li><li>', '</li>'); ?></ul>
        <!-- Post edit link -->
        <span class="edit-link"><?php edit_post_link(__('(Edit)', 'web-dev-tree')); ?></span>
    </footer>
</article>