<?php if ( wc_coupons_enabled() ) { ?>
	<div class="coupon_box">
		<div class="coupon">
			<div class="coupon__code">
				<h4><?php esc_html_e('Do you have a promotional code or certificate?', 'woocommerce')?></h4>
				<label class="field_box">
					<input type="text" name="coupon_code" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
					<button type="button" data-action="apply_coupon">
						<svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M3 12.5L21 12.5M21 12.5L12.5 21M21 12.5L12.5 4" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</label>
			</div>
			<div class="message_box"></div>
		</div>
	</div>
<?php } ?>