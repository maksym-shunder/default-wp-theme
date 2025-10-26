<?php

/* Template Name: Login */

if (is_user_logged_in()) {
	header('Location: /');
}

get_header(); ?>

<main class="user-page">
	<div class="container">
		<?php include get_template_directory() . '/template-parts/components/user/login-form.php'; ?>
	</div>
</main>

<?php get_footer(); ?>
