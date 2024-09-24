<?php
    get_header();
?>
      <section class="section">
        <!-- Resource Categories -->
        <ul class="categories">
          <li class="category" id="all">
              <a href="#">All</a>
          </li>

          <?php
          // Get resources categories
          $args = [
            'taxonomy' => 'category_resource', // Custom Post Taxnomy Reference
            'type'=> 'resource',
            'hide_empty'=> 'true',
          ];

          $categories = get_categories( $args );

          foreach ( $categories as $category ) :
            $cat_name = $category->name;
            $cat_slug = $category->slug;
            $cat_id = $category->term_id;

            // Categories List
            echo '<li class="category" id="' . esc_attr( $cat_slug) . '">';
            echo '<a href="#">'. esc_html( $cat_name ) .'</a>';
            echo '</li>';

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

        echo '</ul>';
        echo '<ul class="tree" id="resources-list">';
          // Resources Node Tree

          foreach ( $categories as $category ) :
            $cat_slug = $category->slug;

            $query_args = [
              'post_type' => 'resource',
              'tax_query' => [
                  [
                      'taxonomy' => 'category_resource',
                      'field'    => 'slug',
                      'terms'    => $cat_slug,
                  ],
              ],
          ];

          $query_post = new WP_Query( $query_args );

          if ( $query_post->have_posts() ) :

            while ( $query_post->have_posts() ) : $query_post->the_post();
            // Custom Post Field: external_link
            $external_link = get_post_meta(get_the_ID(), '_external_link', true);

            // Print Resource Node
            echo '<li class="node ' . esc_attr($cat_slug) . '">';
            echo '<a href="'. esc_url($external_link) .'" rel="noopener noreferrer" target="_blank">';
            echo get_the_title() . '<span>' . get_the_excerpt() . '</span>';
            echo '</a>';
            echo '</li>';

          endwhile;

        endif;

        wp_reset_postdata();

      endforeach;
      
      echo '</ul>';

      ?>

      </section>
      <?php
        get_footer();
      ?>
