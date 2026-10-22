<section class="empty-state">
  <span class="empty-icon" aria-hidden="true">✳</span>
  <p class="eyebrow">BẮT ĐẦU TỪ ĐÂY</p>
  <h2><?php echo is_search() ? 'Chưa tìm thấy bài viết phù hợp' : 'Câu chuyện đầu tiên đang chờ bạn'; ?></h2>
  <p><?php echo is_search() ? 'Thử một từ khóa ngắn hơn hoặc quay về trang chủ để khám phá nội dung mới.' : 'Giao diện blog đã sẵn sàng. Hãy đăng nhập trang quản trị và tạo bài viết đầu tiên để nội dung xuất hiện tại đây.'; ?></p>
  <?php if (current_user_can('edit_posts')) : ?><a class="button button-primary" href="<?php echo esc_url(admin_url('post-new.php')); ?>">+ Tạo bài viết đầu tiên</a><?php else : ?><a class="button button-primary" href="<?php echo esc_url(wp_login_url()); ?>">Đăng nhập quản trị</a><?php endif; ?>
</section>
