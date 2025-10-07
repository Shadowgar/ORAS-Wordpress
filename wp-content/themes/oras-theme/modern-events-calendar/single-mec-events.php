<?php
/**
 * Override the Modern Events Calendar single event template so MEC content
 * inherits the child theme header, footer, and Astra layout wrappers.
 *
 * @package ORAS Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'MEC' ) ) {
    return;
}

$section_id     = apply_filters( 'mec_single_page_html_id', 'main-content' );
$section_classes = apply_filters( 'mec_single_page_html_class', 'mec-container' );
$section_classes = trim( $section_classes . ' oras-mec-container' );

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

            <?php while ( have_posts() ) : the_post(); ?>
                <div class="oras-mec-single glass">
                    <?php
                    $mec = MEC::instance();
                    echo MEC_kses::full( $mec->single() );
                    ?>
                </div>
            <?php endwhile; // end of the loop. ?>

            <?php comments_template(); ?>
        </section>

        <?php do_action( 'mec_after_main_content' ); ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();

