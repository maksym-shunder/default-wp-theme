<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>
<div class="widget_shopping_cart_content">
	<ul class="woocommerce-mini-cart cart_list">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				/**
				 * This filter is documented in woocommerce/templates/cart/cart.php.
				 *
				 * @since 2.1.0
				 */
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('medium'), $cart_item, $cart_item_key );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
				<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
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

							<div class="actions_box">
								<div class="product-quantity product-quantity-picker">
									<?php
									$product_quantity = woocommerce_quantity_input([
										'input_name'  => "cart[{$cart_item_key}][qty]",
										'input_value' => $cart_item['quantity'],
										'max_value'   => $_product->get_max_purchase_quantity(),
										'min_value'   => '0',
										'product_name' => $_product->get_name(),
									], $_product, false);

									echo apply_filters(
										'woocommerce_cart_item_quantity',
										'<button type="button" class="increment">
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M8.0013 2.1665C8.13391 2.1665 8.26109 2.21918 8.35486 2.31295C8.44862 2.40672 8.5013 2.5339 8.5013 2.6665V7.49984H13.3346C13.4672 7.49984 13.5944 7.55252 13.6882 7.64628C13.782 7.74005 13.8346 7.86723 13.8346 7.99984C13.8346 8.13245 13.782 8.25962 13.6882 8.35339C13.5944 8.44716 13.4672 8.49984 13.3346 8.49984H8.5013V13.3332C8.5013 13.4658 8.44862 13.593 8.35486 13.6867C8.26109 13.7805 8.13391 13.8332 8.0013 13.8332C7.86869 13.8332 7.74152 13.7805 7.64775 13.6867C7.55398 13.593 7.5013 13.4658 7.5013 13.3332V8.49984H2.66797C2.53536 8.49984 2.40818 8.44716 2.31442 8.35339C2.22065 8.25962 2.16797 8.13245 2.16797 7.99984C2.16797 7.86723 2.22065 7.74005 2.31442 7.64628C2.40818 7.55252 2.53536 7.49984 2.66797 7.49984H7.5013V2.6665C7.5013 2.5339 7.55398 2.40672 7.64775 2.31295C7.74152 2.21918 7.86869 2.1665 8.0013 2.1665Z" fill="#333131"/>
											</svg>
										</button>
										' . $product_quantity . '
										<button type="button" class="decrement">
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M4.00156 8.4001C3.89548 8.4001 3.79373 8.35796 3.71872 8.28294C3.64371 8.20793 3.60156 8.10618 3.60156 8.0001C3.60156 7.89401 3.64371 7.79227 3.71872 7.71725C3.79373 7.64224 3.89548 7.6001 4.00156 7.6001H12.0016C12.1076 7.6001 12.2094 7.64224 12.2844 7.71725C12.3594 7.79227 12.4016 7.89401 12.4016 8.0001C12.4016 8.10618 12.3594 8.20793 12.2844 8.28294C12.2094 8.35796 12.1076 8.4001 12.0016 8.4001H4.00156Z" fill="#333131"/>
											</svg>
										</button>',
										$cart_item_key,
										$cart_item
									);
									?>
								</div>

								<a href="<?php echo wc_get_cart_remove_url( $cart_item_key );?>" data-action="cart-remove-product">
									<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
										<g clip-path="url(#clip0_1270_13965)">
											<path d="M12.0639 15.1112H3.93946C3.76844 15.1072 3.59988 15.0695 3.44343 15.0003C3.28698 14.9311 3.14569 14.8317 3.02765 14.7079C2.90961 14.5841 2.81712 14.4382 2.75547 14.2786C2.69382 14.1191 2.66421 13.9489 2.66835 13.7779V4.99121H3.55724V13.7779C3.553 13.8322 3.55953 13.8868 3.57647 13.9386C3.59341 13.9904 3.62041 14.0383 3.65593 14.0796C3.69145 14.1209 3.73479 14.1548 3.78345 14.1793C3.83211 14.2038 3.88513 14.2184 3.93946 14.2223H12.0639C12.1182 14.2184 12.1713 14.2038 12.2199 14.1793C12.2686 14.1548 12.3119 14.1209 12.3474 14.0796C12.383 14.0383 12.41 13.9904 12.4269 13.9386C12.4438 13.8868 12.4504 13.8322 12.4461 13.7779V4.99121H13.335V13.7779C13.3392 13.9489 13.3095 14.1191 13.2479 14.2786C13.1862 14.4382 13.0938 14.5841 12.9757 14.7079C12.8577 14.8317 12.7164 14.9311 12.5599 15.0003C12.4035 15.0695 12.2349 15.1072 12.0639 15.1112Z" fill="#B9C7A8"/>
											<path d="M13.6796 4.00022H2.22179C2.10391 4.00022 1.99087 3.95339 1.90752 3.87004C1.82417 3.78669 1.77734 3.67365 1.77734 3.55577C1.77734 3.4379 1.82417 3.32485 1.90752 3.2415C1.99087 3.15815 2.10391 3.11133 2.22179 3.11133H13.6796C13.7974 3.11133 13.9105 3.15815 13.9938 3.2415C14.0772 3.32485 14.124 3.4379 14.124 3.55577C14.124 3.67365 14.0772 3.78669 13.9938 3.87004C13.9105 3.95339 13.7974 4.00022 13.6796 4.00022Z" fill="#B9C7A8"/>
											<path d="M9.33203 5.77783H10.2209V12.4445H9.33203V5.77783Z" fill="#B9C7A8"/>
											<path d="M5.77734 5.77783H6.66623V12.4445H5.77734V5.77783Z" fill="#B9C7A8"/>
											<path d="M10.2218 2.60423H9.37734V1.77756H6.62179V2.60423H5.77734V1.77756C5.77706 1.54931 5.86458 1.32971 6.02179 1.16423C6.17899 0.998749 6.39383 0.900084 6.62179 0.888672H9.37734C9.60531 0.900084 9.82014 0.998749 9.97734 1.16423C10.1345 1.32971 10.2221 1.54931 10.2218 1.77756V2.60423Z" fill="#B9C7A8"/>
										</g>
										<defs>
											<clipPath id="clip0_1270_13965">
												<rect width="16" height="16" fill="white"/>
											</clipPath>
										</defs>
									</svg>
								</a>
							</div>
						</div>
					</div>
				</li>
				<?php
			}
		}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>

	<div class="woocommerce-mini-cart__total total">
		<h5><?php esc_html_e('Total amount', 'woocommerce'); ?></h5>

		<span>
			<?php
			/**
			 * Hook: woocommerce_widget_shopping_cart_total.
			 *
			 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
			 */
			//do_action( 'woocommerce_widget_shopping_cart_total' );


			wc_cart_totals_order_total_html();
			?>
		</span>
	</div>

	<div class="button_box">
		<a href="<?php echo wc_get_checkout_url(); ?>" class="button" <?=(WC()->cart && WC()->cart->is_empty()) ? 'disabled' : ''?>><?php _e('Proceed to checkout', 'woocommerce-mini-cart'); ?></a>
	</div>
</div>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

	<!--	<p class="woocommerce-mini-cart__buttons buttons">--><?php //do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?><!--</p>-->

	<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

<?php else : ?>
	<div class="widget_shopping_cart_content">
		<div class="mini-cart__empty_box">
			<h4><?php esc_html_e( 'Your cart is empty', 'woocommerce' ); ?></h4>
			<p><?php esc_html_e( 'Add products to start shopping.', 'woocommerce' ); ?></p>
		</div>

		<div class="button_box">
			<a href="<?php echo wc_get_checkout_url(); ?>" class="button" <?=(WC()->cart && WC()->cart->is_empty()) ? 'disabled' : ''?>><?php _e('Proceed to checkout', 'woocommerce-mini-cart'); ?></a>
		</div>
	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
