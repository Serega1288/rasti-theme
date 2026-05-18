<?php

class salesdriveClass{
	
	private $salesdrive_domain;
	private $salesdrive_key;
	private $first_sync_order_id;
	private $salesdrive_link_product_type;
	
	function __construct(){
		$all_options = get_option('salesdrive_options');
		$this->salesdrive_key = $all_options['salesdrive_form_key'];
		$this->salesdrive_domain = 'https://'.$all_options['salesdrive_domain'];
		
		if(isset($all_options['salesdrive_link_product_type']) && $all_options['salesdrive_link_product_type']){
			$this->salesdrive_link_product_type = $all_options['salesdrive_link_product_type'];
		}
		else{
			$this->salesdrive_link_product_type = 'id';
		}
		
		if(isset($all_options['salesdrive_first_sync_order_id'])){
			$this->first_sync_order_id = $all_options['salesdrive_first_sync_order_id'];
		}
		else{
			$this->first_sync_order_id = 0;
		}
	}
	
	public function sendToSalesdrive($order_id){
		// prevent sending orders to SalesDrive multiple times
		if(get_post_meta($order_id, 'salesdrive_order_is_sent', true)){
			return;
		}
		// prevent sending to SalesDrive orders created before instaling integration
		if($order_id<$this->first_sync_order_id){
			return;
		}
		
		$order = new WC_Order($order_id);
		$order_items = $order->get_items();

		$i=0;
		$products = [];
		foreach($order_items as $item){
			$prod = $item->get_product();
			$item_meta_data_set = $item->get_meta_data();
			$item_meta_data = $item_meta_data_set[0];
			$product_id = $prod->get_id();
			$product_sku = $prod->get_sku();
			$product_name = $prod->get_name();
			$product_quantity = $item->get_quantity();
			$product_price = $item->get_total()/$item->get_quantity();

			$j=0;
			$item_meta_data_array = array();
			foreach($item_meta_data_set as $item_meta_data){
				$item_meta_data_array[] = $item_meta_data->get_data();
				$j++;
			}
			/*
			foreach($item_meta_data_array as $item_meta_data_item){
				if(strpos($item_meta_data_item['key'],'pa_') === 0){
					$product_name.=' - '.$item_meta_data_item['value'];
				}
			}
			*/
			if($this->salesdrive_link_product_type == 'id'){
				$products[$i]['id'] = $product_id;
			}
			else{
				$products[$i]['id'] = $product_sku ? $product_sku : $product_name;
			}
			$products[$i]['sku'] = $product_sku;
			$products[$i]['name'] = $product_name;
			$products[$i]['amount'] = $product_quantity;
			$products[$i]['costPerItem'] = $product_price;
			$i++;
		}

		$shipping_method = '';
		$shipping_instance_id = '';
		if(!empty($order->get_items('shipping'))){
			$order_shippings = $order->get_items('shipping');
			foreach($order_shippings as $order_shipping){
				$shipping_method = $order_shipping->get_method_title();
				if(method_exists($order_shipping, 'get_instance_id')){
					$shipping_instance_id = $order_shipping->get_instance_id();
				}
				$shipping_cost = $order_shipping->get_total();
				if($shipping_cost>0){
					$products[$i]['id'] = 'DELIVERY';
					$products[$i]['name'] = 'DELIVERY';
					$products[$i]['amount'] = '1';
					$products[$i]['costPerItem'] = $shipping_cost;
					$i++;
				}
			}
		}

		$payment_method_code = trim($order->get_payment_method());
		$payment_method = $payment_method_code;

		$salesdrive_options = get_option('salesdrive_options');
		$salesdrive_match_delivery_methods = [];
		$salesdrive_match_payment_methods = [];
		if(!empty($salesdrive_options['salesdrive_match_delivery_methods'])){
			$salesdrive_match_delivery_methods = $salesdrive_options['salesdrive_match_delivery_methods'];
		}
		if(!empty($salesdrive_options['salesdrive_match_payment_methods'])){
			$salesdrive_match_payment_methods = $salesdrive_options['salesdrive_match_payment_methods'];
		}
		if($shipping_instance_id && !empty($salesdrive_match_delivery_methods[$shipping_instance_id])){
			$shipping_method = $salesdrive_match_delivery_methods[$shipping_instance_id];
		}
		if($payment_method_code && !empty($salesdrive_match_payment_methods[$payment_method_code])){
			$payment_method = $salesdrive_match_payment_methods[$payment_method_code];
		}
		
		$order_fees = $order->get_items('fee');
		foreach($order_fees as $order_fee){
			$fee_name = $order_fee->get_name();
			$fee_total = $order_fee->get_total();
			if($fee_total){
				$products[$i]['id'] = $fee_name;
				$products[$i]['name'] = $fee_name;
				$products[$i]['amount'] = '1';
				$products[$i]['costPerItem'] = $fee_total;
				$i++;
			}
		}

		$meta_data = $order->get_meta_data();
		$j=0;
		$meta_data_values=[];
		foreach($meta_data as $meta_data_item){
			$meta_data_item_array = $meta_data_item->get_data();
			$meta_data_values[$meta_data_item_array['key']] = $meta_data_item_array['value'];
			$j++;
		}
		
		/* DEBUG */
		/*
		$handle = fopen(dirname(__FILE__).'/salesdrive_log.txt', "a");
		$date = date('m/d/Y h:i:s a', time());
		ob_start();
		print($date.". ".$_SERVER['REMOTE_ADDR']."\n");

		print('meta_data_values: '."\n");
		print_r($meta_data_values);

		$htmlStr = ob_get_contents()."\n";
		ob_end_clean(); 
		fwrite($handle,$htmlStr);
		*/
		/* END DEBUG */
		
		$first_name = trim($order->get_billing_first_name()) ? trim($order->get_billing_first_name()) : trim($order->get_shipping_first_name());
		$last_name = trim($order->get_billing_last_name()) ? trim($order->get_billing_last_name()) : trim($order->get_shipping_last_name());
		$email = trim($order->get_billing_email());
		$phone = trim($order->get_billing_phone());
		$company = trim($order->get_billing_company()) ? trim($order->get_billing_company()) : trim($order->get_shipping_company());
		$order_id = $order->get_id();
		$comment = $order->get_customer_note();
		
		$shipping_address = '';
		$country = trim($order->get_billing_country()) ? trim($order->get_billing_country()) : trim($order->get_shipping_country());
		$postcode = trim($order->get_billing_postcode()) ? trim($order->get_billing_postcode()) : trim($order->get_shipping_postcode());
		if($postcode){
			$shipping_address .= $postcode.', ';
		}
		$state = trim($order->get_billing_state()) ? trim($order->get_billing_state()) : trim($order->get_shipping_state());
		if($state){
			$shipping_address .= $state.', ';
		}
		$city = trim($order->get_billing_city()) ? trim($order->get_billing_city()) : trim($order->get_shipping_city());
		if($city){
			$shipping_address .= $city.', ';
		}
		$address_1 = trim($order->get_billing_address_1()) ? trim($order->get_billing_address_1()) : trim($order->get_shipping_address_1());
		if($address_1){
			$shipping_address .= $address_1.', ';
		}
		$address_2 = trim($order->get_billing_address_2()) ? trim($order->get_billing_address_2()) : trim($order->get_shipping_address_2());
		if($address_2){
			$shipping_address .= $address_2.', ';
		}
		$warehouse = $address_1;
		/*
		$warehouse = trim($meta_data_values['_billing_myfield11']);
		if($warehouse){
			$shipping_address .= $warehouse;
		}
		*/
		$shipping_address = trim($shipping_address,', ');

		$novaposhta = array();
		$novaposhta['ServiceType'] = 'Warehouse';
		$novaposhta['city'] = $city;
		$novaposhta['WarehouseNumber'] = $warehouse;
		
		$site = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		$site = preg_replace('/^www\./','',$site);

		$salesdrive_values = [
			"form" => $this->salesdrive_key,
			"fName"=>$first_name,
			"lName"=>$last_name,
			"phone"=>$phone,
			"email"=>$email,
			"company"=>$company,
			"externalId"=>$order_id,
			"comment"=>$comment,
			"products"=>$products, 
			"payment_method"=>$payment_method,
			"shipping_method"=>$shipping_method,
			"shipping_address"=>$shipping_address,
			"novaposhta"=> $novaposhta,		
			"sajt"=> $site,		
		];
		
		// if utm-data is set in cookies than use cookies. Otherwise use post_meta data
		if(
			isset($_COOKIE["prodex24source_full"]) || 
			isset($_COOKIE["prodex24source"]) ||
			isset($_COOKIE["prodex24medium"]) ||
			isset($_COOKIE["prodex24campaign"]) ||
			isset($_COOKIE["prodex24content"]) ||
			isset($_COOKIE["prodex24term"])
		){
			$salesdrive_values['prodex24source_full'] = isset($_COOKIE["prodex24source_full"])?$_COOKIE["prodex24source_full"]:"";
			$salesdrive_values['prodex24source'] = isset($_COOKIE["prodex24source"])?$_COOKIE["prodex24source"]:"";
			$salesdrive_values['prodex24medium'] = isset($_COOKIE["prodex24medium"])?$_COOKIE["prodex24medium"]:"";
			$salesdrive_values['prodex24campaign'] = isset($_COOKIE["prodex24campaign"])?$_COOKIE["prodex24campaign"]:"";
			$salesdrive_values['prodex24content'] = isset($_COOKIE["prodex24content"])?$_COOKIE["prodex24content"]:"";
			$salesdrive_values['prodex24term'] = isset($_COOKIE["prodex24term"])?$_COOKIE["prodex24term"]:"";
		}
		else{
			$salesdrive_values['prodex24source_full'] = get_post_meta($order_id, 'prodex24source_full', true);
			$salesdrive_values['prodex24source'] = get_post_meta($order_id, 'prodex24source', true);
			$salesdrive_values['prodex24medium'] = get_post_meta($order_id, 'prodex24medium', true);
			$salesdrive_values['prodex24campaign'] = get_post_meta($order_id, 'prodex24campaign', true);
			$salesdrive_values['prodex24content'] = get_post_meta($order_id, 'prodex24content', true);
			$salesdrive_values['prodex24term'] = get_post_meta($order_id, 'prodex24term', true);
		}

		$this->send_to_salesdrive('/handler/', $salesdrive_values);
		
		// mark order as sent to SalesDrive
		update_post_meta($order_id, 'salesdrive_order_is_sent', true);
		
		// update salesdrive_first_sync_order_id if not set
		$all_options = get_option('salesdrive_options');
		if(!$all_options['salesdrive_first_sync_order_id']){
			$all_options['salesdrive_first_sync_order_id'] = $order_id;
			update_option('salesdrive_options', $all_options);
		}
	}

