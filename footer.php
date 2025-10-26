<?php
$footer_logo = get_field('footer_logo', 'option');
$footer_copyright = get_field('footer_copyright', 'option');
?>

<footer
	class="footer"
	id="footer"
>
	<div class="container">

	</div>
</footer>

<?php get_template_part('template-parts/popups/example-popup'); ?>

<?php wp_footer(); ?>

</body>
</html>

