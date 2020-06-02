<?php
/**
* Plugin Name: Penumbra Vuupt integration
* Plugin URI: https://penumbra.design/
* Description: Vuupt service integration via API.
* Version: 1.0
* Author: Penumbra design et web.
* Author URI: https://penumbra.design/
**/

include( plugin_dir_path( __FILE__ ) . 'front-end/custom-fields.php');

function pnmbr_vuupt_register_settings() {
   add_option( 'pnmbr_vuupt_api', '');
   add_option( 'pnmbr_vuupt_maps_api', '');
   register_setting( 'pnmbr_vuupt_options_group', 'pnmbr_vuupt_api', 'pnmbr_vuupt_callback' );
   register_setting( 'pnmbr_vuupt_options_group', 'pnmbr_vuupt_maps_api', 'pnmbr_vuupt_callback' );
}
add_action( 'admin_init', 'pnmbr_vuupt_register_settings' );

function pnmbr_vuupt_register_options_page() {
  add_options_page('Page Title', 'VUUPT', 'manage_options', 'pnmbr_vuupt', 'pnmbr_vuupt_options_page');
}
add_action('admin_menu', 'pnmbr_vuupt_register_options_page');

function pnmbr_vuupt_options_page()
{
?>
  <div>
  <?php screen_icon(); ?>
  <h2>Penumbra Vuupt Integration</h2>
  <form method="post" action="options.php">
  <?php settings_fields( 'pnmbr_vuupt_options_group' ); ?>
  <h3>Settings</h3>
  <p>Here you'll find the settings for integrating Vuupt into your woocommerce store.</p>
  <table>
  <tr valign="top">
  <th scope="row"><label for="pnmbr_vuupt_api">API Key</label></th>
  <td><input type="text" id="pnmbr_vuupt_api" name="pnmbr_vuupt_api" value="<?php echo get_option('pnmbr_vuupt_api'); ?>" /></td>
  </tr>
  <tr valign="top">
  <th scope="row"><label for="pnmbr_vuupt_maps_api">Google maps Javascript API Key</label></th>
  <td><input type="text" id="pnmbr_vuupt_maps_api" name="pnmbr_vuupt_maps_api" value="<?php echo get_option('pnmbr_vuupt_maps_api'); ?>" /></td>
  </tr>
  </table>
  <?php  submit_button(); ?>
  </form>
  </div>
<?php
}

// PLUGIN ACTIONS

add_action( 'woocommerce_thankyou', 'pnmbr_add_to_vuupt');

add_action('woocommerce_order_status_changedNO', 'pnmbr_update_orderstatus', 20, 4 );
function pnmbr_update_orderstatus( $order_id, $old_status, $new_status, $order ){
    if ( ($old_status == 'on-hold' || $old_status == 'pending') && ($new_status == 'processing' || $new_status == 'completed')) {
      pnmbr_add_to_vuupt($order_id);
    }
}

add_action('wp_insert_postNO', function($order_id)
{
    if(!did_action('woocommerce_checkout_order_processed')
        && get_post_type($order_id) == 'shop_order'
        // && validate_order($order_id)
				)
    {
         pnmbr_add_to_vuupt($order_id);
    }
});

function validate_order($order_id)
{
    $order = new \WC_Order($order_id);
    $user_meta = get_user_meta($order->get_user_id());
    if($user_meta)
        return true;
    return false;
}