	private function send_to_salesdrive($salesdrive_url, $salesdrive_values) {
		$_salesdrive_ch = curl_init();
		curl_setopt($_salesdrive_ch, CURLOPT_URL, $this->salesdrive_domain.$salesdrive_url);
		curl_setopt($_salesdrive_ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($_salesdrive_ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		curl_setopt($_salesdrive_ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($_salesdrive_ch, CURLOPT_POST, 1);
		curl_setopt($_salesdrive_ch, CURLOPT_POSTFIELDS, json_encode($salesdrive_values));
		curl_setopt($_salesdrive_ch, CURLOPT_TIMEOUT, 10);

		$_salesdrive_res = curl_exec($_salesdrive_ch);
		curl_close($_salesdrive_ch);
	}
	
	public function saveUtmToOrder($order_id){
		if(isset($_COOKIE["prodex24source_full"])){
			update_post_meta($order_id, 'prodex24source_full', $_COOKIE["prodex24source_full"]);
		}
		if(isset($_COOKIE["prodex24source"])){
			update_post_meta($order_id, 'prodex24source', $_COOKIE["prodex24source"]);
		}
		if(isset($_COOKIE["prodex24medium"])){
			update_post_meta($order_id, 'prodex24medium', $_COOKIE["prodex24medium"]);
		}
		if(isset($_COOKIE["prodex24campaign"])){
			update_post_meta($order_id, 'prodex24campaign', $_COOKIE["prodex24campaign"]);
		}
		if(isset($_COOKIE["prodex24content"])){
			update_post_meta($order_id, 'prodex24content', $_COOKIE["prodex24content"]);
		}
		if(isset($_COOKIE["prodex24term"])){
			update_post_meta($order_id, 'prodex24term', $_COOKIE["prodex24term"]);
		}
	}

	public function getPaymentMethods(){
		$response = $this->executeApi('/api/payment-methods/');
		return $response;
	}
	
	public function getDeliveryMethods(){
		$response = $this->executeApi('/api/delivery-methods/');
		return $response;
	}
	
	public function getStatuses(){
		$response = $this->executeApi('/api/statuses/');
		return $response;
	}
	
	public function executeApi($salesdrive_url){
		$headers = [
			'Content-Type: application/json',
			'Form-Api-Key: '.$this->salesdrive_key
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->salesdrive_domain.$salesdrive_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		$result = curl_exec($ch);
		$error = curl_error($ch);
		$result_decoded = json_decode($result,true);

		return $result_decoded;
	}
	
}