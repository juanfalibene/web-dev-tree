<!-- Post navigation -->
<?php if ( get_previous_posts_link() || get_next_posts_link() ) : ?>
<nav class="row" id="post-nav">
    <div class="post-previous">
        <?php if ( get_previous_posts_link() ) : ?>
            <?php previous_posts_link('<span>&laquo; Previous</span>'); ?>
        <?php endif; ?>
    </div>
    <div class="post-next text-right">
        <?php if ( get_next_posts_link() ) : ?>
            <?php next_posts_link('<span>Next &raquo;</span>'); ?>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>