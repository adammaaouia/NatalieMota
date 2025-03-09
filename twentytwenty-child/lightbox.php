<?php
// Si on est dans la boucle, récupère l'ID du post actuel
$post_id = get_the_ID(); 

// Récupérer la catégorie du post
$category_name = get_field('categories', $post_id);
$category_name = $category_name ? $category_name : 'Non spécifiée';

// Récupérer la référence via un champ personnalisé
$reference = get_post_meta($post_id, 'reference', true);
$reference = $reference ? esc_html($reference) : 'Non spécifiée';
?>

<!-- lightbox.php -->
<div id="lightbox" class="lightbox" style="display: none;">
    <div class="lightbox-content">
        <div id="prev" class="lightbox-nav left">
            <i class="fa-solid fa-arrow-left-long"></i>
            <span class="nav-label">Précédente</span>  <!-- Label ajouté -->
        </div>

        <img id="lightbox-image" src="" alt="">

        <div id="next" class="lightbox-nav right">
            <span class="nav-label">Suivante</span>  <!-- Label ajouté -->
            <i class="fa-solid fa-arrow-right-long"></i>
        </div>

        <span id="close-lightbox" class="close-lightbox">&times;</span>

        <!-- Informations supplémentaires -->
        <p class="post-categories"><span id="category-name">Non spécifiée</span></p>
        <p class="post-ref"><span id="reference-value">Non spécifiée</span></p>
    </div>
</div>



<script>
document.addEventListener("DOMContentLoaded", function () {
    function initializeLightboxEvents() {
        const galleryGrid = document.querySelector(".gallery-grid");
        const lightbox = document.getElementById("lightbox");
        const lightboxImage = document.getElementById("lightbox-image");
        const closeLightbox = document.getElementById("close-lightbox");
        const prevArrow = document.getElementById("prev");
        const nextArrow = document.getElementById("next");

        const categoryName = document.getElementById("category-name");
        const referenceValue = document.getElementById("reference-value");

        let currentIndex = 0;
        let images = [];
        let postIds = [];

        function updateImageList() {
            images = Array.from(document.querySelectorAll(".gallery-item img"), img => img.src);
            postIds = Array.from(document.querySelectorAll(".fullscreen-icon"), icon => icon.getAttribute("data-post-id"));
        }

        function updateLightboxInfo(postId) {
            console.log("Chargement des détails pour post_id :", postId);
            fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
                method: "POST",
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'load_post_details',
                    post_id: postId,
                    nonce: "<?php echo wp_create_nonce('load_post_details_nonce'); ?>"
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (data.success) {
                    categoryName.textContent = data.data.category || "Non spécifiée";
                    referenceValue.textContent = data.data.reference || "Non spécifiée";
                } else {
                    console.error("Erreur AJAX:", data.message);
                }
            })
            .catch(error => console.error("Erreur AJAX:", error));
        }

        function openLightbox(imageSrc, index) {
            updateImageList();
            currentIndex = index;
            lightboxImage.src = images[currentIndex];
            lightbox.style.display = "flex";
            updateLightboxInfo(postIds[currentIndex]);
        }

        function closeLightboxFunction() {
            lightbox.style.display = "none";
        }

        function showPreviousImage() {
            if (currentIndex > 0) {
                currentIndex--;
                lightboxImage.src = images[currentIndex];
                updateLightboxInfo(postIds[currentIndex]);
            }
        }

        function showNextImage() {
            if (currentIndex < images.length - 1) {
                currentIndex++;
                lightboxImage.src = images[currentIndex];
                updateLightboxInfo(postIds[currentIndex]);
            }
        }

        function handleFullscreenClick(e) {
            updateImageList();
            const imgElement = e.target.closest(".gallery-item").querySelector("img");
            const index = images.indexOf(imgElement.src);

            if (index !== -1) {
                openLightbox(imgElement.src, index);
            }
        }

        function attachEvents() {
            document.querySelectorAll(".fullscreen-icon").forEach(icon => {
                icon.removeEventListener("click", handleFullscreenClick);
                icon.addEventListener("click", handleFullscreenClick);
            });

            closeLightbox.removeEventListener("click", closeLightboxFunction);
            closeLightbox.addEventListener("click", closeLightboxFunction);

            prevArrow.removeEventListener("click", showPreviousImage);
            prevArrow.addEventListener("click", showPreviousImage);

            nextArrow.removeEventListener("click", showNextImage);
            nextArrow.addEventListener("click", showNextImage);
        }

        attachEvents();

        document.addEventListener("postsUpdated", function () {
            updateImageList();
            attachEvents();
        });
    }

    initializeLightboxEvents();
});

</script>
