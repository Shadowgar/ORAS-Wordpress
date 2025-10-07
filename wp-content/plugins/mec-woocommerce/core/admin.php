<?php

namespace MEC_Woocommerce;

final class Admin {

	protected static $instance;

	public function __construct() {

		$this->init_hooks();
	}

	/**
	 * MEC_Admin_Woocommerce Instance
	 *
	 * @return self()
	 */
	public static function getInstance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add Hooks - Actions and Filters
	 *
	 * @return void
	 */
	public function init_hooks(): void {

		add_action( 'admin_init', array( __CLASS__, 'init' ), 10 );

        add_filter( 'mec-settings-items-integrations', [ __CLASS__, 'add_menu_to_mec_settings' ] );
        add_action( 'mec-settings-page-before-form-end', [ __CLASS__, 'display_settings_in_mec_settings_page' ] );

        add_action( 'mec_ticket_properties', [ __CLASS__, 'ticket_properties_html'], 10, 3);
	}

	/**
	 * Init MEC_Admin_Woocommerce after WordPress
	 *
	 * @return void
	 */
	public static function init(): void {

	}

    /**
     * @param array $links
     *  @hooked 'mec-settings-items-integrations'
     *
     * @return array
     */
    public static function add_menu_to_mec_settings( $links ){

        $links[__('Woocommerce', 'mec-woocommerce')] = 'woocommerce_options';

        return $links;
    }

    /**
     * @param array $settings
     *  @hooked 'mec-settings-page-before-form-end'
     *
     * @return array
     */
    public static function display_settings_in_mec_settings_page( $settings ){

        ?>
        <div id="woocommerce_options" class="mec-options-fields">
            <h4 class="mec-form-subtitle"><?php _e('Woocommerce Integration Options', 'mec-woocomerce'); ?></h4>
            <div class="mec-form-row">
                <label class="mec-col-3" for="mec_settings_woocommerce_ticket_product_type"><?php _e('Ticket Product Type in WC', 'mec-woocomerce'); ?></label>
                <div class="mec-col-9">
                    <?php $ticket_product_type = isset( $settings['ticket_product_type'] ) ? $settings['ticket_product_type'] : 'virtual'; ?>
                    <select name="mec[settings][ticket_product_type]">
                        <option value="physical" <?php selected( $ticket_product_type, 'physical' ); ?> ><?php esc_html_e('Physical','mec-woocommerce') ?></option>
                        <option value="virtual" <?php selected( $ticket_product_type, 'virtual' ); ?>><?php esc_html_e('Virtual','mec-woocommerce') ?></option>
                    </select>
                </div>
            </div>

        </div>
        <?php
    }

    public static function ticket_properties_html( $key, $tickets ,$event_id){

        ?>
        <script>
            jQuery(document).ready(function($){

                $.each($(".mec-product-cat-select2"), function(i,v){

                    if( $(v).attr('name').search(":i:") > 0 ){
                        return;
                    }

                    if( typeof $(v).data('select2-id') == 'undefined' ){

                        $(v).select2({
                            placeholder: "<?php esc_attr_e('Select Ticket Category', 'mec-woocommerce') ?>",
                        });
                    }
                });

            });
        </script>

        <div class="mec-form-row">
            <label>
                <?php esc_html_e('Select Ticket Product Categories', 'mec-woocommerce'); ?>
            </label>
            <div style="max-width: 290px;">
                <select name="mec[tickets][<?php echo $key ?>][category_ids][]" style="width:100%" class="mec-product-cat-select2" data-placeholder="<?php esc_attr_e('Select Ticket Category', 'mec-woocommerce') ?>" multiple>
                    <?php
                        $selected_category_ids = isset($tickets['category_ids']) ? (array)$tickets['category_ids'] : [];
                        $categories = get_terms([
                            'hide_empty' => false,
                            'taxonomy' => 'product_cat'
                        ]);
                        foreach( $categories as $category ){

                            $category_id = $category->term_taxonomy_id;
                            $category_name = $category->name;
                            $selected = in_array( $category_id, $selected_category_ids, false );
                            echo '<option value="'.$category_id.'" '. selected( true, $selected, false ) .'>'.$category_name.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }
}