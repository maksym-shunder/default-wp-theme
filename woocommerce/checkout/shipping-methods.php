<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
	<?php $available_methods = WC()->shipping->get_packages(); ?>
	<div class="shipping_methods_box">
		<h3><?php esc_html_e( 'Delivery', 'woocommerce' ); ?></h3>

		<ul id="shipping_method" class="woocommerce-shipping-methods">
			<?php foreach ( $available_methods as $i => $package ) :
				$chosen_method = WC()->session->get( "chosen_shipping_methods" )[ $i ] ?? '';
				$rates = $package['rates'];
				foreach ( $rates as $rate_id => $rate ):
					$checked = $rate_id === $chosen_method ? 'checked' : '';
					?>
					<li>
						<label class="method_box">
							<input type="radio" name="shipping_method[<?=$i?>]" data-index="<?=$i?>" id="shipping_method_<?=$i?>_flat_rate<?=$rate_id?>" value="<?= esc_attr( $rate_id )?>" class="shipping_method" <?=$checked?> />
							<h4><?php esc_html_e($rate->get_label(), 'woocommerce'); ?></h4>
							<?php if ($rate_id === 'free_shipping:1' || $rate_id === 'free_shipping:2'): ?>
								<p><?php esc_html_e('1–3 business days', 'woocommerce')?></p>
							<?php elseif($rate_id === 'free_shipping:3' || $rate_id === 'free_shipping:4'): ?>
								<p><?php esc_html_e('7–14 business days', 'woocommerce')?></p>
							<?php endif;?>
							<p><?= ((int)$rate->cost === 0) ? esc_html__('Free', 'checkout') : wc_price( $rate->cost ) ?></p>
						</label>
					</li>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>