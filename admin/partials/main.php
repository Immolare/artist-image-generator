<?php

use Artist_Image_Generator_Admin as AIGA;

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
    <h1><?php esc_attr_e( 'Artist Image Generator', $this->prefix ); ?></h1>
    <h2 class="nav-tab-wrapper">
        <a href="<?= $this->get_admin_tab_url(AIGA::ACTION_GENERATE); ?>" 
            class="nav-tab<?= $this->echo_admin_tab_active(AIGA::ACTION_GENERATE, true); ?>">
            <?php esc_html_e(AIGA::ACTION_GENERATE); ?>
        </a>
        <a href="<?= $this->get_admin_tab_url(AIGA::ACTION_VARIATE); ?>" 
            class="nav-tab<?= $this->echo_admin_tab_active(AIGA::ACTION_VARIATE, true); ?>">
            <?php esc_html_e(AIGA::ACTION_VARIATE); ?>
        </a>
        <a href="<?= $this->get_admin_tab_url(AIGA::ACTION_SETTINGS); ?>" 
            class="nav-tab<?= $this->echo_admin_tab_active(AIGA::ACTION_SETTINGS, true); ?>">
            <?php esc_html_e(AIGA::ACTION_SETTINGS); ?>
        </a>
        <a href="<?= $this->get_admin_tab_url(AIGA::ACTION_ABOUT); ?>" 
            class="nav-tab<?= $this->echo_admin_tab_active(AIGA::ACTION_ABOUT, true); ?>">
            <?php esc_html_e(AIGA::ACTION_ABOUT); ?>
        </a> 
    </h2>
    <?php 
        if ($this->echo_admin_tab_active(AIGA::ACTION_GENERATE)) {
            require_once $this->get_admin_template(AIGA::ACTION_GENERATE);
        }
        elseif ($this->echo_admin_tab_active(AIGA::ACTION_VARIATE)) {
            require_once $this->get_admin_template(AIGA::ACTION_VARIATE);
        }
        elseif ($this->echo_admin_tab_active(AIGA::ACTION_ABOUT)) {
            require_once $this->get_admin_template(AIGA::ACTION_ABOUT);
        }
        else {
            require_once $this->get_admin_template(AIGA::ACTION_SETTINGS);
        }
    ?>
</div>