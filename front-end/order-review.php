<?php

add_action( 'woocommerce_cart_totals_before_shipping', 'pnmbr_vuupt_review' , 99);
add_action( 'woocommerce_review_order_before_shipping', 'pnmbr_vuupt_review' , 99);
function pnmbr_vuupt_review() {

  global $woocommerce;

?>
        <tr>
            <th><?php echo __('Data da entrega','penumbra') ?></th>
            <td><?php echo date( 'F j, Y', strtotime(get_delivery_when($woocommerce->cart->get_cart(), current_time('Y-m-d')))) ?></td>
        </tr>

    <?php
}
