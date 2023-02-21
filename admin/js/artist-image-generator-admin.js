(function ($) {
    'use strict';

    const data = aig_data;
    const maxCanvasWidth = 350;
    const maxCanvasHeight = 350;

    const templates = {
        generate: wp.template('artist-image-generator-generate'),
        variate: wp.template('artist-image-generator-variate'),
        settings: wp.template('artist-image-generator-settings'),
        about: wp.template('artist-image-generator-about'),
        notice: wp.template('artist-image-generator-notice'),
        result: wp.template('artist-image-generator-result'),
        formImage: wp.template('artist-image-generator-form-image'),
        formPrompt: wp.template('artist-image-generator-form-prompt'),
        formSize: wp.template('artist-image-generator-form-size'),
        formN: wp.template('artist-image-generator-form-n'),
    };

    const tabSelectors = {
        generate: '#tab-container-generate',
        variate: '#tab-container-variate',
        settings: '#tab-container-settings',
        about: '#tab-container-about',
    };

    function buildTab(tabSelector, tabTemplate, data) {
        const $tabContainer = $(tabSelector);
        const html = tabTemplate(data);
        $tabContainer.html(html);

        const $tbodyContainer = $tabContainer.find('.tbody-container');

        if (tabSelector === '#tab-container-variate') {
            $tbodyContainer.append(templates.formImage(data));
        }

        $tbodyContainer.append(templates.formPrompt(data));
        $tbodyContainer.append(templates.formSize(data));
        $tbodyContainer.append(templates.formN(data));

        $tabContainer.find('.notice-container').append(templates.notice(data));
        $tabContainer.find('.result-container').append(templates.result(data));
    }

    function addMediaHandler() {
        $('.add_as_media').on('click', function (e) {
            e.preventDefault();

            const $that = $(this);
            const data = {
                action: 'add_to_media',
                url: $that.parent().find('img').attr('src'),
                description: $('#prompt').val()
            };

            const $parent = $that.parent();
            $parent.find('h2 > .spinner').addClass('is-active');

            jQuery.post(aig_ajax_object.ajax_url, data, function (response) {
                if (response.success) {
                    $that.hide("slow", function () {
                        $parent.find('h2 > .spinner').removeClass('is-active').remove();
                        $parent.find('h2 > .dashicons').addClass('is-active');
                    });
                }
            });

            return false;
        });
    }

    function imageLoaded(image) {
        return new Promise((resolve, reject) => {
            // Vérifie si l'image est déjà chargée
            if (image.complete) {
                resolve();
            } else {
                // Écoute l'événement de chargement de l'image
                image.addEventListener("load", () => {
                    resolve();
                });

                // Écoute l'événement d'erreur de chargement de l'image
                image.addEventListener("error", () => {
                    reject(new Error("Error loading image."));
                });
            }
        });
    }

    async function addInputFileCropperHandler() {
        const input = document.querySelector('input[type="file"].aig_handle_cropper');
        input.addEventListener('change', async function () {
            if (input.files && input.files[0]) {
                const dataUrl = await readFileAsDataURL(input.files[0]);
                const image = await createImage(dataUrl);
                
                await imageLoaded(image);

                const container = createContainer(maxCanvasWidth, maxCanvasHeight);
                const canvas = await createCanvasFromImage(image);
                container.appendChild(canvas);

                const cropperArea = document.getElementById('aig_cropper_canvas_area');
                cropperArea.innerHTML = '';
                cropperArea.appendChild(container);

                const cropper = createCropper(canvas);
                const cropButton = createCropButton(canvas, cropper, input);

                const cropperPreview = document.getElementById('aig_cropper_preview');
                cropperPreview.innerHTML = '<img src="" class="hidden" width="210" height="210"/>';
                cropperPreview.appendChild(cropButton);
            }
        });
    }

    async function createImage(dataUrl) {
        const image = new Image();
        image.src = dataUrl;
        await new Promise(resolve => { image.onload = resolve });
        return image;
    }

    async function updateImageSize(image) {
        return new Promise((resolve, reject) => {
            // Crée un nouvel élément d'image pour s'assurer que l'image est chargée
            const newImage = new Image();
            newImage.onload = function () {
                // Met à jour les dimensions de l'image
                image.width = newImage.width;
                image.height = newImage.height;
                // Renvoie les dimensions de l'image
                resolve({ width: image.width, height: image.height });
            };
            // Charge l'image avec l'URL de l'image ou du canvas
            newImage.src = image.toDataURL ? image.toDataURL() : image.src;
        });
    }

    async function createCanvasFromImage(image) {
        const { width, height } = await updateImageSize(image);

        let imageWidth, imageHeight;

        if (width > height) {
            imageWidth = maxCanvasWidth;
            imageHeight = height * (maxCanvasWidth / width);
        } else {
            imageHeight = maxCanvasHeight;
            imageWidth = width * (maxCanvasHeight / height);
        }

        if (imageWidth > width) {
            imageWidth = width;
            imageHeight = width * (maxCanvasHeight / maxCanvasWidth);
        }

        if (imageHeight > height) {
            imageHeight = height;
            imageWidth = height * (maxCanvasWidth / maxCanvasHeight);
        }

        const canvas = document.createElement("canvas");
        canvas.width = imageWidth * 10;
        canvas.height = imageHeight * 10;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(image, 0, 0, canvas.width, canvas.height);

        return canvas;
    }

    function createContainer(width, height) {
        const container = document.createElement('div');
        container.style.width = width + 'px';
        container.style.height = height + 'px';
        return container;
    }

    function createCropper(canvas) {
        if (!(canvas instanceof HTMLCanvasElement)) {
            console.error("Error on canvas creation.");
            return null;
        }
        const cropper = new Cropper(canvas, {
            aspectRatio: 1 / 1,
            crop: function (event) {
                let imgSrc = this.cropper.getCroppedCanvas({
                    width: 210,
                    height: 210// input value
                }).toDataURL("image/png", 1);

                const previewImg = document.querySelector('#aig_cropper_preview img');
                previewImg.src = imgSrc;
                previewImg.classList.remove('hidden');
            }
        });
        return cropper;
    }

    function createCropButton(canvas, cropper, targetEventInput) {
        const cropButton = document.createElement('button');
        cropButton.setAttribute('role', 'button');
        cropButton.style.width = '100%';
        cropButton.innerHTML = aig_ajax_object.cropperCropLabel;
        cropButton.addEventListener('click', function (e) {
            e.preventDefault();
            const croppedCanvas = cropper.getCroppedCanvas({
                width: 450,
                height: 450,
            });

            croppedCanvas.toBlob(function (blob) {
                const file = new File([blob], 'cropped.png', { type: 'image/png', lastModified: new Date().getTime() });
                let container = new DataTransfer();
                container.items.add(file);
                targetEventInput.files = container.files;
            }, 'image/png');
            return false;
        });
        return cropButton;
    }

    function readFileAsDataURL(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.addEventListener("load", () => {
                resolve(reader.result);
            });

            reader.addEventListener("error", () => {
                reject(new Error("Error reading file."));
            });

            reader.readAsDataURL(file);
        });
    }

    function refreshMyTabContent(data, action) {
        const $tabEl = $('<div id="tab-container-' + action + '"></div>');
        $('body .media-modal-content .media-frame-content').empty().append($tabEl);

        buildTab('#tab-container-' + action, templates[action], data);

        $tabEl.append($(tabSelectors[action]));
        if (action === "variate") {
            addInputFileCropperHandler();
        }
        $tabEl.on('submit', 'form', function (e) {
            e.preventDefault();

            const formData = new FormData($tabEl.find('form')[0]);
            formData.append('action', 'admin_page');

            const spinner = '<div class="spinner is-active" style="margin-top: 0; margin-left:15px; float:none;"></div>';
            $(spinner).insertAfter($tabEl.find('form input[type="submit"]'));

            // Submit the form using AJAX
            jQuery.ajax({
                url: aig_ajax_object.ajax_url,
                type: 'post',
                processData: false,
                contentType: false,
                data: formData,
                success: function (response) {
                    $tabEl.find('.spinner').remove();
                    refreshMyTabContent(response, action);

                    addMediaHandler();
                },
            });
        });

        if (typeof wp.media.frames.modula !== 'undefined') {
            wp.media.frames.modula.content.get().collection.props.set({ ignore: (+ new Date()) });
        }
        if (typeof wp.media.frame.content.get() == null) {
            wp.media.frame.content.get().collection.props.set({ ignore: (+ new Date()) });
        }
    }

    function initAdminMediaModal() {
        const labels = {
            variate: aig_ajax_object.variateLabel,
            generate: aig_ajax_object.generateLabel,
        };
        const l10n = wp.media.view.l10n;
        const mediaFrameSelect = wp.media.view.MediaFrame.Select.prototype;

        mediaFrameSelect.browseRouter = function (routerView) {
            routerView.set({
                upload: {
                    text: l10n.uploadFilesTitle,
                    priority: 20,
                },
                browse: {
                    text: l10n.mediaLibraryTitle,
                    priority: 40,
                },
                generate: {
                    text: labels.generate,
                    priority: 60,
                },
                variate: {
                    text: labels.variate,
                    priority: 80,
                },
            });
        };

        jQuery(document).ready(function ($) {
            if (wp.media) {
                wp.media.view.Modal.prototype.on("open", function () {
                    const activeItem = $('body').find('.media-modal-content').find('.media-router button.media-menu-item.active')[0];
                    const innerTextActive = activeItem ? (activeItem.innerText).toLowerCase() : null;
                    if ([labels.generate.toLowerCase(), labels.variate.toLowerCase()].includes(innerTextActive)) {
                        refreshMyTabContent(data, innerTextActive);
                    }
                });

                let needRefresh = false;
                $(wp.media).on('click', '.media-router button.media-menu-item', function (e) {
                    const activeTab = $('body').find('.media-modal-content').find('.media-router button.media-menu-item.active')[0];
                    const innerTextActiveTab = activeTab ? (activeTab.innerText).toLowerCase() : null;
                    if ([labels.generate.toLowerCase(), labels.variate.toLowerCase()].includes(innerTextActiveTab)) {
                        needRefresh = true;
                        refreshMyTabContent(data, innerTextActiveTab);
                    } else if (needRefresh && wp.media.frame.content.get() !== null && wp.media.frame.content.get().collection !== undefined) {
                        wp.media.frame.content.get().collection.props.set({ ignore: (+ new Date()) });
                        needRefresh = false;
                    }
                    else {
                        needRefresh = false;
                    }
                });
            }
        });
    }

    function initAdminPage() {
        if ($(tabSelectors.settings).length) {
            buildTab(tabSelectors.settings, templates.settings, data);
        } else if ($(tabSelectors.variate).length) {
            buildTab(tabSelectors.variate, templates.variate, data);
            addInputFileCropperHandler();
        } else if ($(tabSelectors.about).length) {
            buildTab(tabSelectors.about, templates.about, data);
        } else if ($(tabSelectors.generate).length) {
            buildTab(tabSelectors.generate, templates.generate, data);
        }

        addMediaHandler();
    }

    function loadScript(url) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = url;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    fetch(aig_ajax_object.cropper_script_path)
        .then(response => response.text())
        .then(scriptContent => {
            const script = document.createElement('script');
            script.textContent = scriptContent;
            document.head.appendChild(script);
            return loadScript(aig_ajax_object.cropper_script_path);
        })
        .then(() => {
            aig_ajax_object.is_media_editor ?
                initAdminMediaModal() :
                initAdminPage()
            ;
        })
        .catch(error => {
            console.error('Error loading script', error);
        });

})(jQuery);