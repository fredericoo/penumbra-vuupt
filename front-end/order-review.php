<?php

add_action( 'woocommerce_cart_totals_before_shipping', 'pnmbr_vuupt_review' , 99);
add_action( 'woocommerce_review_order_before_shipping', 'pnmbr_vuupt_review' , 99);
function pnmbr_vuupt_review() {

  global $woocommerce;

  $dotw =  current_time( 'D' );
  $ampm = current_time( 'a' );

?>
        <tr class="delivery_when">
            <th><?php echo __('Data da entrega','penumbra') ?>
              <?php echo $dotw; echo $ampm; ?>
            <?php echo get_delivery_when($woocommerce->cart->get_cart(), current_time('Y-m-d'))),$dotw,$ampm) ?></th>
            <td><?php echo date_i18n( __('j \d\e F \d\e Y','penumbra'), strtotime(get_delivery_when($woocommerce->cart->get_cart(), current_time('Y-m-d'))),$dotw,$ampm) ?></td>
        </tr>

    <?php
}
