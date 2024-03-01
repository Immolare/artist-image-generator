<script type="text/html" id="tmpl-artist-image-generator-generate">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="notice-container"></div>
        <div class="notice notice-info aig-notice">
            <p>
                <img width="20px" src="<?php echo plugin_dir_url(__FILE__) . '/img/aig-icon.png'; ?>" alt="Artist Image Generator Icon" />
                <strong><?php esc_attr_e('Generate', 'artist-image-generator'); ?>:</strong>
                <?php esc_attr_e('Create images from text-to-image.', 'artist-image-generator'); ?>
                <a target="_blank" href="https://youtu.be/msd81YXw5J8" title="Video demo">
                    <?php esc_attr_e('Watch the demo', 'artist-image-generator'); ?>
                </a>
            </p>
        </div>
        <div class="aig-container aig-container-2">
            <div class="aig-inner-left">
                <table class="form-table" role="presentation">
                    <tbody class="tbody-container"></tbody>
                </table>
                <p class="submit">
                    <input type="hidden" name="generate" value="1" />
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Generate Image(s)', 'artist-image-generator'); ?>" />
                </p>
            </div>
            <div class="aig-inner-right">
                <div class="result-container"></div>
            </div>
        </div>
    </form>
</script>