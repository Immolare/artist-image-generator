<?php

class Artist_Image_Generator_Notice {
    private string $message;
    private string $type;
    private bool $is_dismissible;
    private bool $show_icon;
    private ?string $hide_url;

    public function __construct(
        string $message, 
        string $type = 'info', 
        bool $is_dismissible = true, 
        bool $show_icon = false, 
        ?string $hide_url = null
    ) {
        $this->message = $message;
        $this->type = $type;
        $this->is_dismissible = $is_dismissible;
        $this->show_icon = $show_icon;
        $this->hide_url = $hide_url;
    }

    public function display(): void {
        $icon = $this->show_icon ? $this->getIcon() : '';
        $class = $this->is_dismissible ? 'is-dismissible' : '';

        $allowed_html = $this->getAllowedHtml();

        echo '<div class="notice notice-' . esc_attr($this->type) . ' aig-notice ' . esc_attr($class) . '">
            <p>' . wp_kses($icon, $allowed_html) . wp_kses($this->message, $allowed_html) . '</p>';

        if ($this->hide_url !== null) {
            echo '<p><a href="' . esc_url($this->hide_url) . '">' . esc_html__('Hide this notice', 'artist-image-generator') . '</a></p>';
        }

        echo '</div>';
    }

    private function getIcon(): string {
        return '<img width="20px" src="' . esc_url(plugin_dir_url(__FILE__) . '/img/aig-icon.png') .'" alt="Artist Image Generator Icon" />';
    }

    private function getAllowedHtml(): array {
        return array(
            'a' => array(
                'href' => array(),
                'title' => array(),
                'target' => array()
            ),
            'img' => array(
                'width' => array(),
                'src' => array(),
                'alt' => array()
            )
        );
    }
}