<section class="blog-section">
    <sidebar class="blog-post-sidebar">
        <?php
        if (is_author()) {
            $author = get_queried_object();
            $author_name = $author->display_name;
            $author_desc = get_the_author_meta('description', $author->ID);
            $author_avatar = get_avatar($author->ID, 80);
            $post_count = count_user_posts($author->ID, 'post');
            $author_weblink = get_the_author_meta('user_url', $author->ID);

            echo '<div class="widget widget_author_list">';
            echo '<ul class="author-list">';
            echo '<li class="author-item">';
            echo '<div class="author-avatar">' . $author_avatar . '</div>';
            echo '<div class="author-details">';
            echo '<h4 class="author-name">' . esc_html($author_name) . '</h4>';
            echo '<p class="author-post-count">' . $post_count . ' latest posts</p>';
            if ($author_desc) {
                echo '<p class="author-bio">' . esc_html($author_desc) . '</p>';
            }
            if ($author_weblink) {
                echo '<a href="' . esc_url($author_weblink) . '" class="author-weblink">' . esc_html($author_weblink) . '</a>';
            }
            echo '</div>';
            echo '</li>';
            echo '</ul>';
            echo '</div>';
        }

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