<?php

use Elementor\Controls_Manager;
use ElementPack\Includes\Traits\UtilsTrait;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ElementPack_Dynamic_Tag_ACF_Text extends \Elementor\Core\DynamicTags\Tag
{

    use UtilsTrait;
    private static $dynamic_value_provider;

    public function get_name(): string
    {
        return 'element-pack-acf-text';
    }

    public function get_title(): string
    {
        return esc_html__('Text', 'bdthemes-element-pack');
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
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
        ];
    }

    public function get_supported_fields()
    {
        return [
            'text',
            'textarea',
            'number',
            'email',
            'password',
            'wysiwyg',
            'select',
            'checkbox',
            'radio',
            'true_false',

            // Pro
            'oembed',
            'google_map',
            'date_picker',
            'time_picker',
            'date_time_picker',
            'color_picker',
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

    protected function register_advanced_section()
    {
        $this->advanced_controls();
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


    public function render(): void
    {
        $source      = $this->get_settings_for_display('ep_acf_field_source') ?? 'post';
        $post_id     = $this->get_settings_for_display('ep_acf_field_post_id') ?? get_the_ID();
        $term_id     = $this->get_settings_for_display('ep_acf_field_term_id') ?? get_queried_object_id();
        $user_id     = $this->get_settings_for_display('ep_acf_field_user_id') ?? get_current_user_id();
        $comment_id  = $this->get_settings_for_display('ep_acf_field_comment_id') ?? get_comment_ID();
        $key         = $this->get_settings_for_display('ep_acf_field_key') ?? '';
        $value       = '';

        if (empty($key) || !function_exists('get_field')) {
            echo '';
            return;
        }

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

        // Output the value (handles arrays and objects nicely)
        if (is_array($value)) {
            $output = implode(', ', $value);
        } else {
            $output = (string) $value; // Ensure it's a string
        }

        // Apply word limit
        $output = $this->apply_word_limit($output);

        // Output directly (allows HTML)
        echo $output;
    }
}
