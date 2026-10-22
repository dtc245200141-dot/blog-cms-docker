<?php
/**
 * Plugin Name: Blog CMS Theme First-Run Setup
 * Description: Activates the included Blog CMS Magazine theme once the WordPress site has completed installation.
 */
if (!defined('ABSPATH')) { exit; }

add_action('init', function () {
    if (is_multisite() || get_option('bcm_theme_auto_activated')) {
        return;
    }
    // During the WordPress installation wizard, siteurl is not set yet.
    if (!get_option('siteurl')) {
        return;
    }
    // Give a fresh installation a readable Vietnamese site title/tagline.
    if (get_option('blogname') === 'My WordPress') {
        update_option('blogname', 'Góc Nhìn');
    }
    if (get_option('blogdescription') === 'Just another WordPress site') {
        update_option('blogdescription', 'Blog • Công nghệ • Sáng tạo • Đời sống');
    }

    $theme = wp_get_theme('blog-cms-theme');
    if (!$theme->exists()) {
        return;
    }
    switch_theme('blog-cms-theme');
    update_option('bcm_theme_auto_activated', 1);
}, 1);
