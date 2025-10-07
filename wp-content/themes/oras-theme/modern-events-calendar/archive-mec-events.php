<?php
/**
 * Minimal Modern Events Calendar archive override that renders the
 * assigned page content (typically a MEC shortcode) inside the child
 * theme header and footer so Astra still controls the chrome.
 *
 * @package ORAS Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="oras-mec-fullwidth">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <?php the_content(); ?>
        <?php endwhile; ?>
    <?php else : ?>
        <div class="oras-mec-empty">
            <?php esc_html_e( 'No events found.', 'oras-theme' ); ?>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
