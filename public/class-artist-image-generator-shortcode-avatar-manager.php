<?php
/**
 * Shortcodes Avatar Manager
 * 
 * @link       https://pierrevieville.fr
 * @since      1.0.18
 * 
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Artist_Image_Generator
 * @subpackage Artist_Image_Generator/public
 * @author     Pierre Viéville <contact@pierrevieville.fr>
 */
class Artist_Image_Generator_Shortcode_Avatar_Manager {
    public function filter($avatar, $id_or_email, $size, $default, $alt)
    {
        $user_id = $this->getUserId($id_or_email);

        if ($user_id > 0) {
            $custom_avatar_url = $this->getCustomAvatarUrl($user_id);

            if ($custom_avatar_url) {
                $avatar = $this->generateAvatarHtml($alt, $custom_avatar_url, $size);
            }
        }

        return $avatar;
    }

    public function change()
    {
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('User not logged in.'));
            return;
        }

        $current_user_id = get_current_user_id();
        $image_url = esc_url_raw($_POST['image_url']);

        try {
            $attachment_id = $this->downloadImage($image_url);

            update_user_meta($current_user_id, '_aig_user_avatar', $attachment_id);
            $this->process($attachment_id, $current_user_id);

            wp_send_json(array('success' => true, 'message' => esc_html__('Your profile picture changed successfully.', 'artist-image-generator')));
        } catch (Exception $e) {
            error_log('Exception: ' . $e->getMessage());
            wp_send_json_error($e->getMessage());
        }
    }

    private function process($attachment_id, $current_user_id)
    {
        if (is_plugin_active('simple-local-avatars/simple-local-avatars.php')) {
            $this->processSimpleLocalAvatar($attachment_id, $current_user_id);
        } else if (is_plugin_active('one-user-avatar/one-user-avatar.php')) {
            $this->processOneUserAvatar($attachment_id, $current_user_id);
        }
    }

    private function getUserId($id_or_email)
    {
        if (is_object($id_or_email) && property_exists($id_or_email, 'user_id')) {
            return $id_or_email->user_id;
        } else {
            return is_numeric($id_or_email) ? $id_or_email : 0;
        }
    }

    private function getCustomAvatarUrl($user_id)
    {
        $custom_avatar_id = get_user_meta($user_id, '_aig_user_avatar', true);

        if (!is_int($custom_avatar_id)) {
            update_user_meta($user_id, '_aig_user_avatar', null);
            return null;
        }

        if ($custom_avatar_id) {
            return wp_get_attachment_url($custom_avatar_id);
        }

        return null;
    }

    private function generateAvatarHtml($alt, $custom_avatar_url, $size)
    {
        return '<img alt="' . esc_attr($alt) . '" src="' . esc_url($custom_avatar_url) . '" class="avatar avatar-' . esc_attr($size) . '" width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" />';
    }

    private function downloadImage($image_url)
    {
        $attachment_id = media_sideload_image($image_url, 0, null, 'id');

        if (!$attachment_id) {
            throw new Exception(esc_html__('Error downloading the image.', 'artist-image-generator'));
        }

        return $attachment_id;
    }

    private function processSimpleLocalAvatar($attachment_id, $current_user_id)
    {        
        $simple_local_avatars = new Simple_Local_Avatars();
        $simple_local_avatars->assign_new_user_avatar($attachment_id, $current_user_id);
    }

    private function processOneUserAvatar($attachment_id, $current_user_id)
    {
        global $wp_user_avatar;
        $_POST['wp-user-avatar'] = $attachment_id;
        $wp_user_avatar::wpua_action_process_option_update($current_user_id);
    }
}