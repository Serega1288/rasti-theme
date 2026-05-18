<?php

class syncStockClass{
	
	private $salesdrive_yml_link;
	private $salesdrive_manage_stock;
	private $salesdrive_link_product_type;
	
	function __construct(){
		require_once("../../../../wp-load.php");
		$all_options = get_option('salesdrive_options');
		$this->salesdrive_yml_link = $all_options['salesdrive_yml_link'];
		$this->salesdrive_manage_stock = $all_options['salesdrive_manage_stock'];
		if(isset($all_options['salesdrive_link_product_type']) && $all_options['salesdrive_link_product_type']){
			$this->salesdrive_link_product_type = $all_options['salesdrive_link_product_type'];
		}
		else{
			$this->salesdrive_link_product_type = 'id';
		}
	}
	
	public function sync() {
		$time_start = time();
		$feed = $this->salesdrive_yml_link;
		$xml = file_get_contents($feed);
		$xml = new SimpleXMLElement($xml);
		$offers = $xml->shop->offers;
		$n = count($offers->offer);
		$product_updated_count = 0;
		echo "<h1>Импорт остатков из SalesDrive на сайт</h1>";
		echo "<table cellspacing=0 cellpadding=3 border=1>";
		echo "<tr>";
		echo "<th>ID в XML</th>";
		echo "<th>ID на сайте</th>";
		echo "<th>Остаток в YML</th>";
		if($this->salesdrive_manage_stock){
			echo "<th>Остаток до обновления</th>";
		}
		else{
			echo "<th>Наличие до обновления</th>";
		}
		echo "<th>Результат</th>";
		echo "</tr>";
		for ($i = 0; $i<$n; $i++) {
			$offer = $offers->offer[$i];
			$product_id = (string)$offer['id'];
			$product_quantity = $offer->quantity_in_stock;
			$product = '';
			if($product_quantity>0){
				$product_stock_status = 'instock'; //В наличии
			}
			else{
				$product_stock_status = 'outofstock'; // Нет в наличии
			}
			
			$wc_product_ids = [];
			$stock_before_update_messages = [];
			$stock_update_status_messages = [];
				
			if($this->salesdrive_link_product_type == 'id'){
				$wc_product_ids[0] = $product_id;
			}
			else{
				global $wpdb;
				$result = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT posts.ID
						FROM {$wpdb->posts} as posts
						INNER JOIN {$wpdb->wc_product_meta_lookup} AS lookup ON posts.ID = lookup.product_id
						WHERE
						posts.post_type IN ( 'product', 'product_variation' )
						AND posts.post_status != 'trash'
						AND lookup.sku = %s
						LIMIT 10
						",
						$product_id
					), 'ARRAY_A'
				);
				foreach($result as $row){
					$wc_product_ids[] = $row['ID'];
				}
			}
			echo "<tr>";
			echo "<td>$product_id</td>";
			echo "<td align=right>".implode(', ',$wc_product_ids)."</td>";
			echo "<td align=right>$product_quantity</td>";
			foreach($wc_product_ids as $wc_product_id){
				$product = wc_get_product($wc_product_id);
				if($product){
					// Если режим работы - с количеством товара
					if($this->salesdrive_manage_stock){
						if(!$product->get_manage_stock()){
							$stock_before_update_messages[] = 'включаем работу со складом';
							$product->set_manage_stock(1);
							$product->save();
						}
						else{
							$stock_before_update_messages[] = $product->get_stock_quantity();
						}
						if($product->get_stock_quantity()!=$product_quantity){
								wc_update_product_stock($product, $product_quantity);
								$stock_update_status_messages[] = 'обновлено';
								$product_updated_count++;
						}
						else{
								$stock_update_status_messages[] = 'не изменилось';
						}
					}
					// Если режим работы - с наличием
					else{
						if($product->get_manage_stock()){
							$stock_before_update_messages[] = 'отключаем работу со складом';
							$product->set_manage_stock(0);
							$product->save();
						}
						else{
							$stock_before_update_messages[] = $product->get_stock_status();
						}
						if($product->get_stock_status()!=$product_stock_status){
							wc_update_product_stock_status($wc_product_id, $product_stock_status);
							$stock_update_status_messages[] = 'обновлено';
							$product_updated_count++;
						}
						else{
							$stock_update_status_messages[] = 'не изменилось';
						}
					}
				}
				else{
					$stock_update_status_messages[] = 'товар/вариация не найдена';
				}
			}
			echo "<td align=right>".implode(', ',$stock_before_update_messages)."</td>";
			echo "<td align=right>".implode(', ',$stock_update_status_messages)."</td>";
			echo "</tr>";
		}
		echo "</table><div>&nbsp;</div>";
		$time_finish = time();
		$execution_time = $time_finish - $time_start;
		echo '<p>Всего товаров в экспорте SalesDrive: ' . $n . '. Остатки успешно обновлены у '.$product_updated_count.' товаров/вариаций на сайте!</p>';
		echo '<p>Время выполнения: '.$execution_time.' секунд.</p>';
		
    }

	
}