(function($) {
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

    function buildTab(tabSelector, tabTemplate) {
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

    const tabSelectors = {
        generate: '#tab-container-generate',
        variate: '#tab-container-variate',
        settings: '#tab-container-settings',
        about: '#tab-container-about',
    };

    if ($(tabSelectors.settings).length) {
        buildTab(tabSelectors.settings, templates.settings);
    } else if ($(tabSelectors.variate).length) {
        buildTab(tabSelectors.variate, templates.variate);
    } else if ($(tabSelectors.about).length) {
        buildTab(tabSelectors.about, templates.about);
    } else if ($(tabSelectors.generate).length) {
        buildTab(tabSelectors.generate, templates.generate);
    }

    $(function() {
        $('.add_as_media').on('click', function(e) {
            e.preventDefault();

            const $that = $(this);
            const data = {
                'action': 'add_to_media',
                'url': $that.parent().find('img').attr('src'),
                'description': $('#prompt').val()
            };

            $that.parent().find('h2 > .spinner').addClass('is-active');
            jQuery.post(aig_ajax_object.ajax_url, data, function(response) {
                if (response.success) {
                    $that.hide("slow", function() {
                        $that.parent().find('h2 > .spinner').removeClass('is-active').remove();
                        $that.parent().find('h2 > .dashicons').addClass('is-active');
                    });
                }
            });

            return false;
        });
    });
})(jQuery);
