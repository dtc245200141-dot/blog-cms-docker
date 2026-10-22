<?php get_header(); ?>
<section class="page-hero"><div class="container"><p class="eyebrow">BLOG CMS / JOURNAL</p><h1>Nhật ký <em>góc nhìn.</em></h1><p>Những bài viết mới nhất từ blog.</p></div></section>
<section class="section-space"><div class="container"><?php if (have_posts()) : ?><div class="article-grid"><?php while (have_posts()) : the_post(); get_template_part('template-parts/content', 'card'); endwhile; ?></div><div class="pagination-wrap"><?php the_posts_pagination(array('mid_size' => 2, 'prev_text' => '← Mới hơn', 'next_text' => 'Cũ hơn →')); ?></div><?php else : get_template_part('template-parts/content', 'none'); endif; ?></div></section>
<?php get_footer(); ?>
