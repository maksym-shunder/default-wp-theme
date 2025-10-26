<?php
$order_button_text = esc_html__('Place your order', 'checkout');
echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . $order_button_text . '</button>' ); // @codingStandardsIgnoreLine ?>