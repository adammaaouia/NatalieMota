<?php get_header(); ?>

<main id="single-photo">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

        <?php
        $image = get_the_ID();
        // Récupérer l'image via ACF
        $acf_image = get_field('image', $image); // Remplacer 'image' par le nom de ton champ ACF
        $image_url = !empty($acf_image) ? esc_url($acf_image['url']) : 'default-image.jpg'; // Fallback si l'image ACF est vide
        
        // Autres champs ACF
        $reference = get_field('reference'); // ACF pour la référence photo
        $formats = get_field('format');
        $date_prise = get_the_date('d/m/Y');
        $categories = get_field('categories');
        $type = get_field('type');
        ?>

        <div class="photo-container">
            <!-- Bloc Infos -->
            <div class="photo-info">
                <h1><?php the_title(); ?></h1>
                <p>Référence: <?php echo esc_html(get_field('reference') ? get_field('reference') : 'Non spécifiée'); ?></p>
                <p>Catégorie : <?php echo esc_html(get_field('categories') ? get_field('categories') : 'Non spécifiée'); ?></p>
                <p>Format : <?php echo esc_html(get_field('formats') ? get_field('formats') : 'Non spécifié'); ?></p>
                <p>Type : <?php echo esc_html(get_field('type') ? get_field('type') : 'Non spécifié'); ?></p>
                <p>Année : <?php echo esc_html($date_prise); ?></p>
            </div>


            <!-- Bloc Photo -->
            <div class="photo-display">
                <img src="<?php echo $image_url; ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
            </div>
        </div>

        <!-- Bloc Contact & Navigation -->
        <div class="photo-footer">
            <span>Cette photo vous intéresse ?</span>
            <div class="contact-link">
                <button href="#" class="open-contact-popup" data-ref="<?php echo esc_attr($reference); ?>">
                    Contact
                </button>
            </div>
            <div class="photo-nav">
    <?php
    $prev_post = get_previous_post();
    $next_post = get_next_post();

    // Vérifier s'il existe une photo précédente
    if ($prev_post) :
        // Récupérer l'image ACF pour la photo précédente
        $prev_post_image = get_field('image', $prev_post->ID); // Remplacer 'image' par le nom du champ ACF pour l'image
        $prev_post_image_url = !empty($prev_post_image) ? esc_url($prev_post_image['url']) : 'default-image.jpg'; // Si pas d'image, utiliser une image par défaut
    ?>
    <?php if ($next_post) : ?>
        <a href="<?php echo get_permalink($next_post->ID); ?>" class="nav-link next-photo"
           data-thumbnail="<?php echo get_the_post_thumbnail_url($next_post->ID, 'thumbnail'); ?>">
            ⬅  
        </a>
    <?php endif; ?>

    <!-- Affichage de l'image de la photo précédente juste avant la flèche -->
    <a href="<?php echo get_permalink($prev_post->ID); ?>" class="nav-link prev-photo"
           data-thumbnail="<?php echo get_the_post_thumbnail_url($prev_post->ID, 'thumbnail'); ?>">
            <!-- Image de la photo précédente -->
            <img src="<?php echo $prev_post_image_url; ?>" alt="Aperçu photo précédente" class="prev-photo-thumbnail">
            ➡
        </a>
    <?php endif; ?>
</div>

        </div>

        <!-- Bloc Photos Apparentées -->
        <div class="related-photos">
            <h2>Vous aimerez aussi ?</h2>
            <div class="gallery-grid" data-ajaxurl="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                <?php
                // La requête WP_Query pour récupérer des photos avec la même catégorie que l'image actuelle
                $related_query = new WP_Query(array(
                    'post_type' => 'photos',
                    'posts_per_page' => 2, // Limiter à 2 photos
                    'post__not_in' => array($image), // Exclure l'image actuelle
                    'meta_query' => array(
                        array(
                            'key' => 'categories', // Le champ ACF pour la catégorie
                            'value' => $categories, // Catégorie du post actuel
                            'compare' => '=' // Comparer exactement la catégorie
                        )
                    )
                ));

                // Vérifier si des résultats ont été trouvés
                if ($related_query->have_posts()) : 
                    while ($related_query->have_posts()) : $related_query->the_post();

                        // Récupérer l'image via ACF pour chaque post apparenté
                        $acf_image = get_field('image'); 
                        $image_url = !empty($acf_image) ? esc_url($acf_image['url']) : 'default-image.jpg'; 
                        $title = get_the_title();
                        $permalink = get_permalink();
                ?>
                        <div class="gallery-item">
                            <a href="<?php echo esc_url($permalink); ?>">
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
                else :
                    echo '<p>Aucune photo apparentée trouvée.</p>';
                endif;
                wp_reset_postdata();
                ?>
            </div>
        </div>

    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>

<?php
get_template_part('lightbox');
?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Gestion de la pop-up contact
        document.querySelectorAll(".open-contact-popup").forEach(button => {
            button.addEventListener("click", function (e) {
                e.preventDefault();
                const refPhoto = this.dataset.ref;
                const popup = document.getElementById("contact-popup");
                const modalInput = popup.querySelector("#modal-ref-photo"); 
                if (modalInput) modalInput.value = refPhoto;
                popup.style.display = "flex";
            });
        });

        // Gestion de la lightbox
        document.querySelectorAll(".photo-lightbox-icon").forEach(icon => {
            icon.addEventListener("click", function (e) {
                e.preventDefault();
                const imgSrc = this.dataset.img;
                const lightbox = document.createElement("div");
                lightbox.classList.add("lightbox");
                lightbox.innerHTML = `<div class="lightbox-content"><img src="${imgSrc}" /><span class="close-lightbox">&times;</span></div>`;
                document.body.appendChild(lightbox);
                document.querySelector(".close-lightbox").addEventListener("click", () => lightbox.remove());
            });
        });
    });
</script>

