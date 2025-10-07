<?php

use Elementor\Controls_Manager;
use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_ACF_Gallery extends \Elementor\Core\DynamicTags\Data_Tag
{

    use UtilsTrait;
    private static $dynamic_value_provider;

    public function get_name(): string
    {
        return 'element-pack-acf-gallery';
    }

    public function get_title(): string
    {
        return esc_html__('Gallery', 'bdthemes-element-pack');
    }

    public function get_group(): array
    {
        return ['element-pack-acf'];
    }

    public function is_settings_required()
    {
        return true;
    }

    public function get_categories(): array
    {
        return [\Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY];
    }

    public function get_supported_fields()
    {
        return [
            'gallery',
        ];
    }

    protected function register_controls(): void
    {

        $this->add_control(
            'ep_acf_field_source',
            [
                'label' => esc_html__('Meta Source', 'bdthemes-element-pack'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'post' => esc_html__('Post', 'bdthemes-element-pack'),
                    'taxonomy' => esc_html__('Taxonomy', 'bdthemes-element-pack'),
                    'user' => esc_html__('User', 'bdthemes-element-pack'),
                    'comment' => esc_html__('Comment', 'bdthemes-element-pack'),
                    'options_page' => esc_html__('Options Page', 'bdthemes-element-pack'),
                ],
                'default' => 'post',
            ]
        );

        $this->add_control(
            'ep_acf_field_post_id',
            [
                'label' => esc_html__('Search & Select Post', 'bdthemes-element-pack'),
                'type' => \ElementPack\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
                'multiple' => false,
                'label_block' => true,
                'query_args' => [
                    'query' => 'posts',
                ],
                'condition' => [
                    'ep_acf_field_source' => 'post'
                ],
                'description' => esc_html__('Leave blank to use current post', 'bdthemes-element-pack')
            ]
        );

        $this->add_control(
            'ep_acf_field_term_id',
            [
                'label' => esc_html__('Search & Select Term', 'bdthemes-element-pack'),
                'type' => \ElementPack\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
                'multiple' => false,
                'label_block' => true,
                'query_args' => [
                    'query' => 'terms',
                    'post_type' => '_related_post_type',
                ],
                'condition' => [
                    'ep_acf_field_source' => 'taxonomy'
                ],
                'description' => esc_html__('Leave blank to use current term', 'bdthemes-element-pack')
            ]
        );

        $this->add_control(
            'ep_acf_field_user_id',
            [
                'label' => esc_html__('Search & Select User', 'bdthemes-element-pack'),
                'type' => \ElementPack\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
                'multiple' => false,
                'label_block' => true,
                'query_args' => [
                    'query' => 'authors',
                ],
                'condition' => [
                    'ep_acf_field_source' => 'user'
                ],
                'description' => esc_html__('Leave blank to use current user', 'bdthemes-element-pack')
            ]
        );

        $this->add_control(
            'ep_acf_field_comment_id',
            [
                'label' => esc_html__('Search & Select Comment', 'bdthemes-element-pack'),
                'type' => \ElementPack\Includes\Controls\SelectInput\Dynamic_Select::TYPE,
                'multiple' => false,
                'label_block' => true,
                'query_args' => [
                    'query' => 'comments',
                ],
                'condition' => [
                    'ep_acf_field_source' => 'comment'
                ],
                'description' => esc_html__('Leave blank to use current comment', 'bdthemes-element-pack')
            ]
        );

        $this->add_control(
            'ep_acf_field_key',
            [
                'label' => esc_html__('Key', 'bdthemes-element-pack'),
                'type' => Controls_Manager::SELECT,
                'groups' => self::get_control_options($this->get_supported_fields()),
                'condition' => [
                    'ep_acf_field_source' => ['post', 'taxonomy', 'user', 'comment', 'options_page']
                ]
            ]
        );
    }

    private function extract_meta_key($input): string
    {
        if (!is_string($input) || $input === '') {
            return '';
        }

        $parts = explode(':', $input, 2);
        return isset($parts[1]) && $parts[1] !== ''
            ? trim($parts[1])
            : trim($parts[0]);
    }

    private function get_acf_image_data($image)
    {
        $image_data = [
            'id' => null,
            'url' => '',
        ];

        if (empty($image)) {
            return $image_data;
        }

        if (is_array($image)) {
            $image_data['id'] = $image['id'] ?? null;
            $image_data['url'] = $image['url'] ?? '';
        }

        if (is_numeric($image)) {
            $image_data['id'] = $image;
            $image_data['url'] = wp_get_attachment_image_url($image, 'full');
        }

        if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
            $image_data['url'] = $image;
        }

        return $image_data;
    }

    public function get_value(array $options = [])
    {
        $source      = $this->get_settings_for_display('ep_acf_field_source') ?? 'post';
        $post_id     = $this->get_settings_for_display('ep_acf_field_post_id') ?? get_the_ID();
        $term_id     = $this->get_settings_for_display('ep_acf_field_term_id') ?? get_queried_object_id();
        $user_id     = $this->get_settings_for_display('ep_acf_field_user_id') ?? get_current_user_id();
        $comment_id  = $this->get_settings_for_display('ep_acf_field_comment_id') ?? get_comment_ID();
        $key         = $this->get_settings_for_display('ep_acf_field_key') ?? '';
        $value       = '';

        if (empty($key) || !function_exists('get_field')) return '';

        $key = $this->extract_meta_key($key);

        switch ($source) {
            case 'post':
                $value = get_field($key, $post_id);
                break;

            case 'taxonomy':
                $value = get_field($key, 'term_' . $term_id);
                break;

            case 'user':
                $value = get_field($key, 'user_' . $user_id);
                break;

            case 'comment':
                $value = get_field($key, 'comment_' . $comment_id);
                break;

            case 'options_page':
                $value = get_option('options_' . $key);
                break;
        }
        $images = [];
        if (is_array($value) && !empty($value)) {
            foreach ($value as $image) {
                $images[] = $this->get_acf_image_data($image);
            }
        }

        return $images;
    }
}
