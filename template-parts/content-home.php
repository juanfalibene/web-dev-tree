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
        // Custom Authors List
        $authors = get_users([
            'has_published_posts' => ['post'],
            'orderby' => 'post_count',
            'order' => 'DESC'
        ]);

        if (!empty($authors)) {
            echo '<div class="widget widget_author_list">';
            echo '<h4>Authors</h4>';
            echo '<ul class="author-list">';
            foreach ($authors as $author) {
                $author_name = get_the_author_meta('display_name', $author->ID);
                $author_desc = get_the_author_meta('description', $author->ID);
                $author_avatar = get_avatar($author->ID, 80);
                $post_count = count_user_posts($author->ID, 'post');
                $author_url = get_author_posts_url($author->ID);
                $author_weblink = get_the_author_meta('user_url', $author->ID);

                echo '<li class="author-item">';
                echo '<div class="author-avatar"><a href="' . esc_url($author_url) . '">' . $author_avatar . '</a></div>';
                echo '<div class="author-details">';
                echo '<h4 class="author-name"><a href="' . esc_url($author_url) . '">' . esc_html($author_name) . '</a></h4>';
                echo '<p class="author-post-count"><a href="' . esc_url($author_url) . '">' . $post_count . ' latest posts</a></p>';
                if ($author_desc) {
                    echo '<p class="author-bio">' . esc_html($author_desc) . '</p>';
                }
                if ($author_weblink) {
                    echo '<a href="' . esc_url($author_weblink) . '" class="author-weblink" target="_blank" rel="noopener noreferrer">Website</a>';
                }
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        // Keep the dynamic sidebar if user wants to add more widgets later
        if (is_active_sidebar('home-sidebar')):
            dynamic_sidebar('home-sidebar');
        endif;
        ?>
    </sidebar>
</section>