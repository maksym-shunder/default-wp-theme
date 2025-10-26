<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;
?>

<table class="shop_table woocommerce-checkout-review-order-table">
	<tbody>
	<?php
	do_action( 'woocommerce_review_order_before_cart_contents' );

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

		if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
			$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
			$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('medium'), $cart_item, $cart_item_key );
			$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
			$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
			?>
			<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				<td class="product">
					<div class="product_row">
						<div class="img_box" title="<?php echo esc_html( $product_name ); ?>">
							<a href="<?=$product_permalink?>">
								<?php echo $thumbnail; ?>
							</a>
						</div>
						<div class="info_box">
							<h5 title="<?php echo esc_html( $product_name ); ?>">
								<a href="<?=$product_permalink?>">
									<?php echo esc_html( $product_name ); ?>
								</a>
							</h5>

							<div class="product_price">
								<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
							</div>

							<h5 class="quantity">
								X<?=$cart_item['quantity']?>
							</h5>
						</div>
					</div>
				</td>
			</tr>
			<?php
		}
	}

	do_action( 'woocommerce_review_order_after_cart_contents' );
	?>

	<tr>
		<td colspan="12">
			<?php include (get_template_directory() . '/woocommerce/coupon.php') ?>
		</td>
	</tr>
	</tbody>
	<tfoot>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<td>
					<div>
						<span><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
						<span><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
					</div>
				</td>
			</tr>
		<?php endforeach; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

		<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

		<tr class="order-total">
			<td>
				<div class="total_price">
					<h5><?php esc_html_e( 'Total price', 'woocommerce' ); ?></h5>
					<h5><?php wc_cart_totals_order_total_html(); ?></h5>
				</div>
			</td>
		</tr>

		<tr class="hide_desktop">
			<td>
				<?php get_template_part('woocommerce/checkout/place-order-button'); ?>
			</td>
		</tr>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	</tfoot>
</table>
