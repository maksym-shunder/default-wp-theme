# Digiway Theme

Custom WordPress theme with WooCommerce support. The theme includes a custom Gutenberg blocks system based on ACF fields, resource loading optimization, and a flexible settings system via ACF.

## 🚀 Key Features

- ✅ **WooCommerce Integration** — full WooCommerce integration with custom templates
- ✅ **ACF Blocks** — custom Gutenberg blocks system with ACF fields
- ✅ **Performance Optimization** — automatic preloading of critical resources
- ✅ **Maintenance Mode** — maintenance mode functionality
- ✅ **Cart Popup** — shopping cart popup
- ✅ **Auto CSS/JS Enqueue** — automatic styles and scripts enqueuing

## 📦 Requirements

### Required Plugins:
- **Advanced Custom Fields (ACF)** — for working with custom fields and blocks

### Recommended Plugins:
- **WooCommerce** — if using e-commerce functionality

## 📁 Project Structure

```
default-wp-theme/
├── assets/                    # Static resources
│   ├── css/                   # Styles
│   │   ├── components/        # Component styles
│   │   ├── global.css         # Global styles
│   │   └── checkout.css       # Checkout page styles
│   ├── js/                    # JavaScript files
│   │   ├── main.js            # Main script
│   │   ├── woocommerce.js     # WooCommerce scripts
│   │   └── swiper.min.js      # Swiper.js library
│   ├── font/                  # Fonts (PP Neue Montreal)
│   └── images/                # Images
├── inc/                       # PHP modules
│   ├── acf/                   # ACF integration
│   │   ├── acf.php            # Main ACF configuration
│   │   ├── options-pages.php  # Options pages
│   │   ├── registration.php   # Block registration
│   │   └── options-pages-fields/ # Options page fields
│   ├── enqueue-scripts.php    # Scripts and styles enqueuing
│   ├── maintenance-page.php   # Maintenance mode
│   ├── preloads.php           # Resource preloading
│   ├── theme.php              # Main theme functions
│   ├── utils.php              # Utility functions
│   └── woocommerce.php        # WooCommerce integration
├── template-parts/            # Template parts
│   ├── components/            # Components (header, basket, popup)
│   ├── gutenberg-blocks/      # Custom Gutenberg blocks
│   └── popups/                # Popups
├── templates/                 # Custom page templates
│   ├── template-checkout.php
│   ├── template-login.php
│   ├── template-signup.php
│   └── template-reset-password.php
├── woocommerce/               # WooCommerce templates
│   ├── cart/
│   └── checkout/
├── functions.php              # Main theme file
├── header.php                 # Site header
├── footer.php                 # Site footer
├── index.php                  # Main template
├── home.php                   # Blog template
├── single.php                 # Post template
└── 404.php                    # 404 page
```

## ⚙️ Configuration

### 1. Navigation Menus

To configure menus, go to **Appearance → Menus** and create menus with the following locations:

- **Main Menu** — main menu in the header
- **Footer Menu** — footer menu

Menu registration code is located in `inc/theme.php`:

```php
register_nav_menus(array(
    'main-menu'   => esc_html__('Main Menu'),
    'menu-footer' => esc_html__('Footer Menu'),
));
```

### 2. ACF Options Pages

The theme automatically creates options pages in the admin panel:

- **Theme Settings** (main page)
  - **Global Settings** — global theme settings
  - **Settings Header** — header settings (logo, etc.)
  - **Settings Footer** — footer settings

Fields for these pages are located in `inc/acf/options-pages-fields/`.

### 3. Popups

The theme supports a popup system. An example popup is located in `template-parts/popups/example-popup.php`.

**Using a popup:**

```html
<button data-action="togglePopup" data-target="#example_popup">
    Open Popup
</button>
```

> [!NOTE]
> Popups need to be included in `footer.php`.

### 4. Maintenance Mode

