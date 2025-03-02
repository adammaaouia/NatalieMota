<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// BEGIN ENQUEUE PARENT ACTION
if (!function_exists('chld_thm_cfg_locale_css')) :
    function chld_thm_cfg_locale_css($uri)
    {
        if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css')) {
            $uri = get_template_directory_uri() . '/rtl.css';
        }
        return $uri;
    }
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

if (!function_exists('chld_thm_cfg_parent_css')) :
    function chld_thm_cfg_parent_css()
    {
        wp_enqueue_style('chld_thm_cfg_parent', trailingslashit(get_template_directory_uri()) . 'style.css', array());
    }
endif;
add_action('wp_enqueue_scripts', 'chld_thm_cfg_parent_css', 10);
// END ENQUEUE PARENT ACTION

// Charger les images depuis le custom post type "photos"
function filter_photos() {
    check_ajax_referer('load_more_nonce', 'nonce');

    // Récupération des filtres et de l'offset
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : '';
    $offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0; // Utiliser l'offset envoyé en AJAX
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'date_desc'; // Récupérer l'option de tri

    // Définir les arguments pour la requête WP
    $args = [
        'post_type'      => 'photos',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        'orderby'        => 'date',
        'order'          => $sort === 'date_asc' ? 'ASC' : 'DESC', // Appliquer l'ordre en fonction du filtre
        'offset'         => $offset,
        'meta_query'     => []
    ];

    // Ajouter les filtres si présents
    if (!empty($category)) {
        $args['meta_query'][] = [
            'key'     => 'categories',
            'value'   => $category,
            'compare' => 'LIKE'
        ];
    }

    if (!empty($format)) {
        $args['meta_query'][] = [
            'key'     => 'formats',
            'value'   => $format,
            'compare' => 'LIKE'
        ];
    }

    // Requête WP avec les arguments modifiés
    $query = new WP_Query($args);
    $html = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $image = get_field('image', get_the_ID());
            $image_url = !empty($image) ? esc_url($image['url']) : 'default-image.jpg';
            $title = get_the_title();
            $permalink = get_permalink();

            // Ajout de l'HTML pour chaque post
            $html .= '<div class="gallery-item">';
            $html .= '<a href="' . esc_url($permalink) . '">';
            $html .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($title) . '">';
            $html .= '</a>';
            $html .= '<div class="overlay">';
            $html .= '<div class="post-title">' . esc_html($title) . '</div>';
            $category = get_field('categories');
            $html .= '<p class="post-categories">' . esc_html($category ? $category : 'Non spécifiée') . '</p>';
            $html .= '<a href="' . esc_url($permalink) . '" class="eye-icon">&#128065;</a>';
            $html .= '<div class="fullscreen-icon" data-post-id="' . get_the_ID() . '"><i class="fa-solid fa-expand"></i></div>';
            $html .= '</div>'; // Ferme .overlay
            $html .= '</div>'; // Ferme .gallery-item
        }
        wp_reset_postdata();
    }

    wp_send_json_success(['html' => $html, 'offset' => $offset + 8]); // Retourne également le nouvel offset
}

add_action('wp_ajax_filter_photos', 'filter_photos');
add_action('wp_ajax_nopriv_filter_photos', 'filter_photos');

function load_more_photos() {
    check_ajax_referer('load_more_nonce', 'nonce');

    // Récupération des filtres et de l'offset
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : '';
    $offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0; // Utiliser l'offset envoyé en AJAX
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'date_desc'; // Récupérer l'option de tri

    // Définir les arguments pour la requête WP
    $args = [
        'post_type'      => 'photos',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        'orderby'        => 'date',
        'order'          => $sort === 'date_asc' ? 'ASC' : 'DESC', // Appliquer l'ordre en fonction du filtre
        'offset'         => $offset,
        'meta_query'     => []
    ];

    // Ajouter les filtres si présents
    if (!empty($category)) {
        $args['meta_query'][] = [
            'key'     => 'categories',
            'value'   => $category,
            'compare' => 'LIKE'
        ];
    }

    if (!empty($format)) {
        $args['meta_query'][] = [
            'key'     => 'formats',
            'value'   => $format,
            'compare' => 'LIKE'
        ];
    }

    $query = new WP_Query($args);
    $html = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $image = get_field('image', get_the_ID());
            $image_url = !empty($image) ? esc_url($image['url']) : 'default-image.jpg';
            $title = get_the_title();
            $permalink = get_permalink();
    
            // Ajout de l'HTML pour chaque post
            $html .= '<div class="gallery-item">';
            $html .= '<a href="' . esc_url($permalink) . '">';
            $html .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($title) . '">';
            $html .= '</a>';
            $html .= '<div class="overlay">';
            
            // Affichage du titre du post
            $html .= '<div class="post-title">' . esc_html($title) . '</div>';
    
            // Affichage de la catégorie via le champ personnalisé
            $category = get_field('categories');
            $html .= '<p class="post-categories">' . esc_html($category ? $category : 'Non spécifiée') . '</p>';
    
            // Icône "œil" et "plein écran"
            $html .= '<a href="' . esc_url($permalink) . '" class="eye-icon">&#128065;</a>';
            $html .= '<div class="fullscreen-icon" data-post-id="' . get_the_ID() . '"><i class="fa-solid fa-expand"></i></div>';
            
            $html .= '</div>'; // Ferme .overlay
            $html .= '</div>'; // Ferme .gallery-item
        }
        wp_reset_postdata();
    }

    // Renvoi des données et calcul de l'offset suivant
    wp_send_json_success(['html' => $html, 'newOffset' => $offset + 8]);
}

add_action('wp_ajax_load_more_photos', 'load_more_photos');
add_action('wp_ajax_nopriv_load_more_photos', 'load_more_photos');

// Fonction AJAX pour récupérer les détails du post
add_action('wp_ajax_load_post_details', 'load_post_details');
add_action('wp_ajax_nopriv_load_post_details', 'load_post_details');

function load_post_details() {
    // Vérification du nonce pour la sécurité
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'load_post_details_nonce')) {
        echo json_encode(['success' => false, 'message' => 'Nonce invalide']);
        wp_die();
    }

    // Récupère l'ID du post
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if (!$post_id) {
        echo json_encode(['success' => false, 'message' => 'Post ID manquant']);
        wp_die();
    }

    // Récupérer la catégorie du post
    $category_name = get_field('categories', $post_id);
    $category_name = $category_name ? $category_name : 'Non spécifiée';

    // Récupérer la référence via un champ personnalisé
    $reference = get_post_meta($post_id, 'reference', true);
    $reference = $reference ? esc_html($reference) : 'Non spécifiée';

    // Réponse JSON avec les données
    echo json_encode([
        'success' => true,
        'data' => [
            'category' => $category_name,
            'reference' => $reference
        ]
    ]);

    wp_die();
}


