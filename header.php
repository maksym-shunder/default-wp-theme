<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta
		name="viewport"
		content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"
	>
	<title><?php bloginfo('name'); ?><?php is_front_page() ? ' - ' . get_bloginfo('description') : ''; ?></title>

	<?php $site_url = get_site_url(); ?>
	<link
		rel="preconnect"
		href="<?= $site_url ?>"
	>
	<link
		rel="dns-prefetch"
		href="<?= $site_url ?>"
	>

	<?php $ver = filemtime(get_template_directory() . '/assets/css/global.css'); ?>
	<link
		rel="preload"
		href="<?= get_template_directory_uri() ?>/assets/css/global.css?ver=<?= $ver ?>"
		as="style"
		onload="this.rel=`stylesheet`"
	>

	<?php $ver = filemtime(get_template_directory() . '/assets/css/components/header.css'); ?>
	<link
		rel="preload"
		href="<?= get_template_directory_uri() ?>/assets/css/components/header.css?ver=<?= $ver ?>"
		as="style"
		onload="this.rel=`stylesheet`"
	>

	<?php
	$blockToPreload = get_first_html_block_name();
	$blockStylesUrl = get_template_directory_uri() . '/template-parts/gutenberg-blocks/' . $blockToPreload . '/assets/style.css';
	$ver = filemtime(get_template_directory() . '/template-parts/gutenberg-blocks/' . $blockToPreload . '/assets/style.css');
	?>
	<link
		rel="preload"
		href="<?= $blockStylesUrl ?>?ver=<?= $ver ?>"
		as="style"
		onload="this.rel=`stylesheet`"
	>

	<link
		rel="preload"
		href="<?= get_field('header_logo', 'option')['url'] ?? '' ?>"
		as="image"
	>

	<!--	Remove if no jQuery -->
	<link
		rel="preload"
		href="/wp-includes/js/jquery/jquery.min.js"
		as="script"
	>


	<link
		rel="preload"
		href="<?= get_template_directory_uri() ?>/assets/font/PPNeueMontreal-Book.woff2"
		as="font"
		type="font/woff2"
		crossorigin
	>

	<?php wp_head(); ?>
</head>

<body <?php body_class('page__body'); ?>>
<?php wp_body_open(); ?>

<header
	class="header"
	id="header"
>
	<div class="container">
		<div class="flex_wrapper">
			<div class="logo_wrapper">
				<span
					class="burger hide_desktop"
					data-action="toggleMobileMenu"
				>
					<svg
						class="icon-open"
						width="24"
						height="24"
						viewBox="0 0 24 24"
						xmlns="http://www.w3.org/2000/svg"
					>
						<line
							x1="2.5"
							y1="5.5"
							x2="21.5"
							y2="5.5"
							stroke="#102F13"
							stroke-linecap="round"
						/>
						<line
							x1="2.5"
							y1="11.5"
							x2="21.5"
							y2="11.5"
							stroke="#102F13"
							stroke-linecap="round"
						/>
						<line
							x1="2.5"
							y1="17.5"
							x2="21.5"
							y2="17.5"
							stroke="#102F13"
							stroke-linecap="round"
						/>
					</svg>

					<svg
						class="icon-close"
						width="14"
						height="14"
						viewBox="0 0 14 14"
						xmlns="http://www.w3.org/2000/svg"
					>
						<path
							d="M1 1L13 13M13 1L1 13"
							stroke="black"
							stroke-linecap="round"
						/>
					</svg>
				</span>

				<a
					href="/"
					class="site-logo"
				>
					logo
					<img
						src="<?= get_field('header_logo', 'option')['url'] ?? '' ?>"
						alt="<?= get_field('header_logo', 'option')['alt'] ?? '' ?>"
						fetchpriority="high"
					>
				</a>
			</div>

			<div class="nav_box">
				<?php wp_nav_menu([
					'theme_location' => 'main-menu',
					'container'      => false,
					'menu_class'     => 'header_menu',
				]); ?>
			</div>

			<div class="user_box">
				<button
					data-action="togglePopup"
					data-target="#example_popup"
					class="primary_button"
				>
					Example Popup
				</button>

				<?php get_template_part('template-parts/components/basket'); ?>
			</div>
		</div>
	</div>
</header>

<?php get_template_part('template-parts/components/header/cart-popup'); ?>