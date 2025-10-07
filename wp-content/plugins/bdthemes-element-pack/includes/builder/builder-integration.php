<?php

namespace ElementPack\Includes\Builder;

if ( ! defined( 'WPINC' ) ) {
	die;
}

use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Core\DocumentTypes\PageBase;
use ElementPack\Base\Singleton;
use ElementPack\Includes\Builder\Meta;
use ElementPack\Includes\Builder\Builder_Template_Helper;
use ElementPack\Includes\Builder\Builder_Post_Singleton;
use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;


class Builder_Integration {

	use Singleton;

	private $current_template = null;
	public $current_template_id = null;

	function __construct() {
		add_filter( 'template_include', [ $this, 'set_builder_template' ], 9999 );
		add_action( 'elementor/editor/init', [ $this, 'set_sample_post' ], 999 );

		add_action( 'print_default_editor_scripts', array( $this, 'my_custom_fonts' ) );

		add_action( 'elementor/documents/register_controls', [ $this, 'register_document_controls' ] );
	}

	public function my_custom_fonts() {
		if ( is_admin() && Plugin::instance()->editor->is_edit_mode() ) {
			if ( isset( $_REQUEST['bdt-template'] ) ) {
				wp_register_style( 'bdt-template-builder-hide-preview-btn-inline', false ); // phpcs:ignore
				wp_enqueue_style( 'bdt-template-builder-hide-preview-btn-inline' );
				wp_add_inline_style(
					'bdt-template-builder-hide-preview-btn-inline',
					'#elementor-panel-footer-saver-preview {display:none!important}'
				);
			}
		}
	}
	function set_sample_post() {
		if ( Builder_Template_Helper::isTemplateEditMode() ) {
			$object = Builder_Post_Singleton::instance();
			$object::set_sample_post();
		}
	}

