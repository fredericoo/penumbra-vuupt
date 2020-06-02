<?php

add_action( 'woocommerce_cart_totals_before_shipping', 'pnmbr_vuupt_review' , 99);
add_action( 'woocommerce_review_order_before_shipping', 'pnmbr_vuupt_review' , 99);
function pnmbr_vuupt_review($order_id) {

  $order = new \WC_Order($order_id);
    ?>
        <tr>
            <th><?php echo __('Data da entrega','penumbra') ?></th>
            <td><?php echo get_delivery_when($order) ?></td>
        </tr>

    <?php
}
