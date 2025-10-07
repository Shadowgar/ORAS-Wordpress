<?php
namespace ElementPack\Modules\DocumentViewer\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Document Viewer Widget
 * 
 * A widget that allows viewing documents using Google Docs viewer or browser native viewer.
 */
class Document_Viewer extends Module_Base {

	public function get_name() {
		return 'bdt-document-viewer';
	}

	public function get_title() {
		return BDTEP . esc_html__( 'Document Viewer', 'bdthemes-element-pack' );
	}

	public function get_icon() {
		return 'bdt-wi-document-viewer';
	}

	public function get_categories() {
		return [ 'element-pack' ];
	}

	public function get_keywords() {
		return [ 'document', 'viewer', 'record', 'file', 'local' ];
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/8Ar9NQe93vg';
	}
	
	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return false;
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content_layout',
			[
				'label' => esc_html__( 'Layout', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'file_source',
			[
				'label'         => esc_html__( 'File Source', 'bdthemes-element-pack' ),
				'description'   => esc_html__( 'Enter the URL of your document', 'bdthemes-element-pack' ),
				'type'          => Controls_Manager::URL,
				'dynamic'       => [ 'active' => true ],
				'placeholder'   => esc_html__( 'https://example.com/sample.pdf', 'bdthemes-element-pack' ),
				'label_block'   => true,
				'show_external' => false,
			]
		);

		$this->add_responsive_control(
			'document_height',
			[
				'label' => esc_html__( 'Document Height', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 800,
				],
				'range' => [
					'px' => [
						'min'  => 200,
						'max'  => 1500,
						'step' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-document-viewer iframe' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'viewer_type',
			[
				'label' => esc_html__( 'Viewer Type', 'bdthemes-element-pack' ) . BDTEP_NC,
				'type' => Controls_Manager::SELECT,
				'default' => 'google_docs',
				'options' => [
					'google_docs' => esc_html__( 'Google Docs (Public URLs Only)', 'bdthemes-element-pack' ),
					'browser' => esc_html__( 'Browser Native', 'bdthemes-element-pack' ),
				],
			]
		);

		$this->add_control(
			'document_viewer_notice',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Google Docs viewer only works with publicly accessible URLs, not local network files.', 'bdthemes-element-pack' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				'condition' => [
					'viewer_type' => 'google_docs',
				],
			]
		);

		$this->end_controls_section();

	}

	public function render() {
		$settings  = $this->get_settings_for_display();
		$source_url = $settings['file_source']['url'] ? $settings['file_source']['url'] : false;
		$viewer_type = $settings['viewer_type'];

		if (!$source_url) {
			echo '<div class="bdt-alert-warning" bdt-alert>';
			echo '<a class="bdt-alert-close" bdt-close></a>';
			echo '<p>' . esc_html__('Please enter correct URL of your document.', 'bdthemes-element-pack') . '</p>';
			echo '</div>';
			return;
		}

		// Check file extension
		$file_ext = pathinfo($source_url, PATHINFO_EXTENSION);
		$file_ext = strtolower($file_ext);

		if ($viewer_type === 'google_docs') {
			// Google Docs viewer (for public URLs only)
			// $viewer_base = 'https://docs.google.com/viewer?';
			// $query_params = http_build_query([
			// 	'url' => $source_url,
			// 	'embedded' => 'true'
			// ]);

			// Special case: if it's a Google Sheets link
			if (strpos($source_url, 'docs.google.com/spreadsheets') !== false) {
				$final_url = $source_url . (strpos($source_url, '?') === false ? '?' : '&') . 'widget=true&headers=false';
			} else {
				$viewer_base = 'https://docs.google.com/viewer?';
				$query_params = http_build_query([
					'url' => $source_url,
					'embedded' => 'true'
				]);
				$final_url = $viewer_base . $query_params;
			}
			
			// Check if URL is local
			$is_local_url = false;
			$parsed_url = parse_url($source_url);
			if (isset($parsed_url['host'])) {
				$host = $parsed_url['host'];
				// Check for localhost, IP addresses, or local domains
				if ($host === 'localhost' || 
					preg_match('/^127\.\d+\.\d+\.\d+$/', $host) || 
					preg_match('/^192\.168\.\d+\.\d+$/', $host) || 
					preg_match('/^10\.\d+\.\d+\.\d+$/', $host) || 
					preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\.\d+\.\d+$/', $host) || 
					strpos($host, '.local') !== false || 
					strpos($host, '.test') !== false || 
					strpos($host, '.localhost') !== false) {
					$is_local_url = true;
				}
			}
			?>
			<div class="bdt-document-viewer">
				<?php if ($is_local_url): ?>
				<div class="bdt-alert-info" bdt-alert>
					<p><?php echo esc_html__('Note: Google Docs viewer only works with publicly accessible URLs, not local network files.', 'bdthemes-element-pack'); ?></p>
				</div>
				<?php endif; ?>
				<iframe src="<?php echo esc_url( $final_url ); ?>" class="bdt-document"></iframe>
			</div>
			<?php
		} else {
			// Browser native viewer
			?>
			<div class="bdt-document-viewer">
				<iframe src="<?php echo esc_url($source_url); ?>" class="bdt-document"></iframe>
			</div>
			<?php
		}
	}
}
