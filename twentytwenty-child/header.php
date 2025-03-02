<?php
/**
 * Header file for the Twenty Twenty Child Theme.
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Child
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Photographe freelance spécialisé dans la capture de moments uniques. Services de photographie pour événements, portraits et plus. Capturer la beauté de chaque instant.">
    <title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
    
    <!-- Intégration de la police Space Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <header id="site-header">
        <div class="container">
            <!-- Logo -->
            <a href="<?php echo home_url('/'); ?>" class="site-logo">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/Photos NMota/unnamed.png" alt="Logo de Nathalie Mota">
            </a>

            <!-- Navigation Desktop (Menu normal) -->
            <nav class="desktop-navigation">
                <ul>
                    <li><a href="<?php echo home_url('/'); ?>" class="space-mono-regular">ACCUEIL</a></li>
                    <li><a href="<?php echo home_url('/a-propos'); ?>" class="space-mono-regular">À PROPOS</a></li>
                    <li><a href="#" id="open-contact-popup" class="space-mono-regular">CONTACT</a></li>
                </ul>
            </nav>

            <!-- Bouton Hamburger (Mobile) -->
            <button id="menu-toggle" class="hamburger-menu" aria-label="Menu">
                ☰
            </button>

            <!-- Menu Mobile -->
            <nav class="mobile-navigation" id="mobile-menu">
                <ul>
                    <li><a href="<?php echo home_url('/'); ?>" class="space-mono-regular">ACCUEIL</a></li>
                    <li><a href="<?php echo home_url('/a-propos'); ?>" class="space-mono-regular">À PROPOS</a></li>
                    <li><a href="#" id="mobile-open-contact-popup" class="space-mono-regular">CONTACT</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Pop-up Contact -->
    <div id="contact-popup">
        <div class="popup-content">
            <div class="custom-form">
                <div class="contact-title">
                    <span class="span1">CONTACTCONTACTCONTACT</span>
                    <span class="span2">CONTACTCONTACTCONTACT</span>
                </div>
            </div>
            <button id="close-popup">&times;</button>
            <?php echo do_shortcode('[contact-form-7 id="24c7615" title="Contact"]'); ?>
        </div>
    </div>

    <!-- Script JavaScript pour la pop-up et le menu mobile -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Gérer la pop-up contact
            const openPopupDesktop = document.getElementById('open-contact-popup');
            const openPopupMobile = document.getElementById('mobile-open-contact-popup');
            const closePopup = document.getElementById('close-popup');
            const popup = document.getElementById('contact-popup');

            if (openPopupDesktop && openPopupMobile && closePopup && popup) {
                openPopupDesktop.addEventListener('click', (e) => {
                    e.preventDefault();
                    popup.style.display = 'flex';
                });

                openPopupMobile.addEventListener('click', (e) => {
                    e.preventDefault();
                    popup.style.display = 'flex';
                });

                closePopup.addEventListener('click', () => {
                    popup.style.display = 'none';
                });

                window.addEventListener('click', (e) => {
                    if (e.target === popup) {
                        popup.style.display = 'none';
                    }
                });
            } else {
                console.error('Un des éléments nécessaires pour la pop-up est introuvable.');
            }

            // JavaScript pour le menu mobile
            const menuToggle = document.getElementById("menu-toggle");
            const mobileMenu = document.getElementById("mobile-menu");

            mobileMenu.style.display = 'none';

            menuToggle.addEventListener("click", () => {
                mobileMenu.style.display = (mobileMenu.style.display === 'none') ? 'block' : 'none';
            });

            document.addEventListener("click", (event) => {
                if (!mobileMenu.contains(event.target) && !menuToggle.contains(event.target)) {
                    mobileMenu.style.display = 'none';
                }
            });
        });
    </script>

    <?php wp_footer(); ?>
</body>
</html>

