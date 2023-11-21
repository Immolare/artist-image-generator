(function ($) {
    'use strict';

    const data = aig_data;
    const maxCanvasWidth = 450;
    const maxCanvasHeight = 450;
    var action = '';

    const templates = {
        generate: wp.template('artist-image-generator-generate'),
        variate: wp.template('artist-image-generator-variate'),
        edit: wp.template('artist-image-generator-edit'),
        editDemo: wp.template('artist-image-generator-edit-demo'),
        public: wp.template('artist-image-generator-public'),
        settings: wp.template('artist-image-generator-settings'),
        about: wp.template('artist-image-generator-about'),
        notice: wp.template('artist-image-generator-notice'),
        result: wp.template('artist-image-generator-result'),
        formImage: wp.template('artist-image-generator-form-image'),
        formPrompt: wp.template('artist-image-generator-form-prompt'),
        formModel: wp.template('artist-image-generator-form-model'),
        formSize: wp.template('artist-image-generator-form-size'),
        formN: wp.template('artist-image-generator-form-n'),
    };

    const tabSelectors = {
        generate: '#tab-container-generate',
        variate: '#tab-container-variate',
        edit: '#tab-container-edit',
        public: '#tab-container-public',
        settings: '#tab-container-settings',
        about: '#tab-container-about',
    };

    function buildTab(tabSelector, tabTemplate, data) {
        const $tabContainer = $(tabSelector);
        const html = tabTemplate(data);
        $tabContainer.html(html);

        const $tbodyContainer = $tabContainer.find('.tbody-container');

        if (tabSelector === '#tab-container-variate' || tabSelector === '#tab-container-edit') {
            $tbodyContainer.append(templates.formImage(data));
        }

        if (tabSelector !== '#tab-container-public') {
            const words = tabSelector.split('-');
            const lastWord = words[words.length - 1];
            action = lastWord;

            if (tabSelector === '#tab-container-generate') {
                $tbodyContainer.append(templates.formModel(data));
            }

            $tbodyContainer.append(templates.formPrompt(data));
            $tbodyContainer.append(templates.formSize(data));
            $tbodyContainer.append(templates.formN(data));

            if (tabSelector === '#tab-container-generate') {
                // Appeler la fonction restoreFieldValues au chargement de la page pour restaurer les valeurs depuis le stockage local
                restoreFieldValues();

                const modelSelect = document.getElementById("model");
                const sizeSelect = document.getElementById("size");
                const nSelect = document.getElementById("n");

                modelSelect.addEventListener("change", handleModelChange);
                sizeSelect.addEventListener("change", handleSizeChange);
                nSelect.addEventListener("change", handleNChange);

                // Appeler la fonction handleModelChange au chargement de la page pour s'assurer que les règles sont appliquées
                handleModelChange();
                handleSizeChange();
                handleNChange();
            }

            $tabContainer.find('.notice-container').append(templates.notice(data));
            $tabContainer.find('.result-container').append(templates.result(data));
        }
    }


    // Fonction pour gérer le changement de sélection du champ "Size"
    function handleSizeChange() {
        const sizeSelect = document.getElementById("size");

        // Enregistrer la valeur du champ "Size" dans le stockage local
        localStorage.setItem("selectedSize", sizeSelect.value);
    }

    // Fonction pour gérer le changement de sélection du champ "N"
    function handleNChange() {
        const nSelect = document.getElementById("n");

        // Enregistrer la valeur du champ "N" dans le stockage local
        localStorage.setItem("selectedN", nSelect.value);
    }

    // Fonction pour gérer le changement de sélection du champ "Model"
    function handleModelChange() {
        const modelSelect = document.getElementById("model");
        const sizeSelect = document.getElementById("size");
        const nSelect = document.getElementById("n");

        // Récupérer la valeur sélectionnée dans le champ "Model"
        const selectedModel = modelSelect.value;

        // Définir le tableau des tailles autorisées en fonction du modèle sélectionné
        let allowedSizes = [];
        if (selectedModel === "dall-e-3") {
            allowedSizes = ["1024x1024", "1024x1792", "1792x1024"];
        } else {
            allowedSizes = ["256x256", "512x512", "1024x1024"];
        }

        // Vider les options actuelles du champ "Size"
        sizeSelect.innerHTML = "";

        // Ajouter les nouvelles options en fonction du tableau des tailles autorisées
        allowedSizes.forEach(function (size) {
            const option = document.createElement("option");
            option.value = size;
            option.text = size;
            sizeSelect.add(option);
        });

        // Sélectionner la première option si elle est disponible, sinon, sélectionner la première option
        if (sizeSelect.options.length > 0) {
            const firstAvailableOption = Array.from(sizeSelect.options).find(option => !option.disabled);
            sizeSelect.value = firstAvailableOption ? firstAvailableOption.value : sizeSelect.options[0].value;
        }

        // Réinitialiser le champ "n" en sélectionnant la première option disponible
        nSelect.selectedIndex = 0;

        // Limiter le champ "n" à 1 si le modèle est "dall-e-3", sinon réactiver toutes les options pour le champ "n"
        for (let j = 1; j < nSelect.options.length; j++) {
            nSelect.options[j].style.display = selectedModel === "dall-e-3" ? "none" : "block";
            nSelect.options[j].disabled = selectedModel === "dall-e-3";
        }

        // Limiter le champ "n" à 1 si le modèle est "dall-e-3", sinon autoriser les valeurs de 1 à 10
        nSelect.options[0].disabled = selectedModel === "dall-e-3";
        for (let j = 1; j < nSelect.options.length; j++) {
            nSelect.options[j].style.display = selectedModel === "dall-e-3" ? "none" : "block";
            nSelect.options[j].disabled = false;
        }

        // Enregistrer la valeur du champ "Model" dans le stockage local
        localStorage.setItem("selectedModel", selectedModel);
        // Enregistrer la valeur du champ "Size" dans le stockage local
        localStorage.setItem("selectedSize", sizeSelect.value);
        // Enregistrer la valeur du champ "N" dans le stockage local
        localStorage.setItem("selectedN", nSelect.value);
    }

    // Fonction pour restaurer les valeurs des champs depuis le stockage local
    function restoreFieldValues() {
        const modelSelect = document.getElementById("model");
        const sizeSelect = document.getElementById("size");
        const nSelect = document.getElementById("n");

        // Restaurer la valeur du champ "Model" depuis le stockage local
        const storedModel = localStorage.getItem("selectedModel");
        if (storedModel) {
            modelSelect.value = storedModel;
        }

        // Restaurer la valeur du champ "Size" depuis le stockage local
        const storedSize = localStorage.getItem("selectedSize");
        if (storedSize) {
            sizeSelect.value = storedSize;
        }

        // Restaurer la valeur du champ "N" depuis le stockage local
        const storedN = localStorage.getItem("selectedN");
        if (storedN) {
            nSelect.value = storedN;
        }
    }


    function addFabricDrawing(canvas) {
        const originalCanvas = canvas;
        const fabricCanvas = new fabric.Canvas(canvas.id, { backgroundImage: originalCanvas.toDataURL("image/png", 1) });

        fabricCanvas.isDrawingMode = true;

        // Customize the brush options
        let brushSize = 50;
        fabricCanvas.freeDrawingBrush.width = brushSize;
        fabricCanvas.freeDrawingBrush.color = 'rgba(0, 0, 0, 1)';
        fabricCanvas.freeDrawingBrush.globalCompositeOperation = 'source-out';

        // Create a new canvas element to store the mask
        const maskCanvas = document.createElement('canvas');
        maskCanvas.width = fabricCanvas.width;
        maskCanvas.height = fabricCanvas.height;
        const maskContext = maskCanvas.getContext('2d');

        // Listen to the path:created event
        fabricCanvas.on('path:created', function (e) {
            const path = e.path;
            path.globalCompositeOperation = 'destination-out';
            fabricCanvas.renderAll();

            // Check if the fabric canvas is usable
            if (fabricCanvas.width > 0 && fabricCanvas.height > 0) {
                // Clear the mask canvas and set a transparent background
                maskContext.clearRect(0, 0, maskCanvas.width, maskCanvas.height);
                maskContext.fillStyle = 'rgba(0, 0, 0, 0)'; // Transparent background
                maskContext.fillRect(0, 0, maskCanvas.width, maskCanvas.height);

                // Copy the fabric canvas content to the mask canvas
                maskContext.drawImage(fabricCanvas.getElement(), 0, 0, fabricCanvas.width, fabricCanvas.height);
                const maskImageData = maskContext.getImageData(0, 0, fabricCanvas.width, fabricCanvas.height);

                // Convert the mask canvas to transparent PNG data URL
                // Iterate through the mask image data and set alpha channel to 0 (transparent)
                for (let i = 0; i < maskImageData.data.length; i += 4) {
                    maskImageData.data[i + 3] = 0; // Set alpha channel to 0
                }

                // Put the modified mask image data back onto the mask canvas
                maskContext.putImageData(maskImageData, 0, 0);

                // Create a new canvas for the final result
                const resultCanvas = document.createElement('canvas');
                resultCanvas.width = fabricCanvas.width;
                resultCanvas.height = fabricCanvas.height;
                const resultContext = resultCanvas.getContext('2d');

                // Draw the original image on the result canvas
                resultContext.drawImage(originalCanvas, 0, 0);

                // Draw the mask on the result canvas
                resultContext.drawImage(maskCanvas, 0, 0);

                // Convert the result canvas to a blob
                resultCanvas.toBlob(function (blob) {
                    // Create a file from the blob
                    const file = new File([blob], 'mask.png', { type: 'image/png', lastModified: new Date().getTime() });

                    // Create a DataTransfer object to store the file
                    let container = new DataTransfer();
                    container.items.add(file);

                    // Assign the files to the input element
                    const maskInput = document.querySelector('#mask');
                    maskInput.files = container.files;
                }, 'image/png');
            }
        });

        // Create Undo button
        const undoButton = document.createElement('button');
        undoButton.innerHTML = '<span class="dashicons dashicons-undo"></span>';
        undoButton.type = 'button';
        undoButton.className = 'button-link';
        undoButton.addEventListener('click', function (e) {
            e.preventDefault();

            fabricCanvas.undo();
        });

        // Create Brush Size range input
        const brushSizeInput = document.createElement('input');
        brushSizeInput.id = 'aig_brush_size_input';
        brushSizeInput.type = 'range';
        brushSizeInput.min = '1';
        brushSizeInput.max = '100';
        brushSizeInput.value = brushSize;
        brushSizeInput.addEventListener('input', function () {
            brushSize = parseInt(brushSizeInput.value);
            fabricCanvas.freeDrawingBrush.width = brushSize;
        });

        // Create Brush Size range input
        //const brushSizeInputLabel = document.createElement('label');
        //brushSizeInputLabel.for = 'aig_brush_size_input';
        //brushSizeInputLabel.textContent = 'Brush size';

        // Create container div for buttons
        const buttonContainer = document.createElement('div');
        buttonContainer.className = 'button-container';
        //buttonContainer.style.display = 'grid';
        //buttonContainer.appendChild(brushSizeInputLabel);
        buttonContainer.appendChild(brushSizeInput);
        buttonContainer.appendChild(undoButton);

        // Append the button container and fabric canvas to the parent container
        const parentContainer = document.querySelector('#aig_cropper_preview');
        parentContainer.style.position = 'relative';
        parentContainer.appendChild(buttonContainer);

        // Function to undo the last path
        fabricCanvas.undo = function () {
            const paths = fabricCanvas.getObjects('path');
            if (paths.length > 0) {
                const lastPath = paths[paths.length - 1];
                fabricCanvas.remove(lastPath);
                fabricCanvas.renderAll();
            }
        };
    }

    function addDrawingTool(container, canvasId, cropper) {
        const croppedCanvas = cropper.getCroppedCanvas({
            width: maxCanvasWidth,
            height: maxCanvasHeight // input value
        });
        croppedCanvas.id = 'canvas-draw';
        const cropperContainer = cropper.container;
        cropper.destroy(); // Destroy the cropper

        cropperContainer.appendChild(croppedCanvas); // Append the original canvas back to the container

        const cancelButton = document.createElement('button');
        cancelButton.type = 'button';
        cancelButton.textContent = aig_ajax_object.cancelLabel;;
        cancelButton.className = 'button aig_cancel_button';
        cancelButton.style.width = '100%';
        cancelButton.addEventListener('click', function (e) {
            e.preventDefault();
            container.removeChild(cancelButton);
            const canvas = document.getElementById('aig_cropper_canvas_area');
            canvas.hidden = true;
            addInputFileCropperHandler(true); // Initialize a new cropper on the original canvas
        });
        container.appendChild(cancelButton);

        if (action === 'edit') {
            const fabricButton = document.createElement('button');
            fabricButton.type = 'button';
            fabricButton.textContent = aig_ajax_object.maskLabel;
            fabricButton.className = 'button';
            fabricButton.style.width = '100%';
            fabricButton.addEventListener('click', function (e) {
                e.preventDefault();
                addFabricDrawing(croppedCanvas);

                this.parentNode.removeChild(this);
            });

            container.appendChild(fabricButton);
        }
    }

    function showDrawingButtons(cropper) {
        const drawingToolContainer = document.getElementById('drawing-button-container');
        drawingToolContainer.innerHTML = '';
        drawingToolContainer.style.display = 'block';

        const canvas = document.getElementById('aig_cropper_canvas_area');
        addDrawingTool(drawingToolContainer, canvas.id, cropper);
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

    async function addInputFileCropperHandler(refresh) {
        refresh = refresh || false;
        const input = document.querySelector('input[type="file"].aig_handle_cropper');
        const originalInput = document.getElementById('original');

        async function init(fileInput, keepOriginal) {
            fileInput = fileInput || input;
            keepOriginal = keepOriginal || false;
            if (fileInput.files && fileInput.files[0]) {
                const dataUrl = await readFileAsDataURL(fileInput.files[0]);
                const image = await createImage(dataUrl);

                await imageLoaded(image);

                const container = createContainer(maxCanvasWidth, maxCanvasHeight);
                const canvas = await createCanvasFromImage(image);
                container.appendChild(canvas);

                const cropperArea = document.getElementById('aig_cropper_canvas_area');
                cropperArea.innerHTML = '';
                cropperArea.hidden = false;
                cropperArea.appendChild(container);

                const cropper = createCropper(canvas);
                const cropButton = createCropButton(canvas, cropper, input);
                const cropperPreview = document.getElementById('aig_cropper_preview');
                cropperPreview.innerHTML = '';


                const imgElement = document.createElement('img');
                imgElement.src = '';
                imgElement.className = 'hidden';
                imgElement.width = 210;
                imgElement.height = 210;

                const divDrawingTools = document.createElement('div');
                divDrawingTools.id = 'drawing-button-container';

                const divParent = document.createElement('div');
                divParent.id = 'aig_cropper_preview_inner';

                divParent.appendChild(imgElement);
                divParent.appendChild(cropButton);
                divParent.appendChild(divDrawingTools);
                cropperPreview.appendChild(divParent);

                // Update original input with original image
                if (!keepOriginal) {
                    const originalFile = new File([input.files[0]], 'original.png', {
                        type: 'image/png',
                        lastModified: new Date().getTime()
                    });
                    const originalContainer = new DataTransfer();
                    originalContainer.items.add(originalFile);
                    originalInput.files = originalContainer.files;
                }
            }
        }

        if (refresh) {
            init(originalInput, true);
        }
        else {
            if (input) {
                input.addEventListener('change', async function () {
                    init();
                });
            }
        }
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
                let imgSrc = cropper.getCroppedCanvas({
                    width: 210,
                    height: 210 // input value
                }).toDataURL("image/png", 1);

                const previewImg = document.querySelector('#aig_cropper_preview_inner img');
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
        cropButton.classList.add('button');
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
                showDrawingButtons(cropper);
            }, 'image/png');

            this.parentNode.removeChild(this);

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

        var template = templates[action];

        if (action === "edit" && !aig_ajax_object.valid_licence) {
            template = templates.editDemo;
        }

        buildTab('#tab-container-' + action, template, data);

        $tabEl.append($(tabSelectors[action]));
        if (action === "variate" || (action === "edit" && aig_ajax_object.valid_licence)) {
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
            edit: aig_ajax_object.editLabel,
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
                edit: {
                    text: labels.edit,
                    priority: 80,
                },
            });
        };

        jQuery(document).ready(function ($) {
            if (wp.media) {
                let needRefreshItem = false;
                let needRefreshTab = false;
                wp.media.view.Modal.prototype.on("open", function () {
                    const activeItem = $('body').find('.media-modal-content').find('.media-router button.media-menu-item.active')[0];
                    const menuItemId = $(activeItem).attr('id');
                    const menuItemIdWithoutPrefix = menuItemId.replace(/^menu-item-/, '');
                    if (["generate", "variate", "edit"].includes(menuItemIdWithoutPrefix)) {
                        refreshMyTabContent(data, menuItemIdWithoutPrefix);
                        needRefreshItem = true;
                    } else if (needRefreshItem && wp.media.frame.content.get() !== null && wp.media.frame.content.get().collection !== undefined) {
                        wp.media.frame.content.get().collection.props.set({ ignore: (+ new Date()) });
                        needRefreshItem = false;
                    }
                    else {
                        needRefreshItem = false;
                    }
                });

                $(wp.media).on('click', '.media-router button.media-menu-item', function (e) {
                    const activeTab = $('body').find('.media-modal-content').find('.media-router button.media-menu-item.active')[0];
                    const menuTabId = $(activeTab).attr('id');
                    const menuTabIdWithoutPrefix = menuTabId.replace(/^menu-item-/, '');
                    if (["generate", "variate", "edit"].includes(menuTabIdWithoutPrefix)) {
                        needRefreshTab = true;
                        refreshMyTabContent(data, menuTabIdWithoutPrefix);
                    } else if (needRefreshTab && wp.media.frame.content.get() !== null && wp.media.frame.content.get().collection !== undefined) {
                        wp.media.frame.content.get().collection.props.set({ ignore: (+ new Date()) });
                        needRefreshTab = false;
                    }
                    else {
                        needRefreshTab = false;
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
        } else if ($(tabSelectors.edit).length) {
            var template = aig_ajax_object.valid_licence ? templates.edit : templates.editDemo;
            buildTab(tabSelectors.edit, template, data);
            addInputFileCropperHandler();
        } else if ($(tabSelectors.about).length) {
            buildTab(tabSelectors.about, templates.about, data);
        } else if ($(tabSelectors.public).length) {
            buildTab(tabSelectors.public, templates.public, data);
        } else {
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
        .then(() => fetch(aig_ajax_object.drawing_tool_script_path))
        .then(response => response.text())
        .then(scriptContent => {
            const script = document.createElement('script');
            script.textContent = scriptContent;
            document.head.appendChild(script);
            return loadScript(aig_ajax_object.drawing_tool_script_path);
        })
        .then(() => {
            aig_ajax_object.is_media_editor
                ? initAdminMediaModal()
                : initAdminPage();
        })
        .catch(error => {
            console.error('Error loading script', error);
        });


})(jQuery);