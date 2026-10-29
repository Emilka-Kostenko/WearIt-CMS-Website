<?php
/**
 * Orders — custom post type that stores a placed order.
 *
 * One post per order. Customer details and the line items are saved as post meta.
 * Not public: orders are admin-only, you can't browse them on the frontend.
 * Created by wearit_create_order(); the checkout handler in inc/checkout.php calls it.
 *
 * An order carries two separate states:
 *   _wearit_status         where the order is    — new → preparing → shipped → delivered → cancelled
 *   _wearit_payment_status whether it is paid    — awaiting → paid → refunded → failed
 * They are separate because cash on delivery ships before it is paid, while a bank
 * transfer is paid before it ships. One status could not describe both.
 */

// ── Order states ──────────────────────────────────────────────

/** Where the order is in its life. */
function wearit_order_statuses() {
    return [
        'new'       => 'New',
        'preparing' => 'Preparing',
        'shipped'   => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];
}

/** Whether the money has arrived. */
function wearit_payment_statuses() {
    return [
        'awaiting' => 'Awaiting payment',
        'paid'     => 'Paid',
        'refunded' => 'Refunded',
        'failed'   => 'Failed',
    ];
}

// ── Register the order post type ──────────────────────────────

add_action( 'init', function () {
    register_post_type( 'wearit_order', [
        'labels' => [
            'name'          => 'Orders',
            'singular_name' => 'Order',
            'all_items'     => 'All Orders',
            'edit_item'     => 'Order',
        ],
        'public'              => false, // no frontend URL, no archive
        'show_ui'             => true,  // but it does show in the admin sidebar
        'show_in_menu'        => true,
        'exclude_from_search' => true,
        'supports'            => [ 'title' ], // everything else is meta
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'menu_icon'           => 'dashicons-clipboard',
        'menu_position'       => 26,
    ] );
} );

// ── Create an order ───────────────────────────────────────────
/**
 * $customer — name, email, address, city, postcode, country, notes, payment
 * $items     — array of [ 'id', 'title', 'sku', 'size', 'qty', 'price', 'line_total' ]
 * $totals    — [ 'subtotal', 'shipping', 'fee', 'discount', 'coupon', 'total' ]   fee is the payment method's surcharge
 *              (discount + coupon come from inc/coupons.php, '' / 0 when none was used)
 *
 * Returns the new order ID, or 0 if it failed.
 */
function wearit_create_order( array $customer, array $items, array $totals ) {

    $order_id = wp_insert_post( [
        'post_type'   => 'wearit_order',
        'post_status' => 'publish', // "publish" just means saved, orders aren't public anyway
        'post_title'  => 'Order — ' . ( $customer['name'] ?? 'Unknown' ) . ' — ' . current_time( 'Y-m-d H:i' ),
    ], true );

    if ( is_wp_error( $order_id ) ) {
        error_log( 'wearit_order: wp_insert_post failed — ' . $order_id->get_error_message() );
        return 0;
    }

    // customer details, one meta key each so they're readable in the admin
    foreach ( [ 'name', 'email', 'address', 'city', 'postcode', 'country', 'notes' ] as $key ) {
        update_post_meta( $order_id, '_wearit_' . $key, $customer[ $key ] ?? '' );
    }

    // line items go in as one array, wp handles the serialising bleh
    update_post_meta( $order_id, '_wearit_items', $items );

    update_post_meta( $order_id, '_wearit_subtotal', (float) ( $totals['subtotal'] ?? 0 ) );
    update_post_meta( $order_id, '_wearit_shipping', (float) ( $totals['shipping'] ?? 0 ) );
    update_post_meta( $order_id, '_wearit_fee',      (float) ( $totals['fee'] ?? 0 ) );
    update_post_meta( $order_id, '_wearit_discount', (float) ( $totals['discount'] ?? 0 ) );
    update_post_meta( $order_id, '_wearit_coupon',   (string) ( $totals['coupon'] ?? '' ) );
    update_post_meta( $order_id, '_wearit_total',    (float) ( $totals['total'] ?? 0 ) );

    $method = $customer['payment'] ?? 'cod';
    update_post_meta( $order_id, '_wearit_payment', $method );

    // Every order starts unpaid. The method decides what happens next, not this file —
    // see wearit_payment_methods() in inc/checkout.php.
    update_post_meta( $order_id, '_wearit_status', 'new' );
    update_post_meta( $order_id, '_wearit_payment_status', 'awaiting' );

    // a short reference to show the customer, nicer than a raw post id
    $reference = 'WI-' . str_pad( $order_id, 5, '0', STR_PAD_LEFT );
    update_post_meta( $order_id, '_wearit_reference', $reference );

    // first line of the history, so the admin screen always has something to show
    wearit_order_log( $order_id, 'Order placed (' . wearit_payment_label( $method ) . ')' );

    // emails hang off this, see inc/order-emails.php
    do_action( 'wearit_order_created', $order_id );

    return $order_id;
}

