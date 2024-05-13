<?php // Template for generate tab. 
?>
<script type="text/html" id="tmpl-artist-image-generator-generate">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="notice-container"></div>
        <?php
        $message = '<strong>' . esc_html__('Generate', 'artist-image-generator') . ':</strong> ' .
            esc_html__('Create images from text-to-image.', 'artist-image-generator') .
            ' <a target="_blank" href="https://youtu.be/msd81YXw5J8" title="Video demo">' .
            esc_html__('Watch the demo', 'artist-image-generator') . '</a>';

        $notice = new Artist_Image_Generator_Notice($message, 'info', false, true);
        $notice->display();
        ?>
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

<?php // Template for variate tab. 
?>
<script type="text/html" id="tmpl-artist-image-generator-variate">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="notice-container"></div>
        <?php
        $message = '<strong>' . esc_html__('Variate', 'artist-image-generator') . ':</strong> ' .
            esc_html__('Make image variations from an existing one.', 'artist-image-generator') .
            ' <a target="_blank" href="https://youtu.be/FtGFMsLTxYw" title="Video demo">' .
            esc_html__('Watch the demo', 'artist-image-generator') . '</a>';

        $notice = new Artist_Image_Generator_Notice($message, 'info', true, true);
        $notice->display();
        ?>
        <div class="history-container"></div>
        <div class="aig-container aig-container-2">
            <div class="aig-inner-left">
                <table class="form-table" role="presentation">
                    <tbody class="tbody-container"></tbody>
                </table>
                <p class="submit">
                    <input type="hidden" name="variate" value="1" />
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Generate Image(s)', 'artist-image-generator'); ?>" />
                </p>
            </div>
            <div class="aig-inner-right">
                <div class="result-container"></div>
            </div>
        </div>
    </form>
</script>

<?php // Template for edit tab. 
?>
<script type="text/html" id="tmpl-artist-image-generator-edit">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="notice-container"></div>
        <?php
        $message = '<strong>' . esc_html__('Edit', 'artist-image-generator') . ':</strong> ' .
            esc_html__('Customize existing images and generate a full new one.', 'artist-image-generator') .
            ' <a target="_blank" href="https://youtu.be/zfK1yJk9gRc" title="Video demo">' .
            esc_html__('Watch the demo', 'artist-image-generator') . '</a>';

        $notice = new Artist_Image_Generator_Notice($message, 'info', false, true);
        $notice->display();
        ?>
        <div class="history-container"></div>
        <div class="aig-container aig-container-2">
            <div class="aig-inner-left">
                <table class="form-table" role="presentation">
                    <tbody class="tbody-container"></tbody>
                </table>
                <p class="submit">
                    <input type="hidden" name="edit" value="1" />
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Generate Image(s)', 'artist-image-generator'); ?>" />
                </p>
            </div>
            <div class="aig-inner-right">
                <div class="result-container"></div>
            </div>
        </div>
    </form>
</script>

<?php // Template for edit demo tab. 
?>
<script type="text/html" id="tmpl-artist-image-generator-edit-demo">
    <div class="card">
        <h2 class="title">Provide full access to Artist Image Generator</h2>
        <p>With Artist Image Generator Edit Image feature, you can compose, edit and generate full new images from Wordpress.</p>
        <p>By purchasing a unique license, you unlock this powerful functionality along with new pro features, remove credits <strong>and help me to maintain this plugin</strong>.</p>
        <p style="margin: 10px 0;">
            <a href="https://artist-image-generator.com/product/licence-key/" title="Purchase Artist Image Generator Pro Licence key" target="_blank" class="button button-primary" style="width :100%; text-align:center;">
                Buy Artist Image Generator (Pro) - Licence Key
            </a>
        </p>
        <p>Compatible with Block Builders like <strong>Elementor, Beaver Builder, WP Bakery.</strong></p>
        <p>
            Official <a href="https://help.openai.com/en/articles/6516417-dall-e-editor-guide" target="_blank" title="OpenAI DALL·E Editor Guide">OpenAI DALL·E Editor Guide</a>
            - <a href="https://labs.openai.com/editor" target="_blank" title="OpenAI DALL·E Editor">Try OpenAI DALL·E Editor</a>
        </p>
        <iframe width="100%" height="315" src="https://www.youtube.com/embed/zfK1yJk9gRc" title="Artist Image Generator - Image Edition feature" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
</script>

