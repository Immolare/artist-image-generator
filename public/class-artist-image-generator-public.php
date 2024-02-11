<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://pierrevieville.fr
 * @since      1.0.0
 *
 * @package    Artist_Image_Generator
 * @subpackage Artist_Image_Generator/public
 */

/**
 * The public-facing functionality of the plugin.
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

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_shortcode('aig', array($this, 'aig_shortcode'));
    }


    /**
     * Hook : The plugin's ajax page
     *
     * @return void
     */
    public function generate_image()
    {
        $plugin_admin = new Artist_Image_Generator_Admin($this->plugin_name, $this->version);
        $data = $plugin_admin->do_post_request();

        if (wp_doing_ajax()) {
            wp_send_json($data);
            wp_die();
        }

        // Pass the variable to the template
        //wp_localize_script($this->plugin_name, 'aig_data', $data);
    }

    // Définissez la fonction de filtre
    public function get_avatar_filter($avatar, $id_or_email, $size, $default, $alt)
    {
        // Vérifiez si c'est un objet utilisateur
        if (is_object($id_or_email) && property_exists($id_or_email, 'user_id')) {
            $user_id = $id_or_email->user_id;
        } else {
            // Sinon, essayez de récupérer l'ID d'utilisateur à partir de l'argument
            $user_id = is_numeric($id_or_email) ? $id_or_email : 0;
        }

        // Vérifiez si c'est un avatar pour un utilisateur
        if ($user_id > 0) {
            // Récupérez l'URL de l'avatar personnalisé à partir des métadonnées de l'utilisateur
            $custom_avatar_id = get_user_meta($user_id, '_aig_user_avatar', true);

            // Si un avatar personnalisé est défini, utilisez-le
            if ($custom_avatar_id) {
                // Récupérez l'URL de l'attachement à partir de l'ID
                $custom_avatar_url = wp_get_attachment_url($custom_avatar_id);

                if ($custom_avatar_url) {
                    $avatar = '<img alt="' . esc_attr($alt) . '" src="' . esc_url($custom_avatar_url) . '" class="avatar avatar-' . esc_attr($size) . '" width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" />';
                }
            }
        }

        return $avatar;
    }

    public function change_wp_avatar()
    {
        //check_ajax_referer('generate_image', 'nonce');

        // Vérifiez si l'utilisateur est connecté
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();

            // Récupérez l'URL de l'image depuis la requête POST
            $image_url = esc_url_raw($_POST['image_url']);

            try {
                // Téléchargez l'image manuellement et obtenez l'ID de l'attachement
                $attachment_id = media_sideload_image($image_url, 0, null, 'id');

                if ($attachment_id) {
                    // Mettez à jour l'avatar de l'utilisateur WordPress connecté
                    update_user_meta($user_id, '_aig_user_avatar', $attachment_id);
                    wp_send_json(array('success' => true, 'message' => 'WordPress profile picture changed successfully.'));
                } else {
                    throw new Exception('Error downloading the image.');
                }
            } catch (Exception $e) {
                // Ajoutez une sortie pour vérifier les erreurs éventuelles
                error_log('Exception: ' . $e->getMessage());

                wp_send_json_error($e->getMessage());
            }
        } else {
            wp_send_json_error('User not logged in.');
        }
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

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

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/artist-image-generator-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

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

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/artist-image-generator-public.js', array('jquery'), $this->version, false);
        /*wp_localize_script($this->plugin_name, 'aig_ajax_object_public', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            //'cropper_script_path' => plugin_dir_url(__FILE__) . 'js/artist-image-generator-admin-cropper.js',
            //'drawing_tool_script_path' => plugin_dir_url(__FILE__) . 'js/artist-image-generator-admin-drawing.js',
            //'is_media_editor' => $is_media_editor_page,
            //'variateLabel' => esc_attr__('Variate', 'artist-image-generator'),
            //'editLabel' => esc_attr__('Edit (Pro)', 'artist-image-generator'),
            //'publicLabel' => esc_attr__('Public AI Image Generator', 'artist-image-generator'),
            'generateLabel' => esc_attr__('Generate', 'artist-image-generator'),
            //'cropperCropLabel' => esc_attr__('Crop this zone', 'artist-image-generator'),
            //'cropperCancelLabel' => esc_attr__('Cancel the zoom', 'artist-image-generator'),
            //'cancelLabel' => esc_attr__('Cancel', 'artist-image-generator'),
            //'maskLabel' => esc_attr__('Create mask', 'artist-image-generator'),
            //'valid_licence' => $this->check_license_validity(),
        ));*/
    }

    // Ajoutez la fonction pour générer le shortcode
    public function aig_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'prompt' => '',
                'topics' => '',
                'n' => '3',
                'size' => '1024x1024',
                'model' => 'dall-e-2', // Nouveau paramètre pour sélectionner le modèle
                'download' => 'manual' // Nouveau paramètre pour sélectionner le comportement de téléchargement
            ),
            $atts
        );

        $plugin_admin = new Artist_Image_Generator_Admin($this->plugin_name, $this->version);

        // Si le modèle est 'dall-e-3', ajustez la valeur de 'n' à 1
        if ($atts['model'] === 'dall-e-3') {
            $atts['n'] = '1';
            if (!in_array($atts['size'], array('1024x1024', '1024x1792', '1792x1024'))) {
                $atts['size'] = '1024x1024';
            }
        }

        ob_start(); // Commence la mise en mémoire tampon du contenu HTML
