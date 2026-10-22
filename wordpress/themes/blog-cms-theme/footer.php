  </main>
  <footer class="site-footer">
    <div class="container footer-main">
      <div class="footer-about">
        <a class="brand brand-footer" href="<?php echo esc_url(home_url('/')); ?>">
          <span class="brand-mark" aria-hidden="true"><span></span><span></span><span></span></span>
          <span class="brand-copy"><span class="brand-name"><?php bloginfo('name'); ?></span><span class="brand-tagline"><?php bloginfo('description'); ?></span></span>
        </a>
        <p>Một góc nhỏ để đọc điều hay, lưu lại ý tưởng và chia sẻ những câu chuyện đáng nhớ.</p>
      </div>
      <div class="footer-links"><h2>Khám phá</h2><?php wp_nav_menu(array('theme_location' => 'footer', 'container' => false, 'menu_class' => 'footer-nav', 'fallback_cb' => 'bcm_fallback_menu')); ?></div>
      <div class="footer-note"><span class="footer-kicker">BLOG CMS LAB</span><h2>Nội dung tốt bắt đầu từ một góc nhìn mới.</h2><a class="text-link" href="<?php echo esc_url(admin_url('post-new.php')); ?>">Viết bài đầu tiên <span aria-hidden="true">↗</span></a></div>
    </div>
    <div class="container footer-bottom"><span>© <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?>.</span><span>Được xây dựng với WordPress · Docker Compose</span><a href="#main-content">Lên đầu trang ↑</a></div>
  </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
