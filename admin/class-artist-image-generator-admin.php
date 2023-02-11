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
    private $admin_display_settings_template = "admin/partials/_settings.php";
    
    private $admin_actions_array = [
        self::ACTION_GENERATE,
        self::ACTION_SETTINGS, 
        self::ACTION_ABOUT
    ];
    /* 
    * Retrieve this value with:
    * $options = get_option( $this->prefix.'_option_name' ); // Array of All Options
    * $openai_api_key_0 = $options['openai_api_key_0']; // OPENAI_API_KEY
    */
    private $options;

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

    /**
	 * Register new media page for the media menu area.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu() {
		add_media_page(
			$this->plugin_full_name, // page_title
			__( 'Image Generator', $this->prefix ), // menu_title
			'manage_options', // capability
			$this->prefix, // menu_slug
			array( $this, 'admin_page' ) // function
		);
	}

    public function admin_page() {
		$this->options = get_option( $this->prefix.'_option_name' );
        $is_post_request = $_SERVER['REQUEST_METHOD'] == 'POST';
        $is_generation = array_key_exists('generate', $_POST) && $_POST['generate'];
        $is_setting_up = array_key_exists('openai_api_key_0', $this->options);
        $error = [];

        if ( $is_post_request && $is_generation && $is_setting_up) {
            $prompt_input = array_key_exists('prompt', $_POST) ? sanitize_text_field($_POST['prompt']) : '';

            if (empty($prompt_input)) {
                $error = [
                    'msg' => __( 'The Prompt input must be filled in order to generate an image.', $this->prefix )
                ];
            }
            else {
                $size_input = array_key_exists('size', $_POST) ? sanitize_text_field($_POST['size']) : $this->get_default_image_dimensions();
                $n_input = array_key_exists('n', $_POST) ? (int)sanitize_text_field($_POST['n']) : 1;

                if (ob_get_contents()) {
                    ob_end_clean();
                }

                $open_ai = new OpenAi($this->options['openai_api_key_0']);
                $result = $open_ai->image([
                    "prompt" => $prompt_input,
                    "n" => $n_input > 0 && $n_input <= 10 ? (int)$n_input : 1,
                    "size" => $size_input,
                    //"response_format" => "b64_json"
                ]);
                $images = json_decode($result, true);
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
			__( 'Settings', $this->prefix ), // title
			array( $this, 'section_info' ), // callback
			$this->prefix.'-admin' // page
		);

		add_settings_field(
			'openai_api_key_0', // id
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

        $url = $_POST['url'];
        $alt = $_POST['description'];

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
		if ( isset( $input['openai_api_key_0'] ) ) {
			$sanitary_values['openai_api_key_0'] = sanitize_text_field( $input['openai_api_key_0'] );
		}

		return $sanitary_values;
	}

    public function section_info() {}

	public function openai_api_key_0_callback() {
		printf(
			'<input class="regular-text" type="text" name="'.$this->prefix.'_option_name[openai_api_key_0]" id="openai_api_key_0" value="%s">',
			isset( $this->options['openai_api_key_0'] ) ? esc_attr( $this->options['openai_api_key_0']) : ''
		);
	}
}
