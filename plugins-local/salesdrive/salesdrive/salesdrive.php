<?php
/*
 * Plugin Name: SalesDrive
 * Description: Integration
 */
add_action( 'fcapi-init', 'fcapi_init' );
/**
 * Initialization function of plugin
 */
function fcapi_init() {

    // Plugin version:
    if(!defined('FC_API_VERSION')){
        define('FC_API_VERSION', '4.5');
    }
    // Plugin DIR, with trailing slash:
    if(!defined( 'FC_API_PLUGIN_DIR')){
        define('FC_API_PLUGIN_DIR', plugin_dir_path(__FILE__));
    }
    // Plugin URL:
    if(!defined( 'FC_API_PLUGIN_BASE_URL')){
        define('FC_API_PLUGIN_BASE_URL', plugin_dir_url(__FILE__));
    }
    // Plugin ID:
    if(!defined( 'FC_API_PLUGIN_BASE_NAME')){
        define('FC_API_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
    }
    // Load localizations
    load_plugin_textdomain( 'fcapi', false, '/' . basename( FC_API_PLUGIN_DIR ) . '/languages' );
    // Activation and deactivation cations
    register_activation_hook(__FILE__, [ 'FC_API_Core', 'on_activation' ]);
    register_deactivation_hook(__FILE__, [ 'FC_API_Core', 'on_deactivation' ]);
    // Init
    require( plugin_dir_path(__FILE__) . 'inc/FC_API_Core.php');
    FC_API_Core::get_instance();
}

do_action('fcapi-init');

$true_page = 'salesdrive-settings.php'; // это часть URL страницы, рекомендую использовать строковое значение, т.к. в данном случае не будет зависимости от того, в какой файл вы всё это вставите

/*
 * Функция, добавляющая страницу в пункт меню Настройки
 */
function salesdrive_options(){
  global $true_page;
  add_options_page('SalesDrive', 'SalesDrive', 'manage_options', $true_page, 'true_option_page');
}

add_action('admin_menu', 'salesdrive_options');

/**
 * Возвратная функция (Callback)
 */
function true_option_page(){
  wp_enqueue_script( 'fc_api_admin_scripts', FC_API_PLUGIN_BASE_URL . 'inc/admin/assets/js/worker.min.js', [ 'jquery' ] );
  wp_localize_script( 'fc_api_admin_scripts', 'DataObject', [
    'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
    'importCall'       => 'fc_api_import_orders',
  ] );
  wp_enqueue_style( 'fc_api_admin_styles', FC_API_PLUGIN_BASE_URL . 'inc/admin/assets/css/admin-styles.min.css', 99 );

  global $true_page;
  ?><div class="wrap">
  <h2>Интеграция с SalesDrive</h2>
  <form method="post" enctype="multipart/form-data" action="options.php">
    <?php
    settings_fields('salesdrive_options'); // меняем под себя только здесь (название настроек)
    do_settings_sections($true_page);
    ?>
    <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
  </form>
  </div><?php
  include( 'inc/admin/assets/templates/import-button.php' );

}

/*
 * Регистрируем настройки
 * Мои настройки будут храниться в базе под названием salesdrive_options (это также видно в предыдущей функции)
 */
function true_option_settings() {
  global $true_page;
  // Присваиваем функцию валидации ( true_validate_settings() ). Вы найдете её ниже
  register_setting( 'salesdrive_options', 'salesdrive_options', 'true_validate_settings' ); // salesdrive_options

  // Добавляем секцию
  add_settings_section( 'true_section_1', 'Настройка интеграции', '', $true_page );
  add_settings_section( 'true_section_2', 'Передача заказов в SalesDrive', '', $true_page );
  add_settings_section( 'true_section_3', 'Импорт остатков с SalesDrive', '', $true_page );
  add_settings_section( 'true_section_4', 'Передача статусов заказов с SalesDrive на сайт', '', $true_page );

  // Ключ формы
  $true_field_params = array(
    'type'      => 'text', // тип
    'id'        => 'salesdrive_form_key',
    'label_for' => 'salesdrive_form_key' // позволяет сделать название настройки лейблом (если не понимаете, что это, можете не использовать), по идее должно быть одинаковым с параметром id
  );
  add_settings_field('salesdrive_form_key_field', 'Ключ формы', 'true_option_display_settings', $true_page, 'true_section_1', $true_field_params);

  // Ваш домен SalesDrive
  $true_field_params = array(
    'type'      => 'link',
    'id'        => 'salesdrive_domain',
    'label_for' => 'salesdrive_domain' // позволяет сделать название настройки лейблом (если не понимаете, что это, можете не использовать), по идее должно быть одинаковым с параметром id

  );
  add_settings_field( 'salesdrive_domain_field', 'Ваш домен SalesDrive', 'true_option_display_settings', $true_page, 'true_section_1', $true_field_params );

  // Привязывать товар
  $true_field_params = array(
    'type'      => 'radio',
    'id'        => 'salesdrive_link_product_type',
    'label_for' => 'salesdrive_link_product_type',
	'vals' => array(
		'id' => 'по id (рекомендуется): id товара на сайте передается в поле "id" в SalesDrive',
		'sku' => 'по артикулу: артикул товара на сайте передается в поле "id" в SalesDrive',
  	)
  );
  add_settings_field( 'salesdrive_link_product_type', 'Привязывать товар', 'true_option_display_settings', $true_page, 'true_section_1', $true_field_params );

  // Сопоставление способов доставки
  $true_field_params = array(
    'type'      => 'select-delivery-methods',
    'id'        => 'salesdrive_match_delivery_methods',
    'label_for' => 'salesdrive_match_delivery_methods',
  );
  add_settings_field('salesdrive_match_delivery_methods', 'Сопоставление способов доставки', 'true_option_display_settings', $true_page, 'true_section_2', $true_field_params );
  
  // Сопоставление способов оплаты
  $true_field_params = array(
    'type'      => 'select-payment-methods',
    'id'        => 'salesdrive_match_payment_methods',
    'label_for' => 'salesdrive_match_payment_methods',
  );
  add_settings_field('salesdrive_match_payment_methods', 'Сопоставление способов оплаты', 'true_option_display_settings', $true_page, 'true_section_2', $true_field_params );
  
  // Первый синхронизированный заказ с SalesDrive
  $true_field_params = array(
    'type'      => 'text',
    'id'        => 'salesdrive_first_sync_order_id',
    'label_for' => 'salesdrive_first_sync_order_id'
  );
  add_settings_field( 'salesdrive_first_sync_order_id', 'Первый синхронизированный заказ', 'true_option_display_settings', $true_page, 'true_section_2', $true_field_params );

  // Режим работы со складом
  $true_field_params = array(
    'type'      => 'radio',
    'id'        => 'salesdrive_manage_stock',
    'label_for' => 'salesdrive_manage_stock',
	'vals' => array(
		'0' => 'В наличии / Нет в наличии',
		'1' => 'Количество товара на складе',
  	)
  );
  add_settings_field( 'salesdrive_manage_stock_type', 'Режим работы со складом', 'true_option_display_settings', $true_page, 'true_section_3', $true_field_params );

  // Ссылка на экспорт товаров SalesDrive
  $true_field_params = array(
    'type'      => 'text',
    'id'        => 'salesdrive_yml_link',
    'label_for' => 'salesdrive_yml_link'
  );
  add_settings_field( 'salesdrive_yml_link_field', 'Ссылка на экспорт товаров SalesDrive <i class="salesdrive_tooltip">[?]<span class="salesdrive_tooltiptext">В кабинете SalesDrive передите: Настройки → Товары/Услуги → Экспорт YML → Добавить экспорт YML → Включить экспорт YML по ссылке → Скопируйте ссылку</span></i>', 'true_option_display_settings', $true_page, 'true_section_3', $true_field_params );

  // Скрипт для синхронизации остатков
  $true_field_params = array(
    'type'      => 'text-disabled',
    'id'        => 'salesdrive_sync_stock_script',
    'label_for' => 'salesdrive_sync_stock_script'
  );
  add_settings_field( 'salesdrive_sync_stock_script', 'Скрипт для синхронизации остатков', 'true_option_display_settings', $true_page, 'true_section_3', $true_field_params );

  // Команда cron для синхронизации остатков
  $true_field_params = array(
    'type'      => 'text-disabled',
    'id'        => 'salesdrive_sync_stock_script_cron',
    'label_for' => 'salesdrive_sync_stock_script_cron'
  );
  add_settings_field( 'salesdrive_sync_stock_script_cron', 'Команда cron для синхронизации остатков', 'true_option_display_settings', $true_page, 'true_section_3', $true_field_params );

  // Сопоставление статусов
  $true_field_params = array(
    'type'      => 'select-statuses',
    'id'        => 'salesdrive_match_statuses',
    'label_for' => 'salesdrive_match_statuses',
  );
  add_settings_field('salesdrive_match_statuses', 'Сопоставление статусов', 'true_option_display_settings', $true_page, 'true_section_4', $true_field_params );
  
	
}

add_action('admin_init', 'true_option_settings');

/*
 * Функция отображения полей ввода
 * Здесь задаётся HTML и PHP, выводящий поля
*/
function true_option_display_settings($args){
	extract($args);

	$option_name = 'salesdrive_options';
	
	$salesdrive_sync_script = FC_API_PLUGIN_BASE_URL.'inc/syncStock.php';

	$o = get_option($option_name);

	switch($type){
		case 'text-disabled':
			if($id == 'salesdrive_sync_stock_script'){ 
				$value = $salesdrive_sync_script;
			}
			elseif($id == 'salesdrive_sync_stock_script_cron'){
				$value = 'curl '.$salesdrive_sync_script;
			}
			else{
				$o[$id] = esc_attr(stripslashes($o[$id]));
				$value = $o[$id];
			}
			echo "<input class='regular-text' disabled type='text' style='color: #444;' value='$value' />";
			echo (!empty($desc)) ? "<br /><span class='description'>$desc</span>" : "";
			break;
		case 'text':
			$o[$id] = esc_attr(stripslashes($o[$id]));
			if($id == 'salesdrive_first_sync_order_id'){
				echo $o[$id]."<input class='regular-text' type='hidden' id='$id' name='" . $option_name . "[$id]' value='$o[$id]' />";
			}
			else{
				echo "<input class='regular-text' type='text' id='$id' name='" . $option_name . "[$id]' value='$o[$id]' />";
			}
			echo (!empty($desc)) ? "<br /><span class='description'>$desc</span>" : "";
			break;
		case 'link':
			$o[$id] = esc_attr(stripslashes($o[$id]));
			echo "<input class='regular-text' type='text' id='$id' name='" . $option_name . "[$id]' value='$o[$id]' />";
			echo (!empty($desc)) ? "<br /><span class='description'>$desc</span>" : "";
			break;
		case 'textarea':
			$o[$id] = esc_attr( stripslashes($o[$id]) );
			echo "<textarea class='code large-text' cols='50' rows='10' type='text' id='$id' name='" . $option_name . "[$id]'>$o[$id]</textarea>";
			echo (!empty($desc)) ? "<br /><span class='description'>$desc</span>" : "";
			break;
		case 'checkbox':
			$checked = ($o[$id] == 'on') ? " checked='checked'" :  '';
			echo "<label><input type='checkbox' id='$id' name='" . $option_name . "[$id]' $checked /> ";
			echo (!empty($desc)) ? $desc : "";
			echo "</label>";
			break;
		case 'radio':
			echo "<fieldset>";
			if(($id == 'salesdrive_link_product_type') && !$o[$id]){
				$o[$id] = 'id';
			}
			foreach($vals as $v=>$l){
				$checked = ($o[$id] == $v) ? "checked='checked'" : '';
				echo "<label><input type='radio' name='" . $option_name . "[$id]' value='$v' $checked />$l</label><br />";
			}
			echo "</fieldset>";
			break;
		case 'select':
			echo "<select id='$id' name='" . $option_name . "[$id]'>";
			foreach($vals as $v=>$l){
				$selected = ($o[$id] == $v) ? "selected='selected'" : '';
				echo "<option value='$v' $selected>$l</option>";
			}
			echo (!empty($desc)) ? $desc : "";
			echo "</select>";
			break;
		case 'select-delivery-methods':
			include_once(plugin_dir_path(__FILE__).'inc/salesdriveClass.php');
			$salesdrive = new salesdriveClass();
			$salesdrive_delivery_methods = $salesdrive->getDeliveryMethods();
			if(!isset($salesdrive_delivery_methods['success']) || $salesdrive_delivery_methods['success']!=1){
				return;
			}
			$salesdrive_delivery_methods = $salesdrive_delivery_methods['data'];

			$delivery_methods = [];
			$zones = WC_Shipping_Zones::get_zones();
			foreach($zones as $zone){
				foreach($zone['shipping_methods'] as $shipping_method){
					$delivery_methods[] = [
						'title' => $shipping_method->title,
						'instance_id' => $shipping_method->instance_id,
					];
				}
			}
			//print('<pre>delivery_methods:<br>');
			//print_r($delivery_methods);
			
			$salesdrive_match_delivery_methods = [];
			if(!empty($o[$id])){
				$salesdrive_match_delivery_methods = $o[$id];
			}
			
			echo '<table class="salesdrive_match_table" cellspacing=0>';
			echo '<tr><th>На сайте</th><th>В SalesDrive</th></tr>';
			foreach($delivery_methods as $delivery_method){
				echo '<tr>';
				echo '<td>'.$delivery_method['title'].'</td>';
				echo '<td><select name="'.$option_name.'['.$id.']['.$delivery_method['instance_id'].']">';
				echo '<option>---</option>';
				foreach($salesdrive_delivery_methods as $salesdrive_delivery_method){
					echo '<option value="'.htmlspecialchars($salesdrive_delivery_method['parameter']).'" ';
					if(isset($salesdrive_match_delivery_methods[$delivery_method['instance_id']]) && $salesdrive_match_delivery_methods[$delivery_method['instance_id']]==$salesdrive_delivery_method['parameter']){
						echo 'selected';
					}
					echo '>'.$salesdrive_delivery_method['name'].'</option>';
				}
				echo '</select></td>';
				echo '</tr>';
			}
			echo '</table>';
			break;
		case 'select-payment-methods':
			include_once(plugin_dir_path(__FILE__).'inc/salesdriveClass.php');
			$salesdrive = new salesdriveClass();
			$salesdrive_payment_methods = $salesdrive->getPaymentMethods();
			if(!isset($salesdrive_payment_methods['success']) || $salesdrive_payment_methods['success']!=1){
				return;
			}
			$salesdrive_payment_methods = $salesdrive_payment_methods['data'];
			
			$payment_methods = WC()->payment_gateways->get_available_payment_gateways();
			
			$salesdrive_match_payment_methods = [];
			if(!empty($o[$id])){
				$salesdrive_match_payment_methods = $o[$id];
			}
			
			echo '<table class="salesdrive_match_table" cellspacing=0>';
			echo '<tr><th>На сайте</th><th>В SalesDrive</th></tr>';
			foreach($payment_methods as $payment_method){
				echo '<tr>';
				echo '<td>'.$payment_method->title.'</td>';
				echo '<td><select name="'.$option_name.'['.$id.']['.$payment_method->id.']">';
				echo '<option>---</option>';
				foreach($salesdrive_payment_methods as $salesdrive_payment_method){
					echo '<option value="'.htmlspecialchars($salesdrive_payment_method['parameter']).'" ';
					if(isset($salesdrive_match_payment_methods[$payment_method->id]) && $salesdrive_match_payment_methods[$payment_method->id]==$salesdrive_payment_method['parameter']){
						echo 'selected';
					}
					echo '>'.$salesdrive_payment_method['name'].'</option>';
				}
				echo '</select></td>';
				echo '</tr>';
			}
			echo '</table>';
			break;
		case 'select-statuses':
			include_once(plugin_dir_path(__FILE__).'inc/salesdriveClass.php');
			$salesdrive = new salesdriveClass();
			$salesdrive_statuses = $salesdrive->getStatuses();
			if(!isset($salesdrive_statuses['success']) || $salesdrive_statuses['success']!=1){
				return;
			}
			$salesdrive_statuses = $salesdrive_statuses['data'];
			
			$statuses = wc_get_order_statuses();
			
			$salesdrive_match_statuses = [];
			if(!empty($o[$id])){
				$salesdrive_match_statuses = $o[$id];
			}
			
			$salesdrive_url_set_order_status = FC_API_PLUGIN_BASE_URL.'inc/syncStatus.php?formKey='.$o['salesdrive_form_key'];
			
			echo '<div class="salesdrive-alert salesdrive-alert-info" style="margin-bottom: 0;">В SalesDrive установите веб-хук:
						<ul class="salesdrive-ul">
               			<li>Настройки → Общие настройки и интеграции → Другие сервисы → webhook → Добавить</li>
               			</ul>
               			<strong>Данные веб-хука:</strong>
						<ul class="salesdrive-ul">
               			<li>Событие = Изменение статуса заявки</li>
               			<li>Добавьте условия:
               				<ul>
               					<li>Тип = Заявка онлайн</li>
               					<li>Сайт = Текущий сайт (требуется, если у вас несколько сайтов или маркетплейсов)</li>
               				</ul>
						</li>
               			<li>URL для передачи webhook:<br>
               			<input type="text" style="width: 100%" disabled="" value="'.$salesdrive_url_set_order_status.'">
               			</li>
               			<li>Информация о заявке = Только статусы</li>
               			</ul>
               		</div>';
					
			echo '<table class="salesdrive_match_table" cellspacing=0>';
			echo '<tr><th>Статус в SalesDrive</th><th>Статус на сайте</th></tr>';
			foreach($salesdrive_statuses as $salesdrive_status){
				echo '<tr>';
				echo '<td>'.$salesdrive_status['name'].'</td>';
				echo '<td><select name="'.$option_name.'['.$id.']['.$salesdrive_status['id'].']">';
				echo '<option>---</option>';
				foreach($statuses as $status_code=>$status_title){
					echo '<option value="'.$status_code.'" ';
					if(isset($salesdrive_match_statuses[$salesdrive_status['id']]) && $salesdrive_match_statuses[$salesdrive_status['id']]==$status_code){
						echo 'selected';
					}
					echo '>'.$status_title.'</option>';
				}
				echo '</select></td>';
				echo '</tr>';
			}
			echo '</table>';
			break;
	}
	
}

// Функция проверки правильности вводимых полей
function true_validate_settings($input) {
	foreach($input as $k => $v) {
		if(!is_array($v)){
			$valid_input[$k] = trim($v);
		}
		else{
			$valid_input[$k] = $v;
		}
		if($k == 'salesdrive_domain'){
			$salesdrive_domain = $v;
			$salesdrive_domain_parsed = parse_url($salesdrive_domain, PHP_URL_HOST);
			if($salesdrive_domain_parsed){
				$salesdrive_domain = $salesdrive_domain_parsed;
			}
			$valid_input[$k] = $salesdrive_domain;
		}
	}
	return $valid_input;
}

// Интеграция с WayForPay, LiqPay и другими дополнительными модулями
// Вызов (обычно перед $woocommerce->cart->empty_cart()): 
// do_action('send_order_to_salesdrive', $order_id);
add_action('send_order_to_salesdrive', 'sendOrderToSalesdrive');

// Интеграция с awooc
add_action('awooc_after_mail_send', 'awoocToSalesDrive',10,2);
function awoocToSalesDrive($product_id, $order_id){
	sendOrderToSalesdrive($order_id);
}

// Передавать заказы до оплаты (до очистки корзины)

add_filter( 'woocommerce_checkout_order_created', 'salesdrive_add_new_order_before_payment' );
function salesdrive_add_new_order_before_payment($order){
	sendOrderToSalesdrive($order->get_id());
}


// Интеграция с корзиной
add_action('woocommerce_thankyou', 'sendOrderToSalesdrive');
function sendOrderToSalesdrive($order_id){
	include_once(plugin_dir_path(__FILE__).'inc/salesdriveClass.php');
	$salesdrive = new salesdriveClass();
	$salesdrive->sendToSalesdrive($order_id);
}

// Сохранение в заказ utm-меток
add_action('woocommerce_new_order', 'saveUtmToOrderForSalesdrive');
function saveUtmToOrderForSalesdrive($order_id){
	include_once(plugin_dir_path(__FILE__).'inc/salesdriveClass.php');
	$salesdrive = new salesdriveClass();
	$salesdrive->saveUtmToOrder($order_id);
}

// Добавление javascript для отслеживания источников переходов
add_action('wp_head', 'salesdriveAddJavascript');

function salesdriveAddJavascript(){
	if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
		return;
	}
	$javascript = '<script type="text/javascript" src="'.plugin_dir_url(__FILE__).'js/salesdrive.js"></script>'."\n";
	echo $javascript;
}
