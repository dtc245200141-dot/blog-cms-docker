<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="site-shell">
  <div class="topline"><div class="container topline-inner"><span class="topline-note"><span class="live-dot"></span> BLOG • CÔNG NGHỆ • ĐỜI SỐNG</span><span class="topline-date"><?php echo esc_html(wp_date('l, d/m/Y')); ?></span></div></div>
  <header class="site-header">
    <div class="container header-main">
      <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php bloginfo('name'); ?> - Trang chủ">
        <span class="brand-mark" aria-hidden="true"><span></span><span></span><span></span></span>
        <span class="brand-copy"><span class="brand-name"><?php bloginfo('name'); ?></span><span class="brand-tagline"><?php bloginfo('description'); ?></span></span>
      </a>
      <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation"><span></span><span></span><span></span><span class="screen-reader-text">Mở menu</span></button>
      <nav class="primary-navigation" id="primary-navigation" aria-label="Điều hướng chính">
        <?php wp_nav_menu(array('theme_location' => 'primary', 'container' => false, 'menu_class' => 'nav-list', 'fallback_cb' => 'bcm_fallback_menu')); ?>
      </nav>
      <a class="header-search" href="<?php echo esc_url(home_url('/?s=')); ?>" aria-label="Tìm kiếm"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 5 5"></path></svg></a>
    </div>
  </header>
  <main id="main-content" class="site-main">
