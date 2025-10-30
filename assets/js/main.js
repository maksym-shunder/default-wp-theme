(function() {
	document.addEventListener('DOMContentLoaded', function(event) {

		// global events
		document.addEventListener('click', function(event) {
			const target = event.target

			if(target.closest('[data-action="videoControl"]')) {
				const button = target.closest('[data-action="videoControl"]')
				const videoBox = button.closest('[data-video-box]')
				const video = videoBox ? videoBox.querySelector('video') : null
				if(!video) return

				if(video.paused) {
					video.play()
				} else {
					video.pause()
				}
				button.parentElement.classList.toggle('playing')
			}

			if(target.closest('[data-action="toggleMobileMenu"]')) {
				event.preventDefault()

				const button = target.closest('[data-action="toggleMobileMenu"]')
				const header = button.closest('.header')
				const navBox = header ? header.querySelector('.nav_box') : null
				const body = document.body

				button.classList.toggle('open')
				if(navBox) navBox.classList.toggle('open')

				body.classList.remove('overflow')
				if(button.classList.contains('open')) {
					body.classList.add('overflow')
				}

				document.querySelectorAll('.menu-item-has-children.opened').forEach(el => el.classList.remove('opened'))
			}

			if(target.closest('[data-action="toggleSubmenu"]')) {
				event.preventDefault()

				if(window.innerWidth > 1200) return null
				target.closest('.menu-item-has-children').classList.toggle('opened')
			}

			if(target.closest('[data-action="toggleCartPopup"]')) {
				event.preventDefault()

				document.querySelector('#cart_popup').classList.toggle('open')
			}

			if(target.closest('[data-action="togglePopup"]')) {
				event.preventDefault()

				const popup = target.closest('[data-action="togglePopup"]').getAttribute('data-target')
				if(popup) document.querySelector(popup).classList.toggle('open')
			}

			if(target.closest('[data-action="closePopup"]')) {
				target.closest('.popup_inner').classList.remove('open')
			}
		})

		// global scripts
		// arrows for header menu
		document.querySelectorAll('.header .menu-item-has-children').forEach(item => {
			const arrow = document.createElement('span')
			arrow.classList.add('arrow')
			arrow.setAttribute('data-action', 'toggleSubmenu')

			item.append(arrow)
		})

		// add target="_blank" to all external links
		const links = document.querySelectorAll('a[href]')
		const currentHost = window.location.hostname

		links.forEach(link => {
			const url = new URL(link.href, window.location.href)

			if(url.hostname !== currentHost) {
				link.setAttribute('target', '_blank')
				link.setAttribute('rel', 'noopener noreferrer')
			}
		})
	})
})()
