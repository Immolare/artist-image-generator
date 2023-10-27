document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector(".aig-form");

    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            const publicPrompt = document.getElementById("aig_public_prompt").value;
            const topicCheckboxes = document.querySelectorAll("input[name='aig_topics[]']:checked");
            const topics = Array.from(topicCheckboxes).map(input => input.value).join(",");
            const promptInput = document.querySelector("input[name='aig_prompt']");
            const prompt = promptInput ? promptInput.value : "";

            // Remplacez les balises {public_prompt} et {topics} dans le champ prompt
            const promptWithValues = prompt
            .replace("{public_prompt}", publicPrompt)
            .replace("{topics}", topics);

            const container = document.querySelector(".aig-form-container");
            const containerResults = document.querySelector(".aig-results");
            containerResults.innerHTML = '';

            const overlay = document.createElement("div");
            overlay.className = "aig-overlay";
            container.appendChild(overlay);

            const loadingAnimation = document.createElement("div");
            loadingAnimation.className = "aig-loading-animation";
            overlay.appendChild(loadingAnimation);

            const data = {
                action: form.getAttribute("data-action"),
                nonce: form.querySelector("input[name='_wpnonce']").value,
                generate: 1,
                n: form.getAttribute("data-n"),
                size: form.getAttribute("data-size"),
                prompt: promptWithValues,
                public_prompt: publicPrompt,
                topics: topics,
            };

            const ajaxurl = form.getAttribute("action");

            fetch(ajaxurl, {
                method: "POST",
                body: new URLSearchParams(data),
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "Cache-Control": "no-cache",
                },
            })
            .then((response) => response.json())
            .then((result) => {
                overlay.style.display = "none";
                document.querySelector(".aig-results-separator").style.display = 'block';

                const imageContainer = document.createElement("div");
                imageContainer.className = "custom-row";
                containerResults.appendChild(imageContainer);

                result.images.forEach((image, index) => {
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
                    // Utilisez l'icône WordPress pour le bouton de téléchargement (par exemple, un bouton de téléchargement généré par WordPress).
                    downloadButton.innerHTML = '<span class="dashicons dashicons-download"></span> Download Image ' + (index + 1);
                    figCaption.appendChild(downloadButton);

                    figure.appendChild(figCaption);
                    imageContainer.appendChild(figure);

                    // Gestion du téléchargement d'image
                    downloadButton.addEventListener("click", function () {
                        // Créez un lien de téléchargement
                        const link = document.createElement("a");
                        link.href = image.url;
                        link.target = '_blank';
                        link.download = "image" + (index + 1) + ".png";
                        link.style.display = "none";

                        // Ajoutez le lien au DOM et déclenchez le téléchargement
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    });
                });
            })
            .catch((error) => {
                console.error("Erreur lors de la requête API :", error);
            });
        });
    }
});
