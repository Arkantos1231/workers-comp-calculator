<?php
/**
 * The template for displaying single workers comp calculator posts
 * Supports both Block Themes (FSE) and Classic Themes
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if we are using a block theme
if (function_exists('wp_is_block_theme') && wp_is_block_theme()) {
    ?>
    <!doctype html>
    <html <?php language_attributes(); ?>>

    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?>>
        <?php wp_body_open(); ?>

        <div class="wp-site-blocks">
            <?php block_template_part('header'); ?>

            <main class="wp-block-group is-layout-flow" style="min-height: 50vh;">
                <!-- Calculator Content -->
                <?php
                while (have_posts()):
                    the_post();
                    echo do_shortcode('[workers_comp_calculator]');
                endwhile;
                ?>
            </main>

            <?php block_template_part('footer'); ?>
        </div>

        <?php wp_footer(); ?>
    </body>

    </html>
    <?php
} else {
    // Classic Theme Fallback
    get_header();
    ?>

    <div id="primary" class="content-area wcc-classic-container"
        style="width: 100%; max-width: 1200px; margin: 0 auto; padding: 20px;">
        <main id="main" class="site-main" role="main">
            <?php
            while (have_posts()):
                the_post();
                echo do_shortcode('[workers_comp_calculator]');
            endwhile;
            ?>
        </main>
    </div>

    <style>
        .wcc-classic-container {
            width: 100%;
            margin: 0 auto;
        }
    </style>

    <?php
    get_footer();
}
?>