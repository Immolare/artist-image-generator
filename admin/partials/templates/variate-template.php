<script type="text/html" id="tmpl-artist-image-generator-variate">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="notice-container"></div>
        <div class="notice notice-info aig-notice">
            <p>
                <img width="20px" src="<?php echo plugin_dir_url(__FILE__) . '/img/aig-icon.png'; ?>" alt="Artist Image Generator Icon" />
                <strong><?php esc_attr_e('Variate', 'artist-image-generator'); ?>:</strong> 
                <?php esc_attr_e('Make image variations from an existing one.', 'artist-image-generator'); ?>
                <a target="_blank" href="https://youtu.be/FtGFMsLTxYw" title="Video demo">
                    <?php esc_attr_e('Watch the demo', 'artist-image-generator'); ?>
                </a>
            </p>
        </div>
        <table class="form-table" role="presentation">
            <tbody class="tbody-container"></tbody>
        </table>
        <p class="submit">
            <input type="hidden" name="variate" value="1" />
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Generate Image(s)', 'artist-image-generator'); ?>" />
        </p>
        <hr />
        <div class="result-container"></div>
    </form>
</script>