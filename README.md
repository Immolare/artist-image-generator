# Artist Image Generator
Artist Image Generator is a Wordpress plugin to create AI generated royality-free images from scratch. Developed by [Pierre Viéville](https://www.pierrevieville.fr/).

![artist-image-generator-github](https://user-images.githubusercontent.com/11742929/218420318-66f0c7e5-eddb-41e4-831c-4b582b82f193.png)

## How it works ?

The extension uses the API of OpenAI (ChatGPT) and their new artificial intelligence service [DALL·E 2](https://openai.com/dall-e-2/).

DALL·E 2 allows you to [generate images from text input](https://openai.com/blog/dall-e/), but also to generate variations of images from a .png file.

https://user-images.githubusercontent.com/11742929/218429935-96e05c48-3506-4c0a-ab10-c81ede4746c9.mp4

From there you have the capacity to save your generated images into the Wordpress Media Library and use your images everywhere in your website : blog post, page, plugins, etc.

## About the royality-free images generated

According to the principles of this AI service, **all your created images belongs to you**. You can use them for anything because **you are the creator** !

## Install and settings

### Download, install and activate

To use Artist Image Generator, you can download it from the Wordpress Official Store (link incoming) or you can download the latest release on this Github page.

Install and activate the plugin like any other Wordpress extension. Click on the "Settings" page link under the plugin name. From the Wordpress menu, click under **Media Library > Image Generator > Settings tab**.

### Configure an OpenAI API key

Once you are on the Settings tab, you'll have all the instruction to create an OpenAI API key :

- Sign up / Log in into OpenAI developer portail : https://openai.com/api/
- In User > View API keys, create a new secret key : https://platform.openai.com/account/api-keys
- Copy and paste the new secret key in the OPENAI_API_KEY field.
- Press "Save changes" and you are done.

## Usage

From there you got 2 tabs : "Generate" and "Variate".

### "Generate" tab

To create new images from scratch using a text description of the image you want. There are 3 fields :

- Prompt : add a text description of the image you want to create (ie : "An astronaut on a banana").
- Size in pixels : chose the right format for your image (256x256 / 512x512 / 1024x1024).
- Number of images : chose a number of images to be created from 1 to 10 images.

### "Variation" tab

To create new images from scratch using a text description of the image you want. There are 4 fields :

- PNG File : add a .png file so the AI can reuse it to generate variants.
- Prompt (not required) : add a text description of the image for SEO purpose (ie : "An astronaut on a banana").
- Size in pixels : chose the right format for your image (256x256 / 512x512 / 1024x1024).
- Number of images : chose a number of images to be created from 1 to 10 images.

### When the results appears 

Once you click "Generate Image(s)" button, the script will create new images from your description entered previously. On each result, you can click on a "Add to Media Library" button which will save your image into the Wordpress Media Library. 

When its saved into the library, the image will be renamed with the "Prompt" field value you provided. This makes image generation **SEO friendly**. You don't have to rename the image then fill in the "alt text" and "title" fields.

![image](https://user-images.githubusercontent.com/11742929/218435399-5fa0f52f-20f3-4f42-8fba-13b32a83142f.png)

Your images will be available everywhere you can use your Wordpress Media Library like any other image you uploaded before.

### Usage limits 

Read the rates limits on the official documentation : https://platform.openai.com/docs/guides/rate-limits/overview

- The AI generate only 3 images dimensions. All generated image will be on ratio format 1:1 (square).
- If you need to do an image variation, your uploaded file needs to be a .png file <= 4MB and at format 1:1.

## Make a donation to support this plugin development

This plugin is free and made available to the Wordpress community. To allow me to effectively maintain this plugin, feel free to make a donation. To be credited as donator you can email me at **contact@pierrevieville.fr**.

- **BTC** : 3KtmAMgtkusp1a7UCtThQF4DW2uaeHQsuF
- **ETH** : 0x30d2ec629a16fb035d19e5c0e3e06bdf75ee562a
- **LTC** : ltc1q2r3wht2mz4m97yw3mxq823rkgp82e8uz09d6dr

## Donators

/* Be the first to appears here */

## Credits

- [OpenAI GPT-3 Api Client in PHP](https://github.com/orhanerday/open-ai)
- [OpenAI - DALL·E 2](https://openai.com/dall-e-2/)
- [Pierre Viéville](https://www.pierrevieville.fr/)
- [Pierre Viéville's blog](https://developpeur-web.site/)
