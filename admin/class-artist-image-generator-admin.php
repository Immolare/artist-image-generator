<?php

use Orhanerday\OpenAi\OpenAi;

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
    const ACTION_SETTINGS = 'settings';
    const ACTION_ABOUT = 'about';
    const LAYOUT_MAIN = 'main';

    private string $plugin_name;
    private string $plugin_full_name = "Artist Image Generator";
    private string $version;

    private string $prefix;
    private string $admin_partials_path;
    private array $admin_display_templates;
    private array $admin_actions;

    private $options;

    public function __construct(string $plugin_name, string $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->prefix = "artist_image_generator";
        $this->admin_partials_path = "admin/partials/";
        $this->admin_display_templates = [
            'generate' => 'generate',
            'variate' => 'variate',
            'settings' => 'settings',
            'about' => 'about',
            'main' => 'main'
        ];
        $this->admin_actions = [
            self::ACTION_GENERATE,
            self::ACTION_VARIATE,
            self::ACTION_SETTINGS,
            self::ACTION_ABOUT
        ];
    }

    /**
     * Hook : Enqueue CSS scripts
     *
     * @return void
     */
    public function enqueue_styles(): void
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/artist-image-generator-admin.css', array(), $this->version, 'all');
    }

    /**
     * Hook : Enqueue JS scripts
     *
     * @return void
     */
    public function enqueue_scripts(): void
    {
        wp_enqueue_script('wp-util');
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/artist-image-generator-admin.js', array(
            'wp-util',  'jquery',
            'underscore'
        ), $this->version, true);
        wp_localize_script($this->plugin_name, 'aig_ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
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
            'OPENAI_API_KEY', // title
            array($this, 'openai_api_key_0_callback'), // callback
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
     * Hook : The plugin's administration page
     *
     * @return void
     */
    public function admin_page()
    {
        $images = $error = [];

        $this->options = get_option($this->prefix . '_option_name');

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $this->is_setting_up()) {
            $is_generation = array_key_exists('generate', $_POST) && sanitize_text_field($_POST['generate']);
            $is_variation = array_key_exists('variate', $_POST) && sanitize_text_field($_POST['variate']);
            $prompt_input = array_key_exists('prompt', $_POST) ? sanitize_text_field($_POST['prompt']) : null;
            $size_input = array_key_exists('size', $_POST) ? sanitize_text_field($_POST['size']) : $this->get_default_image_dimensions();
            $n_input = array_key_exists('n', $_POST) ? sanitize_text_field($_POST['n']) : 1;

            if ($is_generation) {
                if (empty($prompt_input)) {
                    $error = [
                        'msg' => __('The Prompt input must be filled in order to generate an image.', 'artist-image-generator')
                    ];
                } else {
                    $response = $this->generate($prompt_input, $n_input, $size_input);
                }
            } elseif ($is_variation) {
                $errorMsg = __('A .png square (1:1) image of maximum 4MB needs to be uploaded in order to generate a variation of this image.', 'artist-image-generator');
                $image_file = $_FILES['image']['size'] > 0 ? $_FILES['image'] : null;

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
            }

            if (isset($response)) {
                if (array_key_exists('error', $response)) {
                    $error = ['msg' => $response['message']];
                } else {
                    $images = $response;
                }
            }
        }

        $data = [
            'error' => $error,
            'images' => $images && count($images) ? $images['data'] : [],
            'prompt_input' => $prompt_input,
            'size_input' => $size_input,
            'n_input' => $n_input
        ];

        // Pass the variable to the template
        wp_localize_script($this->plugin_name, 'aig_data', $data);

        require_once $this->get_admin_template(self::LAYOUT_MAIN);
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
    private function generate(string $prompt_input, int $n_input, string $size_input): array
    {
        $num_images = max(1, min(10, (int) $n_input));
        $open_ai = new OpenAi($this->options[$this->prefix . '_openai_api_key_0']);
        $result = $open_ai->image([
            "prompt" => $prompt_input,
            "n" => $num_images,
            "size" => $size_input,
        ]);
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
            "image" => $image,
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
                admin_url('admin.php?page=' . $this->prefix)
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

        <?php // Template for generate tab. ?>
        <script type="text/html" id="tmpl-artist-image-generator-generate">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="notice-container"></div>
                <table class="form-table" role="presentation">
                    <tbody class="tbody-container"></tbody>
                </table>
                <p class="submit">
                    <input type="hidden" name="generate" value="1" />
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Generate Image(s)', 'artist-image-generator'); ?>" />
                </p>
                <hr />
                <div class="result-container"></div>
            </form>
        </script>

        <?php // Template for variate tab. ?>
        <script type="text/html" id="tmpl-artist-image-generator-variate">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="notice-container"></div>
                <div class="notice notice-info inline" style="margin-top:15px;">
                    <p><?php esc_attr_e('Heads up ! To make an image variation you need to provide a .png file less than 4MB in a 1:1 format (square). You can add a prompt input to describe the image. This value will be used to fill the image name and alternative text.', 'artist-image-generator'); ?></p>
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

        <?php // Template for settings tab. ?>
        <script type="text/html" id="tmpl-artist-image-generator-settings">
            <h2><?php esc_attr_e('How to get your OpenAI API key ?', 'artist-image-generator'); ?></h2>
            <ol>
                <li>
                    <?php esc_attr_e('Sign up / Log in into OpenAI developer portail', 'artist-image-generator'); ?> :
                    <a target="_blank" title="OpenAI Developer Portail" href="https://openai.com/api/">https://openai.com/api/</a>
                </li>
                <li>
                    <?php esc_attr_e('In User > View API keys, create a new secret key', 'artist-image-generator'); ?> :
                    <a target="_blank" title="OpenAI - API keys" href="https://platform.openai.com/account/api-keys">https://platform.openai.com/account/api-keys</a>
                </li>
                <li>
                    <?php esc_attr_e('Copy and paste the new secret key in the OPENAI_API_KEY field right here.', 'artist-image-generator'); ?>
                </li>
                <li>
                    <?php esc_attr_e('Press "Save changes" and you are done.', 'artist-image-generator'); ?>
                </li>
            </ol>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->prefix . '_option_group');
                do_settings_sections($this->prefix . '-admin');
                submit_button();
                ?>
            </form>
        </script>

        <?php // Template for about tab. ?>
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

        <?php // Child template for notice block (notice-container). ?>
        <script type="text/html" id="tmpl-artist-image-generator-notice">
            <# if ( data.error && data.error.msg ) { #>
                <div class="notice notice-error inline" style="margin-top:15px;">
                    <p><?php echo esc_html('{{ data.error.msg }}'); ?></p>
                </div>
                <# } #>
        </script>

        <?php // Child template for result block (result-container). ?>
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

        <?php // Child template for form/image block (tbody-container). ?>
        <script type="text/html" id="tmpl-artist-image-generator-form-image">
            <tr>
                <th scope="row">
                    <label for="image"><?php esc_attr_e('PNG square file (< 4MB)', 'artist-image-generator'); ?></label>
                </th>
                <td>
                    <input type="file" name="image" id="image" class="regular-text" />
                </td>
            </tr>
        </script>

        <?php // Child template for form/n block (tbody-container). ?>
        <script type="text/html" id="tmpl-artist-image-generator-form-n">
            <tr>
                <th scope="row">
                    <label for="n"><?php esc_attr_e('Number of images', 'artist-image-generator'); ?></label>
                </th>
                <td>
                    <select name="n" id="n">
                        <# for (var i=1; i <=10; i++) { #>
                            <# var is_selected=(data.n_input && data.n_input==i) ? 'selected' : '' ; #>
                                <option value="<?php echo esc_attr('{{ i }}'); ?>" <?php echo esc_attr('{{ is_selected }}'); ?>>
                                    <?php echo esc_attr('{{ i }}'); ?>
                                </option>
                                <# } #>
                    </select>
                </td>
            </tr>
        </script>

        <?php // Child template for form/prompt block (tbody-container). ?>
        <script type="text/html" id="tmpl-artist-image-generator-form-prompt">
            <tr>
                <th scope="row">
                    <label for="prompt"><?php esc_attr_e('Prompt', 'artist-image-generator'); ?></label>
                </th>
                <td>
                    <input type="text" id="prompt" name="prompt" class="regular-text" placeholder="<?php esc_attr_e('Ex: A bowl of soup as a planet in the universe as digital art', 'artist-image-generator'); ?>" value="<?php echo esc_attr('{{ data.prompt_input }}'); ?>" />
                </td>
            </tr>
        </script>

        <?php // Child template for form/size block (tbody-container). ?>
        <script type="text/html" id="tmpl-artist-image-generator-form-size">
            <# var is_selected_256=(data.size_input && data.size_input=='256x256' ) ? 'selected' : '' ; #>
                <# var is_selected_512=(data.size_input && data.size_input=='512x512' ) ? 'selected' : '' ; #>
                    <# var is_selected_1024=(!data.size_input || (data.size_input && data.size_input=='1024x1024' )) ? 'selected' : '' ; #>
                        <tr>
                            <th scope="row">
                                <label for="size"><?php esc_attr_e('Size in pixels', 'artist-image-generator'); ?></label>
                            </th>
                            <td>
                                <select name="size" id="size">
                                    <option value="256x256" <?php echo esc_attr('{{ is_selected_256 }}'); ?>>256x256</option>
                                    <option value="512x512" <?php echo esc_attr('{{ is_selected_512 }}'); ?>>512x512</option>
                                    <option value="1024x1024" <?php echo esc_attr('{{ is_selected_1024 }}'); ?>>1024x1024</option>
                                </select>
                            </td>
                        </tr>
        </script>

<?php
    }
}
