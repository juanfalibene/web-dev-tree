<section class="blog-section">
    <sidebar class="blog-post-sidebar">
        <?php
        if (is_active_sidebar('archive-sidebar')):
            dynamic_sidebar('archive-sidebar');
        endif;
        ?>
    </sidebar>
    <div class="blog-post-list archive">
        <header class="archive-header">
            <h1 class="archive-title">
                <?php
                if (is_category()):
                    single_cat_title();
                elseif (is_tag()):
                    single_tag_title();
                elseif (is_author()):
                    printf(__('Posts by %s', 'web-dev-tree'), '<span class="vcard">' . get_the_author() . '</span>');
                elseif (is_day()):
                    printf(__('Daily Archives: %s', 'web-dev-tree'), get_the_date());
                elseif (is_month()):
                    printf(__('Monthly Archives: %s', 'web-dev-tree'), get_the_date('F Y'));
                elseif (is_year()):
                    printf(__('Yearly Archives: %s', 'web-dev-tree'), get_the_date('Y'));
                elseif (is_tax()):
                    $taxonomy = get_taxonomy(get_queried_object()->taxonomy);
                    single_term_title();
                else:
                    _e('Archives', 'web-dev-tree');
                endif;
                ?>
            </h1>
        </header>
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
</section>