<?php
/**
 * The main template file
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

get_header();
?>

<?php
// Récupère un post aléatoire
$random_photo = get_posts([
    'post_type'      => 'photos',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    'orderby'        => 'rand',
]);

if (!empty($random_photo)) {
    $random_image = get_field('image', $random_photo[0]->ID);
    $random_image_url = !empty($random_image) ? esc_url($random_image['url']) : 'default-image.jpg';
}
?>

<!-- Banner Section -->
<section id="banner" style="background-image: url('<?php echo $random_image_url; ?>');">
    <div class="banner-content">
        <h1 class="space-mono-bold-italic">PHOTOGRAPHE EVENT</h1>
    </div>
</section>

<!-- Main Content -->
<main id="site-content">
    <!-- Filtres -->
    <section id="filters">
        <select id="filter-category">
            <option value="">Catégories</option>
            <?php
            $categories = [];
            $posts = get_posts([
                'post_type'      => 'photos',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            ]);

            foreach ($posts as $post) {
                $post_categories = get_field('categories', $post->ID);
                if (!empty($post_categories)) {
                    foreach ((array) $post_categories as $category) {
                        if (!in_array($category, $categories)) {
                            $categories[] = $category;
                        }
                    }
                }
            }

            foreach ($categories as $category) {
                echo '<option value="' . esc_attr($category) . '">' . esc_html($category) . '</option>';
            }
            ?>
        </select>

        <select id="filter-format">
            <option value="">Formats</option>
            <?php
            $formats = [];
            foreach ($posts as $post) {
                $post_formats = get_field('formats', $post->ID);
                if (!empty($post_formats)) {
                    foreach ((array) $post_formats as $format) {
                        if (!in_array($format, $formats)) {
                            $formats[] = $format;
                        }       
                    }       
                }
            }

            foreach ($formats as $format) {
                echo '<option value="' . esc_attr($format) . '">' . esc_html($format) . '</option>';
            }
            ?>
        </select>

        <select id="filter-sort">
            <option value="date_desc">Trier par</option>
            <option value="date_asc">Plus ancien</option>
            <option value="date_desc">Plus récent</option>
        </select>
    </section>
    
    <!-- Section Galerie -->
    <section id="gallery">
        <div class="gallery-grid" data-ajaxurl="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
            <?php
            // Requête initiale pour afficher les photos
            $args = [
                'post_type'      => 'photos',
                'post_status'    => 'publish',
                'posts_per_page' => 8,
                'orderby'        => 'date',
                'order'          => 'DESC'
            ];
            $query = new WP_Query($args);
            
            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    
                    $image = get_field('image', get_the_ID());
                    $image_url = !empty($image) ? esc_url($image['url']) : 'default-image.jpg'; // Fallback
                    $title = get_the_title();
                    $permalink = get_permalink();
                    $reference = get_post_meta(get_the_ID(), 'reference', true);
                    $categories = get_the_category();
                    $category_names = wp_list_pluck($categories, 'name');
                    $category_list = implode(', ', $category_names);
            ?>
                    <div class="gallery-item">
                        <a href="<?php echo esc_url($image_url); ?>" 
                           class="lightbox-trigger"
                           data-reference="<?php echo esc_attr($reference); ?>"
                           data-category="<?php echo esc_attr($category_list); ?>">
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                        </a>
                        <div class="overlay">
                            <div class="post-title"><?php echo esc_html($title); ?></div>
                            

                            <p class="post-categories" id="overlay-category">
                                <?php 
                                // Utilise get_field() pour récupérer la catégorie via un champ personnalisé
                                $category = get_field('categories');
                                echo esc_html($category ? $category : 'Non spécifiée');
                                ?>
                            </p>

                            <p class="post-ref" id="overlay-reference">
                                <?php echo esc_html($reference ? $reference : 'Non spécifiée'); ?>
                            </p>


                            <a href="<?php echo esc_url($permalink); ?>" class="eye-icon">&#128065;</a>
                            <div class="fullscreen-icon" data-post-id="<?php echo get_the_ID(); ?>"><i class="fa-solid fa-expand"></i></div>
                        </div>
                    </div>

            <?php
                endwhile;
            endif;
            wp_reset_postdata();
            ?>
        </div>
        <button id="load-more" class="load-more-button" data-offset="8" data-nonce="<?php echo wp_create_nonce('load_more_nonce'); ?>">Charger plus</button>
    </section>
</main>

<?php get_footer(); ?>

<?php
get_template_part('lightbox');
?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const galleryGrid = document.querySelector(".gallery-grid");
    const loadMoreButton = document.querySelector("#load-more");
    const formatFilter = document.querySelector("#filter-format");
    const categoryFilter = document.querySelector("#filter-category");
    const sortFilter = document.querySelector("#filter-sort");
    let offset = parseInt(loadMoreButton.dataset.offset, 10);
    const nonce = loadMoreButton.dataset.nonce;

    // Fonction pour mettre à jour les données de la galerie
    function updateGalleryData() {
        images = Array.from(document.querySelectorAll(".gallery-item img"), img => img.src);
        postIds = Array.from(document.querySelectorAll(".fullscreen-icon"), icon => icon.getAttribute("data-post-id"));
    }

    // Fonction pour charger plus d'images via AJAX
    function loadMoreImages() {
        let formData = new FormData();
        formData.append("action", "load_photos");
        formData.append("offset", offset); // Ajouter l'offset pour le chargement
        formData.append("nonce", nonce);

        // Récupérer les valeurs actuelles des filtres
        const selectedFormat = formatFilter.value;
        const selectedCategory = categoryFilter.value;
        const selectedSort = sortFilter.value;

        formData.append("format", selectedFormat);
        formData.append("category", selectedCategory);
        formData.append("sort", selectedSort);

        fetch(galleryGrid.dataset.ajaxurl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                let tempDiv = document.createElement("div");
                tempDiv.innerHTML = data.data.html;
                let newItems = tempDiv.querySelectorAll(".gallery-item");

                newItems.forEach(item => galleryGrid.appendChild(item));

                // Déclencher un événement pour signaler que de nouveaux posts ont été ajoutés
                document.dispatchEvent(new Event("postsUpdated"));

                offset = data.data.newOffset; // Mettre à jour l'offset avec la nouvelle valeur renvoyée par le serveur
                updateGalleryData();

                if (!data.data.has_more) {
                    loadMoreButton.style.display = "none"; // Cacher le bouton si plus d'images sont disponibles
                }
            }
        })
        .catch(error => console.error("Erreur AJAX:", error));
    }

    // Fonction pour mettre à jour la galerie selon les filtres
    function updateGallery() {
        let formData = new FormData();
        formData.append("action", "load_photos");
        formData.append("format", formatFilter.value);
        formData.append("category", categoryFilter.value);
        formData.append("sort", sortFilter.value); 
        formData.append("nonce", nonce);

        fetch(galleryGrid.dataset.ajaxurl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                galleryGrid.innerHTML = data.data.html; 
                offset = 8; // Réinitialiser l'offset à 8 pour le premier chargement
                loadMoreButton.style.display = "block"; 
            }
        })
        .catch(error => console.error("Erreur AJAX:", error));
    }

    // Gestion du clic sur le bouton "Load More"
    loadMoreButton.addEventListener("click", function (e) {
        e.preventDefault();
        loadMoreImages();
    });

    // Mise à jour de la galerie lors des changements de filtres
    formatFilter.addEventListener("change", updateGallery);
    categoryFilter.addEventListener("change", updateGallery);
    sortFilter.addEventListener("change", updateGallery);
});

</script>
