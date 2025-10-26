(function() {
	document.addEventListener('DOMContentLoaded', () => {
		const $ = jQuery
		const body = $('body')
		const cartBody = $('.cart_body')

		//coupon
		$(document).on('click', '[data-action="apply_coupon"]', function(e) {
			e.preventDefault()

			const that = $(this)
			const coupon = that.parent().find('[name="coupon_code"]').val()

			$.ajax({
				url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
				type: 'POST',
				data: {
					coupon_code: coupon,
					security: wc_checkout_params.apply_coupon_nonce
				},
				success: function(response) {
					console.log(response, 'response')
					$(document.body).trigger('update_checkout')
					that.closest('.coupon_box').find('.message_box').html(response)
				}
			})
		})

		//cart
		$(document).on('click', '[data-action="add-to-cart"]', async function(e) {
			e.preventDefault()
			const button = $(this)
			button.addClass('loading')
			let productID = $(this).data('product-id')

			await addToCart(productID)

			button.removeClass('loading')
		})

		$(document).on('submit', '[data-action="add-to-cart-form"]', async function(e) {
			e.preventDefault()

			const button = $(this).find('button[type="submit"]')
			button.addClass('loading')
			const productID = $(this).find('input:checked').val()

			await addToCart(productID)

			button.removeClass('loading')
		})

		function addToCart(productID, quantity = 1) {
			return new Promise((resolve, reject) => {
				$.ajax({
					type: 'POST',
					url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
					data: {
						product_id: productID,
						quantity
					},
					success: function(response) {
						$(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash])
						resolve(1)
					},
					error: function(error) {
						console.error('Add to cart error:', error)
						reject(error)
					}
				})
			})
		}

		$(document).on('change', '.woocommerce-mini-cart .qty', function() {
			let cart_item_key = $(this).attr('name').match(/\[(.*?)\]/)[1]
			let new_qty = $(this).val()
			updateCart(cart_item_key, new_qty)
		})

		$(document).on('click', '.product-quantity-picker .increment, .product-quantity-picker .decrement', function() {
			let $input = $(this).closest('.product-quantity-picker').find('input[type="number"]')
			let currentVal = parseInt($input.val(), 10)
			let max = $input.attr('max') ? parseInt($input.attr('max'), 10) : null
			let min = $input.attr('min') ? parseInt($input.attr('min'), 10) : 0

			if($(this).hasClass('increment')) {
				if(max === null || max === -1 || currentVal < max) {
					$input.val(currentVal + 1).trigger('change')
				}
			} else if($(this).hasClass('decrement')) {
				if(currentVal > min) {
					$input.val(currentVal - 1).trigger('change')
				}
			}
		})

		function updateCart(itemKey, newQty) {
			let data = {
				action: 'woocommerce_update_cart_item',
				cart_item_key: itemKey,
				new_qty: newQty
			}

			// Add class to cart to indicate loading
			cartBody.addClass('processing')

			$.ajax({
				type: 'POST',
				url: wc_add_to_cart_params.ajax_url,
				data: data,
				success: function() {
					$(document.body).trigger('wc_fragment_refresh')
				}
			})
				.always(function() {
					cartBody.removeClass('processing')
				})
		}

		$(document).on('click', '[data-action="cart-remove-product"]', function(e) {
			e.preventDefault()

			const url = $(this).attr('href')
			cartBody.addClass('processing')

			fetch(url)
				.then(() => $(document.body).trigger('wc_fragment_refresh'))
				.finally(() => cartBody.removeClass('processing'))
		})
		//end cart


	})
})()