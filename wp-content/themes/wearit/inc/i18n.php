<?php
/**
 * Danish / English — the EN | DA switch in the header.
 *
 * Language
 *   ?lang=da or ?lang=en sets a cookie for a year, then the page reloads without ?lang.
 *   The WordPress admin always stays English.
 *
 * Text written in the theme
 *   Wrapped in __( 'Text', 'wearit' ) / esc_html_e( … ) as usual in WordPress.
 *   The Danish for each text is in inc/lang-da.php (English => Danish).
 *
 * Text typed in the admin
 *   SCF: any text field "name" can get a twin "name_da" — shown instead when the site is Danish.
 *   Posts, products, initiatives, pages: SCF fields "title_da", "excerpt_da", "content_da".
 *   Empty twin → the English text is shown.
 *
 * Prices
 *   Danish shows kroner at a fixed rate (1 EUR = WEARIT_DKK_RATE DKK), "149,20 kr.".
 *   Customers are still charged in euros: the checkout says so, and the thank-you page,
 *   emails, admin and Stripe always use wearit_format_eur().
 */

const WEARIT_LANG_COOKIE = 'wearit_lang';
const WEARIT_DKK_RATE    = 7.46;

// ── Which language ────────────────────────────────────────────

function wearit_lang() {
    static $lang = null;
    if ( ! empty( $GLOBALS['wearit_lang_override'] ) ) {
        return $GLOBALS['wearit_lang_override'];
    }
    if ( $lang !== null ) {
        return $lang;
    }

    $is_admin_screen = is_admin() && ! wp_doing_ajax() && basename( $_SERVER['SCRIPT_NAME'] ?? '' ) !== 'admin-post.php';
    if ( $is_admin_screen ) {
        $lang = 'en';
        return $lang;
    }

    $asked  = isset( $_GET['lang'] ) ? sanitize_key( $_GET['lang'] ) : '';
    $cookie = isset( $_COOKIE[ WEARIT_LANG_COOKIE ] ) ? sanitize_key( $_COOKIE[ WEARIT_LANG_COOKIE ] ) : '';
    $lang   = in_array( $asked, [ 'en', 'da' ], true ) ? $asked : ( $cookie === 'da' ? 'da' : 'en' );
    return $lang;
}

function wearit_is_danish() {
    return wearit_lang() === 'da';
}

/**
 * Run $callback in another language, e.g. an email in the language the order was
 * placed in, sent later from the (English) admin. Returns what $callback returns.
 */
function wearit_with_lang( $lang, callable $callback ) {
    $before                          = $GLOBALS['wearit_lang_override'] ?? null;
    $GLOBALS['wearit_lang_override'] = $lang === 'da' ? 'da' : 'en';
    try {
        return $callback();
    } finally {
        $GLOBALS['wearit_lang_override'] = $before;
    }
}

/** The language an order was placed in (saved by wearit_create_order()). Older orders: English. */
function wearit_order_lang( $order_id ) {
    return get_post_meta( $order_id, '_wearit_lang', true ) === 'da' ? 'da' : 'en';
}

/** The current page with ?lang= set, for the switch links. */
function wearit_lang_url( $lang ) {
    $uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
    return add_query_arg( 'lang', $lang, home_url( $uri ) );
}

// ?lang=… → remember it, then load the same page without ?lang so links stay clean.
add_action( 'template_redirect', function () {
    if ( ! isset( $_GET['lang'] ) || ! in_array( $_GET['lang'], [ 'en', 'da' ], true ) ) {
        return;
    }
    setcookie( WEARIT_LANG_COOKIE, sanitize_key( $_GET['lang'] ), [
        'expires'  => time() + YEAR_IN_SECONDS,
        'path'     => COOKIEPATH ? COOKIEPATH : '/',
        'domain'   => COOKIE_DOMAIN,
        'secure'   => is_ssl(),
        'httponly' => false,
        'samesite' => 'Lax',
    ] );
    if ( ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) === 'GET' ) {
        wp_safe_redirect( remove_query_arg( 'lang' ) );
        exit;
    }
}, 1 );

// <html lang="da-DK"> so screen readers read Danish with a Danish voice (WCAG 3.1.1).
add_filter( 'language_attributes', function ( $output ) {
    return wearit_is_danish() ? preg_replace( '/lang="[^"]*"/', 'lang="da-DK"', $output ) : $output;
} );

// ── Theme text: English → Danish ──────────────────────────────

