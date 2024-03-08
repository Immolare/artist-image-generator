<?php
/**
 * The public-facing functionality of the plugin.
 * 
 * @link       https://pierrevieville.fr
 * @since      1.0.0
 * 
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Artist_Image_Generator
 * @subpackage Artist_Image_Generator/public
 * @author     Pierre Viéville <contact@pierrevieville.fr>
 */
class Artist_Image_Generator_Public
{
    private $plugin_name;
    private $version;
    private $plugin_admin;
    private $avatar_manager;
    private $data_validator;

    const DEFAULT_ACTION = 'generate_image';
    const DEFAULT_PROMPT = '';
    const DEFAULT_TOPICS = '';
    const DEFAULT_N = '3';
    const DEFAULT_SIZE = '1024x1024';
    const DEFAULT_MODEL = 'dall-e-2';
    const DEFAULT_DOWNLOAD = 'manual';
    const DEFAULT_QUALITY = 'standard';
    const DEFAULT_STYLE = 'vivid';

    const POSSIBLE_SIZES_DALLE_2 = ['256x256', '512x512', '1024x1024'];
    const POSSIBLE_SIZES_DALLE_3 = ['1024x1024', '1024x1792', '1792x1024'];
    const POSSIBLE_QUALITIES_DALLE_3 = ['standard', 'high'];
    const POSSIBLE_STYLES_DALLE_3 = ['vivid', 'natural'];
    const POSSIBLE_MODELS = ['dall-e-2', 'dall-e-3'];
    const POSSIBLE_ACTIONS = ['generate_image', 'variate_image'];

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_shortcode('aig', array($this, 'aig_shortcode'));

        $this->include_required_files();

