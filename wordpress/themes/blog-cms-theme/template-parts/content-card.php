<article <?php post_class('article-card'); ?>>
  <a class="card-image" href="<?php the_permalink(); ?>" aria-label="Đọc bài: <?php the_title_attribute(); ?>">
    <?php if (has_post_thumbnail()) : the_post_thumbnail('large', array('loading' => 'lazy')); else : ?><span class="image-placeholder"><span class="placeholder-orbit orbit-one"></span><span class="placeholder-orbit orbit-two"></span><span class="placeholder-letter"><?php echo esc_html(mb_substr(wp_strip_all_tags(get_the_title()), 0, 1)); ?></span></span><?php endif; ?>
    <span class="image-arrow" aria-hidden="true">↗</span>
  </a>
  <div class="card-content">
    <div class="card-meta"><?php echo wp_kses_post(bcm_category_badge()); ?><span><?php echo esc_html(get_the_date('d/m/Y')); ?></span></div>
    <h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    <p class="card-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>
    <a class="read-link" href="<?php the_permalink(); ?>">Đọc bài viết <span aria-hidden="true">→</span></a>
  </div>
</article>
