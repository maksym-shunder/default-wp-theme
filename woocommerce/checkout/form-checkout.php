<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>
<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div id="customer_details">
			<div class="fields_block">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="fields_block">
				<?php include (get_template_directory() . '/woocommerce/checkout/shipping-methods.php')?>

				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>

			<div class="fields_block">
				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
			</div>
		</div>

	<?php endif; ?>

	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

	<!-- 	<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3> -->

	<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order">
		<h3>
			<?php esc_html_e( 'Order summary', 'woocommerce' ); ?>
		</h3>
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>


<script>
	(function () {
		const $ = jQuery

		const salonsData = JSON.parse('<?=json_encode(get_all_salons())?>')
		console.log(salonsData, 'salonsData')

		function addSelect(data, name, connectedTo, appendTo) {
			const select = document.createElement('select');
			select.name = name
			select.setAttribute('data-connect', connectedTo)

			data.forEach(el => {
				const option = document.createElement('option')
				option.value = el
				option.textContent = el

				select.append(option)
			})

			document.querySelector(appendTo).prepend(select)
			$(`[name="${name}"]`).trigger('change')
		}

		$(document).on('change', 'select[name="cities-select"]', function () {
			const value = $(this).val()

			const currentCityData = salonsData.find(item => {
				if(item.city === value) return item
			})

			const salons = currentCityData.salons.map(item => item.address)
			$('select[name="salons-select"]').remove()
			addSelect(salons, 'salons-select', '#shipping_address_1', '#shipping_address_1_field')

			console.log(currentCityData)
		})

		$(document).on('change', '[data-connect]', function () {
			const target = $($(this).attr('data-connect'))

			target.val($(this).val()).trigger('change')
		})

		$(document).on('change', '.shipping_address input', function () {
			let combinedAddress = ''

			document.querySelectorAll('.shipping_address input').forEach((el) => combinedAddress += (el.value) ? `${el.value}, ` : '')

			$('#billing_address_1').val(combinedAddress)
		})

		$(document).on('change', '#shipping_method .shipping_method', function () {
			const value = $(this).val()
			const conditionalFields = $('.conditional-field')

			conditionalFields.removeClass('show')

			if(value === 'free_shipping:2' || value === 'free_shipping:4') {
				conditionalFields.addClass('show')
				conditionalFields.find('input').val('')
			}
		});

		// init
		(function () {
			const cities = salonsData.map(item => item.city);
			addSelect(cities, 'cities-select', '#shipping_city', '#shipping_city_field')
		})();
	})()
</script>