<script type="text/html" id="tmpl-artist-image-generator-public">
    <div class="aig-container aig-container-3">
        <style>
            .aig-code {
                background-color: #f0f0f0;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
        </style>
        <div class="card">
            <h2 class="title"><?php esc_attr_e('Shortcode (Bêta)', 'artist-image-generator'); ?></h2>
            <p><?php esc_attr_e('To create a public AI image generation form in WordPress, you can use the following shortcode:', 'artist-image-generator'); ?></p>
            <div class="aig-code">
                [aig prompt="Your custom description here with {topics} and {public_prompt}" topics="Comma-separated list of topics" n="3" size="1024x1024" model="dall-e-3" style="vivid" quality="hd" download="manual"]
            </div>
            <p><?php esc_attr_e('Replace "Your custom description here" with your own description, and specify the topics you want to offer as a comma-separated list. You can use the following placeholders in your description:', 'artist-image-generator'); ?></p>
            <ul>
                <li>- {topics} : <?php esc_attr_e('To include a list of topics that users can select.', 'artist-image-generator'); ?></li>
                <li>- {public_prompt} : <?php esc_attr_e('To include a prompt for users.', 'artist-image-generator'); ?></li>
            </ul>
            <p><?php esc_attr_e('You can also use the following optional attributes in the shortcode:', 'artist-image-generator'); ?></p>
            <ul>
                <li>- n : <?php esc_attr_e('Number of images to generate (default is 3, maximum 10).', 'artist-image-generator'); ?></li>
                <li>- size : <?php esc_attr_e('The size of the images to generate (e.g., "256x256", "512x512", "1024x1024" for dall-e-2, "1024x1024", "1024x1792", "1792x1024" for dall-e-3. Default is 1024x1024).', 'artist-image-generator'); ?></li>
                <li>- model : <?php esc_attr_e('OpenAi model to use (e.g., "dall-e-2", "dall-e-3". Default is "dall-e-2").', 'artist-image-generator'); ?></li>
                <li>- quality : <?php esc_attr_e('Quality of the image to generate (e.g., "standard", "hd". Default is "standard". Only with dall-e-3).', 'artist-image-generator'); ?></li>
                <li>- style : <?php esc_attr_e('Style of the image to generate (e.g., "natural", "vivid". Default is "vivid". Only with dall-e-3).', 'artist-image-generator'); ?></li>
                <li>- download : <?php esc_attr_e('Download an image or use it as WP profile picture (e.g., "manual", "wp_avatar". Default is "manual").', 'artist-image-generator'); ?></li>
            </ul>
            <p><?php esc_attr_e('Once you have the shortcode ready, you can add it to any page or post in WordPress to display the public AI image generation form.', 'artist-image-generator'); ?></p>
            <p><a href="https://github.com/Immolare/artist-image-generator" target="_blank" title="Visit Github">Feedback and donation</a> are welcome !</p>
        </div>
        <div class="card">
            <h2 class="title"><?php esc_attr_e('Exemple: Rendering the shortcode into a page', 'artist-image-generator'); ?></h2>
            <p><?php esc_attr_e('The shortcode:', 'artist-image-generator'); ?></p>
            <div class="aig-code">
                [aig prompt="Painting of {public_prompt}, including following criterias: {topics}"
                topics="Impressionism, Surrealism, Portraits, Landscape Painting, Watercolor Techniques, Oil Painting, Street Art, Hyperrealism, Cat, Dog, Bird, Person"
                download="manual" model="dall-e-3"]
            </div>
            <p><?php esc_attr_e('The result:', 'artist-image-generator'); ?></p>
            <img style="width:100%" src="<?php echo plugin_dir_url(__FILE__) . '/img/aig-public-form.jpg'; ?>" alt="Exemple of form render" />
        </div>

    </div>
</script>