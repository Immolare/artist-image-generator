=== Artist Image Generator ===
Contributors: Immolare
Tags: Image creation, OpenAI, DALL·E 2, AI, Artifical Intelligence, Image variation, creativity
Donate link: https://github.com/Immolare/artist-image-generator#make-a-donation-to-support-this-plugin-development
Requires at least: 5.9
Tested up to: 6.1.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Artist Image Generator is a Wordpress plugin using the power of AI to create royality-free images from scratch for your Wordpress website.



== Description ==
### Why Artist Image Generator ?

**Artist Image Generator** is an image creation tool using DALL·E 2. DALL·E 2 is a new artifical intelligence service provided by OpenAI (creators of ChatGPT) which allow users to create images from a text description they provide.

We\'ve integrated this tool in a Wordpress plugin to improve the creativity of the WP community. From there **you have access to a powerful AI assistant for helping you illustrate your blog posts, pages and more**.

You can stop looking for royalty free images on the internet and just use our image creation tool to helping you illustrate any element of your Wordpress site.

### What are the possibilities of Artist Image Generator ?

With **Artist Image Generator** you can easily :

- **Create images from text input** :  provide a description of what you want, define the size and the number of images the AI needs to generate. The plugin will makes you (1-10) images based on your text in a artistic or realistic way.

- **Make an image variation from a .png file** :  provide a .png file, a description of the image, define the size and the number of images the AI needs to generate. The plugin will makes you (1-10) images variation from your file.

- **Save the images you created yourself** : your creations can be saved in the Wordpress Media Library to use them wherever you want. Just selected the images you want to keep and add it to the WP library.

### Artist Image Generator\'s demo :

https://www.youtube.com/watch?v=nEeR_qmkvWg

### What are advantages of our plugin ?

- **An AI assistant helping you illustrate your posts** : no need to search random images on Internet, just describe what you want and let the AI do the work for you in few seconds

- **SEO friendly** : when you save your images, the plugin will automaticaly rename the generated files in a SEO way using the description you provided. Then, the plugin will add some \"alt text\" and \"title\"  to help you improve your website SEO.

- **Begginer friendly, light and easy to use** : contrary to other plugins you can find, there is only 2 core functionnalities that are really easy to use. Ideal for Wordpress users which are not developers and want some easy functions to generate images.

- **Easy way to connect** : you just need an **OpenAI API Key to work**. All the steps are described in the settings page of the plugin, in a pedagogic way.

- **Royalty-free images** - Use the images you create on your website without worrying about licensing issues. You are the creator, all the image belongs to you and are free to use.

- **Unlimited users** : Any WordPress user on any site can use Artist Image Generator to create new images from scratch.

- **Unlimited sites** : You can use the plugin on multiple websites. It is just a tools to generate image and save them in the Wordpress Media Library.

- **Free to use, no extra licence** : Artist Image Generator provide a free way to use an artifical intelligence for the Wordpress community. All the functionnalities are free.

- **Fully Translatable** : The plugin is translatable in any language the classic way (.po, .mo files)

### Privacy

The Artist Image Generator doesn\'t collect any data. When you prompt a file or description, the file / description is sent to the DALL·E 2 API. The API returns a response with the images generated. From there the plugin just use the description to rename and fill the image metadatas for your Wordpress website.

### Credits

- [OpenAI GPT-3 Api Client in PHP](https://github.com/orhanerday/open-ai)
- [OpenAI - DALL·E 2](https://openai.com/dall-e-2/)
- [Pierre Viéville](https://www.pierrevieville.fr/)
- [Pierre Viéville\'s blog](https://developpeur-web.site/)


== Installation ==
### Install the plugin

The Artist Image Generator plugin\'s installation is easy :

- Search the Artist Image Generator plugin in the Wordpress extensions library and click \"Install\"
- Or download the zip archive file and upload it through the \"upload plugin\" tools
- Click on \"Activate\" and the plugin is ready to configure

Globally install and activate the plugin like any other Wordpress extension. Then click on the \"Settings\" page link under the plugin name. The plugin is available in the Wordpress menu. Click under **Media Library > Image Generator > Settings tab** to configure.

### Configure the plugin

The plugin use an artificial intelligence called **DALL·E 2**, a tier service provided by OpenAI. To use it, you have to **generate a OpenAI API Key**. Don\'t worry, its easy !

Once you are on the plugin\'s \"Settings\" tab, you\'ll have all the instructions to create an OpenAI API key :

- Sign up / Log in into OpenAI developer portail : https://openai.com/api/
- In **User > View API keys, create a new secret key** : https://platform.openai.com/account/api-keys
- Copy and paste the new secret key in the **OPENAI_API_KEY** field.
- Press \"Save changes\" and **you are ready to use the plugin**.




== Frequently Asked Questions ==
= Is the AI good to create new image from prompt ? =

Yes quite a lot. Just provide a full description of what you need. The AI will generate some image and you ca then fine tunes and reroll the results as you want according to the API limitations. Heads up ! This is an AI, so the results are not every time the best looking images ever according to your description, but in general this is an epic way to generate illustration

= What kind of images you can generate ? =

You can generate drawings, painting, realistic, artistic images with the plugin. Just provide the style you want and the AI will take care of generating the rights styled image for you.

= Is the Artist Image Generator free ? =

Yes it is. All the functionnality are developed for free for the Wordpress Community.

= What is the copyright license of images I create? =

All generated images you create are public domain and are yours to license as you see fit. We retain no rights to them. Because the public domain is not a unified concept across legal jurisdictions, the specific license of generated images is that of the [CC0 1.0 Universal Public Domain Dedication](https://creativecommons.org/publicdomain/zero/1.0/).

= What restrictions are there for image generation ? =

Yes. We provide just a tool to use DALL·E 2 service so we are not responsible of what you generate. Keep in mind DALL·E 2 have restrictions on image creation and variations so please respect  that to use correctly the service. 

For an up to date list of **what you can\'t do using DALL·E 2 service**, please read theses official links :

- [OpenAI - Blog post about restrictions](https://help.openai.com/en/articles/6338764-are-there-any-restrictions-to-how-i-can-use-dall-e-2-is-there-a-content-policy)
- [OpenAI - Content Policy](https://labs.openai.com/policies/content-policy)

= Is there a rate limitation for using the API =

Yes. Read the rates limits on the official documentation : [OpenAI - Rate Limits](https://platform.openai.com/docs/guides/rate-limits/overview)

- The AI generate only 3 images dimensions. All generated image will be on ratio format 1:1 (square).
- If you need to do an image variation, your uploaded file needs to be a .png file <= 4MB and at format 1:1.

== Changelog ==
1.0 - 2023-02-14
----------------------------------------------------------------------
- Initial release