<?php

use Artist_Image_Generator_License as License;
use Artist_Image_Generator_Setter as Setter;
use Artist_Image_Generator_Dalle as Dalle;

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
    const DEFAULT_LIMIT_PER_USER = 0; // no limit
    const DEFAULT_LIMIT_PER_USER_REFRESH_DURATION = 0; // no refresh; in seconds

    const POSSIBLE_SIZES_DALLE_2 = ['256x256', '512x512', '1024x1024'];
    const POSSIBLE_SIZES_DALLE_3 = ['1024x1024', '1024x1792', '1792x1024'];
    const POSSIBLE_QUALITIES_DALLE_3 = ['standard', 'high'];
    const POSSIBLE_STYLES_DALLE_3 = ['vivid', 'natural'];
    const POSSIBLE_MODELS = ['dall-e-2', 'dall-e-3'];
    const POSSIBLE_ACTIONS = ['generate_image', 'variate_image'];

    private const ERROR_TYPE_LIMIT_EXCEEDED = 'limit_exceeded_error';
    private const ERROR_TYPE_INVALID_FORM = 'invalid_form_error';

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_shortcode('aig', array($this, 'aig_shortcode'));

        $this->include_required_files();

        $this->avatar_manager = new Artist_Image_Generator_Shortcode_Avatar_Manager();
        $this->data_validator = new Artist_Image_Generator_Shortcode_Data_Validator();
    }

    private function include_required_files()
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-artist-image-generator-shortcode-avatar-manager.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-artist-image-generator-shortcode-data-validator.php';
    }

    private function check_and_update_user_limit($post_data)
    {
        if (isset($post_data['user_limit'])) {
            $user_id = get_current_user_id();
            $user_ip = $_SERVER['REMOTE_ADDR'];
            $user_identifier = $user_id ? 'artist_image_generator_user_' . $user_id : 'artist_image_generator_ip_' . $user_ip;
            $requests = get_transient($user_identifier);
            $duration = isset($post_data['user_limit_duration']) && $post_data['user_limit_duration'] > 0 ? (int)$post_data['user_limit_duration'] : 0;
            $expiration = get_option('_transient_timeout_' . $user_identifier);

            if ($requests === false || ($duration > 0 && time() > $expiration)) {
                $requests = 0;
                set_transient($user_identifier, $requests, $duration);
            }

            $requests++;

            if ((int)$post_data['user_limit'] < $requests) {
                $duration_msg = $duration > 0 ? sprintf(__(' Please try again in %d seconds.', 'artist-image-generator'), $expiration - time()) : '';
                $error_message = esc_html__('You have reached the limit of requests.', 'artist-image-generator') . $duration_msg;
                wp_send_json(array(
                    'error' => array(
                        'type' => self::ERROR_TYPE_LIMIT_EXCEEDED, 
                        'message' => $error_message
                    )
                ));
                wp_die();
            }

            set_transient($user_identifier, $requests, $duration);
        }
    }

    public function generate_image()
    {
        $post_data = [];
        $dalle = new Dalle();
        
        if(wp_doing_ajax() && Setter::is_setting_up()) {
            $post_data = $dalle->sanitize_post_data();

            if (!check_ajax_referer('generate_image', '_ajax_nonce', false)) {
                wp_send_json(array(
                    'error' => array(
                        'type' => self::ERROR_TYPE_INVALID_FORM, 
                        'message' => esc_html__('Invalid nonce.', 'artist-image-generator')
                    )
                ));
                wp_die();
            }

            $this->check_and_update_user_limit($post_data);

            if (isset($post_data['generate'])) {
                $response = $dalle->handle_generation($post_data);
            }

            if (isset($response)) {
                list($images, $error) = $dalle->handle_response($response);
            }

            $data = $dalle->prepare_data($images ?? [], $error ?? [], $post_data);

            //$data = '{"error":[],"images":[{"url":"https://artist-image-generator.com/wp-content/uploads/img-rck1GT0eGIYLu4oAXFEMqsPT.png"}],"model_input":"dall-e-2","prompt_input":"Painting of a bird, including following criterias:","size_input":"1024x1024","n_input":"1","quality_input":"","style_input":""}';

            //$array = json_decode($data, true);
            wp_send_json($data);
            wp_die();
        }

        wp_die("Should not be reached. Check configuration.");       
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

        $atts['user_limit'] = $this->data_validator->validateInt($atts['user_limit'], 0, PHP_INT_MAX, self::DEFAULT_LIMIT_PER_USER);
        $atts['user_limit_duration'] = $this->data_validator->validateInt($atts['user_limit_duration'], 0, PHP_INT_MAX, self::DEFAULT_LIMIT_PER_USER_REFRESH_DURATION);
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
                'style' => self::DEFAULT_STYLE,
                'user_limit' => self::DEFAULT_LIMIT_PER_USER,
                'user_limit_duration' => self::DEFAULT_LIMIT_PER_USER_REFRESH_DURATION,
                'mask_url' => '',
                'origin_url' => '',
            ),
            $atts
        );
    }

    private function generate_shortcode_html($atts)
    {
        if (!License::license_check_validity() && esc_attr($atts['model']) === 'dall-e-3') {
            $atts['n'] = 1;    
        }

        $nonce_field = wp_nonce_field(esc_attr($atts['action']), '_ajax_nonce', true, false);

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
                action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                <?php if (!empty($atts['mask_url']) && !empty($atts['origin_url'])) { ?>
                    data-origin-url="<?php echo esc_url($atts['origin_url']); ?>"
                    data-mask-url="<?php echo esc_url($atts['mask_url']); ?>"
                <?php } ?>
            >
                <input type="hidden" name="aig_prompt" value="<?php echo esc_attr($atts['prompt']); ?>" />
                <input type="hidden" name="action" value="<?php echo esc_attr($atts['action']); ?>" />
                <input type="hidden" name="user_limit" value="<?php echo esc_attr($atts['user_limit']); ?>" />
                <input type="hidden" name="user_limit_duration" value="<?php echo esc_attr($atts['user_limit_duration']); ?>" />

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

        return $this->generate_shortcode_html($atts);
    }
}
