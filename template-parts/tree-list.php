<section class="tree-list">
    <!-- Resource Categories -->
    <ul class="categories">
        <li class="category" id="all">
            <a href="#">All</a>
        </li>

        <?php
        // Get resources categories
        $args = [
            'taxonomy' => 'category_resource', // Custom Post Taxnomy Reference
            'type' => 'resource',
            'hide_empty' => 'true',
        ];

        $categories = get_categories($args);

        foreach ($categories as $category):
            $cat_name = $category->name;
            $cat_slug = $category->slug;
            $cat_id = $category->term_id;

            // Categories List
            ?>
            <li class="category cat-<?php echo esc_attr($cat_id) ?>" id="<?php esc_attr($cat_slug) ?>">
            <a href="#"><?php echo esc_html($cat_name) ?></a>
            </li>

            <?php

            // Resource Post Elements
            $query_args = [
                'post_type' => 'resource',
                'tax_query' => [
                    [
                        'taxonomy' => 'category_resource',
                        'field' => 'slug',
                        'terms' => $cat_slug // Category Filter
                    ],
                ],
            ];

        endforeach;

        ?>

    </ul>
    <ul class="tree" id="resources-list">

        <?php
        // Resources Node Tree
        
        foreach ($categories as $category):
            $cat_slug = $category->slug;
            $cat_id = $category->term_id;

            $query_args = [
                'post_type' => 'resource',
                'tax_query' => [
                    [
                        'taxonomy' => 'category_resource',
                        'field' => 'slug',
                        'terms' => $cat_slug,
                    ],
                ],
            ];

            $query_post = new WP_Query($query_args);

            if ($query_post->have_posts()):

                while ($query_post->have_posts()):
                    $query_post->the_post();
                    // Custom Post Field: external_link
                    $external_link = get_post_meta(get_the_ID(), '_external_link', true);

                    // Print Resource Node
                    ?>
                    <li class="node <?php echo esc_attr($cat_slug) . ' cat-' . esc_attr($cat_id) ?>">
                    <a href="<?php echo esc_url($external_link) ?>" rel="noopener noreferrer" target="_blank">
                    <?php get_the_title() ?><span><?php get_the_excerpt() ?></span>
                    </a>
                    </li>

                    <?php

                endwhile;

            endif;

            wp_reset_postdata();

        endforeach;

        ?>

    </ul>
</section>