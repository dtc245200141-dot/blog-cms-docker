<?php
/** Theme setup and assets. */
if (!defined('ABSPATH')) { exit; }

function bcm_theme_setup() {
    load_theme_textdomain('blog-cms-theme', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('custom-logo', array('height' => 80, 'width' => 260, 'flex-height' => true, 'flex-width' => true));
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets'));
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/theme.css');
    register_nav_menus(array(
        'primary' => __('Điều hướng chính', 'blog-cms-theme'),
        'footer' => __('Điều hướng chân trang', 'blog-cms-theme'),
    ));
}
add_action('after_setup_theme', 'bcm_theme_setup');

function bcm_enqueue_assets() {
    $version = wp_get_theme()->get('Version');
    wp_enqueue_style('bcm-theme-style', get_template_directory_uri() . '/assets/css/theme.css', array(), $version);
    wp_enqueue_script('bcm-theme-script', get_template_directory_uri() . '/assets/js/theme.js', array(), $version, true);
}
add_action('wp_enqueue_scripts', 'bcm_enqueue_assets');

function bcm_excerpt_length($length) { return 24; }
add_filter('excerpt_length', 'bcm_excerpt_length', 999);
function bcm_excerpt_more($more) { return '…'; }
add_filter('excerpt_more', 'bcm_excerpt_more');

function bcm_post_reading_time($post_id = 0) {
    $post_id = $post_id ?: get_the_ID();
    $content = get_post_field('post_content', $post_id);
    $words = str_word_count(wp_strip_all_tags(strip_shortcodes($content)));
    return max(1, (int) ceil($words / 220));
}

function bcm_category_badge($post_id = 0) {
    $categories = get_the_category($post_id ?: get_the_ID());
    if (!empty($categories)) {
        return '<a class="category-pill" href="' . esc_url(get_category_link($categories[0]->term_id)) . '">' . esc_html($categories[0]->name) . '</a>';
    }
    return '<span class="category-pill">Góc nhìn</span>';
}

function bcm_fallback_menu() {
    echo '<ul class="nav-list">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">Trang chủ</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#latest-stories')) . '">Bài viết</a></li>';
    echo '<li><a href="' . esc_url(home_url('/?s=')) . '">Tìm kiếm</a></li>';
    $categories = get_categories(array('number' => 4, 'hide_empty' => false, 'exclude' => 1));
    foreach ($categories as $category) {
        echo '<li><a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a></li>';
    }
    echo '</ul>';
}

function bcm_body_classes($classes) {
    $classes[] = 'blog-cms-site';
    if (is_front_page()) { $classes[] = 'is-front-page'; }
    return $classes;
}
add_filter('body_class', 'bcm_body_classes');
