<section class="blog-section">
    <div class="blog-post-list loop">
        <?php
        if (have_posts()):
            while (have_posts()):
                the_post();
                get_template_part('template-parts/content', get_post_type());
            endwhile;
            get_template_part('template-parts/pagination');
        else:
            get_template_part('template-parts/loop-error');
        endif;
        ?>
    </div>
    <sidebar class="blog-post-sidebar">
        <?php
        if (is_active_sidebar('home-sidebar')):
            dynamic_sidebar('home-sidebar');
        endif;
        ?>
    </sidebar>
</section>