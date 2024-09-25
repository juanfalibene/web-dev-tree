<?php
if (is_front_page()) {
    get_template_part('template-parts/tree-list');
} elseif (is_home()) {
    get_template_part('template-parts/content-home');
} elseif (is_page()) {
    get_template_part('template-parts/content-page');
} elseif (is_single()) {
    get_template_part('template-parts/content-single');
} elseif (is_archive()) {
    get_template_part('template-parts/content-archive');
} elseif (is_search()) {
    get_template_part('template-parts/content-search');
} elseif (is_404()) {
    get_template_part('template-parts/content-404');
} else {
    get_template_part('template-parts/content-default');
}
