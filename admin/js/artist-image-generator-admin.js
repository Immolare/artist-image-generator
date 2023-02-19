(function ($) {
    'use strict';

    const data = aig_data;

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

    function refreshMyTabContent(data, action) {
        const $tabEl = $('<div id="tab-container-' + action + '"></div>');
        $('body .media-modal-content .media-frame-content').empty().append($tabEl);

        buildTab('#tab-container-' + action, templates[action], data);
        $tabEl.append($(tabSelectors[action]));
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
                        refreshMyTabContent(data,innerTextActive);
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
        } else if ($(tabSelectors.about).length) {
            buildTab(tabSelectors.about, templates.about, data);
        } else if ($(tabSelectors.generate).length) {
            buildTab(tabSelectors.generate, templates.generate, data);
        }

        addMediaHandler();
    }

    aig_ajax_object.is_media_editor ?
        initAdminMediaModal() :
        initAdminPage()
    ;

})(jQuery);