function pnmbr_add_to_vuupt( $order_id ){

	// Order Setup Via WooCommerce

	$order = new WC_Order( $order_id );

	// Iterate Through Items

	//$items = $order->get_items();
	//foreach ( $items as $item ) {
    // $product_id = $item['product_id'];
    // $product = new WC_Product($item['product_id']);

        // Only add Marmitas
        //if ( has_term( 'marmita', 'product_cat', $product_id ) ) {
        if (!$order) {
          $order->add_order_note('não foi possível coletar dados do pedido.');
          return false;
        }

          $status = $order->get_status();
	       	$name		= $order->billing_first_name;
        	$surname	= $order->billing_last_name;
        	$email		= $order->billing_email;
          $phone = '55'.$order->billing_phone;
          $notes = $order->get_customer_note();
          $address = formatted_shipping_address($order);
        	$apikey 	= get_option('pnmbr_vuupt_api');

					$service_id = get_post_meta( $order_id, 'service_id' )[0];
					$customer_id = get_post_meta( $order_id, 'customer_id' )[0];

					// API Callout to URL
        	$url_customer = 'https://api.vuupt.com/api/v1/customers'.($customer_id ? '/'.$customer_id : '');
        	$url_service = 'https://api.vuupt.com/api/v1/services'.($service_id ? '/'.$service_id : '');


          $geocoded = getGeocodeData($address);
          if (is_array($geocoded)) {

					$body_customer = array(
						"name"	=> "{$name} {$surname}",
						"address" 		=> $address,
						"address_complement" => $order->shipping_address_2,
						"latitude" 		=> $geocoded['latitude'] ?: 0,
						"longitude" 		=> $geocoded['longitude'] ?: 0,
		        "email" => $email,
		        "phone_number" => $phone,
					);

			$response = wp_remote_post( $url_customer,
				array(
					'headers'   => array(
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'api_key '.$apikey ),
					'method'    => ( $customer_id ? 'PUT' : 'POST' ),
					'timeout' => 75,
					'body'		=> json_encode($body_customer),
				)
			);

			$vars = json_decode($response['body'],true);

      if ($order->get_payment_method() == 'cod' || $order->get_payment_method() == 'bacs') {
        $ordertitle = '[R$ '.$order->get_total().'] ';
      } else if ($order->get_payment_method() == 'gerencianet_oficial' && $status == 'on-hold') {
        $ordertitle = '[BB '.$order->get_total().'] ';
      } else {
        $ordertitle = '[PG] ';
      }
			$ordertitle .= '#'.$order_id;
      $ordertitle .= ($notes ? '→'.$notes : '');

			//LOOP ALL THE PRODUCTS IN THE CART
			$products = $order->get_items();
			$ptotal = 0;
      foreach($products as $product) {
			  $ptotal += $product['qty'];
        // $_product =  wc_get_product( $product['product_id'] );
            //GET GET PRODUCT M3
            // $prod_m3 = $_product->get_length() *
            //            $_product->get_width() *
            //            $_product->get_height();
            //MULTIPLY BY THE CART ITEM QUANTITY
            //DIVIDE BY 1000000 (ONE MILLION) IF ENTERING THE SIZE IN CENTIMETERS
            // $prod_m3 = ($prod_m3 * $product['qty']) / 10000;
            //PUSH RESULT TO ARRAY
            // array_push($cart_prods_m3, $prod_m3);
      }
			// $dimension = (int)array_sum($cart_prods_m3) ** (1/3);

			$orderdate = date( 'Y-m-d', $order->get_date_created()->getOffsetTimestamp());
			$dotw =  date( 'D', $order->get_date_created ()->getOffsetTimestamp());
  		$ampm = date( 'a', $order->get_date_created ()->getOffsetTimestamp());

			if (($dotw == 'Mon' && $ampm == 'am') ) {
		    $deliveryperiod = 'next tuesday';
		  } else if (($dotw == 'Thu' && $ampm == 'pm') || (in_array ( $dotw, ['Fri', 'Sat', 'Sun']) )) {
		    $deliveryperiod = 'next tuesday';
		  } else if ($dotw == 'Thu' && $ampm == 'am') {
		    $deliveryperiod = 'next friday';
		  } else {
		    $deliveryperiod = 'next friday';
		  }

			$next_delivery = date('Y-m-d', strtotime($deliveryperiod, strtotime($order->get_date_created()) ));

      $items = $order->get_items();
    	foreach ( $items as $item ) {
        $product_id = $item['product_id'];
        switch (get_field('vuupt_override', $product_id)) {
          case 'none':
            break;
          case 'date':
            $next_delivery = get_field('vuupt_date', $product_id);
            break;
          case 'next':
            $next_delivery = date('Y-m-d', strtotime(get_field('vuupt_next', $product_id), strtotime($order->get_date_created()) ));
            break;
        }
      }
        // $product = new WC_Product($item['product_id']);
            // Only add Marmitas
            //if ( has_term( 'marmita', 'product_cat', $product_id ) ) {

      $body_service = array(
				"title"	=> $ordertitle,
        "notes" => $notes,
				"customer_id" 		=> $vars['customer']['id'],
				"dimension_1" => $ptotal,
				"dimension_2" => $ptotal,
				"dimension_3" => $ptotal,
				"dimension_4" => $ptotal,
				"dimension_5" => $ptotal,
				"note" => $phone,
				"scheduled_start" => $next_delivery." 11:00:00",
				"scheduled_end" => $next_delivery." 21:00:00",
			);

      $response = wp_remote_post( $url_service,
				array(
					'headers'   => array(
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'api_key '.$apikey ),
					'method'    => ( $service_id ? 'PUT' : 'POST' ),
					'timeout' => 75,
					'body'		=> json_encode($body_service),
				)
			);

			$vars_service = json_decode($response['body'],true);

      if ($vars_service['service']['id'] || $service_id) {
        // Adding meta to avoid duplicity
        update_post_meta($order_id, 'customer_id', $customer_id ?: $vars['customer']['id'] );
        update_post_meta($order_id, 'service_id', $service_id ?: $vars_service['service']['id'] );

        // Add order note with customer ID
        $order->add_order_note( 'VUUPT: Service ID '.$service_id ?: $vars_service['service']['id'].' agendado para '.$deliveryperiod.':'.$orderdate.'->'.$next_delivery );

      } else {
        $order->add_order_note( 'erro ao criar serviço: '.print_r($vars_service,true) );
        $order->add_order_note( 'erro ao criar cliente: '.print_r($vars,true) );
      }

			return true;

    } else {
      $order->add_order_note(print_r($geocoded,true));
      $order->add_order_note( 'erro ao geocodar endereço. certifique-se de que a <a href="'.get_home_url().'/wp-admin/options-general.php?page=pnmbr_vuupt">chave de API está correta</a> e os módulos instalados: Geocoding API, Maps Javascript API' );
      return false;
    }
}

