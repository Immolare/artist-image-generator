<script type="text/html" id="tmpl-artist-image-generator-settings">
    <div class="aig-container aig-container-3">
        <div class="card">
            <h2 class="title">
                <?php esc_attr_e('How to get your OpenAI API key ?', 'artist-image-generator'); ?>
            </h2>
            <p>
                1. <?php esc_attr_e('Log in into OpenAI developer portail', 'artist-image-generator'); ?> :
                <a target="_blank" title="OpenAI Developer Portail" href="https://openai.com/api/">https://openai.com/api/</a>
            </p>
            <p>
                2. <?php esc_attr_e('Create a new secret key', 'artist-image-generator'); ?> :
                <a target="_blank" title="OpenAI - API keys" href="https://platform.openai.com/account/api-keys">https://platform.openai.com/account/api-keys</a>
            </p>
            <p>
                3. <?php esc_attr_e('Copy/paste the secret key in the OPENAI_KEY field.', 'artist-image-generator'); ?>
            </p>
            <p>
                4. <?php esc_attr_e('Press "Save changes" and you are done.', 'artist-image-generator'); ?>
            </p>
            <hr />
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->prefix . '_option_group');
                do_settings_sections($this->prefix . '-admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php if (!$this->check_license_validity()) : ?>
            <div class="card">
                <h2 class="title">Provide full access to Artist Image Generator</h2>
                <p>With Artist Image Generator Edit Image feature, you can compose, edit and generate full new images from Wordpress.</p>
                <p>By purchasing a unique license, you unlock this powerful functionality along with new pro features, remove credits, <strong>and help me to maintain this plugin</strong>.</p>
                <p style="margin: 10px 0;">
                    <a href="https://artist-image-generator.com/product/licence-key/" title="Purchase Artist Image Generator Pro Licence key" target="_blank" class="button button-primary" style="width :100%; text-align:center;">
                        Buy Artist Image Generator (Pro) - Licence Key
                    </a>
                </p>
                <p>Compatible width Block Builders like <strong>Elementor, Beaver Builder, WP Bakery.</strong></p>
                <p>
                    Official <a href="https://help.openai.com/en/articles/6516417-dall-e-editor-guide" target="_blank" title="OpenAI DALL·E Editor Guide">OpenAI DALL·E Editor Guide</a>
                    - <a href="https://labs.openai.com/editor" target="_blank" title="OpenAI DALL·E Editor">Try OpenAI DALL·E Editor</a>
                </p>
                <iframe width="100%" height="315" src="https://www.youtube.com/embed/zfK1yJk9gRc" title="Artist Image Generator - Image Edition feature" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        <?php endif; ?>
</script>