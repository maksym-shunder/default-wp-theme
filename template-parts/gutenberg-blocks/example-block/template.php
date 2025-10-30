<?php
$title = get_field('text');

$block_anchor = $block['anchor'] ?? '';
$block_classes = 'example-block';
if (!empty($block['className'])) {
	$block_classes .= ' ' . $block['className'];
}
?>

<section
	class="<?= esc_attr($block_classes) ?>"
	id="<?= esc_attr($block_anchor) ?>"
>
	<div class="container">
		<img
			src="https://dev.dynastystomatology.pl/wp-content/uploads/2025/10/9f59a739bd83b1c62206963460a021fd8b827a45-1-1.avif"
			alt=""
			fetchpriority="high"
		/>

		<img
			src="https://dev.dynastystomatology.pl/wp-content/uploads/2025/10/image.avif"
			alt=""
			fetchpriority="high"
		/>
	</div>
</section>