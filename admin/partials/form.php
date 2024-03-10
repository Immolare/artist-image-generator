<?php // Child template for notice block (notice-container).  
?>
<script type="text/html" id="tmpl-artist-image-generator-notice">
    <# if ( data.errors && data.errors.length> 0 ) { #>
        <div class="notice notice-error inline" style="margin-top:15px;">
            <# _.each( data.errors, function( error ) { #>
                <p><?php esc_html_e('{{ error }}'); ?></p>
                <# }); #>
        </div>
        <# } #>
</script>

<?php // Child template for result block (result-container). 
?>
<script type="text/html" id="tmpl-artist-image-generator-result">
    <div class="aig-container">
        <# if ( data.images ) { #>
            <# _.each(data.images, function(image, k) { #>
                <div class="card">
                    <h2 class="title">
                        <?php esc_attr_e('Image N°', 'artist-image-generator'); ?><?php echo esc_attr('{{ k + 1 }}'); ?>
                        <div class="spinner" style="margin-top: 0;"></div>
                        <span class="dashicons dashicons-yes alignright" style="color:#46B450"></span>
                    </h2>
                    <img src="{{ image.url }}" width="100%" height="auto">
                    <a class="button add_as_media" href="javascript:void(0);">
                        <?php esc_attr_e('Add to media library', 'artist-image-generator'); ?>
                    </a>
                </div>
                <# }) #>
                    <# } #>
    </div>
</script>

<?php // Child template for form/history block (tbody-container). 
?>
<script type="text/html" id="tmpl-artist-image-generator-history">
    <button role="button" class="button" id="toggle-history-button"><?php esc_attr_e('Toggle history', 'artist-image-generator'); ?></button>
    <div class="aig-container aig-container-history" hidden>
        <# if (data.images_history) { #>
            <# _.each(data.images_history, function(image, k) { #>
                <div class="thumbnail">
                    <img src="{{ image.url }}" width="100%" height="auto" alt="{{ image.alt }}">
                    <a class="add_history_as_media" href="javascript:void(0);">
                        <div class="spinner" style="margin-top: 0;"></div>
                        <span class="dashicons dashicons-database-add" title="<?php esc_attr_e('Add to media library', 'artist-image-generator'); ?>"></span>
                    </a>
                </div>
                <# }) #>
                    <# } #>
    </div>
</script>

<?php // Child template for form/image block (tbody-container). 
?>
<script type="text/html" id="tmpl-artist-image-generator-form-image">
    <tr>
        <th scope="row">
            <label for="image"><?php esc_attr_e('File (.png, .jpg)', 'artist-image-generator'); ?></label>
            <p class="description"><?php esc_attr_e('Upload a file and crop it.', 'artist-image-generator'); ?></p>
        </th>
        <td>
            <input type="file" name="image" id="image" class="regular-text aig_handle_cropper" accept=".png,.jpg" />
            <input type="file" name="mask" id="mask" class="regular-text" accept=".png,.jpg" hidden readonly />
            <input type="file" name="original" id="original" class="regular-text" accept=".png,.jpg" hidden readonly />
        </td>
    </tr>
    <tr>
        <th id="aig_cropper_preview" scope="row"></th>
        <td id="aig_cropper_canvas_area"></td>
    </tr>
</script>

<?php // Child template for form/n block (tbody-container). 
?>
<script type="text/html" id="tmpl-artist-image-generator-form-n">
    <tr>
        <th scope="row">
            <label for="n"><?php esc_attr_e('Number of images', 'artist-image-generator'); ?></label>
            <p class="description">
                <?php esc_attr_e('Be aware of server / api limits.', 'artist-image-generator'); ?>
            </p>
        </th>
        <td>
            <select name="n" id="n">
                <# _.each(data.n, function(n) { #>
                    <# var is_selected=(data.n_input && data.n_input==n) ? 'selected' : '' ; #>
                        <option value="{{ n }}" {{ is_selected }}>
                            {{ n }}
                        </option>
                        <# }); #>
            </select>
        </td>
    </tr>
</script>

<?php // Child template for form/prompt block (tbody-container). 
?>
<script type="text/html" id="tmpl-artist-image-generator-form-prompt">
    <tr>
        <th scope="row">
            <label for="prompt">
                <?php esc_attr_e('Prompt', 'artist-image-generator'); ?>
                <?php esc_attr_e(' / Alt text', 'artist-image-generator'); ?>
            </label>
            <p class="description"><?php esc_attr_e('Describe the full new image you want to generate with DALL·E.', 'artist-image-generator'); ?></p>
        </th>
        <td>
            <textarea id="prompt" name="prompt" class="regular-text" placeholder="<?php esc_attr_e('Ex: a sunlit indoor lounge area with a pool containing a flamingo', 'artist-image-generator'); ?>"><?php echo esc_textarea('{{ data.prompt_input }}'); ?></textarea>
        </td>
    </tr>