/** Read one order back as a tidy array. Null if the id isn't an order. */
function wearit_get_order( $order_id ) {

    $post = get_post( $order_id );
    if ( ! $post || $post->post_type !== 'wearit_order' ) {
        return null;
    }

    return [
        'id'             => $post->ID,
        'reference'      => get_post_meta( $post->ID, '_wearit_reference', true ),
        'date'           => get_the_date( 'j M Y, H:i', $post ),
        'name'           => get_post_meta( $post->ID, '_wearit_name', true ),
        'email'          => get_post_meta( $post->ID, '_wearit_email', true ),
        'address'        => get_post_meta( $post->ID, '_wearit_address', true ),
        'city'           => get_post_meta( $post->ID, '_wearit_city', true ),
        'postcode'       => get_post_meta( $post->ID, '_wearit_postcode', true ),
        'country'        => get_post_meta( $post->ID, '_wearit_country', true ),
        'notes'          => get_post_meta( $post->ID, '_wearit_notes', true ),
        'items'          => get_post_meta( $post->ID, '_wearit_items', true ) ?: [],
        'subtotal'       => (float) get_post_meta( $post->ID, '_wearit_subtotal', true ),
        'shipping'       => (float) get_post_meta( $post->ID, '_wearit_shipping', true ),
        'fee'            => (float) get_post_meta( $post->ID, '_wearit_fee', true ), // 0 on orders saved before the fee was stored
        'discount'       => (float) get_post_meta( $post->ID, '_wearit_discount', true ),
        'coupon'         => (string) get_post_meta( $post->ID, '_wearit_coupon', true ),
        'total'          => (float) get_post_meta( $post->ID, '_wearit_total', true ),
        'status'         => get_post_meta( $post->ID, '_wearit_status', true ) ?: 'new',
        'payment'        => get_post_meta( $post->ID, '_wearit_payment', true ) ?: 'cod',
        'payment_status' => get_post_meta( $post->ID, '_wearit_payment_status', true ) ?: 'awaiting',
        'history'        => get_post_meta( $post->ID, '_wearit_history', true ) ?: [],
    ];
}

// ── History ───────────────────────────────────────────────────

/**
 * Append a line to the order's history. Every status change goes through here,
 * so the admin screen shows who changed what and when.
 */
function wearit_order_log( $order_id, $message ) {
    $history   = get_post_meta( $order_id, '_wearit_history', true ) ?: [];
    $user      = wp_get_current_user();
    $history[] = [
        'time'    => current_time( 'j M Y, H:i' ),
        'by'      => $user->ID ? $user->display_name : 'Customer',
        'message' => $message,
    ];
    update_post_meta( $order_id, '_wearit_history', $history );
}

// ── Changing state ────────────────────────────────────────────

/** Move the order along. Returns false if the status isn't one we know. */
function wearit_set_order_status( $order_id, $status ) {
    $statuses = wearit_order_statuses();
    if ( ! isset( $statuses[ $status ] ) ) {
        return false;
    }

    $before = get_post_meta( $order_id, '_wearit_status', true );
    if ( $before === $status ) {
        return true; // nothing to do
    }

    update_post_meta( $order_id, '_wearit_status', $status );
    wearit_order_log( $order_id, 'Status: ' . ( $statuses[ $before ] ?? $before ) . ' → ' . $statuses[ $status ] );

    // Cash on delivery is paid at the door, so delivering it marks it paid.
    if ( $status === 'delivered'
        && get_post_meta( $order_id, '_wearit_payment', true ) === 'cod'
        && get_post_meta( $order_id, '_wearit_payment_status', true ) === 'awaiting'
    ) {
        wearit_set_payment_status( $order_id, 'paid' );
    }

    do_action( 'wearit_order_status_changed', $order_id, $status, $before );
    return true;
}

