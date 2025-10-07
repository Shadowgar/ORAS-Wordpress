<?php
/**
 * Archive template wrapper for Modern Events Calendar events.
 *
 * Mirrors the plugin archive template so Modern Events Calendar can
 * bootstrap its hooks, while keeping the Astra header/footer and ORAS
 * theme wrappers.
 *
 * @package ORAS Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'MEC' ) ) {
    get_header();
    ?>
    <div class="oras-mec-empty">
        <?php esc_html_e( 'Modern Events Calendar is not available.', 'oras-theme' ); ?>
    </div>
    <?php
    get_footer();

    return;
}

$main     = MEC::getInstance( 'app.libraries.main' );
$settings = $main->get_settings();

$title_tag = isset( $settings['archive_title_tag'] ) && trim( $settings['archive_title_tag'] ) ? $settings['archive_title_tag'] : 'h1';

get_header();
?>

<section id="<?php echo esc_attr( apply_filters( 'mec_archive_page_html_id', 'main-content' ) ); ?>" class="<?php echo esc_attr( apply_filters( 'mec_archive_page_html_class', 'mec-container oras-mec-archive' ) ); ?>">
    <?php do_action( 'mec_before_main_content' ); ?>

    <?php if ( have_posts() ) : ?>

        <?php do_action( 'mec_before_events_loop' ); ?>

        <?php the_post(); ?>
        <?php $title = apply_filters( 'mec_archive_title', get_the_title() ); ?>

        <?php if ( trim( $title ) ) : ?>
            <<?php echo esc_html( $title_tag ); ?> class="oras-mec-archive__title"><?php echo MEC_kses::element( $title ); ?></<?php echo esc_html( $title_tag ); ?>>
        <?php endif; ?>

        <?php if ( is_active_sidebar( 'mec-archive' ) ) : ?>
            <div class="mec-archive-wrapper mec-wrap oras-mec-archive__layout">
                <div class="mec-archive-content col-md-8">
                    <?php the_content(); ?>
                </div>
                <aside class="mec-archive-sidebar col-md-4">
                    <?php dynamic_sidebar( 'mec-archive' ); ?>
                </aside>
            </div>
        <?php else : ?>
            <div class="oras-mec-fullwidth">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>

        <?php do_action( 'mec_after_events_loop' ); ?>

    <?php else : ?>

        <div class="oras-mec-empty">
            <?php $main->display_not_found_message(); ?>
        </div>

    <?php endif; ?>
</section>

<?php
do_action( 'mec_after_main_content' );
get_footer();
