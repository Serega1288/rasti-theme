<?php
// Die if accessed directly
if ( !defined('ABSPATH') ) {
	die;
}

class FC_API_Admin {

	private static $_instance = null;
	private $salesdrive_key;
	private $salesdrive_domain;
	private $salesdrive_link_product_type;
	private $uncategorized = 'Uncategorized';
	private $true_page;
	
	private function __construct(){
		global $wpdb;
		$this->true_page = 'salesdrive-settings.php'; // это часть URL страницы, рекомендую использовать строковое значение, т.к. в данном случае не будет зависимости от того, в какой файл вы всё это вставите
		add_action('wp_ajax_fc_api_import_orders', [
			$this,
			'fc_api_import_order_callback'
		]);
		$all_options = get_option('salesdrive_options');
		$this->salesdrive_key = $all_options['salesdrive_form_key'];
		$this->salesdrive_domain = 'https://'.$all_options['salesdrive_domain'];
		$this->salesdrive_link_product_type = $all_options['salesdrive_link_product_type'] ? $all_options['salesdrive_link_product_type'] : 'id';
	}
	
	public static function get_instance(){
		if(null === self::$_instance){
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	
	// custom box with import order
	public function fc_api_project_box($post){
		add_action('admin_enqueue_scripts', [ $this, 'fc_api_admin_scripts' ], 99);
	}
	
	public function fc_api_project_box_render(){
		global $post;
	}
	
	public function fc_api_import_order_callback(){
		$time_start = time();
		$offset = isset($_POST['offset']) ? $_POST['offset'] : 0;
		$variationCount = isset($_POST['variationCount']) ? $_POST['variationCount'] : 0;
		$timeElapsed = isset($_POST['timeElapsed']) ? $_POST['timeElapsed'] : 0;
		$category_count = 0;
		$response_text = '';
		if(!$offset){
			$category_count = $this->sd_export_categories();
			//$response_text .= "Экспортировано $category_count категорий. ";
		}
		$product_export_result = $this->sd_export_products($offset);
		$product_count = $product_export_result['products'];
		$products_with_variations = $product_export_result['products_with_variations'];
		$time_finish = time();
		$execution_time = $time_finish - $time_start;
		$timeElapsed += $execution_time;
		$exported = $product_count+$offset;
		$variationCount += $products_with_variations;
		$response_text .= "Обработано $exported товаров. ";
		if($products_with_variations != $product_count){
			$response_text .= "С учетом вариаций, создано $products_with_variations товаров. ";
		}
		$response_text .= "Время выполнения: $execution_time секунд. ";
		if($product_count<5){
			$finish = 1;
		}
		else{
			$finish = 0;
		}
		$result = array(
			'result' => 'finished',
			'product_count' => $product_count,
			'products_with_variations' => $product_export_result['products_with_variations'],
			'category_count' => $category_count,
			'execution_time' => $execution_time,
			'response_text' => $response_text,
			'finish' => $finish,
			'variationCount' => $variationCount,
			'timeElapsed' => $timeElapsed,
			'exported' => $exported
		);
		wp_send_json($result);
	}
	
	// callback function after click import
	public function fc_api_admin_scripts() {
		global $post;
		wp_enqueue_script('fc_api_admin_scripts', FC_API_PLUGIN_BASE_URL . 'inc/admin/assets/js/worker.js', [ 'jquery' ]);
		wp_localize_script('fc_api_admin_scripts', 'DataObject', array(
			'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
			'importCall'       => 'fc_api_import_orders',
		));
		wp_enqueue_style('fc_api_admin_styles', FC_API_PLUGIN_BASE_URL . 'inc/admin/assets/css/admin-styles.min.css', 99);
	}
	
	public function sd_export_categories() {
		$args = array(
			'taxonomy'     => 'product_cat',
			'orderby'      => 'name',
			'show_count'   => 0,
			'pad_counts'   => 0,
			'hierarchical' => 1,
			'title_li'     => '',
			'hide_empty'   => 0
		);
		$all_categories = get_categories($args);
		foreach($all_categories as $category){
			if($category->name != $this->uncategorized){
				$categories[] =	array(
					"id" => $category->cat_ID,
					"name" => $category->name,
					"parentId" => $category->parent
				);
			}
		}
		$salesdrive_values = array(
			"form" => $this->salesdrive_key,
			"action" => "update",
			"category" => $categories,
		);
		$this->send_to_salesdrive('/category-handler/', $salesdrive_values);
		return count($categories);
	}
	
	public function sd_export_products($offset=0){
		$query = new WC_Product_Query(array(
			'return' => 'ids',
			'limit' => '100',
			'offset' => $offset,
		));
		$products = $query->get_products();
		$_pf = new WC_Product_Factory();
		$i=0;
		$sd_products = array();
		$sd_products_i = 0;
		for($j=0; $j<count($products); $j++) {
			$product = wc_get_product($products[$j]);
			$product_id = $product->get_id();
			if(get_post_status($product_id)=='draft'){
				continue;
			}
			$product_attributes = $product->get_attributes();
			$params = [];
			$pi = 0;
			foreach($product_attributes as $product_attribute_object){
				$product_attribute = $product_attribute_object->get_data();
				if(!$product_attribute['variation'] && $product_attribute['visible']){
					$attribute = wc_get_attribute($product_attribute['id']);
					$product_terms = wc_get_product_terms($product->get_id(), $product_attribute_object->get_taxonomy());
					$param_values = [];
					foreach($product_terms as $product_term){
						$param_values[] = $product_term->name;
					}
					$params[$pi]['name'] = $attribute->name;
					$params[$pi]['type'] = 'multiselect';
					$params[$pi]['value'] = $param_values;
					$pi++;
				}
			}
			
			$product_name = $product->get_name();
			$product_discount = [];
			$product_discount['value'] = '';
			$product_discount['date_start'] = '';
			$product_discount['date_end'] = '';
			$product_price = $product->get_price();
			$product_regular_price = $product->get_regular_price();
			$product_sale_price = $product->get_sale_price();
			if($product_sale_price && $product_sale_price<$product_regular_price){
				$product_date_on_sale_from = $product->get_date_on_sale_from();	
				if($product_date_on_sale_from){
					$product_date_on_sale_from = $product_date_on_sale_from->date('d.m.Y');
				}
				else{
					$product_date_on_sale_from = '';
				}
				$product_date_on_sale_to = $product->get_date_on_sale_to();
				if($product_date_on_sale_to){
					$product_date_on_sale_to = $product_date_on_sale_to->date('d.m.Y');
				}
				else{
					$product_date_on_sale_to = '';
				}
				$product_discount['value'] = $product_regular_price - $product_sale_price;
				$product_discount['date_start'] = $product_date_on_sale_from;
				$product_discount['date_end'] = $product_date_on_sale_to;
			}
			
			$product_sku = $product->get_sku();
			$product_weight = $product->get_weight();
			$product_length = $product->get_length();
			$product_width = $product->get_width();
			$product_height = $product->get_height();
			$product_volume = 0;
			if($product_weight && $product_length && $product_height){
				$product_volume = round($product_length * $product_width * $product_height / 100 / 100 / 100, 4);
			}
			
			$product_url = get_site_url() . '/product/' . $product->get_slug() . '/';
			$product_manage_stock = $product->get_manage_stock();
			$product_description = str_replace("\n", "<br>", $product->get_description());
			$product_has_variations = $product->is_type('variable');
			if($product_manage_stock){
				$product_stock_quantity = $product->get_stock_quantity();
			}
			else{
				$product_stock_quantity = '';
			}
			$pr_cats = get_the_terms($products[$j], 'product_cat');
			$pr_cat = $pr_cats[0];
			if($pr_cat->name != $this->uncategorized){
				$product_category = array(
					"id" => $pr_cat->term_id,
					"name" => $pr_cat->name
				);
			}
			else{
				$product_category = '';
			}
			$pr_img = $product->get_image_id();
			$pr_img_medium = wp_get_attachment_image_src($pr_img, 'medium');
			$product_img[] = array(
				"fullsize" => wp_get_attachment_url($pr_img),
				"thumbnail" => $pr_img_medium[0]
			);
			$images = $product->get_gallery_image_ids();
			foreach($images as $image) {
				$image_medium = wp_get_attachment_image_src($image, 'medium');
				$product_img[] = array(
					"fullsize" => wp_get_attachment_url($image),
					"thumbnail" => $image_medium[0]
				);
			}
			
			if($product_has_variations){
				$variations = $product->get_available_variations();
				if(count($variations)>0){
					foreach($variations as $variation){
						if($variation['variation_is_active'] && $variation['variation_is_visible']){
							$variation_id = $variation['variation_id'];
							$variation_object = new WC_Product_Variation($variation['variation_id']);
							$variation_sku = isset($variation['variation_sku']) ? $variation['variation_sku'] : (isset($variation['sku']) ? $variation['sku'] : "");
							$variation_price = $variation['display_price'];
							$variation_regular_price = $variation['display_regular_price'];
							$variation_sale_price = $variation_object->get_sale_price();
							$variation_discount = [];
							$variation_discount['value'] = '';
							$variation_discount['date_start'] = '';
							$variation_discount['date_end'] = '';
							
							if($variation_sale_price && $variation_sale_price<$variation_regular_price){
								$variation_date_on_sale_from = $variation_object->get_date_on_sale_from();	
								$variation_date_on_sale_to = $variation_object->get_date_on_sale_to();
								if($variation_date_on_sale_from){
									$variation_date_on_sale_from = $variation_date_on_sale_from->date('d.m.Y');
									$variation_discount['date_start'] = $variation_date_on_sale_from;
								}
								$variation_date_on_sale_to = $variation_object->get_date_on_sale_to();
								if($variation_date_on_sale_to){
									$variation_date_on_sale_to = $variation_date_on_sale_to->date('d.m.Y');
									$variation_discount['date_end'] = $variation_date_on_sale_to;
								}
								$variation_discount['value'] = $variation_regular_price - $variation_sale_price;
							}
							/*
							if($variation_id==32){
								//print_r(($variation_object));
								print("variation_regular_price: $variation_regular_price \n");
								print("variation_sale_price: $variation_sale_price \n");
								print("variation_date_on_sale_from: $variation_date_on_sale_from \n");
								print("variation_date_on_sale_to: $variation_date_on_sale_to \n");
							}
							*/
							$variation_description = $variation['variation_description'];
							if($variation_description){
								$variation_description = $variation_description."\n\n".$product_description;
							}
							else{
								$variation_description = $product_description;
							}
							$variation_images = array();
							if(!empty($variation['image'])){
								if(!empty($variation['image']['url'])){
									$variation_images[0]['fullsize'] = $variation['image']['url'];
								}
								if(!empty($variation['image']['thumb_src'])){
									$variation_images[0]['thumbnail'] = $variation['image']['thumb_src'];
								}
							}
							else{
								$variation_images = $product_img;
							}
							$variation_name = $product_name;
							foreach($variation['attributes'] as $variation_attribute => $term_slug){
								$taxonomy = str_replace( 'attribute_', '', $variation_attribute );
								$attribute_value = get_term_by( 'slug', $term_slug, $taxonomy )->name;
								if(!$attribute_value){
									$attribute_value = $term_slug;
								}
								$variation_name .= ' - '.$attribute_value;
							}
							$variation_stock_quantity = $variation['max_qty'];
							if($this->salesdrive_link_product_type == 'id'){
								$sd_products[$sd_products_i]['id'] = $variation_id;
							}
							if($this->salesdrive_link_product_type == 'sku'){
								$sd_products[$sd_products_i]['id'] = $variation_sku;
							}
							$sd_products[$sd_products_i]['name'] = $variation_name;
							$sd_products[$sd_products_i]['costPerItem'] = $variation_regular_price;
							$sd_products[$sd_products_i]['discount'] = $variation_discount;
							$sd_products[$sd_products_i]['sku'] = $variation_sku;
							//$sd_products[$sd_products_i]['currency'] = '';
							//$sd_products[$sd_products_i]['stockBalance'] = $variation_stock_quantity;
							$sd_products[$sd_products_i]['description'] = $variation_description;
							$sd_products[$sd_products_i]['url'] = $product_url;
							$sd_products[$sd_products_i]['category'] = $product_category;
							$sd_products[$sd_products_i]['images'] = $variation_images;
							$sd_products[$sd_products_i]['params'] = $params;
							if($product_weight){
								$sd_products[$sd_products_i]['weight'] = $product_weight;
							}
							//if($product_volume){
							//	$sd_products[$sd_products_i]['volume'] = $product_volume;
							//}
							if($product_length || $product_width || $product_height){
								$sd_products[$sd_products_i]['length'] = $product_length;
								$sd_products[$sd_products_i]['width'] = $product_width;
								$sd_products[$sd_products_i]['height'] = $product_height;
							}
							if($this->salesdrive_link_product_type == 'sku' && !$variation_sku){
								unset($sd_products[$sd_products_i]);
							}
							else{
								$sd_products_i++;
							}
						}
					}
				}
			}
			else{
				if($this->salesdrive_link_product_type == 'id'){
					$sd_products[$sd_products_i]['id'] = $product_id;
				}
				if($this->salesdrive_link_product_type == 'sku'){
					$sd_products[$sd_products_i]['id'] = $product_sku;
				}
				$sd_products[$sd_products_i]['name'] = $product_name;
				$sd_products[$sd_products_i]['costPerItem'] = $product_regular_price;
				$sd_products[$sd_products_i]['discount'] = $product_discount;
				$sd_products[$sd_products_i]['sku'] = $product_sku;
				//$sd_products[$sd_products_i]['currency'] = '';
				//$sd_products[$sd_products_i]['stockBalance'] = $product_stock_quantity;
				$sd_products[$sd_products_i]['description'] = $product_description;
				$sd_products[$sd_products_i]['url'] = $product_url;
				$sd_products[$sd_products_i]['category'] = $product_category;
				$sd_products[$sd_products_i]['images'] = $product_img;
				$sd_products[$sd_products_i]['params'] = $params;
				if($product_weight){
					$sd_products[$sd_products_i]['weight'] = $product_weight;
				}
				//if($product_volume){
				//	$sd_products[$sd_products_i]['volume'] = $product_volume;
				//}
				if($product_length || $product_width || $product_height){
					$sd_products[$sd_products_i]['length'] = $product_length;
					$sd_products[$sd_products_i]['width'] = $product_width;
					$sd_products[$sd_products_i]['height'] = $product_height;
				}
				
				if($this->salesdrive_link_product_type == 'sku' && !$product_sku){
					unset($sd_products[$sd_products_i]);
				}
				else{
					$sd_products_i++;
				}
			}
			unset($product_img);
		}
		
		$salesdrive_values = array(
			"form" => $this->salesdrive_key,
			"action" => "update",
			"product" => $sd_products,
		);

		$this->send_to_salesdrive('/product-handler/', $salesdrive_values);

		$result_array = array(
			'products' => count($products),
			'products_with_variations' => count($sd_products),
		);
		return $result_array;
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
}
