<?php

/* Template Name: Reset Password */

get_header(); ?>

	<main class="user-page">
		<div class="container">

			<?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
				<div class="thankyou_box">
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Inventore, quidem.</p>
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis doloremque explicabo hic quaerat sit vitae.</p>
				</div>
			<?php else:
				include get_template_directory() . '/template-parts/components/user/reset-pass-form.php';
			endif; ?>
		</div>
	</main>

<?php get_footer(); ?>
<?php