        $this->plugin_admin = new Artist_Image_Generator_Admin($this->plugin_name, $this->version);
        $this->avatar_manager = new Artist_Image_Generator_Shortcode_Avatar_Manager();
        $this->data_validator = new Artist_Image_Generator_Shortcode_Data_Validator();
    }

    private function include_required_files()
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-artist-image-generator-shortcode-avatar-manager.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-artist-image-generator-shortcode-data-validator.php';
    }

    public function generate_image()
    {
        $data = $this->plugin_admin->do_post_request();

        if (wp_doing_ajax()) {
            wp_send_json($data);
            wp_die();
        }
    }

    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/artist-image-generator-public.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/artist-image-generator-public.js', array('jquery'), $this->version, false);
    }

    public function get_avatar_filter($avatar, $id_or_email, $size, $default, $alt)
    {
        $this->avatar_manager->filter($avatar, $id_or_email, $size, $default, $alt);
    }

    public function change_wp_avatar()
    {
        $this->avatar_manager->change();
    }

    private function validate_atts(&$atts)
    {    
        $atts['action'] = $this->data_validator->validateString($atts['action'], self::POSSIBLE_ACTIONS, self::DEFAULT_ACTION);
        $atts['n'] = $this->data_validator->validateInt($atts['n'], 1, 10, self::DEFAULT_N);
        $atts['model'] = $this->data_validator->validateString($atts['model'], self::POSSIBLE_MODELS, self::DEFAULT_MODEL);
    
        if ($atts['model'] === 'dall-e-3') {
            $atts['n'] = $this->data_validator->validateInt($atts['n'], 1, 10, self::DEFAULT_N);
            $atts['size'] = $this->data_validator->validateSize($atts['size'], self::POSSIBLE_SIZES_DALLE_3, self::DEFAULT_SIZE);
            $atts['quality'] = $this->data_validator->validateString($atts['quality'], self::POSSIBLE_QUALITIES_DALLE_3, self::DEFAULT_QUALITY);
            $atts['style'] = $this->data_validator->validateString($atts['style'], self::POSSIBLE_STYLES_DALLE_3, self::DEFAULT_STYLE);
        } else {
            $atts['size'] = $this->data_validator->validateSize($atts['size'], self::POSSIBLE_SIZES_DALLE_2, self::DEFAULT_SIZE);
            $atts['n'] = $this->data_validator->validateInt($atts['n'], 1, 10, self::DEFAULT_N);
            $atts['quality'] = null;
            $atts['style'] = null;
        }
    }

    private function get_default_atts($atts)
    {
        return shortcode_atts(
            array(
                'action' => self::DEFAULT_ACTION,
                'prompt' => self::DEFAULT_PROMPT,
                'topics' => self::DEFAULT_TOPICS,
                'n' => self::DEFAULT_N,
                'size' => self::DEFAULT_SIZE,
                'model' => self::DEFAULT_MODEL,
                'download' => self::DEFAULT_DOWNLOAD,
                'quality' => self::DEFAULT_QUALITY,
                'style' => self::DEFAULT_STYLE
            ),
            $atts
        );
    }

    private function generate_shortcode_html($atts, $plugin_admin)
    {
        if (!$plugin_admin->check_license_validity() && esc_attr($atts['model']) === 'dall-e-3') {
            $atts['n'] = 1;    
        }

        $nonce_field = wp_nonce_field(esc_attr($atts['action']), '_wpnonce', true, false);

        $allowed_html = array(
            'input' => array(
                'type' => array(),
                'id' => array(),
                'name' => array(),
                'value' => array()
            )
        );

        ob_start();

        ?>
        <div class="aig-form-container">
            <form method="post" class="aig-form" 
                data-action="<?php echo esc_attr($atts['action']); ?>" 
                data-n="<?php echo esc_attr($atts['n']); ?>" 
                data-size="<?php echo esc_attr($atts['size']); ?>" 
                data-quality="<?php echo esc_attr($atts['quality']); ?>"
                data-style="<?php echo esc_attr($atts['style']); ?>"
                data-model="<?php echo esc_attr($atts['model']); ?>" 
                data-download="<?php echo esc_attr($atts['download']); ?>" 
                action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                <input type="hidden" name="aig_prompt" value="<?php echo esc_attr($atts['prompt']); ?>" />
                <input type="hidden" name="action" value="<?php echo esc_attr($atts['action']); ?>" />
                <?php echo wp_kses($nonce_field, $allowed_html); ?>
                <div class="form-group">
                    <fieldset class="aig-topic-buttons">
                        <legend class="form-label"><?php esc_html_e('Topics:', 'artist-image-generator'); ?></legend>
                        <?php
                        $topics_string = $atts['topics'];
                        if (!empty($topics_string)) {
                            $topics = explode(',', $topics_string);
                            foreach ($topics as $topic) {
                                $topic = trim($topic);
                        ?>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="aig_topics[]" value="<?php echo esc_attr($topic); ?>" class="form-check-input" id="aig_topic_<?php echo esc_attr($topic); ?>">
                                    <label class="form-check-label" for="aig_topic_<?php echo esc_attr($topic); ?>"><?php echo esc_html($topic, 'artist-image-generator'); ?></label>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </fieldset>
                    <small id="aig_topics_help" class="form-text text-muted"><?php esc_html_e('Select one or more topics for image generation.', 'artist-image-generator'); ?></small>
                </div>
                <hr />
                <div class="form-group">
                    <label for="aig_public_prompt" class="form-label"><?php esc_html_e('Description:', 'artist-image-generator');?></label>
                    <textarea name="aig_public_prompt" id="aig_public_prompt" class="form-control" placeholder="<?php esc_html_e("Enter a description for the image generation (e.g., 'A beautiful cat').", 'artist-image-generator'); ?>"></textarea>
                    <small id="aig_public_prompt_help" class="form-text text-muted"><?php esc_html_e('Enter a brief description for the image generation.', 'artist-image-generator');?></small>
                </div>
                <hr class="aig-results-separator" style="display:none" />
                <div class="aig-errors"></div>
                <div class="aig-results"></div>
                <hr />
                <button type="submit" class="btn btn-primary"><?php esc_html_e('Generate Image / Retry'); ?></button>
            </form>
        </div>
    <?php

        return ob_get_clean();
    }

    public function aig_shortcode($atts)
    {
        $atts = $this->get_default_atts($atts);

        // Validate attributs
        $this->validate_atts($atts);

        return $this->generate_shortcode_html($atts, $this->plugin_admin);
    }
}
