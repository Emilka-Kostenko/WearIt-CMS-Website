

Products are a WordPress custom post type (`product`), not WooCommerce.
All the code lives in `inc/products.php`. The shop page is `archive-product.php` (URL: `/products/`).


```php
$product = wearit_get_product( $product_id );   // null if the ID is not a product
```

Returns:

| key               | type     | example                                   |
|-------------------|----------|-------------------------------------------|
| `id`              | int      | `236` — **use this as the cart key**      |
| `title`           | string   | `Shadow Hoodie`                           |
| `slug`            | string   | `shadow-hoodie`                           |
| `permalink`       | string   | URL of the product post                   |
| `sku`             | string   | `WI-HOOD-001`                             |
| `price`           | float    | `89.0` — **always read the price on the server, never trust the browser** |
| `price_formatted` | string   | `€89`                                      |
| `color`           | string   | `black` (slug)                            |
| `color_label`     | string   | `Black`                                   |
| `color_hex`       | string   | `#1a1a1a`                                 |
| `sizes`           | string[] | `["S","M","L","XL","XXL"]` — the sizes you can choose from |
| `size_range`      | string   | `S–XXL`                                   |
| `category`        | string   | `hoodies` (slug)                          |
| `category_label`  | string   | `Hoodies`                                 |
| `in_stock`        | bool     | `true`                                    |
| `is_new`          | bool     | `true`                                    |
| `image`           | string   | image URL, or `''` when there is none     |

Size values are plain strings: clothing `XS S M L XL XXL`, bottoms `28 30 32 34 36 38`, sneakers `US 6` … `US 13`.
Check a chosen size with `in_array( $size, $product['sizes'], true )`.

## "Add to Cart" button (interface only)

Each card has:

```html
<button type="button" class="product-card__add"
        data-product-id="236" data-sku="WI-HOOD-001" data-price="89">Add to Cart</button>
```

It does nothing yet. Read `data-product-id`, then call `wearit_get_product()` on the server for the real data.
A size-picker modal is planned for later; it can read `sizes` from the same function.

## Other ways to read products

- Any WP_Query: `new WP_Query( [ 'post_type' => 'product' ] )`, then `wearit_get_product( get_the_ID() )`.
- REST: `/wp-json/wp/v2/products` (fields are under `acf`).
- Categories: taxonomy `product_category` — Jackets, Hoodies, T-Shirts, Pants & Jeans, Sneakers.

## Editing products

WP admin → **Products**. Fields: Price, Color, Sizes, "New" badge, In stock, SKU, Product image, plus category and featured image.
The "Order" box (Page Attributes) sets the order on the shop page (lowest first).
Images, first one found wins: the SCF **Product image**, the featured image, then the file `assets/products/{slug}.webp` (or `.jpg`, `.jpeg`, `.png`) in this theme.
JPG/PNG uploads get their resized copies (e.g. `product-card`) saved as WebP automatically.
Only theme files are in git. Images uploaded in the admin live in `wp-content/uploads`, which is **not** in git.

## Demo data

WP admin → Products → **Seed demo products** creates the 5 categories and 10 products. Safe to click again (existing ones are skipped).
This site uses the shared team database, so products are already there for everyone.
