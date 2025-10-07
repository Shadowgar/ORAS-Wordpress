<div class="ep-box-row">
    <div class="ep-box-col-8" id="ep-sl-event-name">
        <h2 class="ep-fw-bold ep-fs-2 ep-mt-3 ep-border-left ep-border-3 ep-border-warning ep-ps-3 ep-text-break" id="ep_single_event_title">
            <?php echo esc_html( wp_strip_all_tags($args->post->post_title) );?>
        </h2>
    </div>
    <div class="ep-box-col-4" style="padding-top: 12px; text-align: right;">
        <!-- Social Icons in same row as title -->
        <?php if ( ! empty( $args->event->em_social_links ) ) { ?>
            <?php if ( ! empty( $args->event->em_social_links['facebook'] ) ) { ?>
                <a href="<?php echo esc_url( $args->event->em_social_links['facebook'] ); ?>" target="_blank" title="<?php echo esc_attr('Facebook'); ?>" style="display: inline-block; margin: 0 2px; padding: 2px;">
                    <?php $image_url = plugin_dir_url( EP_PLUGIN_FILE ) . 'public/partials/images/facebook-icon.png'; ?>
                    <img src="<?php echo esc_url( $image_url ); ?>" width="24" style="display: block;" />
                </a><?php
            }
            if ( ! empty( $args->event->em_social_links['instagram'] ) ) { ?>
                <a href="<?php echo esc_url( $args->event->em_social_links['instagram'] ); ?>" target="_blank" title="<?php echo esc_attr('Instagram'); ?>" style="display: inline-block; margin: 0 2px; padding: 2px;">
                    <?php $image_url = plugin_dir_url( EP_PLUGIN_FILE )  . 'public/partials/images/instagram-icon.png'; ?>
                    <img src="<?php echo esc_url( $image_url ); ?>" width="24" style="display: block;" />
                </a><?php
            }
            if ( ! empty( $args->event->em_social_links['linkedin'] ) ) { ?>
                <a href="<?php echo esc_url( $args->event->em_social_links['linkedin'] ); ?>" target="_blank" title="<?php echo esc_attr('Linkedin'); ?>" style="display: inline-block; margin: 0 2px; padding: 2px;">
                    <?php $image_url = plugin_dir_url( EP_PLUGIN_FILE ) . 'public/partials/images/linkedin-icon.png'; ?>
                    <img src="<?php echo esc_url( $image_url ); ?>" width="24" style="display: block;" />
                </a><?php
            }
            if ( ! empty( $args->event->em_social_links['twitter'] ) ) { ?>
                <a href="<?php echo esc_url( $args->event->em_social_links['twitter'] ); ?>" target="_blank" title="<?php echo esc_attr('Twitter'); ?>" style="display: inline-block; margin: 0 2px; padding: 2px;">
                    <?php $image_url = plugin_dir_url( EP_PLUGIN_FILE ) . 'public/partials/images/twitter-icon.png'; ?>
                    <img src="<?php echo esc_url( $image_url ); ?>" width="24" style="display: block;" />
                </a><?php
            }
            if ( ! empty( $args->event->em_social_links['youtube'] ) ) { ?>
                <a href="<?php echo esc_url( $args->event->em_social_links['youtube'] ); ?>" target="_blank" title="<?php echo esc_attr('Youtube'); ?>" style="display: inline-block; margin: 0 2px; padding: 2px;">
                    <?php $image_url = plugin_dir_url( EP_PLUGIN_FILE ) . 'public/partials/images/youtube-icon.png'; ?>
                    <img src="<?php echo esc_url( $image_url ); ?>" width="24" style="display: block;" />
                </a><?php
            }
        } ?>
    </div>
</div>