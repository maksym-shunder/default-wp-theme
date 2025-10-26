<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta
		name="viewport"
		content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
	>
	<meta
		http-equiv="X-UA-Compatible"
		content="ie=edge"
	>
	<title>Coming Soon</title>
</head>

<style>
	* {
		box-sizing: border-box;
		margin: 0;
		padding: 0;
		font-family: sans-serif;
	}

	.coming-soon {
		display: flex;
		flex-direction: column;
		width: 100vw;
		height: 100vh;
		background-color: #003512;
	}

	.header {
		margin-top: 40px;
		margin-left: 40px;
		width: 300px;
		filter: invert(1);

		img {
			width: 100%;
		}
	}

	.body {
		padding: 0 16px;
		flex-grow: 1;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		text-align: center;
		color: #fff;

		h1 {
			font-size: 72px;
			margin-bottom: 32px;
		}

		p {
			font-size: 36px;
		}

		span {
			margin-top: 32px;
			display: inline-block;
			font-size: 26px;
			padding: 12px 32px;
			background-color: #109558;
			border-radius: 30px;
		}
	}

	@media (max-width: 767px) {
		.header {
			margin: 40px auto;
		}

		.body {
			h1 {
				font-size: 42px;
			}

			p {
				font-size: 21px;
			}

			span {
				font-size: 16px;
			}
		}
	}
</style>

<body>

<main class="coming-soon">
	<div class="header">
		<img
			src="<?= get_field('header_logo', 'option')['url'] ?>"
			alt="<?= get_field('header_logo', 'option')['alt'] ?>"
		>
	</div>

	<div class="body">
		<h1>Wkrótce startujemy!</h1>
		<p>Troskliwe technologie.</p>
		<p>Inteligentne innowacje.</p>
		<span>W SEBONE SKUPIAMY SIĘ NA TYM, ŻEBY UŁATWIĆ CI DBANIE O SIEBIE</span>
	</div>
</main>

</body>
</html>