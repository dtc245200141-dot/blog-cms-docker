<?php get_header(); ?>
<?php
$hero_query = new WP_Query(array('post_type' => 'post', 'posts_per_page' => 1, 'post_status' => 'publish', 'ignore_sticky_posts' => false));
$hero_id = $hero_query->have_posts() ? (int) $hero_query->posts[0]->ID : 0;
?>
<section class="hero-section">
  <div class="container hero-grid">
    <div class="hero-copy">
      <p class="eyebrow"><span class="eyebrow-line"></span> NƠI Ý TƯỞNG BẮT ĐẦU</p>
      <h1>Mỗi ngày một<br><em>góc nhìn mới.</em></h1>
      <p class="hero-description">Những câu chuyện về công nghệ, sáng tạo và cuộc sống — được chọn lọc để bạn đọc chậm lại, nghĩ sâu hơn và tìm thấy điều hữu ích.</p>
      <div class="hero-actions"><a class="button button-primary" href="#latest-stories">Khám phá bài viết <span aria-hidden="true">↘</span></a><a class="quiet-link" href="<?php echo esc_url(admin_url('post-new.php')); ?>">Chia sẻ câu chuyện <span aria-hidden="true">↗</span></a></div>
      <div class="hero-proof"><div class="avatar-stack"><span>Ý</span><span>T</span><span>✦</span></div><span>Ý tưởng nhỏ, giá trị dài lâu.</span></div>
    </div>
    <div class="hero-art" aria-label="Minh họa hình khối cho blog" role="img">
      <div class="art-orbit art-orbit-large"></div><div class="art-orbit art-orbit-small"></div><div class="art-sun"></div><div class="art-card art-card-back"></div><div class="art-card art-card-front"><span class="art-card-label">NOTES / 001</span><span class="art-card-title">Ideas<br>into stories.</span><span class="art-card-line"></span><span class="art-card-foot">READ • THINK • CREATE</span></div><span class="art-star star-one">✳</span><span class="art-star star-two">✦</span><span class="art-caption">A LITTLE SPACE<br>FOR BIG IDEAS</span>
      <div class="hero-art-bottom"><span class="hero-art-dot"></span><span><?php echo $hero_id ? 'BÀI VIẾT NỔI BẬT' : 'BLOG CMS MAGAZINE'; ?></span><span class="art-bottom-line"></span><span>01 — ∞</span></div>
    </div>
  </div>
</section>
<section class="topic-strip"><div class="container topic-inner"><span class="topic-label">CÓ THỂ BẠN QUAN TÂM</span><div class="topic-links">
<?php $topic_categories = get_categories(array('number' => 5, 'hide_empty' => false, 'exclude' => 1)); if ($topic_categories) : foreach ($topic_categories as $topic) : ?><a href="<?php echo esc_url(get_category_link($topic->term_id)); ?>"><?php echo esc_html($topic->name); ?><span aria-hidden="true">↗</span></a><?php endforeach; else : ?><a href="#latest-stories">Công nghệ <span aria-hidden="true">↗</span></a><a href="#latest-stories">Sáng tạo <span aria-hidden="true">↗</span></a><a href="#latest-stories">Đời sống <span aria-hidden="true">↗</span></a><?php endif; ?>
</div></div></section>
<section class="featured-section section-space">
 <div class="container">
  <div class="section-heading"><div><p class="eyebrow">ĐƯỢC CHỌN LỌC</p><h2>Điểm nhấn <em>hôm nay.</em></h2></div><span class="section-index">01 / FEATURED</span></div>
  <?php if ($hero_id) : $hero_query->the_post(); ?>
  <article class="featured-story">
    <a class="featured-image" href="<?php the_permalink(); ?>"><?php if (has_post_thumbnail()) : the_post_thumbnail('large', array('loading' => 'lazy')); else : ?><span class="featured-placeholder"><span class="featured-shape"></span><span class="featured-shape featured-shape-two"></span><span class="featured-placeholder-word">FEATURED<br>STORY</span></span><?php endif; ?><span class="featured-image-tag">BÀI VIẾT NỔI BẬT</span></a>
    <div class="featured-copy"><div class="card-meta"><?php echo wp_kses_post(bcm_category_badge()); ?><span><?php echo esc_html(get_the_date('d/m/Y')); ?></span></div><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 38)); ?></p><div class="featured-footer"><span><?php echo esc_html(bcm_post_reading_time()); ?> phút đọc</span><a class="round-arrow" href="<?php the_permalink(); ?>" aria-label="Đọc bài viết">↗</a></div></div>
  </article>
  <?php wp_reset_postdata(); else : ?>
  <div class="featured-story featured-empty"><div class="featured-placeholder"><span class="featured-shape"></span><span class="featured-shape featured-shape-two"></span><span class="featured-placeholder-word">YOUR NEXT<br>BIG IDEA</span></div><div class="featured-copy"><p class="eyebrow">KHU VỰC BÀI VIẾT NỔI BẬT</p><h3>Nội dung hay bắt đầu từ một ý tưởng.</h3><p>Khi bạn xuất bản bài viết đầu tiên trong WordPress, bài viết mới nhất sẽ tự động xuất hiện tại khu vực này.</p><a class="button button-primary" href="<?php echo esc_url(admin_url('post-new.php')); ?>">Tạo bài viết đầu tiên <span aria-hidden="true">↗</span></a></div></div>
  <?php endif; ?>
 </div>
</section>
<section class="latest-section section-space" id="latest-stories"><div class="container"><div class="section-heading"><div><p class="eyebrow">MỚI TRÊN BLOG</p><h2>Những câu chuyện <em>mới nhất.</em></h2></div><a class="text-link" href="<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>">Tất cả bài viết <span aria-hidden="true">→</span></a></div>
<?php $latest_query = new WP_Query(array('post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 6, 'post__not_in' => $hero_id ? array($hero_id) : array(), 'ignore_sticky_posts' => true, 'paged' => max(1, get_query_var('paged')))); ?>
<?php if ($latest_query->have_posts()) : ?><div class="article-grid"><?php while ($latest_query->have_posts()) : $latest_query->the_post(); get_template_part('template-parts/content', 'card'); endwhile; ?></div><?php else : ?><div class="article-grid article-grid-empty"><?php get_template_part('template-parts/content', 'none'); ?></div><?php endif; wp_reset_postdata(); ?>
</div></section>
<section class="newsletter-section"><div class="container newsletter-card"><div class="newsletter-symbol">✳</div><div><p class="eyebrow">BẮT ĐẦU CHIA SẺ</p><h2>Điều bạn biết có thể<br><em>là điều ai đó cần đọc.</em></h2><p>Đăng nhập trang quản trị để viết bài, thêm chuyên mục và xây dựng blog của riêng bạn.</p></div><a class="button button-light" href="<?php echo esc_url(admin_url('post-new.php')); ?>">Viết bài ngay <span aria-hidden="true">↗</span></a></div></section>
<?php get_footer(); ?>