function wearit_danish_strings() {
    static $strings = null;
    if ( $strings === null ) {
        $strings = require get_template_directory() . '/inc/lang-da.php';
    }
    return $strings;
}

add_filter( 'gettext', function ( $translation, $text, $domain ) {
    if ( ( $domain !== 'wearit' && $domain !== 'default' ) || ! wearit_is_danish() ) {
        return $translation;
    }
    $strings = wearit_danish_strings();
    return $strings[ $text ] ?? $translation;
}, 10, 3 );

add_filter( 'gettext_with_context', function ( $translation, $text, $context, $domain ) {
    if ( ( $domain !== 'wearit' && $domain !== 'default' ) || ! wearit_is_danish() ) {
        return $translation;
    }
    $strings = wearit_danish_strings();
    return $strings[ $context . '|' . $text ] ?? $translation;
}, 10, 4 );

// "5 mins" in "5 mins ago" (human_time_diff)
add_filter( 'ngettext', function ( $translation, $single, $plural, $number, $domain ) {
    if ( ( $domain !== 'default' && $domain !== 'wearit' ) || ! wearit_is_danish() ) {
        return $translation;
    }
    $strings = wearit_danish_strings();
    $key     = $single . '|' . $plural;
    if ( ! isset( $strings[ $key ] ) ) {
        return $translation;
    }
    return (int) $number === 1 ? $strings[ $key ][0] : $strings[ $key ][1];
}, 10, 5 );

// Month and weekday names are read once, before the theme loads: read them again in Danish.
add_action( 'init', function () {
    if ( wearit_is_danish() ) {
        $GLOBALS['wp_locale'] = new WP_Locale();
    }
}, 1 );

// ── Text typed in the admin: Danish twins ─────────────────────

/** Raw value of a SCF field by name, wherever SCF keeps it. */
function wearit_scf_raw( $post_id, $name ) {
    if ( function_exists( 'acf_decode_post_id' ) ) {
        $decoded = acf_decode_post_id( $post_id );
        $type    = $decoded['type'];
        $id      = $decoded['id'];
    } else {
        $type = 'post';
        $id   = $post_id;
    }

    switch ( $type ) {
        case 'option':
            return get_option( 'options_' . $name );
        case 'term':
            return get_term_meta( (int) $id, $name, true );
        case 'user':
            return get_user_meta( (int) $id, $name, true );
        default:
            return get_post_meta( (int) $id, $name, true );
    }
}

// Any SCF text field: "name_da" wins when the site is Danish. Works inside repeaters too,
// because SCF passes sub fields with their full name (drops_items_0_drops_title).
// No "name_da" filled in → the stored English is looked up in inc/lang-da-content.php.
add_filter( 'acf/load_value', function ( $value, $post_id, $field ) {
    if ( ! wearit_is_danish() || ! in_array( $field['type'] ?? '', [ 'text', 'textarea', 'wysiwyg' ], true ) ) {
        return $value;
    }
    if ( empty( $field['name'] ) || str_ends_with( $field['name'], '_da' ) ) {
        return $value;
    }
    $danish = wearit_scf_raw( $post_id, $field['name'] . '_da' );
    if ( is_string( $danish ) && trim( $danish ) !== '' ) {
        return $danish;
    }
    return is_string( $value ) ? wearit_translate_stored( $value ) : $value;
}, 20, 3 );

// ── Stored English → Danish (inc/lang-da-content.php) ─────────

function wearit_normalize_text( $text ) {
    return trim( preg_replace( '/\s+/u', ' ', (string) $text ) );
}

/** The content dictionary with its keys normalised, so line breaks in the admin don't matter. */
function wearit_danish_content() {
    static $content = null;
    if ( $content === null ) {
        $content = [];
        foreach ( require get_template_directory() . '/inc/lang-da-content.php' as $english => $danish ) {
            $content[ wearit_normalize_text( $english ) ] = $danish;
        }
    }
    return $content;
}

/**
 * Danish for one stored text: the content dictionary first, then the theme dictionary.
 * Text with paragraphs or headings goes block by block (wearit_translate_html).
 * Anything without a Danish line comes back unchanged.
 */
function wearit_translate_stored( $text ) {
    if ( ! wearit_is_danish() || ! is_string( $text ) || trim( $text ) === '' ) {
        return $text;
    }
    if ( preg_match( '#<(p|h[1-6]|li|blockquote|figcaption)\b#', $text ) ) {
        return wearit_translate_html( $text );
    }
    $key = wearit_normalize_text( $text );
    return wearit_danish_content()[ $key ] ?? wearit_danish_strings()[ $key ] ?? $text;
}

