<?php
// Die if accessed directly
if(!defined('ABSPATH')){
	die;
}

class FC_API_Core {

    private static $_instance = null;
    private $option;

    function __construct(){
        $this->admin_setup();
        $this->option = $this->fc_api_options();
        if($this->is_cli_running()){
            require_once(FC_API_PLUGIN_DIR.'lib/cli/FC_API_CLI.php');
        }
    }

    public static function get_instance(){
        if(null === self::$_instance ){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    private function is_cli_running(){
        return defined( 'WP_CLI' ) && WP_CLI;
    }

    public static function fc_api_options(){
        global $wpdb;
        $options = (object) [
            'table' => $wpdb->prefix . 'flame_system_api',
            'keys'  => (object) [
                'api'        => 'project_api_key_field',
                'package'    => 'project_api_selected_package',
                'order_id'   => 'ticket_to_order_id',
                'package_id' => 'ticket_to_package_id'
            ]
        ];
        return $options;
    }

    private function admin_setup() {
        if(!is_admin()){
            return;
        }
        require_once(FC_API_PLUGIN_DIR.'inc/admin/FC_API_Admin.php');
        FC_API_Admin::get_instance();
    }

    // on plugin activation hook
    static function on_activation(){
        // Checked install Woocommerce Plugin
        if(!is_plugin_active('woocommerce/woocommerce.php')){
            // Deactivate the plugin
            deactivate_plugins(__FILE__);
            $error_message = __('This plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>', 'fcapi');
            die($error_message);
        }
    }

    // on plugin deactivation hook
    static function on_deactivation() {
        //TBD
    }

}