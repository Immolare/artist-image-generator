<?php if (isset($error) && $error['msg']) : ?>
<div class="notice notice-error inline" style="margin-top:15px;">
	<p><?php echo $error['msg']; ?></p>
</div>
<?php endif; ?>
<form action="" method="post" enctype="multipart/form-data">
    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><label for="prompt"><?php esc_attr_e( 'Prompt', 'artist-image-generator' ); ?></label></th>
                <td>
                    <input type="text" id="prompt" name="prompt" class="regular-text" 
                        placeholder="<?php esc_attr_e( 'Ex: A bowl of soup as a planet in the universe as digital art', 'artist-image-generator' ); ?>" 
                        value="<?php echo esc_attr($prompt_input) ?? ''; ?>" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="size"><?php esc_attr_e( 'Size in pixels', 'artist-image-generator' ); ?></label></th>
                <td>
                    <select name="size" id="size">
                        <option value="256x256" <?php echo $size_input && $size_input ==="256x256" ? 'selected' : ''; ?>>256x256</option>
                        <option value="512x512" <?php echo $size_input && $size_input ==="512x512" ? 'selected' : ''; ?>>512x512</option>
                        <option value="1024x1024" <?php echo !$size_input || ($size_input && $size_input ==="1024x1024") ? 'selected' : ''; ?>>1024x1024</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="n"><?php esc_attr_e( 'Number of images', 'artist-image-generator' ); ?></label></th>
                <td>
                    <select name="n" id="n">
                        <?php $i = 1; while ($i < 11) : ?>
                            <option value="<?php echo $i; ?>" <?php echo $n_input && $n_input == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php $i++; endwhile; ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
    <p class="submit">
        <input type="hidden" name="generate" value="1" />
        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Generate Image(s)', 'artist-image-generator' ); ?>">
    </p>
    <hr />
    <?php if (isset($images) && $images) : $datas = $images['data']; ?>
    <div class="aig-container">
        <?php foreach ($datas as $k => $data) : ?>
            <div class="card">
                <h2 class="title">
                    <?php esc_attr_e( 'Image N°' , 'artist-image-generator' ); ?><?php echo $k+1; ?> 
                    <div class="spinner" style="margin-top: 0;"></div>
                    <span class="dashicons dashicons-yes alignright" style="color:#46B450"></span>
                </h2>
                <img src="<?php echo esc_url($data['url']); ?>" alt="<?php echo $images['created']; ?>" width="100%" height="auto">
                <a class="button add_as_media" href="javascript:void(0);"><?php esc_attr_e( 'Add to media library' , 'artist-image-generator' ); ?></a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</form>
