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

<section id="<?php echo esc_attr( apply_filters( 'mec_single_page_html_id', 'main-content' ) ); ?>" class="<?php echo esc_attr( apply_filters( 'mec_single_page_html_class', 'mec-container oras-mec-single-wrapper' ) ); ?>">
    <?php do_action( 'mec_before_main_content' ); ?>

    <?php if ( class_exists( 'MEC' ) ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <div class="oras-mec-single glass">
                <?php
                $mec = MEC::instance();
                echo MEC_Kses::full( $mec->single() );
                ?>
            </div>
        <?php endwhile; ?>
    <?php elseif ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <div class="oras-mec-single glass">
                <?php the_content(); ?>
            </div>
        <?php endwhile; ?>
    <?php else : ?>
        <div class="oras-mec-empty">
            <?php esc_html_e( 'No event found.', 'oras-theme' ); ?>
        </div>
    <?php endif; ?>

    <?php comments_template(); ?>
</section>

<?php
do_action( 'mec_after_main_content' );
get_footer();
