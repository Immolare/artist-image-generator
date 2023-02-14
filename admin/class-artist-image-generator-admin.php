<?php

use Orhanerday\OpenAi\OpenAi;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://pierrevieville.fr
 * @since      1.0.0
 *
 * @package    Artist_Image_Generator
 * @subpackage Artist_Image_Generator/admin
 */

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
class Artist_Image_Generator_Admin {
    const QUERY_SETUP = 'setup';
    const QUERY_FIELD_ACTION = 'action';
    const ACTION_GENERATE = 'generate';
    const ACTION_VARIATE = 'variate';
    const ACTION_SETTINGS = 'settings';
    const ACTION_ABOUT = 'about';
    
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

    private $plugin_full_name = "Artist Image Generator";

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    private $prefix = "artist_image_generator";
    private $admin_display_main_template = "admin/partials/main.php";
    private $admin_display_about_template = "admin/partials/_about.php";
    private $admin_display_generate_template = "admin/partials/_generate.php";
    private $admin_display_variate_template = "admin/partials/_variate.php";
    private $admin_display_settings_template = "admin/partials/_settings.php";
    
    private $admin_actions_array = [
        self::ACTION_GENERATE,
        self::ACTION_VARIATE,
        self::ACTION_SETTINGS, 
        self::ACTION_ABOUT
    ];
    /* 
    * Retrieve this value with:
    * $options = get_option( $this->prefix.'_option_name' ); // Array of All Options
    * $openai_api_key_0 = $options[$this->prefix.'_openai_api_key_0']; // OPENAI_API_KEY
    */
    private $options;

    private function generate($prompt_input, $n_input, $size_input) {
        $n = $n_input > 0 && $n_input <= 10 ? (int)$n_input : 1;
        $open_ai = new OpenAi($this->options[$this->prefix.'_openai_api_key_0']);
        $result = $open_ai->image([
            "prompt" => $prompt_input,
            "n" => $n,
            "size" => $size_input,
            //"response_format" => "b64_json"
        ]);
        
        return json_decode($result, true);
    }