	function register_document_controls( $document ) {
		if ( ! $document instanceof PageBase || ! $document::get_property( 'has_elements' ) ) {
			return;
		}

		if ( Plugin::instance()->preview->is_preview_mode() )
			return;

		if ( ! Builder_Template_Helper::isTemplateEditMode() ) {
			return;
		}

		global $post;

		if ( ! isset( $post->ID ) ) {
			return;
		}
		$meta = get_post_meta( $post->ID );

		$templateMeta = optional( $meta )[ Meta::TEMPLATE_TYPE ];
		if ( ! isset( $templateMeta[0] ) ) {
			return;
		}
		$postMeta = $templateMeta[0];
		$postMeta = explode( '|', $postMeta );
		$postType = $postMeta[0];

		if ( 'single' != $postMeta[1] ) {
			return;
		}

		$document->start_controls_section(
			'bdt_page_setting_preview',
			[ 
				'label' => esc_html__( 'Builder Settings', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			]
		);

		$document->add_control(
			'bdt_builder_sample_post_id',
			[ 
				'label'       => esc_html__( 'Builder Post', 'bdthemes-element-pack' ),
				'type'        => Dynamic_Select::TYPE,
				'multiple'    => false,
				'label_block' => true,
				'query_args'  => [ 
					'post_type' => $postType
				],
			]
		);

		$document->add_control(
			'bdt_builder_sample_apply_preview',
			[ 
				'type'        => Controls_Manager::BUTTON,
				'label'       => esc_html__( 'Apply & Preview', 'bdthemes-element-pack' ),
				'label_block' => true,
				'show_label'  => false,
				'text'        => esc_html__( 'Apply & Preview', 'bdthemes-element-pack' ),
				'separator'   => 'none',
				'event'       => 'ElementPackBuilderSetting:applySinglePagePostOnPreview',
			]
		);

		$document->end_controls_section();
	}

	/**
	 * Rewrite default template
	 *
	 */
	function set_builder_template( $template ) {
		if ( Builder_Template_Helper::isTemplateEditMode() ) {
			return $this->setBackendTemplate( $template );
		} else {
			return $this->setFrontendTemplate( $template );
		}
	}


	protected function setBackendTemplate( $template ) {
		return $template;
	}


	protected function setFrontendTemplate( $template ) {
		// if (get_post_type() == 'product') {
		//     global $product;
		//     $product = wc_get_product();
		// }



		if ( defined( 'ELEMENTOR_PATH' ) ) {
			$elementorTem = ELEMENTOR_PATH . "modules/page-templates/templates/";
			$elementorTem = explode( $elementorTem, $template );
			if ( count( $elementorTem ) == 2 ) {
				return $template;
			}
		}

		// single posts
		if ( is_single() && 'post' == get_post_type() ) {
			//single__3168
			if ( $custom_template = $this->get_template_id( 'single', 'post' ) ) {
				$this->current_template_id = $custom_template;
				return $this->getTemplatePath( 'posts/single', $template );
			}
		}

		// archive page
		if ( ( is_archive() || is_home() ) && get_post_type( get_the_ID() ) == 'post' ) {
			if ( is_category() ) {
				if ( $custom_template = $this->get_template_id( 'category', 'post' ) ) {
					$this->current_template_id = $custom_template;
					return $this->getTemplatePath( 'posts/category', $template );
				}
			} elseif ( is_tag() ) {
				if ( $custom_template = $this->get_template_id( 'tag', 'post' ) ) {
					$this->current_template_id = $custom_template;
					return $this->getTemplatePath( 'posts/tag', $template );
				}
			} elseif ( is_author() ) {
				if ( $custom_template = $this->get_template_id( 'author', 'post' ) ) {
					$this->current_template_id = $custom_template;
					return $this->getTemplatePath( 'posts/author', $template );
				}
			} elseif ( is_date() ) {
				if ( $custom_template = $this->get_template_id( 'date', 'post' ) ) {
					$this->current_template_id = $custom_template;
					return $this->getTemplatePath( 'posts/date', $template );
				}
			} else {
				if ( $custom_template = $this->get_template_id( 'archive', 'post' ) ) {
					$this->current_template_id = $custom_template;
					return $this->getTemplatePath( 'posts/archive', $template );
				}
			}
		}

		// Pages
		if ( is_page() && ! is_page_template() && 'page' == get_post_type() ) {
			if ( $custom_template = $this->get_template_id( 'single', 'page' ) ) {
				$this->current_template_id = $custom_template;
				return $this->getTemplatePath( 'pages/single', $template );
			}
		}

		//  404 page
		if ( is_404() ) {
			if ( $custom_template = $this->get_template_id( '404', 'page' ) ) {
				$this->current_template_id = $custom_template;
				return $this->getTemplatePath( 'pages/404', $template );
			}
		}

		// search page
		if ( is_search() ) {
			if ( $custom_template = $this->get_template_id( 'search', 'page' ) ) {
				$this->current_template_id = $custom_template;
				return $this->getTemplatePath( 'pages/search', $template );
			}
		}
		// front page
		// if (is_front_page()) {
		//     if ($custom_template = $this->get_template_id('home', 'page')) {
		//         $this->current_template_id = $custom_template;
		//         return $this->getTemplatePath('pages/home', $template);
		//     }
		// }

		// Custom post types - single
		if ( is_single() && ! in_array( get_post_type(), [ 'post', 'page' ] ) ) {
			$post_type = get_post_type();
			if ( $custom_template = $this->get_template_id( 'single', $post_type ) ) {
				$this->current_template_id = $custom_template;
				// Try specific template first, then fall back to generic custom template
				if ( $template_path = $this->getTemplatePath( "custom/{$post_type}/single", '' ) ) {
					return $template_path;
				}
				return $this->getTemplatePath( "custom/single", $template );
			}
		}

		// Custom post types - archive
		if ( is_post_type_archive() && ! in_array( get_post_type(), [ 'post', 'page' ] ) ) {
			$post_type = get_post_type();
			if ( $custom_template = $this->get_template_id( 'archive', $post_type ) ) {
				$this->current_template_id = $custom_template;
				// Try specific template first, then fall back to generic custom template
				if ( $template_path = $this->getTemplatePath( "custom/{$post_type}/archive", '' ) ) {
					return $template_path;
				}
				return $this->getTemplatePath( "custom/archive", $template );
			}
		}

		// Custom post types - taxonomy archives
		if ( is_tax() ) {
			$taxonomy = get_queried_object();
			if ( $taxonomy && isset( $taxonomy->taxonomy ) ) {
				$post_types = get_taxonomy( $taxonomy->taxonomy )->object_type;
				foreach ( $post_types as $post_type ) {
					if ( ! in_array( $post_type, [ 'post', 'page' ] ) ) {
						if ( $custom_template = $this->get_template_id( $taxonomy->taxonomy, $post_type ) ) {
							$this->current_template_id = $custom_template;
							// Try specific template first, then fall back to generic custom template
							if ( $template_path = $this->getTemplatePath( "custom/{$post_type}/{$taxonomy->taxonomy}", '' ) ) {
								return $template_path;
							}
							return $this->getTemplatePath( "custom/archive", $template );
						}
					}
				}
			}
		}


		// themes header
		if ( is_page() && ! is_page_template() && 'themes' == get_post_type() ) {
			if ( $custom_template = $this->get_template_id( 'header', 'themes' ) ) {
				$this->current_template_id = $custom_template;
				return $this->getTemplatePath( 'themes/header', $template );
			}
		}

		return $template;
	}


	public function getThemeTemplatePath( $slug ) {

		$fullPath = get_template_directory() . "/bdthemes-element-pack/$slug";
		if ( file_exists( $fullPath ) ) {
			return $fullPath;
		}
	}

	public function getPluginTemplatePath( $slug ) {

		$fullPath = BDTEP_PATH . "includes/builder/templates/$slug";
		if ( file_exists( $fullPath ) ) {
			return $fullPath;
		}
	}


	/**
	 * Get Template Path ID
	 *
	 * @param $slug
	 * @param $postType
	 *
	 * @return mixed|void|null
	 */
	public function get_template_id( $slug, $postType = false ) {

		if ( 'post' == $postType ) {
			$patterns = [
				'single' 	=> ['post', '_bdthemes_builder_post|single__'],
				'archive' 	=> ['post', '_bdthemes_builder_post|archive__'],
				'category' 	=> ['post', '_bdthemes_builder_post|category__'],
				'tag' 		=> ['post', '_bdthemes_builder_post|tag__'],
				'author' 	=> ['post', '_bdthemes_builder_post|author__'],
				'date' 		=> ['post', '_bdthemes_builder_post|date__'],
			];
		} elseif ( 'page' == $postType ) {
			$patterns = [
				'single'    => ['page', '_bdthemes_builder_page|single__'],
				'404'     	=> ['page', '_bdthemes_builder_page|404__'],
				'search'    => ['page', '_bdthemes_builder_page|search__'],
			];
		} else {
			// Handle custom post types
			$patterns = [
				'single' 	=> [$postType, "_bdthemes_builder_{$postType}|single__"],
				'archive' 	=> [$postType, "_bdthemes_builder_{$postType}|archive__"],
			];
			
			// Add taxonomy patterns for custom post types
			$taxonomies = get_object_taxonomies( $postType, 'objects' );
			foreach ( $taxonomies as $taxonomy ) {
				if ( $taxonomy->public && $taxonomy->show_ui ) {
					$patterns[ $taxonomy->name ] = [$postType, "_bdthemes_builder_{$postType}|{$taxonomy->name}__"];
				}
			}
		}
	
		if (isset($patterns[$slug]) && $postType === $patterns[$slug][0]) {
			$results = Builder_Template_Helper::searchTemplateOptions($patterns[$slug][1]);
	
			$option_values = array_map(fn($result) => $result->option_value, $results);
			$slug = !empty($option_values) ? "{$slug}__" . end($option_values) : null;
		}


		// Check if this is a promotional WooCommerce page option
		if ( 'product' == $postType && in_array( $slug, [ 'cart', 'checkout', 'myaccount', 'thankyou' ] ) ) {
			// Show promotional message instead of functional template
			$this->show_ultimate_store_kit_promotion( $slug );
			return null;
		}

		$templateId                = Builder_Template_Helper::getTemplate( $slug, $postType );
		$this->current_template_id = apply_filters( 'bdthemes-templates-builder/custom-template', $templateId );

		return $this->current_template_id;
	}

	/**
	 * Show Ultimate Store Kit promotion message
	 *
	 * @param string $page_type The WooCommerce page type
	 */
	private function show_ultimate_store_kit_promotion( $page_type ) {
		$page_names = [
			'cart'      => 'Cart Page',
			'checkout'  => 'Checkout Page', 
			'myaccount' => 'My Account Page',
			'thankyou'  => 'Thank You Page'
		];

		$page_name = isset( $page_names[ $page_type ] ) ? $page_names[ $page_type ] : ucfirst( $page_type );
		
		?>
		<div class="bdt-ultimate-store-kit-promotion" style="
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			padding: 40px 20px;
			text-align: center;
			border-radius: 10px;
			margin: 20px 0;
			box-shadow: 0 10px 30px rgba(0,0,0,0.2);
		">
			<div style="max-width: 600px; margin: 0 auto;">
				<h2 style="color: white; margin-bottom: 20px; font-size: 28px;">
					🚀 Ultimate Store Kit Required
				</h2>
				<p style="font-size: 18px; margin-bottom: 25px; line-height: 1.6;">
					To design the <strong><?php echo esc_html( $page_name ); ?></strong> with Elementor, you need our dedicated WooCommerce plugin:
				</p>
				<div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px; margin: 20px 0;">
					<h3 style="color: #ffd700; margin-bottom: 15px; font-size: 24px;">
						Ultimate Store Kit
					</h3>
					<p style="margin-bottom: 20px; font-size: 16px;">
						The most powerful WooCommerce page builder for Elementor with 50+ widgets, 
						advanced cart & checkout customization, and premium WooCommerce features.
					</p>
					<div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
						<a href="https://bdthemes.com/ultimate-store-kit/" 
						   target="_blank" 
						   style="
							   background: #ffd700;
							   color: #333;
							   padding: 12px 25px;
							   text-decoration: none;
							   border-radius: 25px;
							   font-weight: bold;
							   display: inline-block;
							   transition: all 0.3s ease;
						   "
						   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(255,215,0,0.4)'"
						   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
							Get Ultimate Store Kit
						</a>
						<a href="https://bdthemes.com/ultimate-store-kit/#demo" 
						   target="_blank" 
						   style="
							   background: transparent;
							   color: white;
							   padding: 12px 25px;
							   text-decoration: none;
							   border: 2px solid white;
							   border-radius: 25px;
							   font-weight: bold;
							   display: inline-block;
							   transition: all 0.3s ease;
						   "
						   onmouseover="this.style.background='white'; this.style.color='#667eea'"
						   onmouseout="this.style.background='transparent'; this.style.color='white'">
							View Demo
						</a>
					</div>
				</div>
				<p style="font-size: 14px; opacity: 0.9; margin-top: 20px;">
					✨ Includes 50+ WooCommerce widgets • 🎨 Advanced page builder • 🛒 Cart & Checkout customization
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Get Template Path
	 *
	 * @param $slug
	 * @param $default
	 *
	 * @return mixed|string|void
	 */
	protected function getTemplatePath( $slug, $default = '' ) {
		$phpSlug = "{$slug}.php";

		if ( $template = $this->getThemeTemplatePath( $phpSlug ) ) {
			return $template;
		}

		if ( $template = $this->getPluginTemplatePath( $phpSlug ) ) {
			return $template;
		}

		return $default;
	}
}


Builder_Integration::instance();
