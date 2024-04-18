<?php

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
    <h1><?php echo esc_html__( 'Artist Image Generator', 'artist-image-generator' ); ?></h1>
    <h2 class="nav-tab-wrapper">
        <?php foreach ( Artist_Image_Generator_Constant::ADMIN_ACTIONS as $action ) : ?>
            <a id="menu-item-<?php echo esc_attr( $action ); ?>" href="<?php echo esc_url( $this->get_admin_tab_url( $action ) ); ?>" class="nav-tab <?php echo esc_attr( $this->is_tab_active( $action, true ) ); ?>">
                <?php echo esc_html(Artist_Image_Generator_Constant::ADMIN_ACTIONS_LABELS[$action]); ?>
                <?php if ( esc_attr( $action ) === "about" ) : ?>
                    <span class="dashicons dashicons-bell"></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </h2>
    <?php foreach ( Artist_Image_Generator_Constant::ADMIN_ACTIONS as $action ) : ?>
        <?php if ( $this->is_tab_active( $action ) ) : ?>
            <div id="tab-container-aig-<?php echo esc_attr( $action ); ?>" class="tab-container-aig-<?php echo esc_attr( $action ); ?>"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>