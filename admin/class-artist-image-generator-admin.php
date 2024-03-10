<?php

use Artist_Image_Generator_Constant as Constants;
use Artist_Image_Generator_Tab as Tabs;
use Artist_Image_Generator_Dalle as Dalle;
use Artist_Image_Generator_Setter as Setter;
use Artist_Image_Generator_License as License;

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
    private string $plugin_name;
    private string $version;

    /**
     * Constructor for the Artist_Image_Generator_Admin class.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of the plugin.
     */
    public function __construct(string $plugin_name, string $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        License::license_set_hooks();
    }

    /**
     * Enqueues the plugin's styles.
     */
    public function enqueue_styles(): void {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/artist-image-generator-admin.css', array(), $this->version, 'all');
    }

    /**
     * Enqueues the plugin's scripts.
     */
    public function enqueue_scripts(): void {
        $is_media_editor_page = Setter::is_media_editor_page();
        $is_plugin_page = Setter::is_artist_image_generator_page();
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
     * Returns an array of data for the AJAX object.
     *
     * @return array The data for the AJAX object.
     */
    private function aig_ajax_object(): array {
        return array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'cropper_script_path' => plugin_dir_url(__FILE__) . 'js/artist-image-generator-admin-cropper.js',
            'drawing_tool_script_path' => plugin_dir_url(__FILE__) . 'js/artist-image-generator-admin-drawing.js',
            'is_media_editor' => Setter::is_media_editor_page(),
            'variateLabel' => esc_attr__('Variate', 'artist-image-generator'),
            'editLabel' => esc_attr__('Edit (Pro)', 'artist-image-generator'),
            'publicLabel' => esc_attr__('Shortcodes', 'artist-image-generator'),
            'generateLabel' => esc_attr__('Generate', 'artist-image-generator'),
            'cropperCropLabel' => esc_attr__('Crop this zone', 'artist-image-generator'),
            'cropperCancelLabel' => esc_attr__('Cancel the zoom', 'artist-image-generator'),
            'cancelLabel' => esc_attr__('Cancel', 'artist-image-generator'),
            'maskLabel' => esc_attr__('Create mask', 'artist-image-generator'),
            'editLabel' => esc_attr__('Edit image', 'artist-image-generator'),
            'valid_license' => License::license_check_validity(),
        );
    }

    /**
     * Returns an array of data for the AIG object.
     *
     * @return array The data for the AIG object.
     */
    private function aig_data(): array {
        return array(
            'error' => array(),
            'images' => array(),
            'images_history' => array(),
            'model_input' => "", // dall-e-2
            'prompt_input' => "",
            'size_input' => Constants::DEFAULT_SIZE,
            'n_input' => 1,
            'quality_input' => "",
            'style_input' => ""
        );
    }

    /**
     * Adds metadata to the plugin row.
     *
     * @param array $links The existing links.
     * @param string $file The plugin file.
     * @return array The links with the added metadata.
     */
    public function plugin_row_meta(array $links, string $file): array {
        if (strpos($file, "{$this->plugin_name}/{$this->plugin_name}.php") === false) {
            return $links;
        }

        return Artist_Image_Generator_Setter::add_meta($links);
    }

    /**
     * Adds action links to the plugin.
     *
     * @param array $links The existing links.
     * @param string $file The plugin file.
     * @return array The links with the added actions.
     */
    public function plugin_action_links(array $links, string $file): array {
        if ($file !== "{$this->plugin_name}/{$this->plugin_name}.php" || !current_user_can('manage_options')) {
            return $links;
        }

        return Artist_Image_Generator_Setter::add_action($links, new Tabs());
    }

    /**
     * Adds the plugin's menu to the admin.
     */
    public function admin_menu(): void {
        Setter::set_menu(array($this, 'admin_page'));
    }

    /**
     * Initializes the plugin's settings.
     */
    public function admin_init(): void {
        Setter::set_settings();
    }

    /**
     * Displays the plugin's admin page.
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

        $tab = new Tabs();
        $tab->get_admin_template(Constants::LAYOUT_MAIN);
    }

   /**
     * Handles POST requests.
     *
     * @return array The prepared data.
     */
    public function do_post_request(): array
    {
        $post_data = [];
        $dalle = new Dalle();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Setter::is_setting_up()) {
            
            $post_data = $dalle->sanitize_post_data();

            if (isset($post_data['generate'])) {
                $response = $dalle->handle_generation($post_data);
            } elseif (isset($post_data['variate'])) {
                $response = $dalle->handle_variation($post_data);
            } elseif (isset($post_data['edit']) && License::license_check_validity()) {
                $response = $dalle->handle_edit($post_data);
            }

            if (isset($response)) {
                list($images, $error) = $dalle->handle_response($response);
            }
        }

        return $dalle->prepare_data($images ?? [], $error ?? [], $post_data);
    }

    /**
     * Adds an image to the WordPress media library.
     *
     * This function handles a POST request containing an image URL and description,
     * downloads the image, and adds it to the WordPress media library.
     *
     * If the request is an AJAX request, the function sends a JSON response containing
     * the ID of the new attachment and then terminates execution.
     */
    public function add_to_media(): void
    {
        $dalle = new Dalle();
        $dalle->include_wordpress_files();

        $url = sanitize_url($_POST['url']);
        $alt = sanitize_text_field($_POST['description']);

        [$tmp, $extension, $filename] = $dalle->download_image_and_get_extension($url);

        if (!$tmp) {
            return;
        }

        $args = array(
            'name' => sanitize_title($alt, $filename) . ".$extension",
            'tmp_name' => $tmp,
        );

        $attachment_id = media_handle_sideload($args, 0, $alt);

        wp_delete_file($tmp);

        if (is_wp_error($attachment_id)) {
            return;
        }

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);

        wp_send_json_success(['attachment_id' => (int) $attachment_id]);

        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_die();
        }
    }

    /**
     * Gets an image from a URL.
     *
     * This function handles a POST request containing an image URL,
     * downloads the image, and sends a JSON response containing the image data
     * encoded in base64.
     *
     * If the request is an AJAX request, the function terminates execution after
     * sending the response.
     */
    public function get_from_url(): void
    {
        $dalle = new Dalle();
        $dalle->include_wordpress_files();
        require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
        require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');

        $url = sanitize_url($_POST['url']);

        [$tmp, $extension] = $dalle->download_image_and_get_extension($url);

        if (!$tmp) {
            return;
        }

        $filesystem = new WP_Filesystem_Direct(true);
        $imageData = $filesystem->get_contents($tmp);
                    
        wp_delete_file($tmp);
        wp_send_json_success(['base_64' => base64_encode($imageData)]);

        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_die();
        }
    }

    /**
     * Prints the templates for the tabs.
     *
     * This function includes the PHP files for the 'content' and 'form' templates.
     */
    public function print_tabs_templates(): void
    {
        include_once plugin_dir_path(dirname(__FILE__)) . Constants::ADMIN_PARTIALS_PATH . 'content.php';
        include_once plugin_dir_path(dirname(__FILE__)) . Constants::ADMIN_PARTIALS_PATH . 'form.php';
    }
}
