<?php
/**
 * Page template — genérico
 */

get_header();

while (have_posts()) {
    the_post();
    echo '<main class="devolucoes-page"><div class="devolucoes-container">';
    echo '<h1>' . esc_html(get_the_title()) . '</h1>';
    the_content();
    echo '</div></main>';
}

get_footer();
