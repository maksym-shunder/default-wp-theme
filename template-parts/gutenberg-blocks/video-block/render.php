<?php
$video = theme_field('video');
$video_poster = theme_field('video_poster');

$video_url = '';
if (is_array($video)) {
	$video_url = $video['url'] ?? '';
} elseif (is_numeric($video)) {
	$video_url = wp_get_attachment_url((int) $video);
} elseif (is_string($video)) {
	$video_url = $video;
}

$poster_url = '';
if (is_array($video_poster)) {
	$poster_url = $video_poster['url'] ?? '';
} elseif (is_numeric($video_poster)) {
	$poster_url = wp_get_attachment_url((int) $video_poster);
} elseif (is_string($video_poster)) {
	$poster_url = $video_poster;
}

$block_anchor = $block['anchor'] ?? '';
$block_classes = 'video-block';
if (!empty($block['className'])) {
	$block_classes .= ' ' . $block['className'];
}
$is_preview = !empty($is_preview);
?>

<section
	class="<?= esc_attr($block_classes) ?>"
	id="<?= esc_attr($block_anchor) ?>"
>
	<div class="container">
		<?php if ($video_url): ?>
			<div
				class="video_box"
				data-video-box
			>
				<video
					playsinline
					preload="none"
					poster="<?= esc_url($poster_url) ?>"
				>
					<source
						src="<?= esc_url($video_url) ?>"
						type="video/mp4"
					/>
				</video>

				<?php get_template_part('template-parts/components/video-controls'); ?>
			</div>
		<?php elseif ($is_preview): ?>
			<div class="video-block__placeholder">
				<?= esc_html__('Upload a video and poster in the block fields.', 'digiway') ?>
			</div>
		<?php endif; ?>
	</div>
</section>
