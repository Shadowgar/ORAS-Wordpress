<?php

namespace ElementPack\Includes\Builder;

class Builder_Template_Helper {

	public static function isTemplateEditMode() {

		if ( get_post_type() == Meta::POST_TYPE ) {
			return true;
		}

		if ( isset( $_REQUEST[ Meta::POST_TYPE ] ) ) {
			return true;
		}
	}

	public static function separator() {
		return '|';
	}

	public static function templates( $single = false ) {

		$themes_item = [ 
			'header' => esc_html__( 'Header', 'bdthemes-element-pack' ),
			'footer' => esc_html__( 'Footer', 'bdthemes-element-pack' ),
		];
		$postItem    = [ 
			'single'  => esc_html__( 'Single', 'bdthemes-element-pack' ),
			'archive' => esc_html__( 'Archive', 'bdthemes-element-pack' ),
			'category' => esc_html__('Category', 'bdthemes-element-pack'),
			'tag' => esc_html__('Tag', 'bdthemes-element-pack'),
			'author' => esc_html__('Author', 'bdthemes-element-pack'),
			'date'    => esc_html__( 'Date', 'bdthemes-element-pack' ),
		];
		$pageItem    = [ 
			'single' => esc_html__( 'Single', 'bdthemes-element-pack' ),
			'404'    => esc_html__( 'Error 404', 'bdthemes-element-pack' ),
			'search' => esc_html__( 'Search', 'bdthemes-element-pack' ),
		];
		$shopItem    = [ 
			'single'  => esc_html__( 'Single', 'bdthemes-element-pack' ),
			'archive' => esc_html__( 'Archive', 'bdthemes-element-pack' ),
			'cart'    => esc_html__( 'Cart Page', 'bdthemes-element-pack' ),
			'checkout' => esc_html__( 'Checkout Page', 'bdthemes-element-pack' ),
			'myaccount' => esc_html__( 'My Account Page', 'bdthemes-element-pack' ),
			'thankyou' => esc_html__( 'Thank You Page', 'bdthemes-element-pack' ),
		];

		$templates = [ 
			'themes' => $themes_item,
			'post'   => $postItem,
			'page'   => $pageItem,
			'product' => $shopItem,
		];

		// Automatically add custom post types
		$custom_post_types = get_post_types( [
			'public'   => true,
			'_builtin' => false,
		], 'objects' );

		// Exclude template builder and non-content post types (similar to Elementor's approach)
		$excluded_post_types = [
			'elementor_library',
			'e-floating-buttons', 
			'e-landing-page',
			'attachment',
			'bdt-template-builder',
			'usk-template-builder',
			'upk-template-builder',
			'bdt-custom-template',
			'ep_megamenu_content',
			'product', // Already handled above
			'post',    // Already handled above
			'page',    // Already handled above
			'themes',  // Special case, not a real post type
		];

		foreach ( $custom_post_types as $post_type ) {
			// Skip if already exists or if it's in the excluded list
			if ( isset( $templates[ $post_type->name ] ) || in_array( $post_type->name, $excluded_post_types ) ) {
				continue;
			}

			// Skip post types that don't support editor
			if ( ! post_type_supports( $post_type->name, 'editor' ) ) {
				continue;
			}

			// Skip post types that have no published posts
			$post_count = wp_count_posts( $post_type->name );
			if ( ! $post_count || ( isset( $post_count->publish ) && $post_count->publish == 0 ) ) {
				continue;
			}

			// Skip post types that are not meant for content creation
			if ( ! $post_type->public || ! $post_type->publicly_queryable ) {
				continue;
			}

			// Skip post types that don't support custom fields (often indicates they're not content types)
			if ( ! post_type_supports( $post_type->name, 'custom-fields' ) && ! post_type_supports( $post_type->name, 'title' ) ) {
				continue;
			}

			// Create template options for custom post type
			$custom_post_type_item = [
				'single'  => esc_html__( 'Single', 'bdthemes-element-pack' ),
				'archive' => esc_html__( 'Archive', 'bdthemes-element-pack' ),
			];

			// Add taxonomy archives if the post type has taxonomies
			$taxonomies = get_object_taxonomies( $post_type->name, 'objects' );
			foreach ( $taxonomies as $taxonomy ) {
				if ( $taxonomy->public && $taxonomy->show_ui ) {
					$custom_post_type_item[ $taxonomy->name ] = $taxonomy->label;
				}
			}

			$templates[ $post_type->name ] = $custom_post_type_item;
		}

		if ( $single ) {
			$separator = static::separator();
			$return    = [];

			if ( is_array( $templates ) && ! empty( $templates ) ) {

				foreach ( $templates as $keys => $items ) {

					if ( is_array( $items ) ) {

						foreach ( $items as $itemKey => $item ) {
							$return[ "{$keys}{$separator}{$itemKey}" ] = $item;
						}
					}
				}
			}

			return apply_filters(
				'bdthemes_templates_builder_all_templates',
				$return
			);
		}

		return $templates;
	}

	public static function templateForSelectDropdown() {
		return static::templates();
	}

	public static function getTemplateByIndex( $index ) {
		$index     = trim( $index );
		$templates = static::templates( true );

		return array_key_exists( $index, $templates ) ? $templates[ $index ] : false;
	}

	public static function getTemplatePostTypeByIndex( $index ) {
		$index = trim( $index );

		if ( $item = explode( static::separator(), $index ) ) {
			return get_post_type_object( $item[0] );
		}
	}

	public static function is_elementor_active() {
		return did_action( 'elementor/loaded' );
	}

	public static function getTemplate( $slug, $postType = false ) {

		if ( ! $postType ) {
			$postType = get_post_type();
		}
        
		$separator       = static::separator();
		$template        = strtolower( "{$postType}{$separator}{$slug}" );
		$enabledTemplate = strtolower( Meta::TEMPLATE_ID . $template );

		return get_option( $enabledTemplate );
	}

	public static function getTemplateId( $templateType ) {
		$metaIndex = strtolower( Meta::TEMPLATE_ID . $templateType );
		return intval( get_option( $metaIndex ) );
	}

	public static function searchTemplateOptions( $pattern ) {
		global $wpdb;

		$like_pattern = '%' . $wpdb->esc_like( $pattern ) . '%';
		$query        = $wpdb->prepare(
			"SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE %s",
			$like_pattern
		);
		
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results( $query );

		return $results;
	}
}
