<?php
add_filter( 'woocommerce_get_order_item_totals', 'pnmbr_add_vuupt_review', 10, 2 );

function pnmbr_add_vuupt_review( $total_rows, $myorder_obj ) {

$total_rows['vuuupt_shipping'] = array(
   'label' => __( 'Data da entrega:', 'woocommerce' ),
   'value'   => print_r('$myorder_obj',true)
);

return $total_rows;
}
