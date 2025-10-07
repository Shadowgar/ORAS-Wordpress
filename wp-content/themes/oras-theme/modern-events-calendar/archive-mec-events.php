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
    <?php if ( class_exists( 'MEC' ) ) : ?>
        <?php do_action( 'mec_before_main_content' ); ?>

        <?php
        $calendar_content = '';
        $calendar_page    = null;
        $settings         = array();

        $main = MEC::getInstance( 'app.libraries.main' );

        if ( is_object( $main ) ) {
            $settings = method_exists( $main, 'get_settings' ) ? $main->get_settings() : array();

            $candidate_ids = array();
            foreach ( array( 'archive_page_id', 'breadcrumbs_events_page', 'main_page_id' ) as $settings_key ) {
                if ( ! empty( $settings[ $settings_key ] ) ) {
                    $candidate_ids[] = absint( $settings[ $settings_key ] );
                }
            }

            foreach ( $candidate_ids as $candidate_id ) {
                $calendar_page = get_post( $candidate_id );

                if ( $calendar_page instanceof WP_Post && 'page' === $calendar_page->post_type ) {
                    break;
                }

                $calendar_page = null;
            }

            if ( ! $calendar_page instanceof WP_Post ) {
                $candidate_slugs = array();

                if ( ! empty( $settings['archive_page'] ) && is_string( $settings['archive_page'] ) ) {
                    $candidate_slugs[] = sanitize_title( $settings['archive_page'] );
                }

                if ( method_exists( $main, 'get_main_slug' ) ) {
                    $candidate_slugs[] = sanitize_title( $main->get_main_slug() );
                }

                $candidate_slugs = array_unique( array_filter( $candidate_slugs ) );

                foreach ( $candidate_slugs as $candidate_slug ) {
                    $calendar_page = get_page_by_path( $candidate_slug );

                    if ( $calendar_page instanceof WP_Post ) {
                        break;
                    }
                }
            }
        }

        if ( $calendar_page instanceof WP_Post ) {
            $calendar_content = apply_filters( 'the_content', $calendar_page->post_content );
        } else {
            $calendar_content = do_shortcode( '[MEC id="2527"]' );
        }
        ?>

        <?php if ( is_active_sidebar( 'mec-archive' ) ) : ?>
            <div class="mec-archive-wrapper mec-wrap">
                <div class="mec-archive-content col-md-8">
                    <?php echo $calendar_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
                <div class="mec-archive-sidebar col-md-4">
                    <?php dynamic_sidebar( 'mec-archive' ); ?>
                </div>
            </div>
        <?php else : ?>
            <?php echo $calendar_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php endif; ?>

        <?php do_action( 'mec_after_main_content' ); ?>
    <?php elseif ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <?php the_content(); ?>
        <?php endwhile; ?>
    <?php else : ?>
        <div class="oras-mec-empty">
            <?php esc_html_e( 'Modern Events Calendar content is unavailable. Please ensure the plugin is active.', 'oras-theme' ); ?>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