    private function variate($image_file, $n_input, $size_input) {
        $n = $n_input > 0 && $n_input <= 10 ? (int)$n_input : 1;
        $open_ai = new OpenAi($this->options[$this->prefix.'_openai_api_key_0']);
        $tmp_file = $image_file['tmp_name'];
        $file_name = basename($image_file['name']);
        $image = curl_file_create($tmp_file, $image_file['type'], $file_name);

        $result = $open_ai->createImageVariation([
            "image" => $image,
            "n" => $n,
            "size" => $size_input,
        ]);

        return json_decode($result, true);
    }

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Artist_Image_Generator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Artist_Image_Generator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/artist-image-generator-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/artist-image-generator-admin.js', array( 'jquery' ), $this->version, true );
        wp_localize_script( $this->plugin_name, 'aig_ajax_object', array( 
            'ajax_url' => admin_url( 'admin-ajax.php' ) 
        ) );
    }

    public function plugin_row_meta( $links, $file ) {
        # Display meta links
        if ( strpos( $file, $this->plugin_name.'/'.$this->plugin_name.'.php' ) !== FALSE ) {
            $meta = array(
                'support' => '<a href="https://wordpress.org/support/plugin/'.$this->plugin_name.'" target="_blank"><span class="dashicons dashicons-sos"></span> ' . __( 'Support', 'artist-image-generator' ) . '</a>',
                'review' => '<a href="https://wordpress.org/support/plugin/'.$this->plugin_name.'/reviews/#new-post" target="_blank"><span class="dashicons dashicons-thumbs-up"></span> ' . __( 'Review', 'artist-image-generator' ) . '</a>',
                'github' => '<a href="https://github.com/Immolare/'.$this->plugin_name.'" target="_blank"><span class="dashicons dashicons-randomize"></span> ' . __( 'GitHub', 'artist-image-generator' ) . '</a>',
            );
            $links = array_merge( $links, $meta );
        }
        # Return plugin meta links
        return $links;
    }

    public function plugin_action_links( $links, $file ) {
        # Display settings link
        if ( $file == $this->plugin_name.'/'.$this->plugin_name.'.php' && current_user_can( 'manage_options' ) ) {
            array_unshift(
                $links,
                '<a href="'.$this->get_admin_tab_url(self::ACTION_SETTINGS).'">' . __( ucfirst(self::ACTION_SETTINGS), 'artist-image-generator' ) . '</a>'
            );
        }
        # Return the settings link
        return $links;
    }

    /**
	 * Register new media page for the media menu area.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu() {
		add_media_page(
			$this->plugin_full_name, // page_title
			__( 'Image Generator', 'artist-image-generator' ), // menu_title
			'manage_options', // capability
			$this->prefix, // menu_slug
			array( $this, 'admin_page' ) // function
		);
	}

    public function admin_page() {
		$this->options = get_option( $this->prefix.'_option_name' );
        $is_post_request = $_SERVER['REQUEST_METHOD'] == 'POST';
        $prompt_input = array_key_exists('prompt', $_POST) ? sanitize_text_field($_POST['prompt']) : null;
        $size_input = array_key_exists('size', $_POST) ? sanitize_text_field($_POST['size']) : $this->get_default_image_dimensions();
        $n_input = array_key_exists('n', $_POST) ? (int)sanitize_text_field($_POST['n']) : 1;
        $is_generation = array_key_exists('generate', $_POST) && sanitize_text_field($_POST['generate']);
        $is_variation = array_key_exists('variate', $_POST) && sanitize_text_field($_POST['variate']);
        $is_setting_up = is_array($this->options) && array_key_exists($this->prefix.'_openai_api_key_0', $this->options);
        $error = [];

        if ( $is_post_request && $is_setting_up) {
            if ($is_generation) {
                if (empty($prompt_input)) {
                    $error = [
                        'msg' => __( 'The Prompt input must be filled in order to generate an image.', 'artist-image-generator' )
                    ];
                }
                else {
                    $response = $this->generate($prompt_input, $n_input, $size_input);

                    if (array_key_exists('error', $response)) {
                        $error =  [ 'msg' => $response['message'] ];
                    }
                    else {
                        $images = $response;
                    }
                }
            }
            elseif ($is_variation) {
                $errorMsg = __( 'A .png square (1:1) image of maximum 4MB needs to be uploaded in order to generate a variation of this image.', 'artist-image-generator' );
                $image_file = $_FILES['image']['size'] > 0 ? $_FILES['image'] : null;

                if (empty($image_file)) {
                    $error = [ 'msg' => $errorMsg ];
                }
                else {
                    $image_mime_type = mime_content_type($image_file['tmp_name']);
                    list($image_width, $image_height) = getimagesize($image_file['tmp_name']);
                    $image_wrong_size = $image_file['size'] >= ((1024 * 1024) * 4) || $image_file['size'] == 0;
                    // If you want to allow certain files
                    $allowed_file_types = ['image/png'];
                    if (!in_array($image_mime_type, $allowed_file_types) || $image_wrong_size || $image_height !== $image_width) {
                        $error = [ 'msg' => $errorMsg ];
                    }
                    else {
                        $response = $this->variate($image_file, $n_input, $size_input);

                        if (array_key_exists('error', $response)) {
                            $error =  [ 'msg' => $response['message'] ];
                        }
                        else {
                            $images = $response;
                        }
                    }
                }
            }
        }

        require_once $this->get_admin_template('main');
	}

    public function admin_init() {
		register_setting(
			$this->prefix.'_option_group', // option_group
			$this->prefix.'_option_name', // option_name
			array( $this, 'sanitize' ) // sanitize_callback
		);

		add_settings_section(
			$this->prefix.'_setting_section', // id
			__( 'Settings', 'artist-image-generator' ), // title
			array( $this, 'section_info' ), // callback
			$this->prefix.'-admin' // page
		);

		add_settings_field(
			$this->prefix.'_openai_api_key_0', // id
			'OPENAI_API_KEY', // title
			array( $this, 'openai_api_key_0_callback' ), // callback
			$this->prefix.'-admin', // page
			$this->prefix.'_setting_section' // section
		);
	}

    public function add_to_media() {
        require_once ABSPATH . "/wp-load.php";
        require_once ABSPATH . "/wp-admin/includes/image.php";
        require_once ABSPATH . "/wp-admin/includes/file.php";
        require_once ABSPATH . "/wp-admin/includes/media.php";

        $url = sanitize_text_field($_POST['url']);
        $alt = sanitize_text_field($_POST['description']);

        // Download url to a temp file
        $tmp = download_url( $url );
        if ( is_wp_error( $tmp ) ) {
            return false;
        }

        // Get the filename and extension ("photo.png" => "photo", "png")
        $filename = pathinfo($url, PATHINFO_FILENAME);
        $extension = pathinfo($url, PATHINFO_EXTENSION);

        // An extension is required or else WordPress will reject the upload
        if ( ! $extension ) {
            // Look up mime type, example: "/photo.png" -> "image/png"
            $mime = mime_content_type( $tmp );
            $mime = is_string($mime) ? sanitize_mime_type( $mime ) : false;
            
            // Only allow certain mime types because mime types do not always end in a valid extension (see the .doc example below)
            $mime_extensions = array(
                // mime_type         => extension (no period)
                //'text/plain'         => 'txt',
                //'text/csv'           => 'csv',
                //'application/msword' => 'doc',
                'image/jpg'          => 'jpg',
                'image/jpeg'         => 'jpeg',
                'image/gif'          => 'gif',
                'image/png'          => 'png',
                //'video/mp4'          => 'mp4',
            );
            
            if ( isset( $mime_extensions[$mime] ) ) {
                // Use the mapped extension
                $extension = $mime_extensions[$mime];
            }else{
                // Could not identify extension
                @unlink($tmp);
                return false;
            }
        }
	
        // Upload by "sideloading": "the same way as an uploaded file is handled by media_handle_upload"
        $args = array(
            'name' => sanitize_title($alt, $filename).".$extension",
            'tmp_name' => $tmp,
        );
        
        // Do the upload
        $attachment_id = media_handle_sideload( $args, 0, $title);
        
        // Cleanup temp file
        @unlink($tmp);
        
        // Error uploading
        if ( is_wp_error($attachment_id) ) {
            return false;
        }

        update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
        
        // Success, return attachment ID (int)
        wp_send_json_success(['attachment_id' => (int) $attachment_id]);

        if(defined('DOING_AJAX') && DOING_AJAX) {
            wp_die();
        }
    }

    public function get_default_image_dimensions() {
        return "1024x1024"; //return get_option("large_size_w") . 'x' . get_option("large_size_h");
    }

    public function get_admin_template( $template = self::ACTION_GENERATE , ...$params) {
        $pluginPath = plugin_dir_path( dirname( __FILE__ ) );

        if (!in_array($template, array_merge(['main'], $this->admin_actions_array))) {
            $template = self::ACTION_GENERATE;
        }

        $func = "admin_display_" . $template . "_template";
                
        return $pluginPath . $this->$func;
    }
    
    public function get_admin_tab_url($action) {
        if (!in_array($action, $this->admin_actions_array)) {
            return current($this->admin_actions_array);
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

    public function echo_admin_tab_active( $needle, $with_css_classes = false ) {
        $classes = ' nav-tab-active';
        $action = array_key_exists(self::QUERY_FIELD_ACTION, $_GET) ? $_GET[self::QUERY_FIELD_ACTION] : null;
        $cond1 = is_null($action) && $needle === self::ACTION_GENERATE;
        $cond2 = ($action && $needle === $action && in_array($action, $this->admin_actions_array));

        if ($with_css_classes) {
            return ($cond1 || $cond2) ? $classes : '';
        }
        
        return $cond1 || $cond2;
    }

    public function sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input[$this->prefix.'_openai_api_key_0'] ) ) {
			$sanitary_values[$this->prefix.'_openai_api_key_0'] = sanitize_text_field( $input[$this->prefix.'_openai_api_key_0'] );
		}

		return $sanitary_values;
	}

    public function section_info() {}

	public function openai_api_key_0_callback() {
		printf(
			'<input class="regular-text" type="text" name="'.$this->prefix.'_option_name['.$this->prefix.'_openai_api_key_0]" id="'.$this->prefix.'_openai_api_key_0" value="%s">',
			isset( $this->options[$this->prefix.'_openai_api_key_0'] ) ? esc_attr( $this->options[$this->prefix.'_openai_api_key_0']) : ''
		);
	}
}