/** Mark the money as arrived, refunded, or failed. */
function wearit_set_payment_status( $order_id, $status ) {
    $statuses = wearit_payment_statuses();
    if ( ! isset( $statuses[ $status ] ) ) {
        return false;
    }

    $before = get_post_meta( $order_id, '_wearit_payment_status', true );
    if ( $before === $status ) {
        return true;
    }

    update_post_meta( $order_id, '_wearit_payment_status', $status );
    wearit_order_log( $order_id, 'Payment: ' . ( $statuses[ $before ] ?? $before ) . ' → ' . $statuses[ $status ] );

    // A bank transfer that lands is the signal to start packing.
    if ( $status === 'paid' && get_post_meta( $order_id, '_wearit_status', true ) === 'new' ) {
        wearit_set_order_status( $order_id, 'preparing' );
    }

    do_action( 'wearit_payment_status_changed', $order_id, $status, $before );
    return true;
}

// ── Show the useful stuff in the orders list ──────────────────

add_filter( 'manage_wearit_order_posts_columns', function ( $cols ) {
    return [
        'cb'        => $cols['cb'],
        'reference' => 'Ref',
        'customer'  => 'Customer',
        'total'     => 'Total',
        'payment'   => 'Payment',
        'status'    => 'Status',
        'date'      => 'Date',
    ];
} );

add_action( 'manage_wearit_order_posts_custom_column', function ( $col, $post_id ) {
    $order = wearit_get_order( $post_id );
    if ( ! $order ) {
        return;
    }
    if ( $col === 'reference' ) {
        echo '<strong>' . esc_html( $order['reference'] ) . '</strong>';
    }
    if ( $col === 'customer' ) {
        echo esc_html( $order['name'] ) . '<br /><small>' . esc_html( $order['email'] ) . '</small>';
    }
    if ( $col === 'total' ) {
        echo '$' . esc_html( number_format( $order['total'], 2 ) );
    }
    if ( $col === 'payment' ) {
        $paid  = $order['payment_status'] === 'paid';
        $color = $paid ? '#1a7f37' : '#8a6d00';
        echo esc_html( wearit_payment_label( $order['payment'] ) );
        echo '<br /><small style="color:' . esc_attr( $color ) . ';">'
            . esc_html( wearit_payment_statuses()[ $order['payment_status'] ] ?? $order['payment_status'] )
            . '</small>';
    }
    if ( $col === 'status' ) {
        echo esc_html( wearit_order_statuses()[ $order['status'] ] ?? $order['status'] );
    }
}, 10, 2 );

// ── Order details box on the edit screen ──────────────────────

add_action( 'add_meta_boxes', function () {
    add_meta_box( 'wearit_order_details', 'Order Details', 'wearit_order_details_box', 'wearit_order', 'normal', 'high' );
    add_meta_box( 'wearit_order_actions', 'Status', 'wearit_order_actions_box', 'wearit_order', 'side', 'high' );
} );

