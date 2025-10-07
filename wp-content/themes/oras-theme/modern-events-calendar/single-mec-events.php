<?php
/**
 * Modern Events Calendar single event override that keeps the Astra
 * header/footer while delegating layout to the plugin output inside a
 * themed wrapper.
 *
 * @package ORAS Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="oras-mec-fullwidth">
    <?php if ( class_exists( 'MEC' ) ) : ?>
        <?php do_action( 'mec_before_main_content' ); ?>

        <?php while ( have_posts() ) : the_post(); ?>
            <div class="oras-mec-single glass">
                <?php
                $mec = MEC::instance();
                echo MEC_kses::full( $mec->single() );
                ?>
            </div>
        <?php endwhile; ?>

        <?php do_action( 'mec_after_main_content' ); ?>
    <?php elseif ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <?php the_content(); ?>
        <?php endwhile; ?>
    <?php else : ?>
        <div class="oras-mec-empty">
            <?php esc_html_e( 'No event found.', 'oras-theme' ); ?>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