/** Post text: each paragraph, heading and list item, and each plain-text paragraph. */
function wearit_translate_html( $html ) {
    if ( ! wearit_is_danish() ) {
        return $html;
    }
    $content = wearit_danish_content();

    $html = preg_replace_callback( '#<(p|h[1-6]|li|blockquote|figcaption)\b([^>]*)>(.*?)</\1>#s', function ( $m ) use ( $content ) {
        $key = wearit_normalize_text( $m[3] );
        return isset( $content[ $key ] ) ? '<' . $m[1] . $m[2] . '>' . $content[ $key ] . '</' . $m[1] . '>' : $m[0];
    }, $html );

    $parts = preg_split( '/(\n\s*\n)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
    foreach ( $parts as $i => $part ) {
        if ( strpos( $part, '<' ) === false && trim( $part ) !== '' ) {
            $parts[ $i ] = $content[ wearit_normalize_text( $part ) ] ?? $part;
        }
    }
    return implode( '', $parts );
}

// ── Danish fields in the admin, added automatically ───────────
//
// Every SCF text, text area and WYSIWYG field gets a twin right under it on the edit
// screen: same name + "_da", label "… — Dansk". Nothing to create in SCF by hand, and
// fields added later get their twin too. Twins are not added in SCF's field group
// editor, so they are never saved into a field group as real fields.

/** Field names that never need Danish: links, prices, codes. */
function wearit_needs_danish_twin( $field ) {
    return in_array( $field['type'] ?? '', [ 'text', 'textarea', 'wysiwyg' ], true )
        && ! empty( $field['name'] )
        && ! str_ends_with( $field['name'], '_da' )
        && ! preg_match( '/(url|link|price|sku|email|mail-text|hex)$/', $field['name'] );
}

/** The Danish twin of one field, registered so SCF can find it again when saving. */
function wearit_danish_twin( $field ) {
    $twin                 = $field;
    $twin['ID']           = 0;
    $twin['key']          = $field['key'] . '_da';
    $twin['name']         = $field['name'] . '_da';
    $twin['_name']        = $field['name'] . '_da';
    $twin['label']        = ( $field['label'] ?: $field['name'] ) . ' — Dansk';
    $twin['instructions'] = 'Shown when the site is in Danish. Leave empty to show the English text above.';
    $twin['required']     = 0;
    $twin['default_value'] = '';
    acf_get_store( 'fields' )->set( $twin['key'], $twin );
    return $twin;
}

function wearit_is_field_group_editor() {
    if ( ! is_admin() ) {
        return true;
    }
    $post_type = $_REQUEST['post_type'] ?? '';
    if ( ! $post_type && isset( $_GET['post'] ) ) {
        $post_type = get_post_type( (int) $_GET['post'] );
    }
    if ( ! $post_type && isset( $_POST['post_ID'] ) ) {
        $post_type = get_post_type( (int) $_POST['post_ID'] );
    }
    $action = $_REQUEST['action'] ?? '';
    return $post_type === 'acf-field-group' || ( wp_doing_ajax() && str_starts_with( (string) $action, 'acf/field_group' ) );
}

add_filter( 'acf/load_fields', function ( $fields, $parent ) {
    if ( wearit_is_field_group_editor() ) {
        return $fields;
    }
    $names = array_column( $fields, 'name' );
    $out   = [];
    foreach ( $fields as $field ) {
        $out[] = $field;
        if ( wearit_needs_danish_twin( $field ) && ! in_array( $field['name'] . '_da', $names, true ) ) {
            $out[] = wearit_danish_twin( $field );
        }
    }
    return $out;
}, 20, 2 );

// Saving: SCF looks every posted field up by its key. Top-level twins aren't in a field
// group, so register them from their English field first (repeater rows find theirs
// through the repeater's own sub fields, which the filter above already includes).
add_action( 'acf/save_post', function () {
    $posted = $_POST['acf'] ?? [];
    if ( ! is_array( $posted ) ) {
        return;
    }
    foreach ( array_keys( $posted ) as $key ) {
        if ( ! str_ends_with( (string) $key, '_da' ) || acf_get_store( 'fields' )->has( $key ) ) {
            continue;
        }
        $english = acf_get_field( substr( $key, 0, -3 ) );
        if ( $english && wearit_needs_danish_twin( $english ) ) {
            wearit_danish_twin( $english );
        }
    }
}, 5 );

// Danish title, excerpt and text for posts, products, initiatives and pages.
add_action( 'acf/init', function () {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }
    $location = [];
    foreach ( [ 'post', 'page', 'product', 'initiative' ] as $type ) {
        $location[] = [ [ 'param' => 'post_type', 'operator' => '==', 'value' => $type ] ];
    }
    acf_add_local_field_group( [
        'key'        => 'group_wearit_danish',
        'title'      => 'Dansk',
        'menu_order' => 99,
        'position'   => 'normal',
        'location'   => $location,
        'fields'     => [
            [
                'key'          => 'field_wearit_title_da',
                'label'        => 'Title — Dansk',
                'name'         => 'title_da',
                'type'         => 'text',
                'instructions' => 'Shown when the site is in Danish. Leave empty to show the English title.',
            ],
            [
                'key'          => 'field_wearit_excerpt_da',
                'label'        => 'Excerpt — Dansk',
                'name'         => 'excerpt_da',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'The short text on cards and in search results. Leave empty to show the English one.',
            ],
            [
                'key'          => 'field_wearit_content_da',
                'label'        => 'Text — Dansk',
                'name'         => 'content_da',
                'type'         => 'wysiwyg',
                'instructions' => 'The main text of the page. Leave empty to show the English text.',
            ],
        ],
    ] );
} );

