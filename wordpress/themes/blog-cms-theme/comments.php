<?php if (post_password_required()) { return; } ?>
<section class="comments-area" id="comments">
<?php if (have_comments()) : ?><h2 class="comments-title">Trao đổi cùng nhau (<?php echo esc_html(get_comments_number()); ?>)</h2><ol class="comment-list"><?php wp_list_comments(array('style' => 'ol', 'short_ping' => true, 'avatar_size' => 42)); ?></ol><?php the_comments_navigation(); endif; ?>
<?php if (!comments_open() && get_comments_number()) : ?><p class="no-comments">Bình luận đã đóng.</p><?php endif; ?>
<?php comment_form(array('title_reply' => 'Bạn nghĩ sao về bài viết này?', 'label_submit' => 'Gửi bình luận')); ?>
</section>
