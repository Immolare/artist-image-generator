<?php

use Artist_Image_Generator_Admin as WPOAIIGA;

/**
 * Provide a admin area setup view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://pierrevieville.fr
 * @since      1.0.0
 *
 * @package    Artist_Image_Generator
 * @subpackage Artist_Image_Generator/admin/partials
 */
?>

<div class="wrap">
    <h1><?php esc_attr_e( 'Wordpress OpenAI Image Generator', $this->prefix ); ?></h1>
    <h2 class="nav-tab-wrapper">
        <a href="<?= $this->get_admin_tab_url(WPOAIIGA::ACTION_GENERATE); ?>" 
            class="nav-tab<?= $this->echo_admin_tab_active(WPOAIIGA::ACTION_GENERATE, true); ?>">
            <?php esc_html_e(WPOAIIGA::ACTION_GENERATE); ?>
        </a>
        <a href="<?= $this->get_admin_tab_url(WPOAIIGA::ACTION_SETTINGS); ?>" 
            class="nav-tab<?= $this->echo_admin_tab_active(WPOAIIGA::ACTION_SETTINGS, true); ?>">
            <?php esc_html_e(WPOAIIGA::ACTION_SETTINGS); ?>
        </a>
        <a href="<?= $this->get_admin_tab_url(WPOAIIGA::ACTION_ABOUT); ?>" 
            class="nav-tab<?= $this->echo_admin_tab_active(WPOAIIGA::ACTION_ABOUT, true); ?>">
            <?php esc_html_e(WPOAIIGA::ACTION_ABOUT); ?>
        </a> 
    </h2>
    <?php 
        if ($this->echo_admin_tab_active(WPOAIIGA::ACTION_GENERATE)) {
            require_once $this->get_admin_template(WPOAIIGA::ACTION_GENERATE);
        }
        elseif ($this->echo_admin_tab_active(WPOAIIGA::ACTION_ABOUT)) {
            require_once $this->get_admin_template(WPOAIIGA::ACTION_ABOUT);
        }
        else {
            require_once $this->get_admin_template(WPOAIIGA::ACTION_SETTINGS);
        }
    ?>
</div>