function formatted_shipping_address($order) {
  if ($order->shipping_address_1) {
    return
    $order->shipping_address_1 .' '.
    $order->shipping_number . ', ' .
    $order->shipping_city      . ', ' .
    $order->shipping_state     . ' ' .
    $order->shipping_postcode;
  } else {
    return
    $order->billing_address_1 .' '.
    $order->billing_number . ', ' .
    $order->billing_city      . ', ' .
    $order->billing_state     . ' ' .
    $order->billing_postcode;
  }
}


function getGeocodeData($address)
{
    $geocodederror = '';
    $address = urlencode($address);
    $googleMapUrl = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key=".get_option('pnmbr_vuupt_maps_api');
    $geocodeResponseData = curl_get_contents($googleMapUrl);
    $responseData = json_decode($geocodeResponseData, true);
    if ($responseData['status'] == 'OK') {
        $latitude = isset($responseData['results'][0]['geometry']['location']['lat']) ? $responseData['results'][0]['geometry']['location']['lat'] : "";
        $longitude = isset($responseData['results'][0]['geometry']['location']['lng']) ? $responseData['results'][0]['geometry']['location']['lng'] : "";
        $formattedAddress = isset($responseData['results'][0]['formatted_address']) ? $responseData['results'][0]['formatted_address'] : "";
        if ($latitude && $longitude && $formattedAddress) {
            return [
                'address_formatted' => $formattedAddress,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        } else {
            return 'não foi possível formatar a latitude, longitude, endereço.';
            return false;
        }
    } else {
        return "erro: {$responseData['status']}";
    }
}

function curl_get_contents($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}
