<?php

use LMFW\SDK\License as LMFWLicense;
use Artist_Image_Generator_Constant as Constants;
use Artist_Image_Generator_Notice as Notice;
use Artist_Image_Generator_Setter as Setter;

class Artist_Image_Generator_License {
    /**
     * Creates a new license.
     *
     * @return LMFWLicense The created license.
     */
    private static function license_create(): LMFWLicense {
        return new LMFWLicense(
            Constants::PLUGIN_NAME,
            Constants::LICENSE_SERVER,
            Constants::CUSTOMER_KEY,
            Constants::CUSTOMER_SECRET,
            Constants::PRODUCT_IDS,
            [
                'settings_key' => Constants::OPTION_NAME,
                'option_key' => Constants::LICENCE_KEY
            ],
            Constants::LICENCE_OBJECT,
            Constants::DAYS
        );
    }

    /**
     * Sets the hooks for the license.
     */
    public static function license_set_hooks(): void {
        if (!wp_next_scheduled('artist_image_generator_license_validity')) {
            wp_schedule_event(time(), 'daily', 'artist_image_generator_license_validity');
        }

        add_action('artist_image_generator_license_validity', [__CLASS__, 'license_check_validity_cron']);
        add_action('admin_notices', [__CLASS__, 'license_display_notices']);
        add_action('admin_init', [__CLASS__, 'license_hide_notices']);
    }

    /**
     * Checks the validity of the license.
     *
     * @return bool Whether the license is valid.
     */
    public static function license_check_validity(): bool {
        $license_key = self::license_get_key();
    
        if (!$license_key) {
            return false;
        }
    
        $sdk_license = self::license_create();
        $valid_result = $sdk_license->validate_status($license_key);
    
        return $valid_result['is_valid'];
    }

    public static function license_check_product_ai_image_customizer_presence(): bool {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        return is_plugin_active('artist-image-generator-product-ai-image-customizer/artist-image-generator-product-ai-image-customizer.php');
    }

    /**
     * Checks the validity of the license for the cron job.
     *
     * @return bool Whether the license is valid.
     */
    public static function license_check_validity_cron(): bool {
        $license_key = self::license_get_key();
    
        if (!$license_key) {
            return false;
        }
    
        $sdk_license = self::license_create();
        $valid_status = $sdk_license->validate_status($license_key);
        $valid_until = $sdk_license->valid_until();
        $license_key_about_to_expire = $valid_status['is_valid'] && $valid_until && $valid_until < (time() + 15 * DAY_IN_SECONDS);
        $license_key_is_expired = !$valid_status['is_valid'];
    
        self::license_update_status($license_key_about_to_expire, $license_key_is_expired, $valid_status['is_valid']);
    
        return $valid_status['is_valid'];
    }

    /**
     * Gets the license key.
     *
     * @return ?string The license key.
     */
    public static function license_get_key(): ?string {
        $options = Setter::get_options();
        $keyField = Constants::LICENCE_KEY;
    
        return array_key_exists($keyField, $options) ? $options[$keyField] : null;
    }

    /**
     * Updates the status of the license.
     *
     * @param bool $license_key_about_to_expire Whether the license key is about to expire.
     * @param bool $license_key_is_expired Whether the license key is expired.
     * @param bool $is_valid Whether the license is valid.
     */
    private static function license_update_status(bool $license_key_about_to_expire, bool $license_key_is_expired, bool $is_valid): void {
        if ($license_key_about_to_expire) {
            if (get_option(Constants::LICENCE_EXPIRING_SOON) != 'hidden') {
                update_option(Constants::LICENCE_EXPIRING_SOON, 'display');
            }
        }

        if (!$license_key_is_expired) {
            if (get_option(Constants::LICENCE_INVALID_OR_EXPIRED) != 'hidden') {
                update_option(Constants::LICENCE_INVALID_OR_EXPIRED, 'display');
            }
        }

        if ($is_valid && !$license_key_about_to_expire && get_option(Constants::LICENCE_INVALID_OR_EXPIRED) == 'display') {
            update_option(Constants::LICENCE_INVALID_OR_EXPIRED, 'hidden');
        }
    }

    /**
     * Displays the notices for the license.
     */
    public static function license_display_notices(): void {
        $sdk_license = self::license_create();
        self::license_display_notice(Constants::LICENCE_INVALID_OR_EXPIRED, 'error', __('Your Artist Image Generator license key is expired or invalid.'));
        self::license_display_notice(Constants::LICENCE_EXPIRING_SOON, 'warning', __('Your Artist Image Generator license key is expiring on ') . esc_html(date_i18n(get_option('date_format'), $sdk_license->valid_until())) . '.');
    }

    /**
     * Displays a notice for the license.
     *
     * @param string $option The option for the notice.
     * @param string $type The type of the notice.
     * @param string $message The message of the notice.
     */
    private static function license_display_notice(string $option, string $type, string $message): void {
        if (get_option($option) === 'display') {
            $hide_url = esc_url(add_query_arg(Constants::HIDE_NOTICE, $option));
            $message .= ' <a target="_blank" href="'.esc_url('https://artist-image-generator.com/product/licence-key/').'">';
            $message .= __('Renew your key now', 'artist-image-generator');
            $message .= '</a>.';
            $notice = new Notice($message, $type, true, true, $hide_url);
            $notice->display();
        }
    }

    /**
     * Hides the notices for the license.
     */
    public static function license_hide_notices(): void {
        if (isset($_GET[Constants::HIDE_NOTICE])) {
            update_option(Constants::PLUGIN_NAME_UNDERSCORES . '_aig_license_' . sanitize_text_field($_GET[Constants::HIDE_NOTICE]), 'hidden');
        }
    }

    /**
     * Validates the license.
     *
     * @param string $license_key The license key.
     * @return array The status of the license validation.
     */
    public static function license_validate(string $license_key): array {
        $sdk_license = self::license_create();

        return $sdk_license->validate_status($license_key);
    }

    /**
     * Activates the license.
     *
     * @param string $license_key The license key.
     * @return bool Whether the license was activated.
     */
    public static function license_activate(string $license_key): bool {
        $sdk_license = self::license_create();

        $is_license_activated = get_option(Constants::LICENCE_KEY_ACTIVATED);
        if (!$is_license_activated) {
            $sdk_license->activate($license_key);
            update_option(Constants::LICENCE_KEY_ACTIVATED, true);
        }

        return get_option(Constants::LICENCE_KEY_ACTIVATED);
    }
}