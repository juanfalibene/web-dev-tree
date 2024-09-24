<?php
    get_template_part( 'templates/header' );
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

            echo '<li class="category" id="' . esc_attr( $cat_slug) . '">';
            echo '<a href="#">'. esc_html( $cat_name ) .'</a>';
            echo '</li>';

            endforeach;
          ?>

        </ul>


        <!-- Resources Post Tree -->
        <ul class="tree">
          <li class="node docs active">
            <a href="html/index.html" rel="noopener noreferrer" target="_blank"
              >Docs 01<span>Description</span></a
            >
          </li>
          <li class="node docs">
            <a href="html/index.html" rel="noopener noreferrer" target="_blank"
              >Docs 02<span>Description</span></a
            >
          </li>
          <li class="node inspiration">
            <a href="css/index.html" rel="noopener noreferrer" target="_blank"
              >Ins 01</a
            >
          </li>
          <li class="node voices">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Voices 01</a
            >
          </li>
          <li class="node news">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >News 01</a
            >
          </li>
          <li class="node tools">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Tools 01</a
            >
          </li>
          <li class="node ui">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >UI 01</a
            >
          </li>
          <li class="node ux">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >UX 01</a
            >
          </li>
          <li class="node design">
            <a href="javascript/index.html">Design 01</a>
          </li>
          <li class="node code">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Code 01</a
            >
          </li>
          <li class="node tutorials">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Tutorials 01</a
            >
          </li>
          <li class="node newsletters">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Newsletters 01</a
            >
          </li>
          <li class="node playlists">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Playlists 01</a
            >
          </li>
        </ul>
      </section>
      <?php
        get_template_part('templates/footer');
      ?>