function wearit_order_details_box( $post ) {

    $order = wearit_get_order( $post->ID );
    if ( ! $order ) {
        echo '<p>Nothing saved on this order.</p>';
        return;
    }
    ?>
    <p>
      <strong>Reference:</strong> <?php echo esc_html( $order['reference'] ); ?> &nbsp;|&nbsp;
      <strong>Placed:</strong> <?php echo esc_html( $order['date'] ); ?> &nbsp;|&nbsp;
      <strong>Method:</strong> <?php echo esc_html( wearit_payment_label( $order['payment'] ) ); ?>
    </p>

    <h4>Customer</h4>
    <p>
      <?php echo esc_html( $order['name'] ); ?><br />
      <a href="mailto:<?php echo esc_attr( $order['email'] ); ?>"><?php echo esc_html( $order['email'] ); ?></a><br />
      <?php echo esc_html( $order['address'] ); ?><br />
      <?php echo esc_html( $order['postcode'] . ' ' . $order['city'] ); ?><br />
      <?php echo esc_html( $order['country'] ); ?>
    </p>

    <?php if ( $order['notes'] ) : ?>
      <h4>Notes</h4>
      <p><?php echo esc_html( $order['notes'] ); ?></p>
    <?php endif; ?>

    <h4>Items</h4>
    <table class="widefat striped">
      <thead>
        <tr><th>Product</th><th>Size</th><th>Qty</th><th>Price</th><th>Line total</th></tr>
      </thead>
      <tbody>
        <?php foreach ( $order['items'] as $item ) : ?>
          <tr>
            <td><?php echo esc_html( $item['title'] ?? '' ); ?> <small><?php echo esc_html( $item['sku'] ?? '' ); ?></small></td>
            <td><?php echo esc_html( $item['size'] ?: '—' ); ?></td>
            <td><?php echo esc_html( $item['qty'] ?? 0 ); ?></td>
            <td>$<?php echo esc_html( number_format( (float) ( $item['price'] ?? 0 ), 2 ) ); ?></td>
            <td>$<?php echo esc_html( number_format( (float) ( $item['line_total'] ?? 0 ), 2 ) ); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><th colspan="4" style="text-align:right;">Subtotal</th><th>$<?php echo esc_html( number_format( $order['subtotal'], 2 ) ); ?></th></tr>
        <?php if ( $order['discount'] > 0 ) : ?>
          <tr><th colspan="4" style="text-align:right;">Discount (<?php echo esc_html( $order['coupon'] ); ?>)</th><th>&minus;$<?php echo esc_html( number_format( $order['discount'], 2 ) ); ?></th></tr>
        <?php endif; ?>
        <tr><th colspan="4" style="text-align:right;">Shipping</th><th>$<?php echo esc_html( number_format( $order['shipping'], 2 ) ); ?></th></tr>
        <?php if ( $order['fee'] > 0 ) : ?>
          <tr><th colspan="4" style="text-align:right;"><?php echo esc_html( wearit_payment_label( $order['payment'] ) ); ?> fee</th><th>$<?php echo esc_html( number_format( $order['fee'], 2 ) ); ?></th></tr>
        <?php endif; ?>
        <tr><th colspan="4" style="text-align:right;">Total</th><th>$<?php echo esc_html( number_format( $order['total'], 2 ) ); ?></th></tr>
      </tfoot>
    </table>

    <h4>History</h4>
    <table class="widefat striped">
      <tbody>
        <?php foreach ( array_reverse( $order['history'] ) as $line ) : ?>
          <tr>
            <td style="width:150px;"><?php echo esc_html( $line['time'] ); ?></td>
            <td style="width:120px;"><?php echo esc_html( $line['by'] ); ?></td>
            <td><?php echo esc_html( $line['message'] ); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php
}

/** The sidebar box: two dropdowns and a save button. */
function wearit_order_actions_box( $post ) {

    $order = wearit_get_order( $post->ID );
    if ( ! $order ) {
        return;
    }
    wp_nonce_field( 'wearit_order_actions', 'wearit_order_actions_nonce' );
    ?>
    <p>
      <label for="wearit_status"><strong>Order status</strong></label><br />
      <select name="wearit_status" id="wearit_status" style="width:100%;">
        <?php foreach ( wearit_order_statuses() as $key => $label ) : ?>
          <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $order['status'], $key ); ?>>
            <?php echo esc_html( $label ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label for="wearit_payment_status"><strong>Payment</strong></label><br />
      <select name="wearit_payment_status" id="wearit_payment_status" style="width:100%;">
        <?php foreach ( wearit_payment_statuses() as $key => $label ) : ?>
          <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $order['payment_status'], $key ); ?>>
            <?php echo esc_html( $label ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </p>
    <p class="description">
      Changing these writes a line into the order's history. Marking a bank transfer paid
      moves the order to Preparing on its own.
    </p>
    <?php
}

/** Save the two dropdowns when the order is updated. */
add_action( 'save_post_wearit_order', function ( $post_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! isset( $_POST['wearit_order_actions_nonce'] )
        || ! wp_verify_nonce( $_POST['wearit_order_actions_nonce'], 'wearit_order_actions' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['wearit_status'] ) ) {
        wearit_set_order_status( $post_id, sanitize_key( wp_unslash( $_POST['wearit_status'] ) ) );
    }
    if ( isset( $_POST['wearit_payment_status'] ) ) {
        wearit_set_payment_status( $post_id, sanitize_key( wp_unslash( $_POST['wearit_payment_status'] ) ) );
    }
} );