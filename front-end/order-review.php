<?php

add_action( 'woocommerce_cart_totals_before_shipping', 'pnmbr_vuupt_review' , 99);
add_action( 'woocommerce_review_order_before_shipping', 'pnmbr_vuupt_review' , 99);
function pnmbr_vuupt_review() {
    ?>
        <tr>
            <th><?php __('Data da entrega','penumbra') ?></th>
            <td>000000</td>
        </tr>

    <?php
}
