<?php

use Artist_Image_Generator_Constant as Constants;

class Artist_Image_Generator_Tab
{
    private $plugin_path;

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path(dirname(__FILE__));
    }

    /**
     * Get the path of the admin template.
     *
     * @param string $template
     * @param mixed ...$params
     * @return void
     */
    public function get_admin_template(string $template, ...$params): void
    {
        $valid_templates = Constants::ADMIN_DISPLAY_TEMPLATES;

        if (!in_array($template, $valid_templates)) {
            $template = Constants::ACTION_GENERATE;
        }

        $template_path = Constants::ADMIN_DISPLAY_TEMPLATES[$template];

        if ($template !== Constants::LAYOUT_MAIN) {
            $template_path .= '-template';
        }

        require_once $this->plugin_path . Constants::ADMIN_PARTIALS_PATH . $template_path . '.php';
    }

    /**
     * Get the URL of the admin tab.
     *
     * @param string $action
     * @return string
     */
    public function get_admin_tab_url(string $action): string
    {
        if (!in_array($action, Constants::ADMIN_ACTIONS)) {
            $action = Constants::ACTION_GENERATE;
        }

        return esc_url(
            add_query_arg(
                array(
                    Constants::QUERY_FIELD_ACTION => $action
                ),
                admin_url('upload.php?page=' . Constants::PLUGIN_NAME_UNDERSCORES)
            )
        );
    }

    /**
     * Check if the tab is active and return CSS classes if needed.
     *
     * @param string $needle
     * @param boolean $with_css_classes
     * @return boolean|string
     */
    public function is_tab_active(string $needle, bool $with_css_classes = false)
    {
        $classes = ' nav-tab-active';
        $action = $_GET[Constants::QUERY_FIELD_ACTION] ?? null;
        $cond1 = is_null($action) && $needle === Constants::ACTION_GENERATE;
        $action_sanitized = sanitize_text_field($action);
        $cond2 = ($action && $needle === $action_sanitized && in_array($action_sanitized, Constants::ADMIN_ACTIONS));
        $bool = $cond1 || $cond2;

        if ($with_css_classes) {
            return $bool ? $classes : '';
        }

        return $bool;
    }
}