
document.addEventListener("DOMContentLoaded", function () {
    const forms = document.querySelectorAll(".aig-form");
    let swiper;

    if (forms.length) {
        forms.forEach((form, formIndex) => {
            form.setAttribute("data-index", formIndex+1);

            if (forms.length > 1) {
                const FORM_TITLE_CLASS = 'aig-form-title';
                const ICON_DOWN = '- ';
                const ICON_RIGHT = '+ ';

                const updateIcon = (element, content, text) => {
                    const icon = content.is(':visible') ? ICON_DOWN : ICON_RIGHT;
                    element.firstChild.textContent = icon + text;
                };

                const formToggleLabel = form.getAttribute("data-toggle-label");
                const formContainer = form.closest('.aig-form-container');
                const formTitleText = formToggleLabel + (formIndex + 1);
                const formTitleElement = document.createElement('div');
                formTitleElement.className = FORM_TITLE_CLASS;
                formTitleElement.innerText = ICON_RIGHT + formTitleText;

                formTitleElement.addEventListener('click', () => {
                    const content = jQuery(formTitleElement.nextElementSibling);
                    content.toggle();
                    updateIcon(formTitleElement, content, formTitleText);
                });

                formContainer.insertAdjacentElement('afterbegin', formTitleElement);

                // Initialize the display of the icon
                if (formIndex > 0) {
                    jQuery(formTitleElement.nextElementSibling).hide();
                }
                updateIcon(formTitleElement, jQuery(formTitleElement.nextElementSibling), formTitleText);
            }

            form.addEventListener("submit", async function (e) {
                e.preventDefault();

                const publicPrompt = form.querySelector("textarea[name='aig_public_prompt']").value;
                const topicCheckboxes = form.querySelectorAll("input[name='aig_topics[]']:checked");
                const topics = Array.from(topicCheckboxes).map(input => input.value).join(",");
                const promptInput = form.querySelector("input[name='aig_prompt']");
                const prompt = promptInput ? promptInput.value : "";
                const promptWithValues = prompt
                    .replace("{public_prompt}", publicPrompt)
                    .replace("{topics}", topics);

                const container = form.parentElement;
                const containerResults = form.querySelector(".aig-results");
                //containerResults.innerHTML = '';

                // remove previous overlay
                const previousOverlay = form.querySelector(".aig-overlay");
                if (previousOverlay) {
                    previousOverlay.remove();
                }

                const overlay = document.createElement("div");
                overlay.className = "aig-overlay";
                container.appendChild(overlay);

                const loadingAnimation = document.createElement("div");
                loadingAnimation.className = "aig-loading-animation";
                overlay.appendChild(loadingAnimation);

                const data = {
                    id: form.getAttribute("data-id"),
                    action: form.getAttribute("data-action"),
                    _ajax_nonce: form.querySelector("input[name='_ajax_nonce']").value,
                    generate: 1,
                    user_limit: form.querySelector("input[name='user_limit']").value,
                    user_limit_duration: form.querySelector("input[name='user_limit_duration']").value,
                    n: form.getAttribute("data-n"),
                    size: form.getAttribute("data-size"),
                    model: form.getAttribute("data-model"),
                    style: form.getAttribute("data-style"),
                    quality: form.getAttribute("data-quality"),
                    download: form.getAttribute("data-download"),
                    prompt: promptWithValues,
                    public_prompt: publicPrompt,
                    topics: topics,
                };

                const ajaxurl = form.getAttribute("action");

                let requests = [];
                if (data.model === 'dall-e-3' && data.n > 1) {
                    requests = Array.from({ length: data.n }, () => {
                        return fetch(ajaxurl, {
                            method: "POST",
                            body: new URLSearchParams(data),
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded",
                                "Cache-Control": "no-cache",
                            },
                        }).then((response) => response.json());
                    });
                } else {
                    requests.push(fetch(ajaxurl, {
                        method: "POST",
                        body: new URLSearchParams(data),
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                            "Cache-Control": "no-cache",
                        },
                    }).then((response) => response.json()));
                }

                try {
                    const responses = await Promise.all(requests);
                    // Merge all responses
                    const mergedResponse = responses.reduce((acc, response) => {
                        if (response.error && response.error.message) {
                            acc.errors.push(response.error.message);
                        }
                        if (response.images && response.images.length > 0) {
                            acc.images = acc.images.concat(response.images);
                        }
                        return acc;
                    }, { images: [], errors: [] });
            
                    overlay.style.display = "none";
                    form.querySelector(".aig-results-separator").style.display = 'block';

                    if (mergedResponse.images && mergedResponse.images.length > 0) {
                        mergedResponse.images.forEach((image, index) => {
                            const figure = document.createElement("figure");
                            figure.className = "custom-col";
        
                            const imgElement = document.createElement("img");
                            imgElement.src = image.url;
                            imgElement.className = "aig-image";
                            imgElement.alt = "Generated Image " + (index + 1);
                            figure.appendChild(imgElement);
        
                            const figCaption = document.createElement("figcaption");
                            const downloadButton = document.createElement("button");
                            downloadButton.setAttribute('type', 'button');
                            downloadButton.className = "aig-download-button";
        
                            const label = form.getAttribute("data-download") === "manual" ? 'Download Image ' + (index + 1) : 'Use Image ' + (index + 1) + ' as profile picture';
                            downloadButton.innerHTML = '<span class="dashicons dashicons-download"></span> ' + label;
                            figCaption.appendChild(downloadButton);
        
                            figure.appendChild(figCaption);
                            containerResults.appendChild(figure);
        
                            // Image download management
                            downloadButton.addEventListener("click", function () {
                                if (form.getAttribute("data-download") !== "wp_avatar") {
                                    const link = document.createElement("a");
                                    link.href = image.url;
                                    link.target = '_blank';
                                    link.download = "image" + (index + 1) + ".png";
                                    link.style.display = "none";
        
                                    form.appendChild(link);
                                    link.click();
                                    form.removeChild(link);
                                } else {
                                    fetch(ajaxurl, {
                                        method: "POST",
                                        body: new URLSearchParams({
                                            action: "change_wp_avatar",
                                            image_url: image.url,
                                        }),
                                        headers: {
                                            "Content-Type": "application/x-www-form-urlencoded",
                                            "Cache-Control": "no-cache",
                                        },
                                    })
                                        .then((response) => response.json())
                                        .then((result) => {
                                            if (confirm("You have successfully changed your profile picture.")) {
                                                window.location.reload();
                                            }
                                        })
                                        .catch((error) => {
                                            console.error("Error API request :", error);
                                        });
                                }
                            });
                        });
                    }

                    if (mergedResponse.errors && mergedResponse.errors.length > 0) {
                        const errorContainer = form.querySelector(".aig-errors");
                        errorContainer.innerHTML = mergedResponse.errors.join('<br>');
                    }

                    let $figures = form.querySelectorAll('.aig-results figure');

                    if ($figures.length > 0) {
                        const aigResults = form.querySelector('.aig-results');
                        const swiperWrapper = document.createElement('div');
                        swiperWrapper.className = 'swiper-wrapper';

                        $figures.forEach(figure => {
                            figure.classList.add('swiper-slide');
                            swiperWrapper.appendChild(figure);
                        });

                        aigResults.innerHTML = '';
                        aigResults.appendChild(swiperWrapper);

                        const swiperPagination = document.createElement('div');
                        swiperPagination.className = 'swiper-pagination';
                        aigResults.appendChild(swiperPagination);

                        if (swiper && aigResults.hasClass == 'swiper-initialized') {
                            swiper.update();
                        }
                        else {
                            swiper = new Swiper(form.querySelector('.aig-results'), {
                                direction: 'horizontal',
                                slidesPerView: 'auto',
                                autoWidth: true,
                                spaceBetween: 0,
                                loop: false,
                                pagination: {
                                    el: '.swiper-pagination',
                                },
                            });
                        }
                    }
            
                } catch (error) {
                    console.error("API Request Error :", error);
                }
            });
        });
    }
});
