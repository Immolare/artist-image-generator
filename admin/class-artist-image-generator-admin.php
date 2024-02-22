<?php

use Orhanerday\OpenAi\OpenAi;

use function Crontrol\Event\get;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Artist_Image_Generator
 * @subpackage Artist_Image_Generator/admin
 * @author     Pierre Viéville <contact@pierrevieville.fr>
 */
class Artist_Image_Generator_Admin
{
    const QUERY_SETUP = 'setup';
    const QUERY_FIELD_ACTION = 'action';
    const ACTION_GENERATE = 'generate';
    const ACTION_VARIATE = 'variate';
    const ACTION_EDIT = 'edit';
    const ACTION_PUBLIC = 'public';
    const ACTION_SETTINGS = 'settings';
    const ACTION_ABOUT = 'about';
    const LAYOUT_MAIN = 'main';
    const DALL_E_MODEL_3 = "dall-e-3";
    const DALL_E_MODEL_2 = "dall-e-2";

    private string $plugin_name;
    private string $plugin_full_name = "Artist Image Generator";
    private string $version;

    private string $prefix;
    private string $admin_partials_path;
    private array $admin_display_templates;
    private array $admin_actions;
    private array $admin_actions_labels;


    // Define system constants
    const AIG_PLUGIN_NAME = "artist-image-generator";
    const AIG_LICENSE_SERVER = 'https://artist-image-generator.com';
    const AIG_CUSTOMER_KEY = 'ck_204741c9c2c41edb13767f951284d6c57360e0d7';
    const AIG_CUSTOMER_SECRET = 'cs_3f2d6cf0fb6e046e69ef629923a3866716cbad17';
    const AIG_PRODUCT_IDS = [21, 1282];
    const AIG_DAYS = 0;

    private $options;
    private $sdk_license;

    public function __construct(string $plugin_name, string $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->prefix = "artist_image_generator";
        $this->admin_partials_path = "admin/partials/";
        $this->admin_display_templates = [
            'generate' => 'generate',
            'variate' => 'variate',
            'edit' => 'edit',
            'public' => 'public',
            'settings' => 'settings',
            'about' => 'about',
            'main' => 'main'
        ];
        $this->admin_actions = [
            self::ACTION_GENERATE,
            self::ACTION_VARIATE,
            self::ACTION_EDIT,
            self::ACTION_PUBLIC,
            self::ACTION_SETTINGS,
            self::ACTION_ABOUT
        ];
        $this->admin_actions_labels = [
            self::ACTION_GENERATE => esc_attr__('Generate', 'artist-image-generator'),
            self::ACTION_VARIATE => esc_attr__('Variate', 'artist-image-generator'),
            self::ACTION_EDIT => esc_attr__('Edit (Pro)', 'artist-image-generator'),
            self::ACTION_PUBLIC => esc_attr__('Shortcodes', 'artist-image-generator'),
            self::ACTION_SETTINGS => esc_attr__('Settings', 'artist-image-generator'),
            self::ACTION_ABOUT => esc_attr__('About', 'artist-image-generator')
        ];

        //delete_option('plugin_license');
        //delete_option('plugin-is-valid');
        //delete_option($this->prefix . '_aig_licence_key_0');
        //delete_option($this->prefix . '_aig_licence_object_0');   
        
        $this->sdk_license = new LMFW\SDK\License(
            self::AIG_PLUGIN_NAME,
            self::AIG_LICENSE_SERVER,
            self::AIG_CUSTOMER_KEY,
            self::AIG_CUSTOMER_SECRET,
            self::AIG_PRODUCT_IDS,
            [
                'settings_key' => $this->prefix . '_option_name',
                'option_key' => $this->prefix . '_aig_licence_key_0'
            ],
            $this->prefix . '_aig_licence_object_0',
            self::AIG_DAYS
        );

        //var_dump($this->sdk_license);die;

        // Schedule license validity check event
        if (!wp_next_scheduled('artist_image_generator_license_validity')) {
            wp_schedule_event(time(), 'daily', 'artist_image_generator_license_validity');
        }

        // Add validity function hook
        add_action('artist_image_generator_license_validity', array($this, 'aig_check_license_validity'));
        add_action('admin_notices', [$this, 'display_admin_notices']);
        add_action('admin_init', [$this, 'hide_admin_notices']);
    }

    /**
     * Hook : Enqueue CSS scripts
     *
     * @return void
     */
    public function enqueue_styles(): void
    {
        //wp_enqueue_style('wp-admin');
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/artist-image-generator-admin.css', array(), $this->version, 'all');
    }

    /**
     * Filter Beaver Builder CSS
     *
     * @return void
     */
    public function enqueue_bb_styles($css, $nodes, $global_settings)
    {
        $css .= file_get_contents(plugin_dir_path(__FILE__) . 'css/artist-image-generator-admin.css');

        return $css;
    }