Activate maintenance mode through the ACF options page **Global Settings** → `maintenance_mode` field.

When the mode is active, all users (except administrators) will be redirected to the `/maintenance` page.

### 5. Disable WooCommerce Payments

If you need to temporarily disable the ability to make purchases, use the option in **Global Settings** → `disable_payments` field.

## 🎨 Custom Gutenberg Blocks

The theme supports creating custom Gutenberg blocks with ACF fields. Example blocks:

- `example-block` — basic block example
- `video-block` — video block

**Block structure:**

```
gutenberg-blocks/
└── example-block/
    ├── index.php       # Block registration
    ├── fields.php      # ACF fields
    ├── template.php    # Display template
    └── assets/
        ├── style.css   # Block styles
        └── script.js   # JavaScript (optional)
```

**Automatic preloading:** The theme automatically preloads styles and images from the first block on the page for speed optimization.

## 🛒 WooCommerce

### Custom Templates

The theme overrides the following WooCommerce templates:

- `woocommerce/cart/mini-cart.php` — mini cart
- `woocommerce/checkout/*` — all checkout page templates
- `woocommerce/coupon.php` — coupons

### Custom Pages

- **Checkout** — use the "Checkout" template for the checkout page
- **Login** — "Login" template for WooCommerce customer login
- **Signup** — "Signup" template for WooCommerce customer registration
- **Reset Password** — "Reset Password" template for WooCommerce customer password reset

### AJAX Functionality

- Update cart item quantities via AJAX
- Cart fragments for updating without page reload
- Custom redirects after login/logout/password reset

## 📝 If WooCommerce is Not Used

If you are not using WooCommerce, follow these steps:

1. **Remove WooCommerce scripts enqueuing** in `inc/enqueue-scripts.php`:
   ```php
   // Remove these lines:
   wp_enqueue_script('wc-cart-fragments');
   wp_enqueue_script('wc-add-to-cart');
   wp_enqueue_script('cart-js', ...);
   ```

2. **Remove jQuery enqueuing** (if not used):
   ```php
   // Uncomment in inc/enqueue-scripts.php:
   wp_deregister_script('jquery');
   ```

3. **Remove files:**
   - `inc/woocommerce.php`
   - `assets/js/woocommerce.js`
   - Entire `woocommerce/` folder
   - All files from `templates/` folder

4. **Remove from `functions.php`:**
   ```php
   require_once __DIR__ . '/inc/woocommerce.php';
   ```

5. **Remove cart components from** `header.php`:
   ```php
   <?php get_template_part('template-parts/components/basket'); ?>
   <?php get_template_part('template-parts/components/header/cart-popup'); ?>
   ```

6. **Remove jQuery preload from** `inc/preloads.php`
   ```php
   <link
       rel="preload"
       href="/wp-includes/js/jquery/jquery.min.js?ver=3.7.1"
       as="script"
   >
   <link
       rel="preload"
       href="/wp-includes/js/jquery/jquery-migrate.min.js?ver=3.4.1"
       as="script"
   >
   ```

## 🔧 Automatic Styles Enqueuing

The theme automatically enqueues all CSS files from the `assets/css/` folder, except excluded ones:
- `header.css`
- `global.css`
- `popup.css`
- `cart-popup.css`

These files are enqueued manually via `inc/preloads.php` for loading optimization.

Styles are enqueued with `media="print"` and `onload="this.media='all'"` attributes for asynchronous loading.

## 🎯 JavaScript Functionality

The main script `assets/js/main.js` includes:

- **Mobile Menu** — mobile menu toggle
- **Popups** — open/close popups
- **Cart** — open/close cart
- **Video Controls** — video playback control
- **External Links** — automatic addition of `target="_blank"` for external links
- **Menu Arrows** — automatic addition of arrows for menu items with submenus

## 📦 Dependencies

### JavaScript Libraries:
- **Swiper.js** — for sliders (included minified version)
