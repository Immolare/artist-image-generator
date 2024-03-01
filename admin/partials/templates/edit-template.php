
<script type="text/html" id="tmpl-artist-image-generator-edit">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="notice-container"></div>
        <div class="notice notice-info aig-notice">
            <p>
                <img width="20px" src="<?php echo plugin_dir_url(__FILE__) . '/img/aig-icon.png'; ?>" alt="Artist Image Generator Icon" />
                <strong><?php esc_attr_e('Edit', 'artist-image-generator'); ?>:</strong> 
                <?php esc_attr_e('Customize existing images and generate a full new one.', 'artist-image-generator'); ?>
                <a target="_blank" href="https://youtu.be/zfK1yJk9gRc" title="Video demo">
                    <?php esc_attr_e('Watch the demo', 'artist-image-generator'); ?>
                </a>
            </p>
        </div>
        <table class="form-table" role="presentation">
            <tbody class="tbody-container"></tbody>
        </table>
        <p class="submit">
            <input type="hidden" name="edit" value="1" />
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Generate Image(s)', 'artist-image-generator'); ?>" />
        </p>
        <hr />
        <div class="result-container"></div>
    </form>
</script>