?>
        <div class="aig-form-container">
            <form method="post" class="aig-form" data-action="generate_image" data-n="<?php echo esc_attr($atts['n']); ?>" 
            data-size="<?php echo esc_attr($atts['size']); ?>" data-model="<?php echo esc_attr($atts['model']); ?>" 
            data-download="<?php echo esc_attr($atts['download']); ?>" action="<?php echo admin_url('admin-ajax.php'); ?>">
                <input type="hidden" name="aig_prompt" value="<?php echo esc_attr($atts['prompt']); ?>" />
                <input type="hidden" name="action" value="generate_image" />
                <?php echo wp_nonce_field('generate_image'); ?>
                <!-- Liste de sujets (topics) sous forme de boutons -->
                <div class="form-group">
                    <fieldset class="aig-topic-buttons">
                        <legend class="form-label">Topics:</legend>
                        <?php
                        $topics_string = $atts['topics'];
                        if (!empty($topics_string)) {
                            $topics = explode(',', $topics_string);
                            foreach ($topics as $topic) {
                                $topic = trim($topic);
                        ?>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="aig_topics[]" value="<?php echo esc_attr($topic); ?>" class="form-check-input" id="aig_topic_<?php echo esc_attr($topic); ?>">
                                    <label class="form-check-label" for="aig_topic_<?php echo esc_attr($topic); ?>"><?php echo esc_html($topic); ?></label>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </fieldset>
                    <small id="aig_topics_help" class="form-text text-muted">Select one or more topics for image generation.</small>
                </div>
                <hr />
                <div class="form-group">
                    <label for="aig_public_prompt" class="form-label">Description:</label>
                    <textarea name="aig_public_prompt" id="aig_public_prompt" class="form-control" placeholder="Enter a description for the image generation (e.g., 'A beautiful cat')."></textarea>
                    <small id="aig_public_prompt_help" class="form-text text-muted">Enter a brief description for the image generation.</small>
                </div>
                <hr class="aig-results-separator" style="display:none" />
                <div class="aig-results"></div>
                <hr />
                <button type="submit" class="btn btn-primary">Generate Image / Retry</button>
                <?php if (!$plugin_admin->check_license_validity()): ?>
                    <p><small id="aig-credits">Powered by <a title="About the plugin" href="https://artist-image-generator.com/" target="_blank">Artist Image Generator</a> - 
                    <a href="https://pierrevieville.fr" title="Visit creator's website" target="_blank">© Pierre V.</a></small></p>
                <?php endif; ?>
            </form>
        </div>
<?php
        return ob_get_clean(); // Récupère le contenu HTML mis en mémoire tampon et le retourne
    }
}