</script>

<?php // Child template for form/model block (tbody-container). 
?>
<script type="text/html" id="tmpl-artist-image-generator-form-model">
    <#
        if (typeof data.model==='undefined' || data.model==='' ) { data.model='' ; } 
        var is_selected_dalle2=(data.model==='' ) ? 'selected' : '' ; 
        var is_selected_dalle3=(data.model==='dall-e-3' ) ? 'selected' : '' ; 
    #>
        <tr>
            <th scope="row">
                <label for="model"><?php esc_attr_e('Model to use', 'artist-image-generator'); ?></label>
                <p class="description">
                    <a href="https://openai.com/dall-e-3" target="_blank" rel="noopener noreferrer" title="More about DALL·E 3"><?php esc_attr_e('More about DALL·E 3 model.', 'artist-image-generator'); ?></a>
                </p>
            </th>
            <td>
                <select name="model" id="model">
                    <option value="" {{ is_selected_dalle2 }}><?php echo esc_html(Artist_Image_Generator_Constant::DALL_E_MODEL_2); ?></option>
                    <option value="dall-e-3" {{ is_selected_dalle3 }}><?php echo esc_html(Artist_Image_Generator_Constant::DALL_E_MODEL_3); ?></option>
                </select>
            </td>
        </tr>
</script>

<?php // Child template for form/size block (tbody-container). 
?>
<script type="text/html" id="tmpl-artist-image-generator-form-size">
    <tr>
        <th scope="row">
            <label for="size"><?php esc_attr_e('Size in pixels', 'artist-image-generator'); ?></label>
            <p class="description"><?php esc_attr_e('Depends of the model used.', 'artist-image-generator'); ?></p>
        </th>
        <td>
            <select name="size" id="size">
                <# _.each(data.sizes, function(size) { #>
                    <# var is_selected=(data.size_input && data.size_input==size) ? 'selected' : '' ; #>
                        <option value="{{ size }}" {{ is_selected }}>{{ size }}</option>
                        <# }); #>
            </select>
        </td>
    </tr>
</script>

<?php // Child template for form/quality block (tbody-container). 
?>
<script type="text/html" id="tmpl-artist-image-generator-form-quality">
    <tr>
        <th scope="row">
            <label for="quality"><?php esc_attr_e('Quality', 'artist-image-generator'); ?></label>
            <p class="description">
                <?php esc_attr_e('HD creates images with finer details.', 'artist-image-generator'); ?>
                <a href="https://platform.openai.com/docs/api-reference/images/create" target="_blank" rel="noopener noreferrer" title="Specs DALL·E 3"><?php esc_attr_e('Specs DALL·E 3 model.', 'artist-image-generator'); ?></a>
            </p>
        </th>
        <td>
            <select name="quality" id="quality">
                <# _.each(data.qualities, function(quality) { #>
                    <# var is_selected=(data.quality_input && data.quality_input==quality) ? 'selected' : '' ; #>
                        <option value="{{ quality }}" {{ is_selected }}>{{ quality }}</option>
                        <# }); #>
            </select>
        </td>
    </tr>
</script>

<?php // Child template for form/style block (tbody-container). 
?>
<script type="text/html" id="tmpl-artist-image-generator-form-style">
    <tr>
        <th scope="row">
            <label for="style"><?php esc_attr_e('Style', 'artist-image-generator'); ?></label>
            <p class="description">
                <?php esc_attr_e('Vivid for hyper-real and dramatic images.', 'artist-image-generator'); ?>
                <a href="https://platform.openai.com/docs/api-reference/images/create" target="_blank" rel="noopener noreferrer" title="Specs DALL·E 3"><?php esc_attr_e('Specs DALL·E 3 model.', 'artist-image-generator'); ?></a>
            </p>
        </th>
        <td>
            <select name="style" id="style">
                <# _.each(data.styles, function(style) { #>
                    <# var is_selected=(data.style_input && data.style_input==style) ? 'selected' : '' ; #>
                        <option value="{{ style }}" {{ is_selected }}>{{ style }}</option>
                        <# }); #>
            </select>
        </td>
    </tr>
</script>