/** "title_da" etc. of a post, or '' when there is none (or the site is English). */
function wearit_post_danish( $post_id, $field ) {
    if ( ! wearit_is_danish() || ! $post_id ) {
        return '';
    }
    $value = get_post_meta( (int) $post_id, $field . '_da', true );
    return is_string( $value ) ? trim( $value ) : '';
}

/** Page titles like "Cart" or "Contact" have a built-in Danish ("page title|Cart") when no title_da is set. */
function wearit_danish_title( $title, $post_id ) {
    $danish = wearit_post_danish( $post_id, 'title' );
    if ( $danish !== '' ) {
        return $danish;
    }
    if ( wearit_is_danish() && get_post_type( $post_id ) === 'page' ) {
        return _x( $title, 'page title', 'wearit' );
    }
    return wearit_is_danish() ? ( wearit_danish_content()[ wearit_normalize_text( $title ) ] ?? $title ) : $title;
}

add_filter( 'the_title', function ( $title, $post_id = 0 ) {
    return wearit_danish_title( $title, $post_id );
}, 10, 2 );

add_filter( 'single_post_title', function ( $title, $post ) {
    return wearit_danish_title( $title, $post->ID ?? 0 );
}, 10, 2 );

add_filter( 'post_type_archive_title', function ( $title ) {
    return wearit_is_danish() ? _x( $title, 'archive title', 'wearit' ) : $title;
} );

add_filter( 'the_content', function ( $content ) {
    $danish = wearit_post_danish( get_the_ID(), 'content' );
    return $danish !== '' ? $danish : wearit_translate_html( $content );
}, 1 );

add_filter( 'get_the_excerpt', function ( $excerpt, $post = null ) {
    $danish = wearit_post_danish( $post->ID ?? get_the_ID(), 'excerpt' );
    return $danish !== '' ? $danish : wearit_translate_stored( $excerpt );
}, 20, 2 );

// Category names (shop filters, product cards, blog categories) through the dictionary.
function wearit_translate_terms( $terms ) {
    if ( ! wearit_is_danish() || ! is_array( $terms ) ) {
        return $terms;
    }
    foreach ( $terms as $term ) {
        if ( $term instanceof WP_Term ) {
            $term->name = __( $term->name, 'wearit' );
        }
    }
    return $terms;
}
add_filter( 'get_terms', 'wearit_translate_terms' );
add_filter( 'get_the_terms', 'wearit_translate_terms' );
add_filter( 'get_term', function ( $term ) {
    if ( wearit_is_danish() && $term instanceof WP_Term ) {
        $term->name = __( $term->name, 'wearit' );
    }
    return $term;
} );

// ── Prices ────────────────────────────────────────────────────

/** 'DKK' when the site is Danish, otherwise 'EUR'. What prices are shown in, not charged in. */
function wearit_display_currency() {
    return wearit_is_danish() ? 'DKK' : 'EUR';
}

/** €54.00 — what the customer is actually charged, in any language. */
function wearit_format_eur( $price ) {
    return '€' . number_format( (float) $price, 2 );
}

/** 402,84 kr. — a euro amount shown in kroner. */
function wearit_format_dkk( $eur ) {
    return number_format( (float) $eur * WEARIT_DKK_RATE, 2, ',', '.' ) . ' kr.';
}
