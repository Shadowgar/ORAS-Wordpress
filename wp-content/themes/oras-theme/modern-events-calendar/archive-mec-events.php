<?php
/**
 * Override the Modern Events Calendar archive template so MEC archive
 * listings load within the Astra layout and inherit the child theme styling.
 *
 * @package ORAS Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'MEC' ) ) {
    return;
}

$section_id      = apply_filters( 'mec_archive_page_html_id', 'main-content' );
$section_classes = apply_filters( 'mec_archive_page_html_class', 'mec-container' );
$section_classes = trim( $section_classes . ' oras-mec-container' );
$main            = MEC::getInstance( 'app.libraries.main' );
$settings        = $main->get_settings();
$title_tag       = isset( $settings['archive_title_tag'] ) && trim( $settings['archive_title_tag'] ) ? $settings['archive_title_tag'] : 'h1';

get_header();
?>

<div id="primary" <?php
if ( function_exists( 'astra_primary_class' ) ) {
    astra_primary_class();
} else {
    echo 'class="content-area"';
}
?>>
    <main id="main" class="site-main oras-mec-main" role="main">
        <section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( $section_classes ); ?>">
            <?php do_action( 'mec_before_main_content' ); ?>

            <?php if ( have_posts() ) : ?>
                <?php do_action( 'mec_before_events_loop' ); ?>

                <?php the_post(); ?>
                <?php $title = apply_filters( 'mec_archive_title', get_the_title() ); ?>

                <?php if ( trim( $title ) ) : ?>
                    <<?php echo esc_html( $title_tag ); ?> class="oras-mec-archive-title">
                        <?php echo MEC_kses::element( $title ); ?>
                    </<?php echo esc_html( $title_tag ); ?>>
                <?php endif; ?>

                <div class="oras-mec-archive glass">
                    <?php if ( is_active_sidebar( 'mec-archive' ) ) : ?>
                        <div class="mec-archive-wrapper mec-wrap">
                            <div class="mec-archive-content col-md-8">
                                <?php the_content(); ?>
                            </div>
                            <aside class="mec-archive-sidebar col-md-4">
                                <?php dynamic_sidebar( 'mec-archive' ); ?>
                            </aside>
                        </div>
                    <?php else : ?>
                        <?php the_content(); ?>
                    <?php endif; ?>
                </div>

                <?php do_action( 'mec_after_events_loop' ); ?>
            <?php else : ?>
                <p class="oras-mec-not-found">
                    <?php $main->display_not_found_message(); ?>
                </p>
            <?php endif; ?>

        </section>

        <?php do_action( 'mec_after_main_content' ); ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();