<?php // Template for public tab. 
?>
<script type="text/html" id="tmpl-artist-image-generator-public">
    <div class="aig-container aig-container-2">
        <style>
            /* Ajout de CSS pour améliorer l'apparence */
            .aig-code {
                background-color: #f0f0f0;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                margin-bottom: 18px;
            }
            .card.full {
                max-width: 100%;
            }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; } 
            th, td { border: 1px solid #ccc; padding: 10px; text-align: left; } 
            th { background-color: #f2f2f2; font-weight: bold; }
        </style>
        <div class="card full">
            <h2 class="title"><?php esc_attr_e('Shortcode', 'artist-image-generator'); ?></h2>
            <p><?php esc_attr_e('To create a public AI image generation form in WordPress, you can use the following shortcode:', 'artist-image-generator'); ?></p>
            <div class="aig-code">
                [aig prompt="Your custom description here with {topics} and {public_prompt}" topics="Comma-separated list of topics" n="3" size="1024x1024" model="dall-e-3" style="vivid" quality="hd" download="manual" user_limit="5" user_limit_duration="3600"]
            </div>
            <table>
                <thead>
                    <tr>
                        <th><?php esc_attr_e('Placeholder', 'artist-image-generator'); ?></th>
                        <th><?php esc_attr_e('Description', 'artist-image-generator'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{topics}</td>
                        <td><?php esc_attr_e('To include a list of topics that users can select.', 'artist-image-generator'); ?></td>
                    </tr>
                    <tr>
                        <td>{public_prompt}</td>
                        <td><?php esc_attr_e('To include a prompt for users.', 'artist-image-generator'); ?></td>
                    </tr>
                </tbody>
            </table>
            <p><?php esc_attr_e('You can also use the following optional attributes in the shortcode:', 'artist-image-generator'); ?></p>
            <table>
                <thead>
                    <tr>
                        <th><?php esc_attr_e('Attribute', 'artist-image-generator'); ?></th>
                        <th><?php esc_attr_e('Description', 'artist-image-generator'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>n</td>
                        <td><?php esc_attr_e('Number of images to generate (default is 3, maximum 10).', 'artist-image-generator'); ?></td>
                    </tr>
                    <tr>
                        <td>size</td>
                        <td><?php esc_attr_e('The size of the images to generate (e.g., "256x256", "512x512", "1024x1024" for dall-e-2, "1024x1024", "1024x1792", "1792x1024" for dall-e-3. Default is 1024x1024).', 'artist-image-generator'); ?></td>
                    </tr>
                    <tr>
                        <td>model</td>
                        <td><?php esc_attr_e('OpenAi model to use (e.g., "dall-e-2", "dall-e-3". Default is "dall-e-2").', 'artist-image-generator'); ?></td>
                    </tr>
                    <tr>
                        <td>quality</td>
                        <td><?php esc_attr_e('Quality of the image to generate (e.g., "standard", "hd". Default is "standard". Only with dall-e-3).', 'artist-image-generator'); ?></td>
                    </tr>
                    <tr>
                        <td>style</td>
                        <td><?php esc_attr_e('Style of the image to generate (e.g., "natural", "vivid". Default is "vivid". Only with dall-e-3).', 'artist-image-generator'); ?></td>
                    </tr>
                    <tr>
                        <td>download</td>
                        <td><?php esc_attr_e('Download an image or use it as WP profile picture (e.g., "manual", "wp_avatar". Default is "manual").', 'artist-image-generator'); ?></td>
                    </tr>
                </tbody>
            </table>
            <p><?php esc_attr_e('To handle user limitation use the following optional attributes:', 'artist-image-generator'); ?></p>
            <table>
                <thead>
                    <tr>
                        <th><?php esc_attr_e('Attribute', 'artist-image-generator'); ?></th>
                        <th><?php esc_attr_e('Description', 'artist-image-generator'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>user_limit</td>
                        <td><?php esc_attr_e('Number of images a user can generate (default is 0, unlimited).', 'artist-image-generator'); ?></td>
                    </tr>
                    <tr>
                        <td>user_limit_duration</td>
                        <td><?php esc_attr_e('Duration of the user limit in seconds (default is 0, lifetime).', 'artist-image-generator'); ?></td>
                    </tr>
                </tbody>
            </table>
            <p><?php esc_attr_e('Once you have the shortcode ready, you can add it to any page or post in WordPress to display the public AI image generation form.', 'artist-image-generator'); ?></p>
            <p>
                <a href="https://github.com/Immolare/artist-image-generator" target="_blank" title="Visit Github">
                    Feedback and donation</a> are welcome ! · 
                <a href="https://artist-image-generator.com/" target="_blank" title="Visit Artist Image Generator">
                    Visit the website</a> for more information.
            </p>
        </div>
        <div class="card">
            <h2 class="title"><?php esc_html_e('Exemple: Rendering the shortcode into a page', 'artist-image-generator'); ?></h2>
            <p><?php esc_html_e('The shortcode:', 'artist-image-generator'); ?></p>
            <div class="aig-code">
                [aig prompt="Painting of {public_prompt}, including following criterias: {topics}" user_limit="5" user_limit_duration="86400"
                topics="Impressionism, Surrealism, Portraits, Landscape Painting, Watercolor Techniques, Oil Painting, Street Art, Hyperrealism, Cat, Dog, Bird, Person"
                download="manual" model="dall-e-3"]
            </div>
            <p><?php esc_html_e('The result:', 'artist-image-generator'); ?></p>
            <?php $image_url = plugin_dir_url( dirname( __FILE__ ) ) . 'img/aig-public-form.jpg'; ?>
            <img style="width:100%" src="<?php echo esc_url($image_url); ?>" alt="Exemple of form render" />
        </div>

    </div>
</script>

<?php
if (Artist_Image_Generator_Setter::is_artist_image_generator_page()) :
    // Template for settings tab. 
?>
    <script type="text/html" id="tmpl-artist-image-generator-settings">
        <div class="aig-container aig-container-3">
            <div class="card">
                <h2 class="title">
                    <?php esc_html_e('How to get your OpenAI API key ?', 'artist-image-generator'); ?>
                </h2>
                <p>
                    1. <?php esc_html_e('Log in into OpenAI developer portail', 'artist-image-generator'); ?> :
                    <a target="_blank" title="OpenAI Developer Portail" href="https://openai.com/api/">https://openai.com/api/</a>
                </p>
                <p>
                    2. <?php esc_html_e('Create a new secret key', 'artist-image-generator'); ?> :
                    <a target="_blank" title="OpenAI - API keys" href="https://platform.openai.com/account/api-keys">https://platform.openai.com/account/api-keys</a>
                </p>
                <p>
                    3. <?php esc_html_e('Copy/paste the secret key in the OPENAI_KEY field.', 'artist-image-generator'); ?>
                </p>
                <p>
                    4. <?php esc_html_e('Press "Save changes" and you are done.', 'artist-image-generator'); ?>
                </p>
                <hr />
                <?php settings_errors(); ?>
                <form method="post" action="options.php">
                    <?php
                    settings_fields(Artist_Image_Generator_Constant::PLUGIN_NAME_UNDERSCORES . '_option_group');
                    do_settings_sections(Artist_Image_Generator_Constant::PLUGIN_NAME_UNDERSCORES . '-admin');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php if (!Artist_Image_Generator_License::license_check_validity()) : ?>
                <div class="card">
                    <h2 class="title">Provide full access to Artist Image Generator</h2>
                    <p>With Artist Image Generator Edit Image feature, you can compose, edit and generate full new images from Wordpress.</p>
                    <p>By purchasing a unique license, you unlock this powerful functionality along with new pro features, remove credits, <strong>and help me to maintain this plugin</strong>.</p>
                    <p style="margin: 10px 0;">
                        <a href="https://artist-image-generator.com/product/licence-key/" title="Purchase Artist Image Generator Pro Licence key" target="_blank" class="button button-primary" style="width :100%; text-align:center;">
                            Buy Artist Image Generator - Licence Key
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
            <?php if (!Artist_Image_Generator_License::license_check_product_ai_image_customizer_presence()) : ?>
                <div class="card">
                    <h2 class="title">Wanna sell AI customized products on WooCommerce?</h2>
                    <p>Turn shoppers into designers! Add this AIG plugin to let customers personalize their products with AI generated unique digital art.</p>
                    <p>Check out <strong>Artist Image Generator – Product AI Image Customizer</strong>.</p>
                    <p style="margin: 10px 0;">
                        <a href="https://artist-image-generator.com/product/woo-product-ai-image-customizer-to-sell-personalized-products/" title="Purchase Artist Image Generator Pro Licence key" target="_blank" class="button button-primary" style="width :100%; text-align:center;">
                            Buy Artist Image Generator - Licence Key + Product AI Image Customizer
                        </a>
                    </p>
                    <p>Customers can create <strong>personalized image designs by topic or freehand</strong>.</p>
                    <p>
                        Read official <a href="https://artist-image-generator.com/woocommerce-product-ai-image-customizer-plugin/" target="_blank" title="Product AI Image Customizer">blog post</a>
                    </p>
                    <iframe width="100%" height="315" src="https://www.youtube.com/embed/LiwKbuzT3RA?si=5nmzYSLfNmbH14xt" title="Artist Image Generator - Product AI Image Customizer" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            <?php endif; ?>
    </script>

<?php // Template for about tab. 
?>
<script type="text/html" id="tmpl-artist-image-generator-about">
    <div class="aig-container aig-container-3">
        <div class="card">
            <h2 class="title">
                <?php echo esc_html(Artist_Image_Generator_Constant::PLUGIN_FULL_NAME); ?>
            </h2>
            <p>
                <strong>This plugin was created by me, <a href="https://www.pierrevieville.fr" title="Visit website" target="_blank">Pierre Viéville</a>.</strong>
            </p>
            <p>
                I have been a freelance developer for 10 years.
                <strong><?php echo esc_html(Artist_Image_Generator_Constant::PLUGIN_FULL_NAME); ?></strong> is my first Wordpress plugin. I want to help the Wordpress community to improve the creativity of their content.
            </p>
            <p>
                That's why I made a plugin allowing you to generate <u>royalty-free images</u> that you can use anywhere on your site: media library, blog posts, pages, etc.
            </p>
            <p>
                I hope this plugin will be useful to you in the creation of your new content. If you have any question about this one, feel free to check out my web links.
            </p>
        </div>
        <div class="card">
            <h2 class="title">
                How is it working ?
            </h2>
            <p>
                <strong>This plugin is an integration of OpenAI API with DALL·E.</strong>
            </p>
            <p>
                DALL·E can create original, realistic images and art from a text description. It can combine concepts, attributes, and styles.
                This AI has learned the relationship between images and the text used to describe them.
            </p>
            <p>
                Basically the user input some text describing the images he wants. 1-10 images are generated.
                Then the user can select some images and add them to the Wordpress medias library, ready to use
                for a page or a post blog.
            </p>
            <p>
                The images generated are licenced free for any kind of usage. That are YOUR creations.
            </p>
        </div>
        <div class="card">
            <h2 class="title">
                Wanna help to improve this plugin ?
            </h2>
            <p>
                <strong>This plugin is free-to-use and generate royalty-free images for you. If you want to support my work, feel free to :</strong>
            </p>
            <p>1. share your issues</p>
            <p>2. submit your pull request (PR)</p>
            <p>3. support the developer by a donation</p>
            <p>
                Theses things can be done on the
                <a href="https://github.com/Immolare/<?php echo esc_attr($this->plugin_name); ?>" title="Visit Github" target="_blank">
                    <?php echo esc_html(Artist_Image_Generator_Constant::PLUGIN_FULL_NAME); ?>'s Github page
                </a>.
            </p>
            <p>
                Thanks a lot for using my plugin !
            </p>
        </div>
        <div class="card">
            <h2 class="title">
                Artist Image Generator: Product AI Image Customizer
            </h2>
            <p>
                <strong>Turn shoppers into designers! This plugin empowers customers to personalize products with AI in your WooCommerce store. Unique products, happy customers, boosted sales.
                <a href="https://artist-image-generator.com/product/woo-product-ai-image-customizer-to-sell-personalized-products/" title="Visit Product Page" target="_blank">
                    Take a look here</a>.
                </strong>
            </p>
            <?php $image_url = plugin_dir_url( dirname( __FILE__ ) ) . 'img/ai-image-customizer.png'; ?>
            <img style="width:100%" src="<?php echo esc_url($image_url); ?>" alt="AI Image Customizer render" />
        </div>
        <div class="card">
            <h2 class="title">
                TDMRep: Copyright your website data from AIs
            </h2>
            <p>
                <strong>TDMRep is a plugin that lets you control how robots and AIs like ChatGPT and Bard access your content. It integrates with the TDM Reservation Protocol to help you safeguard your copyright.
                <a href="https://fr.wordpress.org/plugins/tdmrep/" title="Visit TDMRep" target="_blank">
                    Visit plugin page</a>.
                </strong>
            </p>
            <?php $image_url = plugin_dir_url( dirname( __FILE__ ) ) . 'img/tdmrep.png'; ?>
            <img style="width:100%" src="<?php echo esc_url($image_url); ?>" alt="TDMRep render" />
        </div>
        
    </div>
</script>

<?php
endif;
