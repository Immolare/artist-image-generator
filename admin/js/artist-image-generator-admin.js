(function ($) {
    'use strict';

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Constantes ///////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    let data = { ...aig_data }; // defaultData is the constant data
    let maskFabricCanvas;
    let editFabricCanvas;
    let needMediaListRefresh = false;

    // aig_data is the default data
    const SCRIPT_CACHE = {}; // cache librairies
    const MAX_CANVAS = { width: 450, height: 450 };
    const IS_IN_IFRAME = window.self !== window.top;
    const SCOPE = IS_IN_IFRAME ? window.parent.document : document;
    const MIN_DIMENSIONS = 450;
    const CROPPED_DIMENSIONS = 210;
    const CROPPED_FILE_NAME = 'cropped.png';
    const CROPPED_FILE_TYPE = 'image/png';
    const IMAGE_QUALITY = 1;
    const MODEL_CONFIG = {
        "": { sizes: ["256x256", "512x512", "1024x1024"], nValues: [1, 2, 3, 4, 5, 6, 7, 8,  9, 10], qualities:[], styles:[] },
        "dall-e-3": { sizes: ["1024x1024", "1024x1792", "1792x1024"], nValues: [1], qualities:['standard','hd'], styles:['vivid','natural'] },
    };
    const TABS = ['generate', 'public', 'settings', 'variate', 'edit', 'about'];

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Router and Tabs //////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function createTabObject(callback) {
        return TABS.reduce((obj, tab) => {
            obj[tab] = callback(tab);
            return obj;
        }, {});
    }

    const TAB_CONTAINERS = createTabObject(tab => `tab-container-aig-${tab.toLowerCase()}`);
    const TAB_SELECTORS = createTabObject(tab => `.${TAB_CONTAINERS[tab.toLowerCase()]}`);
    const TEMPLATES = {
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
        formQuality: wp.template('artist-image-generator-form-quality'),
        formStyle: wp.template('artist-image-generator-form-style'),
        formN: wp.template('artist-image-generator-form-n'),
    };

    const VIEWS = createTabObject(tab => createView(TEMPLATES[tab], tab));

    // Créer une fonction d'usine pour générer les vues
    function createView(template, action) {
        if (undefined != wp && wp.media) {
            return wp.media.View.extend({
                className: TAB_CONTAINERS[action.toLowerCase()],
                template: template,
                action: action,
                toolbar: null,
                frame: null,
                selectable: false
            });
        }
    }

    function extendFrame(mediaFrame, views, labels) {
        const keys = Object.keys(views);
        //  waitForContentToBeReady get keys as keys and set a value to false
        let waitForContentToBeReady = Object.fromEntries(keys.map(key => [key, false]));

        return mediaFrame.extend({
            initialize: function () {
                mediaFrame.prototype.initialize.apply(this, arguments);
                // setup states
                const self = this;
                var State = wp.media.controller.State.extend({});

                 // Créer une promesse qui résout une fois que tous les états ont été ajoutés
                 keys.forEach(function (key, index) {
                    self.states.add([
                        new State({
                            id: key,
                            title: labels[key],
                            priority: index * 10
                        })
                    ]);

                    self.on('content:render:' + key, self[key + 'ContentRender'], self);
                    // fix FlBuiler refresh default tab
                    self.on('menu:activate:default', function () { 
                        const mode = self?.state()?.get('content');
                        if (mode && mode === key) {
                            waitForContentToBeReady[key] = true;
                        }
                    });
                });

                const eventsRefresh = [
                    'content:render:browse',
                    'content:activate:browse',
                ];
                // refresh media library when an image was added through the plugin
                eventsRefresh.forEach(function (event) {
                    self.on(event, function () {
                        if (needMediaListRefresh && self.content) {
                            const content = self.content.get();
                            
                            if (content && content.collection) {
                                content.collection.props.set({ ignore: (+ new Date()) });
                                needMediaListRefresh = false;
                            }
                        }
                    });
                });
            },
            browseRouter: function (routerView) {
                mediaFrame.prototype.browseRouter.apply(this, arguments);

                var currentState = wp.media.frame.state();
                console.log(currentState.id);

                if (currentState.id === 'insert' ||
                    currentState.id === 'library' ||
                    currentState.id === 'featured-image' ||
                    currentState.id === 'gallery') {
                    keys.forEach(function (key, index ) {
                        routerView.set(key, { text: labels[key], priority: 200+index });
                    });
                } else {
                    // If the current state is not 'insert', 'library' or 'featured-image' and the content is one of yours, force the view to 'browse'
                    const mode = this?.state()?.get('content');
                    if (keys.includes(mode)) {
                        this.state().set('content', 'browse');
                    }
                }
            },
            ...Object.fromEntries(keys.map(key => [key + "ContentRender", function () {
                // create new wp.media.view and set it to the content
                const view = new VIEWS[key];

                this.content.set(view);
                        
                let viewTemplate = TEMPLATES[view.action];
                if(view.action === "edit" && !aig_ajax_object.valid_licence) {
                    viewTemplate = TEMPLATES.editDemo;
                }

                const initTab = function () {
                    buildTab(view.$el, viewTemplate, data);
                            
                    // l'événement se declenche une seule fois
                    view.$el.off('submit').on('submit', 'form', function (e) {
                        handleFormSubmit(e, this).then(function (data) {
                            //buildTab(view.$el, viewTemplate, data);
                            view.$el.find('.notice-container').empty().append(TEMPLATES.notice(data));
                            view.$el.find('.result-container').empty().append(TEMPLATES.result(data));
                            addMediaHandler();
                        }.bind(this));
                    });
                }

                if (waitForContentToBeReady[key]) {
                    this.content.get().on('ready', function () {
                        initTab();
                        waitForContentToBeReady[key] = false;
                    });
                } else {
                    initTab();
                }
            }]))
        });

    }

    function handleFormSubmit(e, formElement) {
        e.preventDefault();

        const $form = $(formElement);
        const formData = new FormData($form.get(0));

        formData.append('action', 'admin_page');

        const spinner = '<div class="spinner is-active" style="margin-top: 0; margin-left:15px; float:none;"></div>';
        $(spinner).insertAfter($form.find('input[type="submit"]'));

        // Submit the form using AJAX
        return jQuery.ajax({
            url: aig_ajax_object.ajax_url,
            type: 'post',
            processData: false,
            contentType: false,
            data: formData
        }).then(function (response) {
            $form.find('.spinner').remove();
            return response;
        }).catch(function (jqXHR, textStatus, errorThrown) {
            console.error('Error: ' + textStatus, errorThrown);
            throw errorThrown;
        });
    }

    function initAdminMediaModal() {
        const labels = {
            variate: aig_ajax_object.variateLabel,
            edit: aig_ajax_object.editLabel,
            generate: aig_ajax_object.generateLabel,
        };

        const availableViews = {
            generate: VIEWS.generate,
            variate: VIEWS.variate,
            edit: VIEWS.edit,
        };

        if ( undefined != wp && wp.media ) {
            let mediaFrameSelect = wp.media.view.MediaFrame.Select;
            wp.media.view.MediaFrame.Select = extendFrame(
                mediaFrameSelect,
                availableViews,
                labels
            );
            let mediaFramePost = wp.media.view.MediaFrame.Post;
            wp.media.view.MediaFrame.Post = extendFrame(
                mediaFramePost,
                availableViews,
                labels
            );
        }
    }

    function initAdminPage() {
        const tabKeys = ['settings', 'variate', 'edit', 'about', 'public', 'generate'];

        for (let key of tabKeys) {
            if ($(TAB_SELECTORS[key]).length) {
                let template = TEMPLATES[key];

                if (key === 'edit' && !aig_ajax_object.valid_licence) {
                    template = TEMPLATES.editDemo;
                }

                buildTab($(TAB_SELECTORS[key]), template, aig_data);

                if (key === 'variate' || key === 'edit') {
                    addInputFileCropperHandler();
                }

                // Exit the loop once we've found a match
                break;
            }
        }

        addMediaHandler();
    }

    function buildTab($tab, template, data) {
        $tab.html(template(data));

        const tabClass = $tab.attr("class");
        const $tbodyContainer = $tab.find('.tbody-container');
    
        if (tabClass === TAB_CONTAINERS.variate ||
            tabClass === TAB_CONTAINERS.edit) {
            $tbodyContainer.append(TEMPLATES.formImage(data));
        }
    
        if (
            tabClass !== TAB_CONTAINERS.public &&
            tabClass !== TAB_CONTAINERS.settings &&
            tabClass !== TAB_CONTAINERS.about) {    
            $tbodyContainer.append(TEMPLATES.formModel(data));
            $tbodyContainer.append(TEMPLATES.formStyle({
                style: MODEL_CONFIG[data.model_input].styles,
                style_input: data.style_input
            }));
            $tbodyContainer.append(TEMPLATES.formQuality({
                quality: MODEL_CONFIG[data.model_input].qualities,
                quality_input: data.quality_input
            }));

            const $modelElParent = $tab.find("#model").closest("tr");
            const $styleElParent = $tab.find("#style").closest("tr");
            const $qualityElParent = $tab.find("#quality").closest("tr");
            if (tabClass !== TAB_CONTAINERS.generate) {
                $modelElParent.attr('hidden', true);
                $styleElParent.attr('hidden', true);
                $qualityElParent.attr('hidden', true);
            }

            $tbodyContainer.append(TEMPLATES.formPrompt(data));
            $tbodyContainer.append(TEMPLATES.formSize({
                sizes: MODEL_CONFIG[data.model_input].sizes,
                size_input: data.size_input
            }));
            
            $tbodyContainer.append(TEMPLATES.formN({
                n: MODEL_CONFIG[data.model_input].nValues,
                n_input: data.n_input
            }));
    
            $tab.find("#model").on('change', () => handleModelChange($tab)).trigger('change');
    
            $tab.find('.notice-container').append(TEMPLATES.notice(data));
            $tab.find('.result-container').append(TEMPLATES.result(data));
    
            if (tabClass === TAB_CONTAINERS.variate || (tabClass === TAB_CONTAINERS.edit && aig_ajax_object.valid_licence)) {
                addInputFileCropperHandler();
            }
        }
    }
    
    function handleModelChange($tab) {
        const $modelSelect = $tab.find("#model");
        const $sizeSelect = $tab.find("#size");
        const $nSelect = $tab.find("#n");
        const $styleSelect = $tab.find("#style");
        const $qualitySelect = $tab.find("#quality");
    
        // Récupérer la valeur sélectionnée dans le champ "Model"
        const selectedModel = $modelSelect.find(':selected').val();
    
        // Récupérer la configuration pour le modèle sélectionné
        const config = MODEL_CONFIG[selectedModel];
    
        // Vider les options actuelles des champs "Size" et "N"
        $sizeSelect.empty();
        $nSelect.empty();
        $styleSelect.empty();
        $qualitySelect.empty();
    
        // Ajouter les nouvelles options en fonction de la configuration du modèle
        config.sizes.forEach(size => $sizeSelect.append(new Option(size, size)));
        config.nValues.forEach(n => $nSelect.append(new Option(n, n)));
        config.styles.forEach(style => $styleSelect.append(new Option(style, style)));
        config.qualities.forEach(quality => $qualitySelect.append(new Option(quality, quality)));

        if (selectedModel !== 'dall-e-3') {
            $qualitySelect.closest('tr').attr('hidden', true);
            $styleSelect.closest('tr').attr('hidden', true);
        }
        else {
            $qualitySelect.closest('tr').removeAttr('hidden');
            $styleSelect.closest('tr').removeAttr('hidden');
        }
    
        // Sélectionner la première option si elle est disponible
        $sizeSelect.prop('selectedIndex', 0);
        $nSelect.prop('selectedIndex', 0);
        $styleSelect.prop('selectedIndex', 0);
        $qualitySelect.prop('selectedIndex', 0);
    }
    
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Variate and edits utilities //////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function handlePathCreated(path, canvas, maskContext, maskCanvas, originalCanvas) {
        path.globalCompositeOperation = 'destination-out';
        canvas.renderAll();

        if (canvas.width > 0 && canvas.height > 0) {
            maskContext.clearRect(0, 0, maskCanvas.width, maskCanvas.height);
            maskContext.fillStyle = 'rgba(0, 0, 0, 0)';
            maskContext.fillRect(0, 0, maskCanvas.width, maskCanvas.height);

            maskContext.drawImage(canvas.getElement(), 0, 0, canvas.width, canvas.height);
            const maskImageData = maskContext.getImageData(0, 0, canvas.width, canvas.height);

            $.each(maskImageData.data, function (i, value) {
                if (i % 4 === 3) {
                    maskImageData.data[i] = 0;
                }
            });

            maskContext.putImageData(maskImageData, 0, 0);

        
            // s'il y a déjà un canvas, on le supprime
            if ($('#canvas-mask-result').length) {
                $('#canvas-mask-result').remove();
            }
            const resultCanvas = $('<canvas></canvas>').appendTo('body')[0];
            $(resultCanvas).attr('hidden', true);
            $(resultCanvas).attr('id', 'canvas-mask-result');
            $(resultCanvas).attr('width', canvas.width);
            $(resultCanvas).attr('height', canvas.height);
            const resultContext = resultCanvas.getContext('2d');

            resultContext.drawImage(originalCanvas, 0, 0);
            resultContext.drawImage(maskCanvas, 0, 0);

            resultCanvas.toBlob(function (blob) {
                const file = new File([blob], 'mask.png', { type: 'image/png', lastModified: new Date().getTime() });

                let container = new DataTransfer();
                container.items.add(file);

                const maskInput = $('#mask')[0];
                maskInput.files = container.files;
            }, 'image/png', 1);
        }
    }

    function initFabricCanvas(canvas, params) {
        // Fix iframe issue for FLBuilder
        const el = $(canvas)[0];
        fabric.window = el.ownerDocument.defaultView;
        fabric.document = el.ownerDocument;
        return new fabric.Canvas(el, params);
    }
    
    function isFabricCanvasReady(canvas) {
        return canvas instanceof fabric.Canvas;
    }

    function addMask(canvas) {
        const originalCanvas = canvas;
        const wasntInit = !isFabricCanvasReady(maskFabricCanvas);

        if (wasntInit) {
            maskFabricCanvas = initFabricCanvas(originalCanvas, {
                backgroundImage: originalCanvas.toDataURL("image/png", 1),
                enableRetinaScaling: false,
                isDrawingMode: true,
                selectable: false,
            });
        }

        $(maskFabricCanvas.lowerCanvasEl.parentNode).removeClass("aig-canvas-locked");

        if (isFabricCanvasReady(editFabricCanvas)) {
            maskFabricCanvas.setBackgroundImage(editFabricCanvas.toDataURL("image/png", 1), maskFabricCanvas.renderAll.bind(maskFabricCanvas));
            $(editFabricCanvas.lowerCanvasEl.parentNode).addClass("aig-canvas-locked");
        }

        maskFabricCanvas.isDrawingMode = true;
        let isMagicWandMode = false;
        let mouseX;
        let mouseY;
        let brushSize = 50;
        maskFabricCanvas.freeDrawingBrush.width = brushSize;
        maskFabricCanvas.freeDrawingBrush.color = 'rgba(0, 0, 0, 1)';
        maskFabricCanvas.freeDrawingBrush.globalCompositeOperation = 'source-out';

        if (!maskFabricCanvas.historyRedo) {
            maskFabricCanvas.historyRedo = [];
        }

        let maskCanvas = $('#canvas-mask-context');
        if (!maskCanvas.length) {
            maskCanvas = $('<canvas>', { id: 'canvas-mask-context', width: maskFabricCanvas.width, height: maskFabricCanvas.height });
        }

        const maskContext = maskCanvas[0].getContext('2d');

        maskFabricCanvas.__eventListeners = {};

        maskFabricCanvas.undo = function () {
            const history = maskFabricCanvas._objects;
            if (history.length > 0) {
                const lastObject = history.pop();
                maskFabricCanvas.historyRedo.push([lastObject]);
                maskFabricCanvas.remove(lastObject);
                maskFabricCanvas.renderAll();
            }
        };

        maskFabricCanvas.redo = function () {
            const lastObject = maskFabricCanvas.historyRedo.pop();
            if (lastObject) {
                maskFabricCanvas.add.apply(maskFabricCanvas, lastObject);
                maskFabricCanvas.renderAll();
            }
        };

        maskFabricCanvas.on('path:created', function (e) {
            const path = e.path;
            path.set({ selectable: false, hoverCursor: "default" }); // Non selectable
            handlePathCreated(path, maskFabricCanvas, maskContext, maskCanvas[0], originalCanvas);
            maskFabricCanvas.historyRedo = [];
        });

        maskFabricCanvas.on('mouse:down', function (options) {
            const event = options.e;
            if (isMagicWandMode) {
                mouseX = event.offsetX;
                mouseY = event.offsetY;
                const x = event.offsetX;
                const y = event.offsetY;
                const pixelColor = maskFabricCanvas.contextContainer.getImageData(x, y, 1, 1).data;
                selectSimilarPixels(pixelColor, 50);
            }
        });

        function selectSimilarPixels(targetColor, tolerance) {
            const imageData = maskFabricCanvas.contextContainer.getImageData(0, 0, maskFabricCanvas.width, maskFabricCanvas.height);
            const data = imageData.data;

            const pathCoordinates = [];
            const visited = new Set();

            const stack = [{ x: mouseX, y: mouseY }];

            function isSimilarColorWithTolerance(color1, color2, tolerance) {
                return Math.abs(color1[0] - color2[0]) <= tolerance &&
                    Math.abs(color1[1] - color2[1]) <= tolerance &&
                    Math.abs(color1[2] - color2[2]) <= tolerance;
            }

            while (stack.length > 0) {
                const { x, y } = stack.pop();

                if (visited.has(`${x}-${y}`)) continue;
                visited.add(`${x}-${y}`);

                const index = (y * maskFabricCanvas.width + x) * 4;
                const red = data[index];
                const green = data[index + 1];
                const blue = data[index + 2];

                if (isSimilarColorWithTolerance([red, green, blue], targetColor, tolerance)) {
                    pathCoordinates.push({ x, y });

                    if (x > 0) stack.push({ x: x - 1, y });
                    if (x < maskFabricCanvas.width - 1) stack.push({ x: x + 1, y });
                    if (y > 0) stack.push({ x, y: y - 1 });
                    if (y < maskFabricCanvas.height - 1) stack.push({ x, y: y + 1 });
                }
            }

            if (pathCoordinates.length > 1) {
                const pathData = pathCoordinates.map(coord => `L ${coord.x} ${coord.y}`).join(' ');
                const path = new (window.parent.fabric || fabric).Path(`M ${pathCoordinates[0].x} ${pathCoordinates[0].y} ${pathData}`, {
                    fill: 'transparent',
                    stroke: 'red',
                    strokeWidth: 2,
                    selectable: false,
                    hoverCursor: "default"
                });
                maskFabricCanvas.add(path);
                maskFabricCanvas.renderAll();
                handlePathCreated(path, maskFabricCanvas, maskContext, maskCanvas[0], originalCanvas, document);
                maskFabricCanvas.historyRedo = [];
            }
        }

        const undoButton = $('<button>', { type: 'button', class: 'button' }).html('<span class="dashicons dashicons-undo"></span>').click(function (e) {
            e.preventDefault();
            maskFabricCanvas.undo();
        });

        const redoButton = $('<button>', { type: 'button', class: 'button' }).html('<span class="dashicons dashicons-redo"></span>').click(function (e) {
            e.preventDefault();
            maskFabricCanvas.redo();
        });

        const brushSizeInput = $('<input>', { id: 'aig_brush_size_input', type: 'range', min: '1', max: '100', value: brushSize }).on('input', function () {
            brushSize = parseInt($(this).val());
            maskFabricCanvas.freeDrawingBrush.width = brushSize;
        });

        const magicWandButton = $('<button>', { type: 'button', class: 'button' }).html('<span class="dashicons dashicons-admin-customizer"></span>').click(function (e) {
            e.preventDefault();
            isMagicWandMode = !isMagicWandMode;
            $(this).toggleClass("active", isMagicWandMode);
            maskFabricCanvas.isDrawingMode = !isMagicWandMode;
        });

        $('.button-container').remove();

        const buttonContainer = $('<div>', { class: 'button-container' }).append(brushSizeInput, magicWandButton, undoButton, redoButton);

        $('#aig_cropper_preview').css('position', 'relative').append(buttonContainer);
    }

    function addDrawings(canvas) {
        const originalCanvas = canvas;
        const wasntInit = !isFabricCanvasReady(editFabricCanvas);
        if (wasntInit) {
            editFabricCanvas = initFabricCanvas(originalCanvas, {
                backgroundImage: originalCanvas.toDataURL("image/png", 1),
                enableRetinaScaling: false,
                isDrawingMode: false,
            });
        }

        $(editFabricCanvas.lowerCanvasEl.parentNode).removeClass("aig-canvas-locked");

        if (isFabricCanvasReady(maskFabricCanvas)) {
            $(maskFabricCanvas.lowerCanvasEl.parentNode).addClass("aig-canvas-locked");
        }

        if (!editFabricCanvas) {
            editFabricCanvas.historyRedo = [];
        }

        editFabricCanvas.__eventListeners = {};

        editFabricCanvas.on('object:modified', updateImageAndPreview);
        editFabricCanvas.on('object:removed', updateImageAndPreview);

        editFabricCanvas.undo = function () {
            const history = editFabricCanvas._objects;
            if (history.length > 0) {
                const lastObject = history.pop();
                editFabricCanvas.historyRedo.push([lastObject]);
                editFabricCanvas.remove(lastObject);
                editFabricCanvas.renderAll();
                updateImageAndPreview();
            }
        };

        editFabricCanvas.redo = function () {
            const lastObject = editFabricCanvas.historyRedo.pop();
            if (lastObject) {
                editFabricCanvas.add.apply(editFabricCanvas, lastObject);
                editFabricCanvas.renderAll();
                updateImageAndPreview();
            }
        };

        $(SCOPE).off('keydown', deleteActiveObject);
        $(SCOPE).on('keydown', deleteActiveObject);

        function deleteActiveObject(e) {
            if (e.keyCode == 46 || e.key == 'Delete' || e.code == 'Delete' || e.key == 'Backspace') {
                if (editFabricCanvas.getActiveObject()) {
                    if (editFabricCanvas.getActiveObject().isEditing) {
                        return;
                    }
                    editFabricCanvas.remove(editFabricCanvas.getActiveObject());
                    editFabricCanvas.renderAll();
                }
            }
        }

        function updateImageAndPreview() {
            const croppedInput = $('#image');
            const previewImg = $('#aig_cropper_preview_inner img');
        
            // Supprimer l'objet actif du canvas cloné
            editFabricCanvas.discardActiveObject().renderAll();
        
            // Obtenir l'élément de canevas inférieur du canvas cloné
            const img = editFabricCanvas.lowerCanvasEl;
            const resultCanvas = $('<canvas>')[0];
            const resultContext = resultCanvas.getContext('2d');
            resultCanvas.width = img.width;
            resultCanvas.height = img.height;
            resultContext.drawImage(img, 0, 0);
            resultCanvas.toBlob(function (blob) {
                const file = new File([blob], 'cropped.png', { type: 'image/png', lastModified: new Date().getTime() });
                let container = new DataTransfer();
                container.items.add(file);
                croppedInput[0].files = container.files;
            }, 'image/png', 1);
        
            const previewCanvas = $('<canvas>')[0];
            const previewContext = previewCanvas.getContext('2d');
            const previewWidth = 210;
            const previewHeight = 210;
        
            previewCanvas.width = previewWidth;
            previewCanvas.height = previewHeight;
        
            previewContext.drawImage(resultCanvas, 0, 0, resultCanvas.width, resultCanvas.height, 0, 0, previewWidth, previewHeight);
            previewImg.attr('src', previewCanvas.toDataURL("image/png", 1));
        }

        function handleImageUpload(event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = new Image();
                    img.onload = function () {
                        const fabricImage = new (window.parent.fabric || fabric).Image(img);
                        const maxImageWidth = editFabricCanvas.width / 2;
                        const maxImageHeight = editFabricCanvas.height / 2;
                        const aspectRatio = img.width / img.height;

                        if (aspectRatio > 1) {
                            fabricImage.scaleToWidth(maxImageWidth, false);
                        } else {
                            fabricImage.scaleToHeight(maxImageHeight, false);
                        }

                        editFabricCanvas.add(fabricImage);
                        fabricImage.set({
                            left: (editFabricCanvas.width - fabricImage.getScaledWidth()) / 2,
                            top: (editFabricCanvas.height - fabricImage.getScaledHeight()) / 2,
                            selectable: true,
                            evented: true,
                        });
                        editFabricCanvas.setActiveObject(fabricImage);
                        editFabricCanvas.renderAll();
                        updateImageAndPreview();
                    };

                    img.src = e.target.result;
                };

                reader.readAsDataURL(file);

                editFabricCanvas.historyRedo = [];
            }
        }

        const $undoButton = $('<button>').html('<span class="dashicons dashicons-undo"></span>').attr('type', 'button').addClass('button').click(e => {
            e.preventDefault();
            editFabricCanvas.undo();
        });

        const $redoButton = $('<button>').html('<span class="dashicons dashicons-redo"></span>').attr('type', 'button').addClass('button').click(e => {
            e.preventDefault();
            editFabricCanvas.redo();
        });

        const $imageUploadInput = $('<input>').attr({ id: 'aig_file_picker_input', type: 'file', accept: 'image/*' }).css('display', 'none').change(handleImageUpload);

        const $imageUploadButton = $('<button>').html('<span class="dashicons dashicons-upload"></span>').addClass('aig_file_picker_button button').click(e => {
            e.preventDefault();
            $imageUploadInput.click();
        });

        $('.button-container').remove();

        const $buttonContainer = $('<div>').addClass('button-container').append($imageUploadButton, $imageUploadInput, $undoButton, $redoButton);

        $('#aig_cropper_preview').css('position', 'relative').append($buttonContainer);
    }

    function showDrawingButtons(cropper) {
        maskFabricCanvas = null;
        editFabricCanvas = null;

        const drawingToolContainer = $('#drawing-button-container');
        drawingToolContainer.empty();
        drawingToolContainer.css('display', 'block');

        const croppedCanvasMask = cropper.getCroppedCanvas({
            width: MAX_CANVAS.width,
            height: MAX_CANVAS.height // input value
        });
        croppedCanvasMask.id = 'canvas-mask';
        const croppedCanvasDraw = cropper.getCroppedCanvas({
            width: MAX_CANVAS.width,
            height: MAX_CANVAS.height
        });
        croppedCanvasDraw.id = 'canvas-draw';
        $(croppedCanvasDraw).attr('hidden', true);
        const cropperContainer = cropper.container;

        cropper.destroy(); // Destroy the cropper

        const $cancelButton = $('<button>')
            .attr('type', 'button')
            .text(aig_ajax_object.cancelLabel)
            .addClass('button aig_cancel_button')
            .css('width', '100%')
            .off('click').on('click', function (e) {
                e.preventDefault();
                drawingToolContainer.remove($cancelButton);
                const canvas = $('#aig_cropper_canvas_area');
                canvas.attr('hidden', true);

                // reinit
                maskFabricCanvas = null;
                editFabricCanvas = null;

                addInputFileCropperHandler(true); // Initialize a new cropper on the original canvas
            });
        drawingToolContainer.append($cancelButton);

        // get the closest parent .tab-container-aig-* class to get the action (tab-container-aig-edit = edit)
        const $parent = $(cropperContainer).parents().filter(function() {
            return this.className.match(/\btab-container-aig-\S+/);
        }).first();
        
        const tabClass = $parent.attr('class');
        if (tabClass === TAB_CONTAINERS.edit) {
            const $editButton = $('<button>')
                .attr('type', 'button')
                .text(aig_ajax_object.editLabel)
                .addClass('button')
                .css('width', '100%')
                .off('click').on('click', function (e) {
                    e.preventDefault();
                    addDrawings(croppedCanvasDraw)
                    $editButton.attr('hidden', true);
                    $editButton.attr('disabled', true);
                    $maskButton.removeAttr('hidden');
                    $maskButton.removeAttr('disabled');

                    $(croppedCanvasMask).attr('hidden', true);
                    $(croppedCanvasDraw).removeAttr('hidden');
                });

            const $maskButton = $('<button>')
                .attr('type', 'button')
                .text(aig_ajax_object.maskLabel)
                .addClass('button')
                .css('width', '100%')
                .off('click').on('click', function (e) {
                    e.preventDefault();
                    addMask(croppedCanvasMask);
                    $maskButton.attr('hidden', true);
                    $maskButton.attr('disabled', true);
                    $editButton.removeAttr('hidden');
                    $editButton.removeAttr('disabled');

                    $(croppedCanvasDraw).attr('hidden', true);
                    $(croppedCanvasMask).removeAttr('hidden');
                });

            drawingToolContainer.append($editButton);
            drawingToolContainer.append($maskButton);
            $(cropperContainer).append(croppedCanvasDraw);
        }

        $(cropperContainer).append(croppedCanvasMask);
    }

    function addMediaHandler() {
        $('.add_as_media').off('click').on('click', function (e) {
            e.preventDefault();

            const $button = $(this);
            const $parent = $button.parent();
            const data = {
                action: 'add_to_media',
                url: $parent.find('img').attr('src'),
                description: $('#prompt').val()
            };

            $parent.find('h2 > .spinner').addClass('is-active');

            jQuery.post(aig_ajax_object.ajax_url, data, (response) => {
                if (response.success) {
                    $button.hide("slow", () => {
                        $parent.find('h2 > .spinner').removeClass('is-active').remove();
                        $parent.find('h2 > .dashicons').addClass('is-active');
                    });

                    needMediaListRefresh = true;
                }
            });

            return false;
        });
    }

    async function addInputFileCropperHandler(refresh) {
        refresh = refresh || false;
        const inputSelector = '#image';
        const originalInput = '#original';

        async function loadImage(fileInput) {
            if (fileInput[0].files && fileInput[0].files[0]) {
                //console.log('File to load:', fileInput[0].files[0]);
                const file = fileInput[0].files[0];

                try {
                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();

                        reader.onload = async () => {
                            const dataUrl = reader.result;
                            const image = new Image();
                            image.src = dataUrl;

                            image.onload = () => resolve(image);
                            image.onerror = () => reject(new Error("Error loading image."));
                        };

                        reader.onerror = () => reject(new Error("Error reading file."));

                        reader.readAsDataURL(file);
                    });
                } catch (error) {
                    console.error('Error in loadImage:', error);
                }
            } else {
                console.log('No file to load.');
            }
            return null;
        }

        async function createAndAppendElements(image, input) {
            const container = $('<div>', {
                css: {
                    width: MAX_CANVAS.width + 'px',
                    height: MAX_CANVAS.height + 'px'
                }
            })[0];
            const canvas = await createCanvasFromImage(image);
            $(container).append(canvas);

            const cropperArea = $('#aig_cropper_canvas_area');
            cropperArea.empty();
            cropperArea.show();
            cropperArea.append(container);

            const cropper = createCropper(canvas);
            const cropButton = createCropButton(canvas, cropper, input);
            const cropperPreview = $('#aig_cropper_preview');
            cropperPreview.empty();

            const imgElement = $('<img>', {
                src: '',
                class: 'hidden',
                width: 210,
                height: 210
            });

            const divDrawingTools = $('<div>', {
                id: 'drawing-button-container'
            });

            const divParent = $('<div>', {
                id: 'aig_cropper_preview_inner'
            });

            divParent.append(imgElement, cropButton, divDrawingTools);
            cropperPreview.append(divParent);
        }

        function updateOriginalInput(input, originalInput) {
            const originalFile = new File([input[0].files[0]], 'original.png', {
                type: 'image/png',
                lastModified: new Date().getTime()
            });
            const originalContainer = new DataTransfer();
            originalContainer.items.add(originalFile);
            originalInput[0].files = originalContainer.files;
        }

        async function init(fileInput, keepOriginal) {
            fileInput = fileInput ? $(fileInput) : $(inputSelector);
            keepOriginal = keepOriginal || false;
            const image = await loadImage(fileInput);
            if (image) {
                createAndAppendElements(image, fileInput);
                if (!keepOriginal) {
                    updateOriginalInput(fileInput, $(originalInput));
                }
            }
        }

        if (refresh) {
            init(originalInput, true);
        }
        else {
            $(inputSelector).off('change').on('change', async function (e) {
                e.stopPropagation();
                init(this);
            });
        }
    }

    async function createCanvasFromImage(image) {
        return new Promise((resolve, reject) => {
            const newImage = new Image();
            newImage.onload = async function () {
                const width = newImage.width;
                const height = newImage.height;
                const aspectRatio = width / height;

                let canvasWidth = MAX_CANVAS.width;
                let canvasHeight = MAX_CANVAS.height;

                if (aspectRatio > 1) {
                    canvasHeight = canvasWidth / aspectRatio;
                } else {
                    canvasWidth = canvasHeight * aspectRatio;
                }

                const canvas = document.createElement('canvas');
                canvas.width = MAX_CANVAS.width;
                canvas.height = MAX_CANVAS.height;

                const ctx = canvas.getContext("2d");

                // Draw the image in the center of the canvas
                const offsetX = (canvas.width - canvasWidth) / 2;
                const offsetY = (canvas.height - canvasHeight) / 2;
                ctx.drawImage(newImage, offsetX, offsetY, canvasWidth, canvasHeight);

                resolve(canvas);
            };
            newImage.src = image.toDataURL ? image.toDataURL() : image.src;
        });
    }

    function createCropper(canvas) {
        const scope_cropper = window.parent.Cropper || Cropper;
        const cropper = new scope_cropper(canvas, {
            aspectRatio: 1,
            viewMode: 0,
            minContainerWidth: MIN_DIMENSIONS,
            minContainerHeight: MIN_DIMENSIONS,
            minCanvasWidth: MIN_DIMENSIONS,
            minCanvasHeight: MIN_DIMENSIONS,
            crop: () => {
                let imgSrc = cropper.getCroppedCanvas({
                    width: CROPPED_DIMENSIONS,
                    height: CROPPED_DIMENSIONS
                }).toDataURL(CROPPED_FILE_TYPE, IMAGE_QUALITY);

                const previewImg = $('#aig_cropper_preview_inner img');
                previewImg.attr('src', imgSrc);
                previewImg.removeClass('hidden');
            }
        });
        return cropper;
    }

    function createCropButton(canvas, cropper, targetEventInput) {
        const cropButton = $('<button>', {
            role: 'button',
            text: aig_ajax_object.cropperCropLabel,
            click: (e) => {
                e.preventDefault();
                const croppedCanvas = cropper.getCroppedCanvas({
                    width: MIN_DIMENSIONS,
                    minWidth: MIN_DIMENSIONS,
                    maxWidth: MIN_DIMENSIONS,
                    height: MIN_DIMENSIONS,
                    minHeight: MIN_DIMENSIONS,
                    maxHeight: MIN_DIMENSIONS,
                    imageSmoothingQuality: "high"
                });

                croppedCanvas.toBlob((blob) => {
                    const file = new File([blob], CROPPED_FILE_NAME, { type: CROPPED_FILE_TYPE, lastModified: new Date().getTime() });
                    let container = new DataTransfer();
                    container.items.add(file);
                    targetEventInput[0].files = container.files;
                    showDrawingButtons(cropper);
                }, CROPPED_FILE_TYPE, IMAGE_QUALITY);

                cropButton.remove();

                return false;
            }
        }).css('width', '100%').addClass('button');

        return cropButton;
    }


    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Loading scripts and librairies ///////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function loadAsyncScript(file, library, version) {
        const id = 'artist-image-generator-js-external-' + library + '-' + version;

        return new Promise((resolve, reject) => {
            if (SCRIPT_CACHE[id]) {
                resolve(SCRIPT_CACHE[id]);
            } else if (!document.getElementById(id)) {
                $.getScript(file)
                    .done(() => {
                        SCRIPT_CACHE[id] = file;
                        resolve(file);
                    })
                    .fail(() => {
                        reject(new Error(`Le script ${file} n'a pas pu être chargé`));
                    });
            } else {
                reject(new Error(`Le script avec l'ID ${id} est déjà chargé`));
            }
        });
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialize ///////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    loadAsyncScript(aig_ajax_object.cropper_script_path, 'cropper', '1.6.1')
        .then(() => loadAsyncScript(aig_ajax_object.drawing_tool_script_path, 'drawing', '5.3.0'))
        .then(() => {
            if (aig_ajax_object.is_media_editor) {
                initAdminMediaModal();
            } else {
                initAdminPage();
            }
        })
        .catch(error => console.error(error));
})(jQuery);