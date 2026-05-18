<?php

class syncStatusClass{
	
	private $salesdrive_form_key;
	private $salesdrive_match_statuses;
	
	function __construct(){
		require_once("../../../../wp-load.php");
		$all_options = get_option('salesdrive_options');
		$this->salesdrive_match_statuses = isset($all_options['salesdrive_match_statuses']) ? $all_options['salesdrive_match_statuses'] : [];
		$this->salesdrive_form_key = isset($all_options['salesdrive_form_key']) ? $all_options['salesdrive_form_key'] : '';
	}
	
	public function syncStatus() {
		
		if(empty($_GET['formKey'])){
			echo 'formKey не передано в веб-хуке.';
			die();
		};
		if(empty($this->salesdrive_form_key)){
			echo 'На сайте не указан ключ формы в настройках модуля SalesDrive.';
			die();
		}
		if($_GET['formKey'] != $this->salesdrive_form_key){
			echo 'Ключ формы в веб-хуке не совпадает с ключом формы на сайте в настройках модуля SalesDrive.';
			die();
		}
		
		if(empty($this->salesdrive_match_statuses)){
			echo 'Сопоставление статусов SalesDrive и Woocommerce не задано.';
			die();
		};
		$json = file_get_contents('php://input');
		$json = json_decode($json, true);
		if(json_last_error() != JSON_ERROR_NONE){
			echo 'Получен не валидный json.';
			die();
		}
		if(empty($json['data'])){
			echo 'json[data] не задано.';
			die();
		}
		$data = $json['data'];
		if(empty($data['externalId'])){
			echo 'externalId не задано.';
			die();
		}
		if(empty($data['statusId'])){
			echo 'statusId не задано.';
			die();
		}
		if(
			empty($this->salesdrive_match_statuses[$data['statusId']]) 
			|| 
			empty(trim($this->salesdrive_match_statuses[$data['statusId']],'-'))
		){
			echo 'Не найдено соответствие для статуса SalesDrive id='.$data['statusId'].'.';
			die();
		}
		$order_id = $data['externalId'];
		$order_status_id = $this->salesdrive_match_statuses[$data['statusId']];

		if(!get_post_status($order_id)){
			echo 'Заказ с id='.$order_id.' не найден.';
			die();
		}
		
		$order = new WC_Order($order_id);
		$order->update_status($order_status_id,'SalesDrive:');
		echo 'Статус на Woocommerce успешно изменен на order_status_id='.$order_status_id.'.';
    }

	
}