    private function aig_ajax_object(): array
    {
        $is_media_editor_page = $this->is_media_editor_page();

        return array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'cropper_script_path' => plugin_dir_url(__FILE__) . 'js/artist-image-generator-admin-cropper.js',
            'drawing_tool_script_path' => plugin_dir_url(__FILE__) . 'js/artist-image-generator-admin-drawing.js',
            'is_media_editor' => $is_media_editor_page,
            'variateLabel' => esc_attr__('Variate', 'artist-image-generator'),
            'editLabel' => esc_attr__('Edit (Pro)', 'artist-image-generator'),
            'publicLabel' => esc_attr__('Shortcodes', 'artist-image-generator'),
            'generateLabel' => esc_attr__('Generate', 'artist-image-generator'),
            'cropperCropLabel' => esc_attr__('Crop this zone', 'artist-image-generator'),
            'cropperCancelLabel' => esc_attr__('Cancel the zoom', 'artist-image-generator'),
            'cancelLabel' => esc_attr__('Cancel', 'artist-image-generator'),
            'maskLabel' => esc_attr__('Create mask', 'artist-image-generator'),
            'editLabel' => esc_attr__('Edit image', 'artist-image-generator'),
            'valid_licence' => $this->check_license_validity(),
        );
    }

    private function aig_data(): array
    {
        return array(
            'error' => [],
            'images' => [],
            'model_input' => "", // dall-e-2
            'prompt_input' => "",
            'size_input' => $this->get_default_image_dimensions(),
            'n_input' => 1,
            'quality_input' => "",
            'style_input' => ""
        );
    }
    /**
     * Hook : Enqueue JS scripts
     *
     * @return void
     */
    public function enqueue_scripts(): void
    {
        $is_media_editor_page = $this->is_media_editor_page();
        $is_plugin_page = $this->is_artist_image_generator_page();
        if ($is_plugin_page || $is_media_editor_page) {
            $dependencies = array('wp-util', 'jquery', 'underscore');
            wp_enqueue_script('wp-util');

            if ($is_media_editor_page) {
                $dependencies[] = 'media-editor';
                wp_enqueue_media();
                wp_enqueue_script('media-editor');
            }

            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/artist-image-generator-admin.js', $dependencies, $this->version, true);
            wp_localize_script($this->plugin_name, 'aig_ajax_object', $this->aig_ajax_object());

            if ($is_media_editor_page) {
                wp_localize_script($this->plugin_name, 'aig_data', $this->aig_data());
            }
        }
    }

    /**
     * Check if current page is the Artist Image Generator page
     *
     * @return bool
     */
    private function is_artist_image_generator_page(): bool
    {
        global $pagenow;

        return $pagenow === 'upload.php' && isset($_GET['page']) && $_GET['page'] === $this->prefix;
    }

    /**
     * Check if current page is the media editor page and the edit action is set
     *
     * @return bool
     */
    private function is_media_editor_page(): bool
    {
        global $pagenow;

        $allowedPages = ['post.php', 'post-new.php', 'customize.php', 'profile.php', 'term.php', 'widgets.php'];

        return (in_array($pagenow, $allowedPages) || isset($_GET['fl_builder']));
    }

    /**
     * Hook : Add links to plugin meta
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    public function plugin_row_meta(array $links, string $file): array
    {
        if (strpos($file, "{$this->plugin_name}/{$this->plugin_name}.php") === false) {
            return $links;
        }

        $meta = array(
            'support' => sprintf('<a href="%1$s" target="_blank"><span class="dashicons dashicons-sos"></span> %2$s</a>', esc_url("https://wordpress.org/support/plugin/{$this->plugin_name}"), esc_html__('Support', 'artist-image-generator')),
            'review' => sprintf('<a href="%1$s" target="_blank"><span class="dashicons dashicons-thumbs-up"></span> %2$s</a>', esc_url("https://wordpress.org/support/plugin/{$this->plugin_name}/reviews/#new-post"), esc_html__('Review', 'artist-image-generator')),
            'github' => sprintf('<a href="%1$s" target="_blank"><span class="dashicons dashicons-randomize"></span> %2$s</a>', esc_url("https://github.com/Immolare/{$this->plugin_name}"), esc_html__('GitHub', 'artist-image-generator')),
        );

        return array_merge($links, $meta);
    }

    /**
     * Hook : Add settings link to plugin action
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    public function plugin_action_links(array $links, string $file): array
    {
        if ($file !== "{$this->plugin_name}/{$this->plugin_name}.php" || !current_user_can('manage_options')) {
            return $links;
        }

        array_unshift(
            $links,
            sprintf('<a href="%1$s">%2$s</a>', $this->get_admin_tab_url(self::ACTION_SETTINGS), esc_html__(ucfirst(self::ACTION_SETTINGS), 'artist-image-generator'))
        );

        return $links;
    }

    /**
     * Hook : Add plugin menu item to the admin menu
     *
     * @return void
     */
    public function admin_menu(): void
    {
        add_media_page(
            $this->plugin_full_name,
            __('Image Generator', 'artist-image-generator'),
            'manage_options',
            $this->prefix,
            [$this, 'admin_page']
        );
    }

    public function check_license_validity() {
        $options = get_option($this->prefix . '_option_name', array());
        $keyField = $this->prefix . '_aig_licence_key_0';

        if (empty($options)) {
            return false;
        }

        $license_key = array_key_exists($keyField, $options) ? $options[$keyField] : null;

        if (!$license_key) {
            return false;
        }

        $valid_result = $this->sdk_license->validate_status($license_key);

        if ($valid_result['is_valid']) {
            return true;
        }

        return false;
    }
    
    public function aig_check_license_validity() {
        $options = get_option($this->prefix . '_option_name', array());
        $keyField = $this->prefix . '_aig_licence_key_0';

        if (empty($options)) {
            return false;
        }

        $license_key = array_key_exists($keyField, $options) ? $options[$keyField] : null;

        if (!$license_key) {
            return false;
        }
    
        // Validate the license status with the retrieved key
        $valid_status = $this->sdk_license->validate_status($license_key);
        $valid_until = $this->sdk_license->valid_until();
    
        // Check if the license is valid
        if ($valid_status['is_valid'] && $valid_until && $valid_until < (time() + 15 * DAY_IN_SECONDS)) {
            if (get_option($this->prefix . '_aig_license_expiring_soon') != 'hidden') {
                update_option($this->prefix . '_aig_license_expiring_soon', 'display');
            }
        }
    
        // Check if the license is expired or invalid
        if (!$valid_status['is_valid']) {
            if (get_option($this->prefix . '_aig_license_invalid_or_expired') != 'hidden') {
                update_option($this->prefix . '_aig_license_invalid_or_expired', 'display');
            }
        }
    
        return $valid_status['is_valid'];
    }

    public function display_admin_notices() {
        $icon = '<img width="20px" src="' . plugin_dir_url(__FILE__) . '/img/aig-icon.png' .'" alt="Artist Image Generator Icon" />';
        if (get_option($this->prefix . '_aig_license_invalid_or_expired') == 'display') {
            $hide_url = add_query_arg($this->prefix . '_hide_notice', 'invalid_or_expired');
            echo '<div class="notice aig-notice notice-error is-dismissible">
                <p>' . $icon . __('Your <strong>Artist Image Generator</strong> license key is expired or invalid. <a target="_blank" href="https://artist-image-generator.com/product/licence-key/">Renew your key now</a>.', 'artist-image-generator') . '</p>
                <p><a href="' . esc_url($hide_url) . '">' . __('Hide this notice', 'artist-image-generator') . '</a></p>
            </div>';
            return;
        }
    
        if (get_option($this->prefix . '_aig_license_expiring_soon') == 'display') {
            $expire_date = date_i18n(get_option('date_format'), $this->sdk_license->valid_until());
            $hide_url = add_query_arg($this->prefix . '_hide_notice', 'expiring_soon');
            echo '<div class="notice aig-notice notice-warning is-dismissible">
                <p>' . $icon . sprintf(__('Your <strong>Artist Image Generator</strong> license key is expiring on %s. <a target="_blank" href="%s">Renew your key now</a>.', 'artist-image-generator'), $expire_date, 'https://artist-image-generator.com/product/licence-key/') . '</p>
                <p><a href="' . esc_url($hide_url) . '">' . __('Hide this notice', 'artist-image-generator') . '</a></p>
            </div>';
        }
    }
    
    public function hide_admin_notices() {
        if (isset($_GET[$this->prefix . '_hide_notice'])) {
            update_option($this->prefix . '_aig_license_' . sanitize_text_field($_GET[$this->prefix . '_hide_notice']), 'hidden');
        }
    }

    /**
     * Hook : Init plugin's parameters
     *
     * @return void
     */
    public function admin_init(): void
    {
        register_setting(
            $this->prefix . '_option_group', // option_group
            $this->prefix . '_option_name', // option_name
            array($this, 'sanitize') // sanitize_callback
        );

        add_settings_section(
            $this->prefix . '_setting_section', // id
            __('Settings', 'artist-image-generator'), // title
            array($this, 'section_info'), // callback
            $this->prefix . '-admin' // page
        );

        add_settings_field(
            $this->prefix . '_openai_api_key_0', // id
            'OPENAI_KEY', // title
            array($this, 'openai_api_key_0_callback'), // callback
            $this->prefix . '-admin', // page
            $this->prefix . '_setting_section' // section
        );

        add_settings_field(
            $this->prefix . '_aig_licence_key_0', // id
            'AIG_KEY', // title
            array($this, 'aig_licence_key_0_callback'), // callback
            $this->prefix . '-admin', // page
            $this->prefix . '_setting_section' // section
        );
    }

    /**
     * Utility function to sanitize input field parameter
     *
     * @param array $input
     * @return array
     */
    public function sanitize(array $input): array
    {
        $sanitizedValues = [];
    
        if (isset($input[$this->prefix . '_openai_api_key_0'])) {
            $sanitizedValues[$this->prefix . '_openai_api_key_0'] = sanitize_text_field($input[$this->prefix . '_openai_api_key_0']);
        }
    
        if (isset($input[$this->prefix . '_aig_licence_key_0'])) {
            $licence_key = sanitize_text_field($input[$this->prefix . '_aig_licence_key_0']);

            if (empty($licence_key)) {
                return $sanitizedValues;
            }

            $is_valid_license = $this->sdk_license->validate_status($licence_key);

            if (is_null($is_valid_license['is_valid']) && is_null($is_valid_license['error'])) {
                add_settings_error(
                    $this->prefix . '_option_name',
                    'invalid_license',
                    __("Invalid licence key", 'artist-image-generator'),
                    'error'
                );

                return $sanitizedValues;
            }

            if ($is_valid_license['error']) {
                add_settings_error(
                    $this->prefix . '_option_name',
                    'invalid_license',
                    $is_valid_license['error'],
                    'error'
                );

                return $sanitizedValues;
            }

            if ($is_valid_license['is_valid']) {
                $is_licence_activated = get_option($this->prefix . '_aig_licence_key_activated_0');
                if (!$is_licence_activated) {
                    $this->sdk_license->activate($licence_key);
                    update_option($this->prefix . '_aig_licence_key_activated_0', true);
                    add_settings_error(
                        $this->prefix . '_option_name',
                        'valid_license',
                        __('License key is valid and was activated', 'artist-image-generator'),
                        'updated'
                    );
                }

                $sanitizedValues[$this->prefix . '_aig_licence_key_0'] = $licence_key;
            }            
        }
    
        return $sanitizedValues;
    }

    /**
     * Section info : Not used
     *
     * @return void
     */
    public function section_info(): void
    {
    }

    /**
     * Utility function to print the input field parameter
     *
     * @return void
     */
    public function openai_api_key_0_callback(): void
    {
        printf(
            '<input class="regular-text" type="text" name="' . $this->prefix . '_option_name[' . $this->prefix . '_openai_api_key_0]" id="' . $this->prefix . '_openai_api_key_0" value="%s">',
            isset($this->options[$this->prefix . '_openai_api_key_0']) ? esc_attr($this->options[$this->prefix . '_openai_api_key_0']) : ''
        );
    }

    /**
     * Utility function to print the input field parameter
     *
     * @return void
     */
    public function aig_licence_key_0_callback(): void
    {
        printf(
            '<input class="regular-text" type="text" name="' . $this->prefix . '_option_name[' . $this->prefix . '_aig_licence_key_0]" id="' . $this->prefix . '_aig_licence_key_0" value="%s">',
            isset($this->options[$this->prefix . '_aig_licence_key_0']) ? esc_attr($this->options[$this->prefix . '_aig_licence_key_0']) : ''
        );
    }

    /**
     * Hook : The plugin's administration page
     *
     * @return void
     */
    public function admin_page()
    {
        $data = $this->do_post_request();

        if (wp_doing_ajax()) {
            wp_send_json($data);
            wp_die();
        }

        // Pass the variable to the template
        wp_localize_script($this->plugin_name, 'aig_data', $data);

        require_once $this->get_admin_template(self::LAYOUT_MAIN);
    }

    /**
     * Utility function to do some post request processing used on admin_page and admin_media_manager_page
     *
     * @return void
     */
    public function do_post_request()
    {
        $images = [];
        $error = [];

        $this->options = get_option($this->prefix . '_option_name');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->is_setting_up()) {
            $is_generation = isset($_POST['generate']) && sanitize_text_field($_POST['generate']);
            $is_variation = isset($_POST['variate']) && sanitize_text_field($_POST['variate']);
            $is_edit = isset($_POST['edit']) && sanitize_text_field($_POST['edit']);
            $prompt_input = isset($_POST['prompt']) ? sanitize_text_field($_POST['prompt']) : null;
            $size_input = isset($_POST['size']) ? sanitize_text_field($_POST['size']) : $this->get_default_image_dimensions();
            $n_input = isset($_POST['n']) ? sanitize_text_field($_POST['n']) : 1;
            // DALL·E 3
            $model = isset($_POST['model']) && sanitize_text_field($_POST['model']) === self::DALL_E_MODEL_3 ? self::DALL_E_MODEL_3 : null;
            $quality_input = null;
            $style_input = null;
            if (isset($_POST['model']) && sanitize_text_field($_POST['model']) === self::DALL_E_MODEL_3) {
                $quality_input = isset($_POST['quality']) ? sanitize_text_field($_POST['quality']) : 'standard';
                $style_input = isset($_POST['style']) ? sanitize_text_field($_POST['style']) : 'vivid';
            }

            if ($is_generation) {
                if (empty($prompt_input)) {
                    $error = [
                        'msg' => __('The Prompt input must be filled in order to generate an image.', 'artist-image-generator')
                    ];
                } else {
                    $response = $this->generate($prompt_input, $n_input, $size_input, $model, $quality_input, $style_input);
                }
            } elseif ($is_variation) {
                $errorMsg = __('A .png square (1:1) image of maximum 4MB needs to be uploaded in order to generate a variation of this image.', 'artist-image-generator');
                $image_file = isset($_FILES['image']) && $_FILES['image']['size'] > 0 ? $_FILES['image'] : null;

                if (empty($image_file)) {
                    $error = ['msg' => $errorMsg];
                } else {
                    $image_mime_type = mime_content_type($image_file['tmp_name']);
                    list($image_width, $image_height) = getimagesize($image_file['tmp_name']);
                    $image_wrong_size = $image_file['size'] >= ((1024 * 1024) * 4) || $image_file['size'] == 0;
                    $allowed_file_types = ['image/png']; // If you want to allow certain files

                    if (!in_array($image_mime_type, $allowed_file_types) || $image_wrong_size || $image_height !== $image_width) {
                        $error = ['msg' => $errorMsg];
                    } else {
                        $response = $this->variate($image_file, $n_input, $size_input);
                    }
                }
            } elseif ($is_edit && $this->check_license_validity()) {
                $errorMsg = __('A .png square (1:1) image of maximum 4MB needs to be uploaded in order to generate a variation of this image.', 'artist-image-generator');
                $image_file = isset($_FILES['image']) && $_FILES['image']['size'] > 0 ? $_FILES['image'] : null;
                $mask_file = isset($_FILES['mask']) && $_FILES['mask']['size'] > 0 ? $_FILES['mask'] : null;

                if (empty($image_file)) {
                    $error = ['msg' => $errorMsg];
                } else {
                    // EDIT MASK FILE                  
                    $image_mime_type = mime_content_type($image_file['tmp_name']);
                    list($image_width, $image_height) = getimagesize($image_file['tmp_name']);
                    $image_wrong_size = $image_file['size'] >= ((1024 * 1024) * 4) || $image_file['size'] == 0;
                    $allowed_file_types = ['image/png']; // If you want to allow certain files

                    if (!in_array($image_mime_type, $allowed_file_types) || $image_wrong_size || $image_height !== $image_width) {
                        $error = ['msg' => $errorMsg];
                    } else {
                        $response = $this->edit($image_file, $mask_file, $prompt_input, $n_input, $size_input);
                    }
                }
            }

            if (isset($response)) {
                if (array_key_exists('error', $response)) {
                    $error = ['msg' => $response['error']['message']];
                } else {
                    $images = $response;
                }
            }
        }

        $data = [
            'error' => $error,
            'images' => count($images) ? $images['data'] : [],
            'model_input' => $model ?? '',
            'prompt_input' => $prompt_input ?? '',
            'size_input' => $size_input ?? $this->get_default_image_dimensions(),
            'n_input' => $n_input ?? 1,
            'quality_input' => $quality_input ?? '',
            'style_input' => $style_input ?? ''
        ];

        return $data;
    }

    /**
     * Utility function to check if the plugin parameters are set
     *
     * @return boolean
     */
    private function is_setting_up(): bool
    {
        return is_array($this->options) && array_key_exists($this->prefix . '_openai_api_key_0', $this->options);
    }

    /**
     * Utility function to communicate with OpenAI API when generating image
     *
     * @param string $prompt_input
     * @param integer $n_input
     * @param string $size_input
     * @return array
     */
    private function generate(
        string $prompt_input, 
        int $n_input, 
        string $size_input, 
        string $model = null,
        string $quality_input = null,
        string $style_input = null
    ): array
    {
        $num_images = max(1, min(10, (int) $n_input));
        $open_ai = new OpenAi($this->options[$this->prefix . '_openai_api_key_0']);
        $params = [
            "prompt" => $prompt_input,
            "n" => $num_images,
            "size" => $size_input,
        ];

        if (!is_null($model)) {
            $params['model'] = self::DALL_E_MODEL_3;
            $params['n'] = 1;
            $params['quality'] = $quality_input ?? 'standard';
            $params['style'] = $style_input ?? 'vivid';
        }

        $result = $open_ai->image($params);

        return json_decode($result, true);
    }

    /**
     * Utility function to communicate with OpenAI API when making a variation of an image
     *
     * @param array $image_file
     * @param integer $n_input
     * @param string $size_input
     * @return array
     */
    private function variate(array $image_file, int $n_input, string $size_input): array
    {
        $num_variations = max(1, min(10, (int) $n_input));
        $open_ai = new OpenAi($this->options[$this->prefix . '_openai_api_key_0']);
        $tmp_file = $image_file['tmp_name'];
        $file_name = basename($image_file['name']);
        $image = curl_file_create($tmp_file, $image_file['type'], $file_name);
        $result = $open_ai->createImageVariation([
            //"model" => self::DALL_E_MODEL_3,
            "image" => $image,
            "n" => $num_variations,
            "size" => $size_input,
        ]);
        return json_decode($result, true);
    }

    /**
     * Utility function to communicate with OpenAI API when making a variation of an image
     *
     * @param array $image_file
     * @param array $mask_file
     * @param string $prompt_input
     * @param integer $n_input
     * @param string $size_input
     * @return array
     */
    private function edit(array $image_file, array $mask_file, string $prompt_input, int $n_input, string $size_input): array
    {
        if (!$this->check_license_validity()) {
            return [];
        }

        $num_variations = max(1, min(10, (int) $n_input));
        $open_ai = new OpenAi($this->options[$this->prefix . '_openai_api_key_0']);
        $tmp_file = $image_file['tmp_name'];
        $file_name = basename($image_file['name']);
        $image = curl_file_create($tmp_file, $image_file['type'], $file_name);

        $tmp_file_mask = $mask_file['tmp_name'];
        $file_name_mask = basename($mask_file['name']);
        $mask = curl_file_create($tmp_file_mask, $mask_file['type'], $file_name_mask);

        $result = $open_ai->imageEdit([
            //"model" => self::DALL_E_MODEL_3,
            "image" => $image,
            "mask" => $mask,
            "prompt" => $prompt_input,
            "n" => $num_variations,
            "size" => $size_input,
        ]);

        return json_decode($result, true);
    }

    /**
     * The ajax part to save generated image into media library
     *
     * @return mixed
     */
    public function add_to_media(): mixed
    {
        require_once ABSPATH . "/wp-admin/includes/image.php";
        require_once ABSPATH . "/wp-admin/includes/file.php";
        require_once ABSPATH . "/wp-admin/includes/media.php";

        $url = sanitize_url($_POST['url']);
        $alt = sanitize_text_field($_POST['description']);

        // Download url to a temp file
        $tmp = download_url($url);
        if (is_wp_error($tmp)) {
            return false;
        }

        // Get the filename and extension ("photo.png" => "photo", "png")
        $filename = pathinfo($url, PATHINFO_FILENAME);
        $extension = pathinfo($url, PATHINFO_EXTENSION);

        // An extension is required or else WordPress will reject the upload
        if (!$extension) {
            // Look up mime type, example: "/photo.png" -> "image/png"
            $mime = mime_content_type($tmp);
            $mime = is_string($mime) ? sanitize_mime_type($mime) : false;

            // Only allow certain mime types because mime types do not always end in a valid extension (see the .doc example below)
            $mime_extensions = array(
                'image/jpg'  => 'jpg',
                'image/jpeg' => 'jpeg',
                'image/gif'  => 'gif',
                'image/png'  => 'png'
            );

            $extension = $mime_extensions[$mime] ?? false;

            if (!$extension) {
                // Could not identify extension
                @unlink($tmp);
                return false;
            }
        }

        // Upload by "sideloading": "the same way as an uploaded file is handled by media_handle_upload"
        $args = array(
            'name' => sanitize_title($alt, $filename) . ".$extension",
            'tmp_name' => $tmp,
        );

        // Do the upload
        $attachment_id = media_handle_sideload($args, 0, $alt);

        // Cleanup temp file
        @unlink($tmp);

        // Error uploading
        if (is_wp_error($attachment_id)) {
            return false;
        }

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);

        // Success, return attachment ID (int)
        wp_send_json_success(['attachment_id' => (int) $attachment_id]);

        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_die();
        }
    }

    /**
     * Utility function to define default image dimensions
     *
     * @return string
     */
    public function get_default_image_dimensions(): string
    {
        return "1024x1024";
    }

    /**
     * Utility function to generate the current admin view
     *
     * @param string $template
     * @param [type] ...$params
     * @return string
     */
    public function get_admin_template(string $template, ...$params): string
    {
        $pluginPath = plugin_dir_path(dirname(__FILE__));

        $valid_templates = $this->admin_display_templates;

        if (!in_array($template, $valid_templates)) {
            $template = self::ACTION_GENERATE;
        }

        $template_path = $this->admin_display_templates[$template];

        if ($template !== self::LAYOUT_MAIN) {
            $template_path .= '-template';
        }

        return $pluginPath . $this->admin_partials_path . $template_path . '.php';
    }

    /**
     * Utility function to get child templates based on
     *
     * @param string $template (error/error) to get templates/error/error-template.php
     * @return string
     */
    public function get_admin_child_template(string $template): string
    {
        $pluginPath = plugin_dir_path(dirname(__FILE__));
        $templatePath = $pluginPath . $this->admin_partials_path . $template . '-template.php';

        if (file_exists($templatePath)) {
            return $templatePath;
        }
    }

    /**
     * Utility function to generate the get the URL of an action 
     *
     * @param string $action
     * @return string
     */
    public function get_admin_tab_url(string $action): string
    {
        if (!in_array($action, $this->admin_actions)) {
            $action = self::ACTION_GENERATE;
        }

        return esc_url(
            add_query_arg(
                [
                    self::QUERY_FIELD_ACTION => $action
                ],
                admin_url('upload.php?page=' . $this->prefix)
            )
        );
    }

    /**
     * Utility function to check if the tab is active and show css classes
     *
     * @param string $needle
     * @param boolean $withCssClasses
     * @return boolean
     */
    public function is_tab_active(string $needle, bool $withCssClasses = false)
    {
        $classes = ' nav-tab-active';
        $action = $_GET[self::QUERY_FIELD_ACTION] ?? null;
        $cond1 = is_null($action) && $needle === self::ACTION_GENERATE;
        $action_sanitized = sanitize_text_field($action);
        $cond2 = ($action && $needle === $action_sanitized && in_array($action_sanitized, $this->admin_actions));
        $bool = $cond1 || $cond2;

        if ($withCssClasses) {
            return $bool ? $classes : '';
        }

        return $bool;
    }

    public function print_tabs_templates()
    {
?>
        <?php // Template for generate tab. 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-generate">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="notice-container"></div>
                <div class="notice notice-info aig-notice">
                    <p>
                        <img width="20px" src="<?php echo plugin_dir_url(__FILE__) . '/img/aig-icon.png'; ?>" alt="Artist Image Generator Icon" />
                        <strong><?php esc_attr_e('Generate', 'artist-image-generator'); ?>:</strong> 
                        <?php esc_attr_e('Create images from text-to-image.', 'artist-image-generator'); ?>
                        <a target="_blank" href="https://youtu.be/msd81YXw5J8" title="Video demo">
                            <?php esc_attr_e('Watch the demo', 'artist-image-generator'); ?>
                        </a>
                    </p>
                </div>
                <div class="aig-container aig-container-2">
                    <div class="aig-inner-left">
                        <table class="form-table" role="presentation">
                            <tbody class="tbody-container"></tbody>
                        </table>
                        <p class="submit">
                            <input type="hidden" name="generate" value="1" />
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Generate Image(s)', 'artist-image-generator'); ?>" />
                        </p>
                    </div>
                    <div class="aig-inner-right">
                        <div class="result-container"></div>
                    </div>
                </div>
            </form>
        </script>

        <?php // Template for variate tab. 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-variate">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="notice-container"></div>
                <div class="notice notice-info aig-notice">
                    <p>
                        <img width="20px" src="<?php echo plugin_dir_url(__FILE__) . '/img/aig-icon.png'; ?>" alt="Artist Image Generator Icon" />
                        <strong><?php esc_attr_e('Variate', 'artist-image-generator'); ?>:</strong> 
                        <?php esc_attr_e('Make image variations from an existing one.', 'artist-image-generator'); ?>
                        <a target="_blank" href="https://youtu.be/FtGFMsLTxYw" title="Video demo">
                            <?php esc_attr_e('Watch the demo', 'artist-image-generator'); ?>
                        </a>
                    </p>
                </div>
                <table class="form-table" role="presentation">
                    <tbody class="tbody-container"></tbody>
                </table>
                <p class="submit">
                    <input type="hidden" name="variate" value="1" />
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Generate Image(s)', 'artist-image-generator'); ?>" />
                </p>
                <hr />
                <div class="result-container"></div>
            </form>
        </script>

        <?php // Template for edit tab. 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-edit">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="notice-container"></div>
                <div class="notice notice-info aig-notice">
                    <p>
                        <img width="20px" src="<?php echo plugin_dir_url(__FILE__) . '/img/aig-icon.png'; ?>" alt="Artist Image Generator Icon" />
                        <strong><?php esc_attr_e('Edit', 'artist-image-generator'); ?>:</strong> 
                        <?php esc_attr_e('Customize existing images and generate a full new one.', 'artist-image-generator'); ?>
                        <a target="_blank" href="https://youtu.be/zfK1yJk9gRc" title="Video demo">
                            <?php esc_attr_e('Watch the demo', 'artist-image-generator'); ?>
                        </a>
                    </p>
                </div>
                <table class="form-table" role="presentation">
                    <tbody class="tbody-container"></tbody>
                </table>
                <p class="submit">
                    <input type="hidden" name="edit" value="1" />
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Generate Image(s)', 'artist-image-generator'); ?>" />
                </p>
                <hr />
                <div class="result-container"></div>
            </form>
        </script>

        <?php // Template for edit demo tab. 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-edit-demo">
            <div class="card">
                <h2 class="title">Provide full access to Artist Image Generator</h2>
                <p>With Artist Image Generator Edit Image feature, you can compose, edit and generate full new images from Wordpress.</p>
                <p>By purchasing a unique license, you unlock this powerful functionality along with new pro features, remove credits <strong>and help me to maintain this plugin</strong>.</p>
                <p style="margin: 10px 0;">
                    <a href="https://developpeur-web.site/produit/artist-image-generator-pro/" title="Purchase Artist Image Generator Pro Licence key" target="_blank" class="button button-primary" style="width :100%; text-align:center;">
                        Buy Artist Image Generator (Pro) - Licence Key
                    </a>
                </p>
                <p>Compatible with Block Builders like <strong>Elementor, Beaver Builder, WP Bakery.</strong></p>
                <p>
                    Official <a href="https://help.openai.com/en/articles/6516417-dall-e-editor-guide" target="_blank" title="OpenAI DALL·E Editor Guide">OpenAI DALL·E Editor Guide</a>
                    - <a href="https://labs.openai.com/editor" target="_blank" title="OpenAI DALL·E Editor">Try OpenAI DALL·E Editor</a>
                </p>
                <iframe width="100%" height="315" src="https://www.youtube.com/embed/zfK1yJk9gRc" title="Artist Image Generator - Image Edition feature" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </script>

        <?php // Template for public tab. 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-public">
            <div class="aig-container aig-container-3">
                <style>
                    /* Ajout de CSS pour améliorer l'apparence */
                    .aig-code {
                        background-color: #f0f0f0;
                        padding: 10px;
                        border: 1px solid #ccc;
                        border-radius: 5px;
                    }
                </style>
                <div class="card">
                    <h2 class="title"><?php esc_attr_e('Shortcode (Bêta)', 'artist-image-generator'); ?></h2>
                    <p><?php esc_attr_e('To create a public AI image generation form in WordPress, you can use the following shortcode:', 'artist-image-generator'); ?></p>
                    <div class="aig-code">
                        [aig prompt="Your custom description here with {topics} and {public_prompt}" topics="Comma-separated list of topics" n="3" size="1024x1024" model="dall-e-3" style="vivid" quality="hd" download="manual"]
                    </div>
                    <p><?php esc_attr_e('Replace "Your custom description here" with your own description, and specify the topics you want to offer as a comma-separated list. You can use the following placeholders in your description:', 'artist-image-generator'); ?></p>
                    <ul>
                        <li>- {topics} : <?php esc_attr_e('To include a list of topics that users can select.', 'artist-image-generator'); ?></li>
                        <li>- {public_prompt} : <?php esc_attr_e('To include a prompt for users.', 'artist-image-generator'); ?></li>
                    </ul>
                    <p><?php esc_attr_e('You can also use the following optional attributes in the shortcode:', 'artist-image-generator'); ?></p>
                    <ul>
                        <li>- n : <?php esc_attr_e('Number of images to generate (default is 3, maximum 10).', 'artist-image-generator'); ?></li>
                        <li>- size : <?php esc_attr_e('The size of the images to generate (e.g., "256x256", "512x512", "1024x1024" for dall-e-2, "1024x1024", "1024x1792", "1792x1024" for dall-e-3. Default is 1024x1024).', 'artist-image-generator'); ?></li>
                        <li>- model : <?php esc_attr_e('OpenAi model to use (e.g., "dall-e-2", "dall-e-3". Default is "dall-e-2").', 'artist-image-generator'); ?></li>
                        <li>- quality : <?php esc_attr_e('Quality of the image to generate (e.g., "standard", "hd". Default is "standard". Only with dall-e-3).', 'artist-image-generator'); ?></li>
                        <li>- style : <?php esc_attr_e('Style of the image to generate (e.g., "natural", "vivid". Default is "vivid". Only with dall-e-3).', 'artist-image-generator'); ?></li>
                        <li>- download : <?php esc_attr_e('Download an image or use it as WP profile picture (e.g., "manual", "wp_avatar". Default is "manual").', 'artist-image-generator'); ?></li>
                    </ul>
                    <p><?php esc_attr_e('Once you have the shortcode ready, you can add it to any page or post in WordPress to display the public AI image generation form.', 'artist-image-generator'); ?></p>
                    <p><a href="https://github.com/Immolare/artist-image-generator" target="_blank" title="Visit Github">Feedback and donation</a> are welcome !</p>
                </div>
                <div class="card">
                    <h2 class="title"><?php esc_attr_e('Exemple: Rendering the shortcode into a page', 'artist-image-generator'); ?></h2>
                    <p><?php esc_attr_e('The shortcode:', 'artist-image-generator'); ?></p>
                    <div class="aig-code">
                        [aig prompt="Painting of {public_prompt}, including following criterias: {topics}"
                        topics="Impressionism, Surrealism, Portraits, Landscape Painting, Watercolor Techniques, Oil Painting, Street Art, Hyperrealism, Cat, Dog, Bird, Person"
                        download="manual" model="dall-e-3"]
                    </div>
                    <p><?php esc_attr_e('The result:', 'artist-image-generator'); ?></p>
                    <img style="width:100%" src="<?php echo plugin_dir_url(__FILE__) . '/img/aig-public-form.jpg'; ?>" alt="Exemple of form render" />
                </div>

            </div>
        </script>

        <?php
        if ($this->is_artist_image_generator_page()) :
            // Template for settings tab. 
        ?>
            <script type="text/html" id="tmpl-artist-image-generator-settings">
                <div class="aig-container aig-container-3">
                    <div class="card">
                        <h2 class="title">
                            <?php esc_attr_e('How to get your OpenAI API key ?', 'artist-image-generator'); ?>
                        </h2>
                        <p>
                            1. <?php esc_attr_e('Log in into OpenAI developer portail', 'artist-image-generator'); ?> :
                            <a target="_blank" title="OpenAI Developer Portail" href="https://openai.com/api/">https://openai.com/api/</a>
                        </p>
                        <p>
                            2. <?php esc_attr_e('Create a new secret key', 'artist-image-generator'); ?> :
                            <a target="_blank" title="OpenAI - API keys" href="https://platform.openai.com/account/api-keys">https://platform.openai.com/account/api-keys</a>
                        </p>
                        <p>
                            3. <?php esc_attr_e('Copy/paste the secret key in the OPENAI_KEY field.', 'artist-image-generator'); ?>
                        </p>
                        <p>
                            4. <?php esc_attr_e('Press "Save changes" and you are done.', 'artist-image-generator'); ?>
                        </p>
                        <hr/>
                        <?php settings_errors(); ?>
                        <form method="post" action="options.php">
                            <?php
                            settings_fields($this->prefix . '_option_group');
                            do_settings_sections($this->prefix . '-admin');
                            submit_button();
                            ?>
                        </form>
                    </div>
                    <?php if (!$this->check_license_validity()) : ?>
                    <div class="card">
                        <h2 class="title">Provide full access to Artist Image Generator</h2>
                        <p>With Artist Image Generator Edit Image feature, you can compose, edit and generate full new images from Wordpress.</p>
                        <p>By purchasing a unique license, you unlock this powerful functionality along with new pro features, remove credits, <strong>and help me to maintain this plugin</strong>.</p>
                        <p style="margin: 10px 0;">
                            <a href="https://developpeur-web.site/produit/artist-image-generator-pro/" title="Purchase Artist Image Generator Pro Licence key" target="_blank" class="button button-primary" style="width :100%; text-align:center;">
                                Buy Artist Image Generator (Pro) - Licence Key
                            </a>
                        </p>
                        <p>Compatible width Block Builders like <strong>Elementor, Beaver Builder, WP Bakery.</strong></p>
                        <p>
                            Official <a href="https://help.openai.com/en/articles/6516417-dall-e-editor-guide" target="_blank" title="OpenAI DALL·E Editor Guide">OpenAI DALL·E Editor Guide</a>
                            - <a href="https://labs.openai.com/editor" target="_blank" title="OpenAI DALL·E Editor">Try OpenAI DALL·E Editor</a>
                        </p>
                        <iframe width="100%" height="315" src="https://www.youtube.com/embed/zfK1yJk9gRc" title="Artist Image Generator - Image Edition feature" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                    <?php endif; ?>
            </script>

            <?php // Template for about tab. 
            ?>
            <script type="text/html" id="tmpl-artist-image-generator-about">
                <div class="aig-container aig-container-3">
                    <div class="card">
                        <h2 class="title">
                            <?php echo esc_attr($this->plugin_full_name); ?>
                        </h2>
                        <p>
                            <strong>This plugin was created by me, <a href="https://www.pierrevieville.fr" title="Visit website" target="_blank">Pierre Viéville</a>.</strong>
                        </p>
                        <p>
                            I have been a freelance developer for 10 years.
                            <strong><?php echo esc_attr($this->plugin_full_name); ?></strong> is my first Wordpress plugin. I want to help the Wordpress community to improve the creativity of their content.
                        </p>
                        <p>
                            That's why I made a plugin allowing you to generate <u>royalty-free images</u> that you can use anywhere on your site: media library, blog posts, pages, etc.
                        </p>
                        <p>
                            I hope this plugin will be useful to you in the creation of your new content. If you have any question about this one, feel free to check out my web links.
                        </p>
                    </div>
                    <div class="card">
                        <h2 class="title">
                            How is it working ?
                        </h2>
                        <p>
                            <strong>This plugin is an integration of OpenAI API with the new AI system <a target="_blank" title="Visit DALL·E 2" href="https://openai.com/dall-e-2/">DALL·E 2</a></strong>
                        </p>
                        <p>
                            DALL·E 2 can create original, realistic images and art from a text description. It can combine concepts, attributes, and styles.
                            This AI has learned the relationship between images and the text used to describe them.
                        </p>
                        <p>
                            Basically the user input some text describing the images he wants. 1-10 images are generated.
                            Then the user can select some images and add them to the Wordpress medias library, ready to use
                            for a page or a post blog.
                        </p>
                        <p>
                            The images generated are licenced free for any kind of usage. That are YOUR creations.
                        </p>
                    </div>
                    <div class="card">
                        <h2 class="title">
                            Wanna help to improve this plugin ?
                        </h2>
                        <p>
                            <strong>This plugin is free-to-use and generate royalty-free images for you. If you want to support my work, feel free to :</strong>
                        </p>
                        <p>1. share your issues</p>
                        <p>2. submit your pull request (PR)</p>
                        <p>3. support the developer by a donation</p>
                        <p>
                            Theses things can be done on the
                            <a href="https://github.com/Immolare/<?php echo esc_attr($this->plugin_name); ?>" title="Visit Github" target="_blank">
                                <?php echo esc_attr($this->plugin_full_name); ?>'s Github page
                            </a>.
                        </p>
                        <p>
                            Thanks a lot for using my plugin !
                        </p>
                    </div>
                </div>
            </script>

        <?php
        endif;
        // Child template for notice block (notice-container). 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-notice">
            <# if ( data.error && data.error.msg ) { #>
                <div class="notice notice-error inline" style="margin-top:15px;">
                    <p><?php echo esc_html('{{ data.error.msg }}'); ?></p>
                </div>
                <# } #>
        </script>

        <?php // Child template for result block (result-container). 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-result">
            <div class="aig-container">
                <# if ( data.images ) { #>
                    <# _.each(data.images, function(image, k) { #>
                        <div class="card">
                            <h2 class="title">
                                <?php esc_attr_e('Image N°', 'artist-image-generator'); ?><?php echo esc_attr('{{ k + 1 }}'); ?>
                                <div class="spinner" style="margin-top: 0;"></div>
                                <span class="dashicons dashicons-yes alignright" style="color:#46B450"></span>
                            </h2>
                            <img src="{{ image.url }}" width="100%" height="auto">
                            <a class="button add_as_media" href="javascript:void(0);">
                                <?php esc_attr_e('Add to media library', 'artist-image-generator'); ?>
                            </a>
                        </div>
                        <# }) #>
                            <# } #>
            </div>
        </script>

        <?php // Child template for form/image block (tbody-container). 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-form-image">
            <tr>
                <th scope="row">
                    <label for="image"><?php esc_attr_e('File (.png, .jpg)', 'artist-image-generator'); ?></label>
                    <p class="description"><?php esc_attr_e('Upload a file and crop it.', 'artist-image-generator'); ?></p>
                </th>
                <td>
                    <input type="file" name="image" id="image" class="regular-text aig_handle_cropper" accept=".png,.jpg" />
                    <input type="file" name="mask" id="mask" class="regular-text" accept=".png,.jpg" hidden readonly />
                    <input type="file" name="original" id="original" class="regular-text" accept=".png,.jpg" hidden readonly />
                </td>
            </tr>
            <tr>
                <th id="aig_cropper_preview" scope="row"></th>
                <td id="aig_cropper_canvas_area"></td>
            </tr>
        </script>

        <?php // Child template for form/n block (tbody-container). 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-form-n">
            <tr>
                <th scope="row">
                    <label for="n"><?php esc_attr_e('Number of images', 'artist-image-generator'); ?></label>
                    <p class="description"><?php esc_attr_e('Depends of the model used.', 'artist-image-generator'); ?></p>
                </th>
                <td>
                    <select name="n" id="n">
                        <# _.each(data.n, function(n) { #>
                            <# var is_selected=(data.n_input && data.n_input==n) ? 'selected' : '' ; #>
                                <option value="{{ n }}" {{ is_selected }}>
                                    {{ n }}
                                </option>
                                <# }); #>
                    </select>
                </td>
            </tr>
        </script>

        <?php // Child template for form/prompt block (tbody-container). 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-form-prompt">
            <tr>
                <th scope="row">
                    <label for="prompt">
                        <?php esc_attr_e('Prompt', 'artist-image-generator'); ?>
                        <?php esc_attr_e(' / Alt text', 'artist-image-generator'); ?>
                    </label>
                    <p class="description"><?php esc_attr_e('Describe the full new image you want to generate with DALL·E.', 'artist-image-generator'); ?></p>
                </th>
                <td>
                    <textarea id="prompt" name="prompt" class="regular-text" placeholder="<?php esc_attr_e('Ex: a sunlit indoor lounge area with a pool containing a flamingo', 'artist-image-generator'); ?>"><?php echo esc_textarea('{{ data.prompt_input }}'); ?></textarea>
                </td>
            </tr>
        </script>

        <?php // Child template for form/model block (tbody-container). 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-form-model">
            <# 
            // Définir la valeur par défaut de data.model si elle n'est pas définie
            if (typeof data.model === 'undefined' || data.model === '') {
                data.model = '';
            }

            var is_selected_dalle2 = (data.model === '') ? 'selected' : '';
            var is_selected_dalle3 = (data.model === 'dall-e-3') ? 'selected' : '';
            #>
            <tr>
                <th scope="row">
                    <label for="model"><?php esc_attr_e('Model to use', 'artist-image-generator'); ?></label>
                    <p class="description">
                        <a href="https://openai.com/dall-e-3" target="_blank" rel="noopener noreferrer" title="More about DALL·E 3"><?php esc_attr_e('More about DALL·E 3 model.', 'artist-image-generator'); ?></a>
                    </p>
                </th>
                <td>
                    <select name="model" id="model">
                        <option value="" {{ is_selected_dalle2 }}><?php echo self::DALL_E_MODEL_2; ?></option>
                        <option value="dall-e-3" {{ is_selected_dalle3 }}><?php echo self::DALL_E_MODEL_3; ?></option>
                    </select>
                </td>
            </tr>
        </script>

        <?php // Child template for form/size block (tbody-container). 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-form-size">
            <tr>
                <th scope="row">
                    <label for="size"><?php esc_attr_e('Size in pixels', 'artist-image-generator'); ?></label>
                    <p class="description"><?php esc_attr_e('Depends of the model used.', 'artist-image-generator'); ?></p>
                </th>
                <td>
                    <select name="size" id="size">
                        <# _.each(data.sizes, function(size) { #>
                            <# var is_selected=(data.size_input && data.size_input==size) ? 'selected' : '' ; #>
                                <option value="{{ size }}" {{ is_selected }}>{{ size }}</option>
                                <# }); #>
                    </select>
                </td>
            </tr>
        </script>

        <?php // Child template for form/quality block (tbody-container). 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-form-quality">
            <tr>
                <th scope="row">
                    <label for="quality"><?php esc_attr_e('Quality', 'artist-image-generator'); ?></label>
                    <p class="description">
                        <?php esc_attr_e('HD creates images with finer details.', 'artist-image-generator'); ?>
                        <a href="https://platform.openai.com/docs/api-reference/images/create" target="_blank" rel="noopener noreferrer" title="Specs DALL·E 3"><?php esc_attr_e('Specs DALL·E 3 model.', 'artist-image-generator'); ?></a>
                    </p>
                </th>
                <td>
                    <select name="quality" id="quality">
                        <# _.each(data.qualities, function(quality) { #>
                            <# var is_selected=(data.quality_input && data.quality_input==quality) ? 'selected' : '' ; #>
                                <option value="{{ quality }}" {{ is_selected }}>{{ quality }}</option>
                                <# }); #>
                    </select>
                </td>
            </tr>
        </script>

        <?php // Child template for form/style block (tbody-container). 
        ?>
        <script type="text/html" id="tmpl-artist-image-generator-form-style">
            <tr>
                <th scope="row">
                    <label for="style"><?php esc_attr_e('Style', 'artist-image-generator'); ?></label>
                    <p class="description">
                        <?php esc_attr_e('Vivid for hyper-real and dramatic images.', 'artist-image-generator'); ?>
                        <a href="https://platform.openai.com/docs/api-reference/images/create" target="_blank" rel="noopener noreferrer" title="Specs DALL·E 3"><?php esc_attr_e('Specs DALL·E 3 model.', 'artist-image-generator'); ?></a>
                    </p>
                </th>
                <td>
                    <select name="style" id="style">
                        <# _.each(data.styles, function(style) { #>
                            <# var is_selected=(data.style_input && data.style_input==style) ? 'selected' : '' ; #>
                                <option value="{{ style }}" {{ is_selected }}>{{ style }}</option>
                                <# }); #>
                    </select>
                </td>
            </tr>
        </script>

<?php
    }
}
