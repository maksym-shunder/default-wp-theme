<?php

/* Template Name: Checkout */

get_header(); ?>

<main class="checkout_wrapper">
	<div class="container">
		<?= do_shortcode('[woocommerce_checkout]') ?>
	</div>
</main>

<?php get_footer(); ?>
