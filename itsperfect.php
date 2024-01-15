<?php
session_start();
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;


/**
 * Plugin Name: Connect-itsp Integration
 * Plugin URI: http://scrumwheel.com/
 * Description: Connect-itsp Integration with woocommerce
 * Version: 2.0
 * Author: Scrumwheel Technologies
 * Author URI: http://scrumwheel.com/
 * License: GPLv2 or later
 * Text-Domain: Connect-itsp Integration
 */


defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );
define('KP_BASE_PATH', plugin_dir_path(__FILE__));
define('KP_BASE_URL', plugin_dir_url(__FILE__));

include_once(ABSPATH . 'wp-config.php');
include_once(ABSPATH . 'wp-includes/class-wpdb.php');
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once('wp-guard/src/WpGuard.php');

global $guard;

$guard = new Anystack\WpGuard\V001\WpGuard(
    __FILE__,
    [
        'api_key' => 'AAKMwpKVGrWAArktuIXOgtKSem5p3cYT',
        'product_id' => '9b12f162-b8b5-465a-a579-6c091ee0e764',
        'product_name' => 'itsperfect',
        'updater' => [
            'enabled' => true, // Enable auto-updater
        ]
    ]
);
global $wpdb;
$queryurl = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_url'";
$api_auth = $wpdb->get_results($queryurl);

if(empty($api_auth)){
    $apiStart =  "";
}else{
    $apiStart =  $api_auth[0]->setting_value; //for consumerkey

}

$querytoken = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_token'";
$api_token = $wpdb->get_results($querytoken);

if(empty($api_token)){
    $token = "";
}else{
    $token = $api_token[0]->setting_value; //for api token key
}

$GLOBALS['apiStart'] = $apiStart;
$GLOBALS['token'] = $token;

/**
 * Below line allows duplicated SKU in woocommerce which is needed to create products by colors
 */
add_filter( 'wc_product_has_unique_sku', '__return_false' ); 


/**
 * Attaches necessary js & css files
 */
add_action( 'admin_enqueue_scripts', 'ip_load_custom_scripts_styles' );
function ip_load_custom_scripts_styles(){
    if(isset($_GET['page']))
         $page = $_GET['page'];
     else
         $page = 'ip_dashboard';

    if( $page == 'ip_create_product' || $page == 'ip_dashboard' || $page == 'ip_update_product' || $page == 'ip_manage_orders' || $page == 'ip_settings' || $page == "ecologi-settings" || $page == 'ecologi-dashboard' || $page == 'wc-orders') {
        wp_enqueue_style( 'bootstrap', plugins_url('assets/css/bootstrap.min.css', __FILE__) );
    
        wp_enqueue_style( 'dataTables.bootstrap', plugins_url('assets/css/dataTables.bootstrap.min.css', __FILE__) );
        wp_enqueue_style( 'ip-custom-css', plugins_url('assets/css/ip_custom.css', __FILE__) );

        wp_enqueue_script( 'bootstrap', plugins_url( 'assets/js/bootstrap.min.js', __FILE__ ) );
        wp_enqueue_script( 'dataTables', plugins_url( 'assets/js/jquery.dataTables.min.js', __FILE__ ) );
        wp_enqueue_script( 'dataTable-bootstrap', plugins_url( 'assets/js/dataTables.bootstrap.min.js', __FILE__ ) );
        wp_enqueue_script( 'ip-custom-js', plugins_url( 'assets/js/ip_custom.js', __FILE__ ) , array() , time() );

        
        // make the ajaxurl var available to the above script
        wp_localize_script( 'ip-custom-js', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    }
    
}

// add_action( 'wp_enqueue_scripts', 'childthemename_scripts' );
// function childthemename_scripts() {
//     wp_enqueue_script( 'ip-custom-js', plugins_url( 'assets/js/ip_custom.js', __FILE__ ) , array() , time() );
// }



function plugin_rewrite_rule() {
    add_rewrite_rule('webhook/?$', 'index.php?custom_endpoint=webhook', 'top');
}
add_action('init', 'plugin_rewrite_rule');


function custom_query_vars($vars) {
    $vars[] = 'custom_endpoint';
    return $vars;
}
add_filter('query_vars', 'custom_query_vars');

function custom_plugin_endpoint_handler() {
    if (get_query_var('custom_endpoint')) {
        include(plugin_dir_path(__FILE__) . 'webhook.php');
        exit();
    }
}
add_action('template_redirect', 'custom_plugin_endpoint_handler');


/**
 * Creates menu for the plugin in the admin section.
 */
add_action('admin_menu', 'kp_register_options_page');
function kp_register_options_page() {
    add_menu_page('Connect-itsp Integration', 'Connect-itsp Integration', 'manage_options', 'ip_dashboard', 'kp_dashboard',  plugins_url('itsperfect/logo.png'),);
   
    add_submenu_page( 'ip_dashboard', 'Create Products', 'Create Products', 'manage_options', 'ip_create_product', 'kp_create_product_index');
    add_submenu_page( 'ip_dashboard', 'Update Products', 'Update Products', 'manage_options', 'ip_update_product', 'kp_update_product_index');
    add_submenu_page( 'ip_dashboard', 'Manage Orders', 'Manage Orders', 'manage_options', 'ip_manage_orders', 'kp_manage_orders_index');
    add_submenu_page( 'ip_dashboard', 'Connect-itsp Settings', 'Connect-itsp Settings', 'manage_options', 'ip_settings', 'kp_settings_index');
}



/*
 * Index Page
 * */
function kp_dashboard(){

    
    if(kp_settings_exists()){
        include KP_BASE_PATH."views/ip_dashboard.php";
    }
    else{
        kp_settings_index();
    }
}

/**
 * Create product view page
 */
//if ($guard->validCallback(function() { }))
//{ 
    function kp_create_product_index(){

     if(kp_settings_exists()){
        $count = 0;
        $data = array();
        $_SESSION['data'] = array();
        $data = itsperfect_get_items('',-1,1,$GLOBALS['apiStart'],$GLOBALS['token']); //set second param to -1 to get all products.
        $_SESSION['data'] = $data;
        $count = count($data);

       

        include KP_BASE_PATH."views/ip_create_product.php";
      
    }
    else{
        kp_settings_index();
    }
    }
/*}
else
{
    function kp_create_product_index(){

             echo "<div>You do not have permission to access this page.Please get Licence version of itsperfect</div>";
    }
}*/




/**
 * Import Categories
 */
$guard->validCallback(function() {

add_action('wp_ajax_import_categories','import_categories');
function import_categories(){
    $erp_categories = GetCategories();

    foreach($erp_categories as $key=>$l1_cat){
        $l1_cat_name = $l1_cat->categorie->en;
        
        $l1_cat_desc = '';
        if(isset($l1_cat->description->en)){
            $l1_cat_desc = $l1_cat->description->en;
        }
        
        $l1_cat_id = 0;

        $cat_term = wp_insert_term( "$l1_cat_name", 'product_cat', array(
            'description' => "$l1_cat_desc", // optional
            'parent' => 0,      // optional
        ) );

        if(isset($cat_term->errors)){
            if(isset($cat_term->error_data['term_exists'])){
               $l1_cat_id =  $cat_term->error_data['term_exists'];
            }
            else{
                echo json_encode($cat_term->errors);
                wp_die();
            }
        }
        else{
            $l1_cat_id = $cat_term['term_id'];
        }

        if(isset($l1_cat->categories) && !empty($l1_cat->categories) && $l1_cat_id != 0){
            foreach($l1_cat->categories as $key=>$l2_cat){
                $l2_cat_name = $l2_cat->categorie->en;
                
                $l2_cat_desc = '';
                if(isset($l2_cat->description->en)){
                    $l2_cat_desc = $l2_cat->description->en;
                }
                
                if($l2_cat_name != ''){
                    $l2_cat_term = wp_insert_term( "$l2_cat_name", 'product_cat', array(
                        'description' => "$l2_cat_desc", // optional
                        'parent' => $l1_cat_id,
                    ) );
            
                    if(isset($l2_cat_term->errors)){
                        if(isset($l2_cat_term->error_data['term_exists'])){
                        $l2_cat_id =  $l2_cat_term->error_data['term_exists'];
                        }
                        else{
                            echo json_encode($l2_cat_term->errors);
                            wp_die();
                        }
                    }
                    else{
                        $l2_cat_id = $l2_cat_term['term_id'];
                    }

                    if(isset($l2_cat->categories) && !empty($l2_cat->categories)){
                        foreach($l2_cat->categories as $key=>$l3_cat){
                            $l3_cat_name = $l3_cat->categorie->en;
                            
                            $l3_cat_desc = '';
                            if(isset($l3_cat->description->en)){
                                $l3_cat_desc = $l3_cat->description->en;
                            }
                            

                            if($l3_cat_name != ''){
                                $l3_cat_term = wp_insert_term( "$l3_cat_name", 'product_cat', array(
                                    'description' => "$l3_cat_desc", // optional
                                    'parent' => $l2_cat_id,
                                ) );
                        
                                if(isset($l3_cat_term->errors)){
                                    if(isset($l3_cat_term->error_data['term_exists'])){
                                    $l3_cat_id =  $l3_cat_term->error_data['term_exists'];
                                    }
                                    else{
                                        echo json_encode($l3_cat_term->errors);
                                        wp_die();
                                    }
                                }
                                else{
                                    $l3_cat_id = $l3_cat_term['term_id'];
                                }
        
                                if(isset($l3_cat->categories) && !empty($l3_cat->categories)){
                                    foreach($l3_cat->categories as $key=>$l4_cat){
                                        $l4_cat_name = $l4_cat->categorie->en;
                                        
                                        $l4_cat_desc = '';
                                        if(isset($l4_cat->description->en)){
                                            $l4_cat_desc = $l4_cat->description->en;
                                        }
                                        
                                        if($l4_cat_name != ''){
                                            $l4_cat_term = wp_insert_term( "$l4_cat_name", 'product_cat', array(
                                                'description' => "$l4_cat_desc", // optional
                                                'parent' => $l3_cat_id,
                                            ) );
                                    
                                            if(isset($l4_cat_term->errors)){
                                                if(isset($l4_cat_term->error_data['term_exists'])){
                                                $l4_cat_term =  $l4_cat_term->error_data['term_exists'];
                                                }
                                                else{
                                                    echo json_encode($l4_cat_term->errors);
                                                    wp_die();
                                                }
                                            }
                                            else{
                                                $l4_cat_term = $l4_cat_term['term_id'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    echo "Categories are synced !";
    wp_die();
}
});

/**
 * Function to get categories from itsperfect.
 */
function GetCategories(){
    
    $url = $GLOBALS['apiStart']."/api/v2/webshops/2/categories/?token=".$GLOBALS['token'];
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 9000000000,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
            "Postman-Token: 590506f4-6d68-4d40-a0bf-143bbe66efef",
            "X-Pagination-Per-Page: 20",
            "cache-control: no-cache"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        // echo "cURL Error #:" . $err;
    } else {
        $data = json_decode($response);
        $data = $data->categories;
        return $data;
    }
}



/**
 * Update product view page
 */

function kp_update_product_index(){
    if(kp_settings_exists()){
        
        $products = wc_get_products( 
            array( 
                'status' => 'publish',
                'limit' => -1 
            )
        );

        include KP_BASE_PATH."views/ip_update_product.php";
    }
    else{
        kp_settings_index();
    }
    
}



/**
 * Manage orders index page
 */

 
 function kp_manage_orders_index(){
    if(kp_settings_exists()){

        $args = array(
            'limit' => 9999,
            // 'return' => 'ids',
            // 'date_completed' => '2018-10-01...2018-10-10',
             'status' => 'processing,on-hold,completed,pending-payment,cancelled'
        );
        
        $query = new WC_Order_Query( $args );
        
        $orders = $query->get_orders();
        
        include KP_BASE_PATH."views/ip_manage_orders.php";
    }
    else{
        kp_settings_index();
    }
}




/**
 * Settings page for itsperfect
 */
function kp_settings_index($message = ''){
    if(kp_is_woo_active()){
    
        global $wpdb;
        
        $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_url'";
        $wc_auth = $wpdb->get_results($query);

        if(empty($wc_auth)){
            $ip_base_url = '';
        }
        else{
            $ip_base_url = $wc_auth[0]->setting_value;
        }
        
        $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_token'";
        $wc_auth = $wpdb->get_results($query);
        if(empty($wc_auth)){
            $ip_api_token = '';
        }
        else{
            $ip_api_token = $wc_auth[0]->setting_value;
        }

        $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'product_status'";
        $wc_auth = $wpdb->get_results($query);
        if(empty($wc_auth)){
            $product_status = '';
        }
        else{
            $product_status = $wc_auth[0]->setting_value;
        }

        $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'product_title_format'";
        $wc_auth = $wpdb->get_results($query);
        if(empty($wc_auth)){
            $product_title_format = array();
        }
        else{
            $product_title_format = explode(",",$wc_auth[0]->setting_value);
          
            
        }

        $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'webhooks'";
        $wc_auth = $wpdb->get_results($query);
        if(empty($wc_auth)){
            $webhooks = array();
        }
        else{
            $webhooks = explode(",",$wc_auth[0]->setting_value);
        }

        include KP_BASE_PATH."views/ip_settings_index.php";

    }
    else{
        echo "
        <div style='margin-top : 100px;text-align : center;'>
            <h5>It looks like you have not installed/activated Woocommerce. <br>
            Please install & activate <a target='_blank' href='https://woocommerce.com/'>Woocommerce</a> and then refresh this page.</h5>
        </div>
        ";
    }
}


/**
 * This gets triggered when plugin is activated.
 */
register_activation_hook(__FILE__,'kp_activation');
function kp_activation()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE `{$wpdb->base_prefix}erp_sync_logs` (
      id int(11) NOT NULL AUTO_INCREMENT,
      module varchar(255) NOT NULL,
      time_taken varchar(255) NOT NULL,
      url_requested varchar(255) NOT NULL,
      error_code varchar(255) NOT NULL,
      created_at datetime NOT NULL,
      modified_at datetime NOT NULL,
      PRIMARY KEY  (id)
      ) $charset_collate;";

    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}erp_settings`(
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `module` varchar(500) NOT NULL,
        `setting_module` varchar(500) NOT NULL,
        `setting_value` varchar(500) NOT NULL,
        `created_at` datetime NOT NULL,
        `modified_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
        ) $charset_collate;";
  
      dbDelta($sql);

       /** Create product's custom attributes */
    kp_create_attribute_taxonomies();
}


/**
 * This gets triggered when plugin is deactivated.
 */
register_deactivation_hook(__FILE__,'kp_deactivation');
function kp_deactivation(){
    global $wpdb;
    $sql = "DROP TABLE {$wpdb->base_prefix}erp_sync_logs";
    $wpdb->query($sql);

    $sql = "DROP TABLE {$wpdb->base_prefix}erp_settings";
    $wpdb->query($sql);
   
    


}

/**
 * Adds a settings link beside the plugin activate button.
 */
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'kp_plugin_add_settings_link' );
function kp_plugin_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=ip_settings">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
    return $links;
}




/**
 * saves configuration of the itsperfect's base URL & token.
 */
add_action("wp_ajax_kp_ip_save_config","kp_ip_save_config");
function kp_ip_save_config(){
    
    global $wpdb;
    $ip_base_url = $ip_api_token = '';
    if(isset($_POST['ip_base_url'])){
        $urlstart = $_POST['ip_base_url'];
        $urlstart = rtrim($urlstart, '/');
    }
    if(isset($_POST['ip_base_url'])){
        $token = $_POST['ip_api_token'];
    }

    if(isset($_POST['product_status'])){
        $product_status = $_POST['product_status'];
    }

    if(isset($_POST['product_title_format'])){
        $product_title_format = implode(",",$_POST['product_title_format']);
    }

    if(isset($_POST['webhooks'])){
        $webhooks = implode(",", $_POST['webhooks']);
    }

    if($urlstart && $token){
        $data = itsperfect_get_items('',20,1,$urlstart,$token);  // erpid, limit, page
        
        if(empty($data)){
            $message = "Itsperfect API is not working at the moment! <br> Please check provided credentials have proper rights or they inputed correctly!";
        }
        else{
            //for the apiUrl
            $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_url'";
            $wc_auth = $wpdb->get_results($query);
            $date = date("Y-m-d H:i:s");
            if(empty($wc_auth)){
                $query = "INSERT INTO {$wpdb->base_prefix}erp_settings (`module`,`setting_module`,`setting_value`,`created_at`,`modified_at`) values ('api_url','api_url','$urlstart','$date','$date')";
                $wpdb->query($query);
            }else{
                $id = $wc_auth[0]->id;
                $query = "UPDATE {$wpdb->base_prefix}erp_settings set setting_value = '$urlstart' , modified_at = '$date' where id = ".$id;
                $wpdb->query($query);
            }

            //for the api Token
            $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_token'";
            $wc_auth = $wpdb->get_results($query);
            $date = date("Y-m-d H:i:s");
            if(empty($wc_auth)){
                $query = "INSERT INTO {$wpdb->base_prefix}erp_settings (`module`,`setting_module`,`setting_value`,`created_at`,`modified_at`) values ('api_token','api_token','$token','$date','$date')";
                $wpdb->query($query);
            }else{
                $id = $wc_auth[0]->id;
                $query = "UPDATE {$wpdb->base_prefix}erp_settings set setting_value = '$token' , modified_at = '$date' where id = ".$id;
                $wpdb->query($query);
            }

              //for product status
              $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'product_status'";
              $wc_auth = $wpdb->get_results($query);
              $date = date("Y-m-d H:i:s");
              if(empty($wc_auth)){
                  $query = "INSERT INTO {$wpdb->base_prefix}erp_settings (`module`,`setting_module`,`setting_value`,`created_at`,`modified_at`) values ('product_status','product_status','$product_status','$date','$date')";
                  $wpdb->query($query);
              }else{
                  $id = $wc_auth[0]->id;
                  $query = "UPDATE {$wpdb->base_prefix}erp_settings set setting_value = '$product_status' , modified_at = '$date' where id = ".$id;
                  $wpdb->query($query);
              }
  
                //for product title format
                $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'product_title_format'";
                $wc_auth = $wpdb->get_results($query);
                $date = date("Y-m-d H:i:s");
                if(empty($wc_auth)){
                    $query = "INSERT INTO {$wpdb->base_prefix}erp_settings (`module`,`setting_module`,`setting_value`,`created_at`,`modified_at`) values ('product_title_format','product_title_format','$product_title_format','$date','$date')";
                    $wpdb->query($query);
                }else{
                    $id = $wc_auth[0]->id;
                    $query = "UPDATE {$wpdb->base_prefix}erp_settings set setting_value = '$product_title_format' , modified_at = '$date' where id = ".$id;
                    $wpdb->query($query);
                }
  
                //webhooks
                $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'webhooks'";
                $wc_auth = $wpdb->get_results($query);
                $date = date("Y-m-d H:i:s");
                if(empty($wc_auth)){
                    $query = "INSERT INTO {$wpdb->base_prefix}erp_settings (`module`,`setting_module`,`setting_value`,`created_at`,`modified_at`) values ('webhooks','webhooks','$webhooks','$date','$date')";
                    $wpdb->query($query);
                }else{
                    $id = $wc_auth[0]->id;
                    $query = "UPDATE {$wpdb->base_prefix}erp_settings set setting_value = '$webhooks' , modified_at = '$date' where id = ".$id;
                    $wpdb->query($query);
                }

            

            $GLOBALS['apiStart'] = $urlstart;
            $GLOBALS['token'] = $token;

            $message = "Configuration saved successfully!";
        }
    }
    else{
        $message = "Something is wrong with the inputed value !";
    }
    echo $message;
}




/**
 * Function to call itsperfect APIs
 */
function itsperfect_get_items($erpid = '',$limit=20,$page=1,$urlstart = '',$token = ''){
    
    $erpitem = '';

    if($erpid){
        $url = $urlstart."/api/v2/items/$erpid/?token=".$token;
    }
    else{
        $url = $urlstart."/api/v2/items/?token=".$token."&limit=".$limit."&page=".$page;
    }
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 9000000000,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
            "Postman-Token: 590506f4-6d68-4d40-a0bf-143bbe66efef",
            "X-Pagination-Per-Page: 20",
            "cache-control: no-cache"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        // echo "cURL Error #:" . $err;
    } else {
        $data = json_decode($response);
        if(isset($data->items)){
            $erpitem = $data->items;
        }
    }
    return $erpitem;
}



/**
 * Function to check if configuration are saved or not  
 */
function kp_settings_exists(){
    global $wpdb;

    $queryurl = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_url'";
    $api_auth = $wpdb->get_results($queryurl);
    
    if(empty($api_auth)){
        $apiStart =  "";
    }else{
        $apiStart =  $api_auth[0]->setting_value; //for API base URL
    }
    
    $querytoken = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_token'";
    $api_token = $wpdb->get_results($querytoken);
    
    if(empty($api_token)){
        $token = "";
    }else{
        $token = $api_token[0]->setting_value; //for api token key
    }
    
    $GLOBALS['apiStart'] = $apiStart;
    $GLOBALS['token'] = $token;

    if($GLOBALS['apiStart'] != '' && $GLOBALS['token'] != ''){
        return 1;
    }
    else{
        return 0;
    }
}


/**
 * Function to check if woocommerce is activated or not
 */
function kp_is_woo_active(){
    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        return 1;
    } else {
        return 0;
    }
}


/*
 * Create ajax Multiple products 
 */
$guard->validCallback(function() {

add_action('wp_ajax_create_multiple_products', 'create_multiple_products');
function create_multiple_products(){
    try {
        $summary = '';
        if(isset($_POST['productId'])){
            $wordpressIds = $_POST['productId'];
            
            if($wordpressIds == "import_all"){
                $idArray = array();    
            }
            else{
                $idArray = json_decode(stripslashes($wordpressIds));    
            }

            $session_check = 0;
            if(isset($_SESSION['data'])){
                if(!empty($_SESSION['data'])){
                    $session_check = 1;
                }
            }


            if(empty($idArray) && $session_check){
                // we already have the response from page load.
                $final_res = array();
                $newidarray = array();
                foreach($_SESSION['data'] as $key=>$value){
                    $final_res[$value->id] = $value;
                    $newidarray[] = $value->id;
                }
            }
            else{
                $res = get_itsperfect_items($idArray);

                if($res){
                    $res = json_decode($res);
                    $final_res = array();
                    $newidarray = array();
                    foreach($res->items as $key=>$value){
                        $final_res[$value->id] = $value;
                        $newidarray[] = $value->id;
                    }
                }
                else{
                    $summary = "Something went wrong with API !";
                    echo $summary;
                    die;
                }
            }
            
            

            if(empty($idArray)){
                $idArray = $newidarray;
            }
            
            foreach ($idArray as $erp_item_id){
                $summary .= create_single_product($erp_item_id,$final_res);
            }

        } else {
            $summary .= "No product Id were received";
        }
        
        echo $summary;
        wp_die();

    } catch (Exception $e){
        echo $e->getMessage();
        echo $e->getTraceAsString();
    }
}

});
/**
 * Create single product
 */
$guard->validCallback(function() {

add_action('wp_ajax_ca_create_single_product','create_single_product');
function create_single_product($erp_item_id = '',$resarray = array()){
    $summary = '';
    if(isset($_POST['post_var'])){
        $erp_item_id = $_POST['post_var'];
    }

    if(empty($resarray)){
        $res = get_itsperfect_items(array($erp_item_id));

        if($res){
            $res = json_decode($res);
            $final_res = array();
            foreach($res->items as $key=>$value){
                $final_res[$value->id] = $value;
            }
        }
        else{
            $summary = "Something went wrong with API !";
            echo $summary;
            die;
        }
    }
    else{
        $final_res = $resarray;
    }

    if($erp_item_id){
        $summary .= create_parent($erp_item_id,$final_res);
    }

    echo $summary;
}
});
/**
 * Call itsperfect API for creating/fetching items
 */
function get_itsperfect_items($idarray = array()){
    $response = 0;
    
    $ids = '';
        
    if(count($idarray) >= 1){
        $ids = implode(",",$idarray);
        $url = $GLOBALS['apiStart']."/api/v2/items/&token=".$GLOBALS['token']."&filter=id+in+".$ids;
    }
    else{
        $url = $GLOBALS['apiStart']."/api/v2/items/&token=".$GLOBALS['token'];
    }
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 900000000000,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
            "Postman-Token: b90e102a-7122-491e-881d-56ad51853bbb",
            "cache-control: no-cache"
        ),
    ));

    $response = curl_exec($curl);

    $err = curl_error($curl);

    curl_close($curl);

    return $response;
}



/**
 * Function which creates main parent products from itsperfect.
 */
$guard->validCallback(function() {

function create_parent($erp_item_id='',$resarray=array()){
    ini_set('max_execution_time', 10101010101);
    ini_set('default_socket_timeout', 10101010101);
    set_time_limit(0);

    /** Create product's custom attributes */
    kp_create_attribute_taxonomies();
    
    global $wpdb;
    
    $data = $resarray[$erp_item_id];

    $width = $data->dimensions->width;
    $height = $data->dimensions->height;
    $depth = $data->dimensions->depth;
    $weight = $data->dimensions->weight;

    // $sql = "SELECT setting_value FROM {$wpdb->base_prefix}erp_settings where module = 'item' and setting_module='createitemby'";
    // $result = $wpdb->get_results($sql);
    $result = array('1');

    if(empty($result)){
        $createby = "byitems";
    }
    else{
        //$createby = $result[0]->setting_value;
        $createby = 'bycolors';
    }

    // The query
    $products = new WP_Query( array(
        'post_type'      => array('product'),
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array( array(
            'key' => 'meta_erp_product_id',
            'value' => array($erp_item_id),
            'compare' => 'IN',
        ) )
    ) );

    $product_ids = array();
    
    // The Loop
    if ( $products->have_posts() ){
        while ( $products->have_posts() ){
            $product = $products->the_post();
            $meta_erp = get_post_meta($products->post->ID,'meta_erp_product_id');
            if(isset($meta_erp[0])){
                $product_ids[$products->post->ID] = $meta_erp[0];
            }
        }
        wp_reset_postdata();
    }

    /** if we want to create by items */
    if($createby == "byitems"){

    }
    if($createby == "bycolors"){
        if($data->colors){
            foreach($data->colors as $key=>$color){
                $check = 0;
                if(!empty($product_ids)){
                    foreach($product_ids as $wp_id=>$erp_item_id){
                        $product = wc_get_product($wp_id);
                        $existing_color = $product->get_attribute('color');
                        
                        $existing_color = trim_color($existing_color);

                        $check_color = $color->color;
                        $check_color = trim_color($check_color);

                        if($check_color == $existing_color){
                            $check = 1;
                        }
                    }
                }

                if($check){
                    continue;
                }

                $product_status = 'draft';
                $product_name = '';
                $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'product_status'";
                $wc_auth = $wpdb->get_results($query);
                if(empty($wc_auth)){
                    $product_status = '';
                }
                else{
                    $product_status = $wc_auth[0]->setting_value;
                }

                $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'product_title_format'";
                $wc_auth = $wpdb->get_results($query);
                if(empty($wc_auth)){
                    $product_title_format = '';
                }
                else{
                    $product_title_format = explode(",",$wc_auth[0]->setting_value);
                    if(in_array('brand_name',$product_title_format))
                    {
                        $product_name .=   "Black and Gold - ";
                    }
                    if(in_array('item_group',$product_title_format))
                    {
                        $item_category_group = $data->itemGroup->itemGroup;
                        $product_name .=   $item_category_group." - ";
                    }
                 
                  }
                  $product_name .=  $data->item." - ".$color->color;

              //  $item_category_group = $data->itemGroup->itemGroup;

                // Creating a variable product
                $product = new WC_Product_Variable();

                // Name and image would be enough
              /*  $product->set_name( "Black and Gold - ".$item_category_group." - ".$data->item." - ".$color->color );
                $product->set_sku($data->itemNumber);
                $product->set_status( 'publish' ); 
*/
                $product->set_name( $product_name );
                $custom_sku = $data->itemNumber;
               
                //$custom_sku = $data->itemNumber.$color->colorNumber;
                $product->set_sku($custom_sku);
                
                $product->set_status( $product_status ); 

                $product->set_catalog_visibility( 'visible' );
                
                $desc = '';
                if(isset($data->description->en)){
                    $desc = $data->description->en;
                }

                $product->set_description($desc);
                $product->set_short_description($desc);

                $product->set_width($width);
                $product->set_height($height);
                $product->set_length($depth);
                $product->set_weight($weight);

                $wpcategories = array();
                $wp_parent_categories = array();
                
                $erp_cat_temps = array();
                $parent_cat_temps = array();
                foreach($data->webshopCategories as $key=>$category){
                    $erp_cat_temps[$category->id] = $category->category->en;
                    $parent_cat_temps[$category->id] = $category->categoryId;
                }
                foreach($erp_cat_temps as $key=>$cat_name){
                    $sql = "SELECT * FROM {$wpdb->base_prefix}terms where name='$cat_name' limit 1";
                    $result = $wpdb->get_results($sql);
                    if(!empty($result)){
                        $term_id = $result[0]->term_id;

                        if($parent_cat_temps[$key] == 0){
                            $tax_sql = "SELECT * FROM {$wpdb->base_prefix}term_taxonomy where term_id='$term_id' and taxonomy='product_cat' and parent = '0' ";
                            $tax_result = $wpdb->get_results($tax_sql);
                            if(!empty($tax_result)){
                                $wpcategories[] = $tax_result[0]->term_id;
                                $wp_parent_categories[] = $tax_result[0]->term_id;
                            }
                        }
                        else{
                            $sql = "SELECT * FROM {$wpdb->base_prefix}terms where name='$cat_name'";
                            $result = $wpdb->get_results($sql);
                            foreach($result as $key=>$term_obj){
                                $tax_sql = "SELECT * FROM {$wpdb->base_prefix}term_taxonomy where term_id='$term_obj->term_id' and taxonomy='product_cat'";
                                $tax_result = $wpdb->get_results($tax_sql);

                                if(!empty($tax_result)){
                                    foreach($tax_result as $key=>$term_result_obj){
                                        if(in_array($term_result_obj->parent,$wp_parent_categories)){
                                            $wpcategories[] = $term_result_obj->term_id;
                                            $wp_parent_categories[] = $term_result_obj->term_id;
                                        }        
                                    }
                                }
                            }
                        }
                    }
                }
               
                $product->set_category_ids( $wpcategories );


                    //tags addition
                    $tags = array();
                    //static itsperfect tag
                    $tag_term = wp_insert_term( "From Itsperfect", 'product_tag', array(
                        'description' => "Product that come from itsperfect", // optional
                        'parent' => 0,      // optional
                    ) );
    
                    if(isset($tag_term->error_data['term_exists'])){
                        $tag_id = $tag_term->error_data['term_exists'];
                    }
    
                    if(is_array($tag_term)){
                        if(isset($tag_term['term_id'])){
                            $tag_id = $tag_term['term_id'];
                        }    
                    }
    
                    $tags[] = $tag_id;
                    
    
                    if(!empty($data->season->season)){
                        $season = $data->season->season;
                        $tag_term = wp_insert_term( "$season", 'product_tag', array(
                            'description' => "Season imported from itsperfect", // optional
                            'parent' => 0,      // optional
                        ) );
    
                        if(isset($tag_term->error_data['term_exists'])){
                            $tag_id = $tag_term->error_data['term_exists'];
                        }
    
                        if(is_array($tag_term)){
                            if(isset($tag_term['term_id'])){
                                $tag_id = $tag_term['term_id'];
                            }    
                        }
                        $tags[] = $tag_id;
                    }
                    $product->set_tag_ids( $tags );
    

                $related_erp_ids = array();
                
                foreach($data->relatedItems as $key=>$relateditem){
                    if(!empty($relateditem->relatedItemNumber)){
                        $related_erp_ids[] = $relateditem->relatedItemNumber;
                    }
                }
                // echo '<pre>';print_r($related_erp_ids);die;

                $custom_products = new WP_Query( array(
                                'post_type'      => array('product'),
                                'post_status'    => 'publish,draft',
                                'posts_per_page' => -1,
                                'meta_query'     => array( array(
                                    'key' => 'meta_erp_product_id',
                                    'value' => $related_erp_ids,
                                    'compare' => 'IN',
                                ) )
                            ) );

                            $related_product_ids = array();
                            
                            // The Loop
                            if ( $custom_products->have_posts() ){
                                while ( $custom_products->have_posts() ){
                                    // echo $custom_products->post->ID."<br>";
                                    $custom_product = $custom_products->the_post();
                                    $meta_erp = get_post_meta($custom_products->post->ID,'meta_erp_product_id');
                                    if(isset($meta_erp[0])){
                                        $related_product_ids[] = $custom_products->post->ID;
                                    }
                                }
                                wp_reset_postdata();
                            }

                if(!empty($related_product_ids)){
                    $product->set_upsell_ids( $related_product_ids );
                }

                // $categories = array();
                // foreach($data->webshopCategories as $key=>$category){
                //     $sql = "SELECT wp_category_id from {$wpdb->base_prefix}erp_category_mapping where erp_category_id = ".$category->id;
                //     $result = $wpdb->get_results($sql);
                //     if(!empty($result)){
                //         $categories[] = $result[0]->wp_category_id;
                //     }
                // }
                // $product->set_category_ids( $categories );

                //time taking image attachments
                // $tc = 0;
                // $img_ids = array();
                // foreach($color->images as $key=>$images){
                //     $ext = pathinfo($images->url, PATHINFO_EXTENSION);
                //     if($ext){
                //         $image_id = rudr_upload_file_by_url($images->url);
                //         if($tc == 0){
                //             $product->set_image_id( $image_id );
                //         }
                //         $img_ids[] = $image_id;
                //         $tc++;
                //     }
                // }
                // $product->save();
                // update_post_meta($product->get_id(), '_product_image_gallery', implode(',',$img_ids));

                // $product->set_regular_price( 500.00 );
                // $product->set_sale_price( 250.00 );
                // // sale schedule
                // $product->set_date_on_sale_from( '2022-05-01' );
                // $product->set_date_on_sale_to( '2022-05-31' );
                // $product->set_upsell_ids( array( 15, 17 ) );
                // $product->set_cross_sell_ids( array( 15, 17, 19, 210 ) );

                $product->save(); 

                $sizes = array();
                foreach($data->sizes as $key=>$value){
                    array_push($sizes,$value->size);
                }

                $theData = array();
                wp_set_object_terms( $product->get_id(), array("$data->id"), 'pa_erp_product_id', false );
                $theData['pa_erp_product_id'] = Array(
                        'name'=> 'pa_erp_product_id',
                        'value'=> '',
                        'is_visible' => '1',
                        'is_variation' => '0',
                        'is_taxonomy' => '1'
                    );

                wp_set_object_terms( $product->get_id(), array( $color->color), 'pa_color', false );
                $theData['pa_color'] = Array(
                        'name'=> 'pa_color',
                        'value'=> '',
                        'is_visible' => '1',
                        'is_variation' => '0',
                        'is_taxonomy' => '1'
                    );
    
    
                if(!empty($sizes)){
                    wp_set_object_terms($product->get_id(), $sizes,'pa_size', false );
                    $theData['pa_size'] = Array(
                            'name'=> 'pa_size',
                            'value'=> '',
                            'is_visible' => '1',
                            'is_variation' => '1',
                            'is_taxonomy' => '1'
                        );               
                }

                update_post_meta( $product->get_id(),'_product_attributes', $theData);
                
                $product->update_meta_data("meta_erp_product_id", $data->id);

                $video_code = '';
                if($data->video){
                    if(!empty($data->video->code)){
                        $video_code = $data->video->code;
                    }
                }
                if(!empty($video_code)){
                    $video_url = 'https://www.youtube.com/watch?v='.$video_code;
                    update_post_meta($product->get_id(),'_nickx_video_text_url',$video_url);
                }

                // save the changes and go on
                $product->save(); 
                $product_id = $product->get_id();


                $sale_price = 0;
                if($color->discountPercentage){
                    $discount_amount = (float)($color->salesListPrice * ($color->discountPercentage/100));
                    $sale_price = (float)($data->salesListPrice - $discount_amount);
                }

                if($product_id){
                    foreach($data->barcodes as $key=>$bar_obj){
                        if(trim_color($bar_obj->color->color) == trim_color($color->color)){
                            if($bar_obj->barcode){
                                $variation = new WC_Product_Variation();
                                $variation->set_parent_id( $product->get_id() );
                                $variation->set_regular_price( $data->salesListPrice );
                                if($sale_price){
                                    $variation->set_sale_price( $sale_price );
                                }

                                $variation->set_stock_quantity(10);

                                $variation->set_sku($bar_obj->barcode);
                                
                                $variation->save();

                                $bar_obj->size->size = strtolower($bar_obj->size->size);
                                $bar_obj->size->size = str_replace(": ","-",$bar_obj->size->size);
                                $bar_obj->size->size = str_replace(" ","-",$bar_obj->size->size);

                                $temp_size = (string)$bar_obj->size->size;
                                
                                update_post_meta($variation->get_id(),'attribute_pa_size',"$temp_size");
                                update_post_meta($variation->get_id(),'erp_barcode',$bar_obj->barcode);
                                $variation->save();
                            }
                        }
                    }
                }
            }

            $message = "Itsperfect product : ". $erp_item_id. " created or already exists. <br>";
        }
        else{
            $message = "This product : ".$erp_item_id." doesn't have colors in it. <br>";
        }
    }

    
    return $message;
}
});


/**
 * Function which creates product's variations
 */
$guard->validCallback(function() {

function create_product_variation( $product_id, $variation_data ){
    // Get the Variable product object (parent)
    $product = wc_get_product($product_id);

    $variation_post = array(
        'post_title'  => $product->get_name(),
        'post_name'   => 'product-'.$product_id.'-variation',
        'post_status' => 'publish',
        'post_parent' => $product_id,
        'post_type'   => 'product_variation',
        'guid'        => $product->get_permalink()
    );

    // Creating the product variation
    $variation_id = wp_insert_post( $variation_post );

    // Get an instance of the WC_Product_Variation object
    $variation = new WC_Product_Variation( $variation_id );

    // Iterating through the variations attributes
    foreach ($variation_data['attributes'] as $attribute => $term_name )
    {
        $taxonomy = 'pa_'.$attribute; // The attribute taxonomy

        // If taxonomy doesn't exists we create it (Thanks to Carl F. Corneil)
        if( ! taxonomy_exists( $taxonomy ) ){
            register_taxonomy(
                $taxonomy,
               'product_variation',
                array(
                    'hierarchical' => false,
                    'label' => ucfirst( $attribute ),
                    'query_var' => true,
                    'rewrite' => array( 'slug' => sanitize_title($attribute) ), // The base slug
                ),
            );
        }

        // Check if the Term name exist and if not we create it.
        if( ! term_exists( $term_name, $taxonomy ) )
            wp_insert_term( $term_name, $taxonomy ); // Create the term

        $term_slug = get_term_by('name', $term_name, $taxonomy )->slug; // Get the term slug

        // Get the post Terms names from the parent variable product.
        $post_term_names =  wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );

        // Check if the post term exist and if not we set it in the parent variable product.
        if( ! in_array( $term_name, $post_term_names ) )
            wp_set_post_terms( $product_id, $term_name, $taxonomy, true );

        // Set/save the attribute data in the product variation
        update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term_slug );
    }

    ## Set/save all other data

    // SKU
    if( ! empty( $variation_data['sku'] ) )
        $variation->set_sku( $variation_data['sku'] );

    // Prices
    if( empty( $variation_data['sale_price'] ) ){
        $variation->set_price( $variation_data['regular_price'] );
    } else {
        $variation->set_price( $variation_data['sale_price'] );
        $variation->set_sale_price( $variation_data['sale_price'] );
    }
    $variation->set_regular_price( $variation_data['regular_price'] );

    // Stock
    if( ! empty($variation_data['stock_qty']) ){
        $variation->set_stock_quantity( $variation_data['stock_qty'] );
        $variation->set_manage_stock(true);
        $variation->set_stock_status('');
    } else {
        $variation->set_manage_stock(false);
    }
    
    $variation->set_weight(''); // weight (reseting)

    $variation->save(); // Save the data
}
});

/**
 * Function which imports images for existing products from itsperfect.
 */
$guard->validCallback(function() {

add_action('wp_ajax_import_images','import_images');
function import_images(){
    global $wpdb;

    $products = wc_get_products( 
        array( 
            'status' => 'publish,draft',
            'limit' => -1
        )
    );

    // echo count($products);die;

    $data = '';
    if(isset($_SESSION['data'])){
        $data = $_SESSION['data'];
    }
    else{
        echo "We didn't get data in session";
        wp_die();
    }

    if(empty($data)){
        //we will figure this part out later
    }
    else{
        $final_data = array();
        foreach($data as $key=>$erp_obj){
            foreach($erp_obj->colors as $key=>$erp_color_obj){
                $color =  $erp_color_obj->color;
                $color = trim_color($color);
                if(!empty($erp_color_obj->images)){
                    foreach($erp_color_obj->images as $key=>$img_obj){
                        $final_data[$erp_obj->id][$color][$img_obj->id] = $img_obj->url;
                    }
                }
            }
        }
    }

    if(!empty($final_data)){
        // echo 'wee here';die;
        foreach($products as $key=>$product){
            $erpid = $product->get_attribute('erp_product_id');
            $existing_color = $product->get_attribute('color');
            $existing_color = trim_color($existing_color);
            if(isset($final_data[$erpid][$existing_color])){
                //time taking image attachments
                $tc = 0;
                $img_ids = array();
                foreach($final_data[$erpid][$existing_color] as $key=>$url){
                    $filename = basename( $url );
                    
                    if( null == ( $thumb_id = does_file_exists( $filename ) ) ) {
                        $ext = pathinfo($url, PATHINFO_EXTENSION);
                        if($ext){
                            $image_id = rudr_upload_file_by_url($url);
                            if($tc == 0){
                                $product->set_image_id( $image_id );
                            }
                            else{
                                $img_ids[] = $image_id;
                            }
                            $tc++;
                        }
                    }
                    else{
                        $image_id = does_file_exists( $filename );
                        if($tc == 0){
                            $product->set_image_id($image_id);
                            $tc++;
                        }
                        else{
                            $img_ids[] = $image_id;
                        }
                    }
                }
                
                if(!empty($img_ids)){
                    update_post_meta($product->get_id(), '_product_image_gallery', implode(',',$img_ids));
                }

                $product->save();
            }
        }
    }
    echo "Total ".count($products)." product's images are updated.";
    wp_die();
}
});

/**
 * Function that updates a specific product in wordpress
 */
$guard->validCallback(function() {

add_action('wp_ajax_update_product','update_product');
function update_product($wp_product_id = ''){
    global $wpdb;

    $message = '';

    if(isset($_POST['wp_product_id'])){
        $wp_product_id = $_POST['wp_product_id'];
    }
    
    if($wp_product_id != ''){
        $product = wc_get_product($wp_product_id);
        $erp_id  = $product->get_attribute('erp_product_id');
        $productcolor   = $product->get_attribute('color');
        if(empty($erp_id) || empty($productcolor)){
            if(empty($erp_id)){
                $meta_erp_id = get_post_meta($wp_product_id,'meta_erp_product_id');
                if(empty($meta_erp_id)){
                    $message = "Itsperfect ID is missing !";
                }
                else{
                    $erp_id = $meta_erp_id[0];
                }
            }
                
            if(empty($productcolor)){
                $message = "Color is missing in the selected product !";
            }
        }

        if($erp_id != '' && !empty($productcolor)){
            $updated_erp_data = itsperfect_get_items($erp_id,'','',$GLOBALS['apiStart'],$GLOBALS['token']);
            // echo '<pre>';print_r($updated_erp_data);die;
            if(!empty($updated_erp_data) && isset($updated_erp_data[0])){
                $updated_erp_data = $updated_erp_data[0];
                foreach($updated_erp_data->colors as $key=>$erp_color){
                    if(trim_color($erp_color->color) == trim_color($productcolor)){
                        $data = $updated_erp_data;

                        $width = $data->dimensions->width;
                        $height = $data->dimensions->height;
                        $depth = $data->dimensions->depth;
                        $weight = $data->dimensions->weight;

                        $product_name = '';
                      
        
                        $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'product_title_format'";
                        $wc_auth = $wpdb->get_results($query);
                        if(empty($wc_auth)){
                            $product_title_format = '';
                        }
                        else{
                            $product_title_format = explode(",",$wc_auth[0]->setting_value);
                            if(in_array('brand_name',$product_title_format))
                            {
                                $product_name .=   "Black And Gold - ";
                            }
                            if(in_array('item_group',$product_title_format))
                            {
                                $item_category_group = $data->itemGroup->itemGroup;
                                $product_name .=   $item_category_group." - ";
                            }
                         
                          }
                          $product_name .=  $data->item." - ".$erp_color->color;


                      //  $product->set_name( $data->item." - ".$erp_color->color );
                         $product->set_name(  $product_name );
                         $custom_sku = $data->itemNumbe;
                        //$custom_sku = $data->itemNumber."".$erp_color->colorNumber;
                        $product->set_sku($custom_sku);
                        /*
                        $item_category_group = $data->itemGroup->itemGroup;

                        $product->set_name( "Black and Gold - ".$item_category_group." - ".$data->item." - ".$erp_color->color );
                        $product->set_sku($data->itemNumber);
                        $product->set_status( 'publish' ); */
                        $product->set_catalog_visibility( 'visible' );
                        
                        $desc = '';
                        if(isset($data->description->en)){
                            $desc = $data->description->en;
                        }

                        $product->set_description($desc);
                        $product->set_short_description($desc);

                        $product->set_width($width);
                        $product->set_height($height);
                        $product->set_length($depth);
                        $product->set_weight($weight);

                        $wpcategories = array();
                        $wp_parent_categories = array();

                        $existing_cats = $product->get_category_ids();

                        // echo '<pre>';print_r($existing_cats);die;
                        
                        $erp_cat_temps = array();
                        $parent_cat_temps = array();
                        foreach($data->webshopCategories as $key=>$category){
                            $erp_cat_temps[$category->id] = $category->category->en;
                            $parent_cat_temps[$category->id] = $category->categoryId;
                        }
                        // echo "<Pre>";print_r($erp_cat_temps);
                        // echo '<br>';
                        // echo '<Pre>';print_r($parent_cat_temps);die;
                        foreach($erp_cat_temps as $key=>$cat_name){
                            $original_cat_name = $cat_name;
                            $cat_name = strtolower($cat_name);
                            $cat_name = str_replace(" ","-",$cat_name);
                            $cat_name = str_replace("&-","",$cat_name);

                            $sql = "SELECT * FROM {$wpdb->base_prefix}terms where name='$original_cat_name' limit 1";
                            //$sql = "SELECT * FROM {$wpdb->base_prefix}terms where slug='$cat_name' limit 1";
                            $result = $wpdb->get_results($sql);
                            if(!empty($result)){
                                $term_id = $result[0]->term_id;

                                if($parent_cat_temps[$key] == 0){
                                    $tax_sql = "SELECT * FROM {$wpdb->base_prefix}term_taxonomy where term_id='$term_id' and taxonomy='product_cat' and parent = '0' ";
                                    $tax_result = $wpdb->get_results($tax_sql);
                                    if(!empty($tax_result)){
                                        $wpcategories[] = $tax_result[0]->term_id;
                                        $wp_parent_categories[] = $tax_result[0]->term_id;
                                    }
                                }
                                else{
                                    $sql = "SELECT * FROM {$wpdb->base_prefix}terms where name = '$original_cat_name'";
                                    // $sql = "SELECT * FROM {$wpdb->base_prefix}terms where slug LIKE '%$cat_name%'";
                                    $result = $wpdb->get_results($sql);
                                    foreach($result as $key=>$term_obj){
                                        
                                        $tax_sql = "SELECT * FROM {$wpdb->base_prefix}term_taxonomy where term_id='$term_obj->term_id' and taxonomy='product_cat'";
                                        $tax_result = $wpdb->get_results($tax_sql);

                                        if(!empty($tax_result)){
                                            foreach($tax_result as $key=>$term_result_obj){
                                                if(in_array($term_result_obj->parent,$wp_parent_categories)){
                                                    $wpcategories[] = $term_result_obj->term_id;
                                                    $wp_parent_categories[] = $term_result_obj->term_id;
                                                }        
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $final_wp_categories = array_unique(array_merge($existing_cats,$wpcategories));
                       
                        $product->set_category_ids( $final_wp_categories );

                          //tags addition
                          $tags = array();
                          //static itsperfect tag
                          $tag_term = wp_insert_term( "From Itsperfect", 'product_tag', array(
                              'description' => "Product that come from itsperfect", // optional
                              'parent' => 0,      // optional
                          ) );
  
                          if(isset($tag_term->error_data['term_exists'])){
                              $tag_id = $tag_term->error_data['term_exists'];
                          }
  
                          if(is_array($tag_term)){
                              if(isset($tag_term['term_id'])){
                                  $tag_id = $tag_term['term_id'];
                              }    
                          }
  
                          $tags[] = $tag_id;
                          
  
                          if(!empty($data->season->season)){
                              $season = $data->season->season;
                              $tag_term = wp_insert_term( "$season", 'product_tag', array(
                                  'description' => "Season imported from itsperfect", // optional
                                  'parent' => 0,      // optional
                              ) );
  
                              if(isset($tag_term->error_data['term_exists'])){
                                  $tag_id = $tag_term->error_data['term_exists'];
                              }
  
                              if(is_array($tag_term)){
                                  if(isset($tag_term['term_id'])){
                                      $tag_id = $tag_term['term_id'];
                                  }    
                              }
                              $tags[] = $tag_id;
                          }
                          $product->set_tag_ids( $tags );

                        $product->save();

                        // time taking image attachments
                        $tc = 0;
                        $img_ids = array();
                        foreach($erp_color->images as $key=>$images){
                            $url = $images->url;
                            
                            $filename = basename( $url );
                    
                            if( null == ( $thumb_id = does_file_exists( $filename ) ) ) {
                                $ext = pathinfo($url, PATHINFO_EXTENSION);
                                if($ext){
                                    $image_id = rudr_upload_file_by_url($url);
                                    if($tc == 0){
                                        $product->set_image_id( $image_id );
                                    }
                                    else{
                                        $img_ids[] = $image_id;
                                    }
                                    $tc++;
                                }
                            }
                            else{
                                $image_id = does_file_exists( $filename );
                                if($tc == 0){
                                    $product->set_image_id($image_id);
                                    $tc++;
                                }
                                else{
                                    $img_ids[] = $image_id;
                                }
                            }
                        }
                        update_post_meta($product->get_id(), '_product_image_gallery', implode(',',$img_ids));

                         // Update the dimensions for this variation
                         $product->update_meta_data('_length', $depth);
                         $product->update_meta_data('_width', $width);
                         $product->update_meta_data('_height', $height);
                         $product->update_meta_data('_weight', $weight);

                        $related_erp_ids = array();
                        foreach($data->relatedItems as $key=>$relateditem){
                            if(!empty($relateditem->relatedItemNumber)){
                                $related_erp_ids[] = $relateditem->relatedItemNumber;
                            }
                        }
                        // echo '<pre>';print_r($related_erp_ids);die;

                        $custom_products = new WP_Query( array(
                                        'post_type'      => array('product'),
                                        'post_status'    => 'publish,draft',
                                        'posts_per_page' => -1,
                                        'meta_query'     => array( array(
                                            'key' => 'meta_erp_product_id',
                                            'value' => $related_erp_ids,
                                            'compare' => 'IN',
                                        ) )
                                    ) );

                                    $related_product_ids = array();
                                    
                                    // The Loop
                                    if ( $custom_products->have_posts() ){
                                        while ( $custom_products->have_posts() ){
                                            // echo $custom_products->post->ID."<br>";
                                            $custom_product = $custom_products->the_post();
                                            $meta_erp = get_post_meta($custom_products->post->ID,'meta_erp_product_id');
                                            if(isset($meta_erp[0])){
                                                $related_product_ids[] = $custom_products->post->ID;
                                            }
                                        }
                                        wp_reset_postdata();
                                    }

                        if(!empty($related_product_ids)){
                            $product->set_upsell_ids( $related_product_ids );
                        }

                        $product->save();

                        $sizes = array();
                        foreach($data->sizes as $key=>$value){
                            array_push($sizes,$value->size);
                        }

                        $theData = array();
                        wp_set_object_terms( $product->get_id(), array("$data->id"), 'pa_erp_product_id', false );
                        $theData['pa_erp_product_id'] = Array(
                                'name'=> 'pa_erp_product_id',
                                'value'=> '',
                                'is_visible' => '1',
                                'is_variation' => '0',
                                'is_taxonomy' => '1'
                            );

                        wp_set_object_terms( $product->get_id(), array($erp_color->color), 'pa_color', false );
                        $theData['pa_color'] = Array(
                                'name'=> 'pa_color',
                                'value'=> '',
                                'is_visible' => '1',
                                'is_variation' => '0',
                                'is_taxonomy' => '1'
                            );
            
            
                        if(!empty($sizes)){
                            wp_set_object_terms($product->get_id(), $sizes,'pa_size', false );
                            $theData['pa_size'] = Array(
                                    'name'=> 'pa_size',
                                    'value'=> '',
                                    'is_visible' => '1',
                                    'is_variation' => '1',
                                    'is_taxonomy' => '1'
                                );               
                        }

                        update_post_meta( $product->get_id(),'_product_attributes', $theData);

                        $sale_price = 0;
                        if($erp_color->discountPercentage){
                            $discount_amount = (float)($erp_color->salesListPrice * ($erp_color->discountPercentage/100));
                            $sale_price = (float)($data->salesListPrice - $discount_amount);
                        }

                        $video_code = '';
                        if($data->video){
                            if(!empty($data->video->code)){
                                $video_code = $data->video->code;
                            }
                        }
                        if(!empty($video_code)){
                            $video_url = 'https://www.youtube.com/watch?v='.$video_code;
                            update_post_meta($product->get_id(),'_nickx_video_text_url',$video_url);
                        }

                  // old code for delete variation and upload new
/*
                        //deleting all old variations
                        $variations = $product->get_children();
                        if($variations){
                            foreach($variations as $key=>$variation_id){
                                wp_delete_post( $variation_id );
                            }
                        }
                        

                        if($product->get_id()){
                            foreach($data->barcodes as $key=>$bar_obj){
                                if(trim_color($bar_obj->color->color) == trim_color($erp_color->color)){
                                    if($bar_obj->barcode){
                                        $variation = new WC_Product_Variation();
                                        $variation->set_parent_id( $product->get_id() );
                                        $variation->set_regular_price( $data->salesListPrice );
                                        if($sale_price){
                                            $variation->set_sale_price( $sale_price );
                                        }

                                        $variation->set_stock_quantity(10);

                                        $variation->set_sku($bar_obj->barcode);
                                        
                                        $variation->save();


                                        
                                        $bar_obj->size->size = strtolower($bar_obj->size->size);
                                        $bar_obj->size->size = str_replace(": ","-",$bar_obj->size->size);
                                        $bar_obj->size->size = str_replace(" ","-",$bar_obj->size->size);
                                        // echo $bar_obj->size->size;die;

                                        $temp_size = (string)$bar_obj->size->size;
                                        
                                        update_post_meta($variation->get_id(),'attribute_pa_size',"$temp_size");
                                        update_post_meta($variation->get_id(),'erp_barcode',$bar_obj->barcode);
                                        $variation->save();
                                    }
                                }
                            }
                        }*/

                            // new code for update existing variation without delete it
                                                    

                            if ($product->get_id()) {
                                // Get existing variations
                                $variations = $product->get_children();

                                foreach ($data->barcodes as $key => $bar_obj) {
                                    if (trim_color($bar_obj->color->color) == trim_color($erp_color->color)) {
                                        if ($bar_obj->barcode) {
                                            foreach ($variations as $variation_id) {
                                                $variation = wc_get_product($variation_id);

                                                if ($variation->get_sku() == $bar_obj->barcode) {
                                                    // Update existing variation
                                                    $variation->set_regular_price($data->salesListPrice);
                                                    
                                                    if ($sale_price) {
                                                        $variation->set_sale_price($sale_price);
                                                    }

                                                    $variation->set_stock_quantity(10);

                                                    // Additional updates for size and attributes
                                                    $bar_obj->size->size = strtolower($bar_obj->size->size);
                                                    $bar_obj->size->size = str_replace(": ", "-", $bar_obj->size->size);
                                                    $bar_obj->size->size = str_replace(" ", "-", $bar_obj->size->size);
                                                    $temp_size = (string) $bar_obj->size->size;
                                                    update_post_meta($variation->get_id(), 'attribute_pa_size', "$temp_size");
                                                    update_post_meta($variation->get_id(), 'erp_barcode', $bar_obj->barcode);

                                                    // Save changes
                                                    $variation->save();

                                                    break; // Exit the loop once the variation is updated
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                        $message = "Product : ".$product->get_id()." is successfully updated.";
                    }
                }
            }
            else{
                $product->set_status('draft');
                $product->save();
                $message = "We couldn't get the response from itsperfect API !!";
            }
        }
    }
    else{
        $message = "We couldn't get product ID, something went wrong !";
    }
    
    echo $message;
    // wp_die();
}
});

/**
 * trim unnecessay thing before comaprision of colors
 */
function trim_color($color){
    $color = strtolower(str_replace(" ","",$color));
    $color = str_replace("-","",$color);
    $color = str_replace("_","",$color);
    $color = str_replace("'","",$color);

    return $color;
}


function does_file_exists($filename) {
    global $wpdb;

    return intval( $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%/$filename'" ) );
}


/**
 * Upload image from URL programmatically
 *
 * @author Misha Rudrastyh
 * @link https://rudrastyh.com/wordpress/how-to-add-images-to-media-library-from-uploaded-files-programmatically.html#upload-image-from-url
 */
function rudr_upload_file_by_url( $image_url ) {

    // it allows us to use download_url() and wp_handle_sideload() functions
    require_once( ABSPATH . 'wp-admin/includes/file.php' );

    // download to temp dir
    $temp_file = download_url( $image_url );

    if( is_wp_error( $temp_file ) ) {
        return false;
    }

    // move the temp file into the uploads directory
    $file = array(
        'name'     => basename( $image_url ),
        'type'     => mime_content_type( $temp_file ),
        'tmp_name' => $temp_file,
        'size'     => filesize( $temp_file ),
    );
    $sideload = wp_handle_sideload(
        $file,
        array(
            'test_form'   => false // no needs to check 'action' parameter
        )
    );

    if( ! empty( $sideload[ 'error' ] ) ) {
        // you may return error message if you want
        return false;
    }

    // it is time to add our uploaded image into WordPress media library
    $attachment_id = wp_insert_attachment(
        array(
            'guid'           => $sideload[ 'url' ],
            'post_mime_type' => $sideload[ 'type' ],
            'post_title'     => basename( $sideload[ 'file' ] ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ),
        $sideload[ 'file' ]
    );

    if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
        return false;
    }

    // update medatata, regenerate image sizes
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    wp_update_attachment_metadata(
        $attachment_id,
        wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] )
    );

    return $attachment_id;

}


/**
 * This function resets the entire store
 */
$guard->validCallback(function() {

add_action('wp_ajax_reset_store','kp_reset_store');
function kp_reset_store(){
    global $wpdb;
    $deletequery = "DELETE relations.*, taxes.*, terms.*
                    FROM {$wpdb->base_prefix}term_relationships AS relations
                    INNER JOIN {$wpdb->base_prefix}term_taxonomy AS taxes
                    ON relations.term_taxonomy_id=taxes.term_taxonomy_id
                    INNER JOIN {$wpdb->base_prefix}terms AS terms
                    ON taxes.term_id=terms.term_id
                    WHERE object_id IN (SELECT ID FROM {$wpdb->base_prefix}posts WHERE post_type='product');";
    $wpdb->query($deletequery);
    $wpdb->query("DELETE FROM {$wpdb->base_prefix}postmeta WHERE post_id IN (SELECT ID FROM wp_posts WHERE post_type = 'product');");
    $wpdb->query("DELETE FROM {$wpdb->base_prefix}posts WHERE post_type = 'product';");


    $wpdb->query("DELETE a,c FROM {$wpdb->base_prefix}terms AS a
              LEFT JOIN {$wpdb->base_prefix}term_taxonomy AS c ON a.term_id = c.term_id
              LEFT JOIN {$wpdb->base_prefix}term_relationships AS b ON b.term_taxonomy_id = c.term_taxonomy_id
              WHERE c.taxonomy = 'product_tag'");
    $wpdb->query("DELETE a,c FROM {$wpdb->base_prefix}terms AS a
              LEFT JOIN {$wpdb->base_prefix}term_taxonomy AS c ON a.term_id = c.term_id
              LEFT JOIN {$wpdb->base_prefix}term_relationships AS b ON b.term_taxonomy_id = c.term_taxonomy_id
              WHERE c.taxonomy = 'product_cat'");

    $wpdb->query("Update wp_postmeta set meta_value ='' WHERE meta_key = '_sku'");

    echo "All products & catgories are removed !";
    wp_die();
}

});

/**
 * This function sends order to itsperfect
 * Order management
 */
$guard->validCallback(function() {

add_action('wp_ajax_send_order_to_erp','send_order_to_erp');
add_action('woocommerce_order_status_processing','send_order_to_erp',500000000); //this is the hook that gets called when order is successfully placed.
function send_order_to_erp($order_id){
    global $wpdb;

    $message = '';
    $resync = 0;

    if(isset($_POST['resync'])){
        $resync = 1;
    }
    
    if(isset($_POST['order_id']) || empty($order_id)){
        $order_id = $_POST['order_id'];
    }

    /**CHECK IF ORDER ALREADY SYNCED OR NOT START*/
    $erp_order_status = get_post_meta($order_id,'erp_order_status');
    if(isset($erp_order_status[0]) && !empty($erp_order_id)){
        $erp_order_status = $erp_order_status[0];
    }
    $erp_order_id = get_post_meta($order_id,'erp_order_id');
    if(isset($erp_order_id[0]) && !empty($erp_order_id)){
        $erp_order_id = $erp_order_id[0];
    }

    if(empty($erp_order_id) || empty($erp_order_status)){
        //first time sync pending do nothing ! 
    }
    else if($erp_order_id != '' || $erp_order_status != ''){
        if($resync){
            //do nothing here
        }
        else{
            //this order already exists
            $order = wc_get_order(  $order_id );
            $note = __("Attempted to sync again!! ");
            $order->add_order_note( $note );
            echo "This order is already synced !!";
            wp_die();
        }
    }
    /**CHECK IF ORDER ALREADY SYNCED OR NOT END*/

    $order = wc_get_order(  $order_id );
    $note = __("Attempted to sync this order ! ");
    $order->add_order_note( $note );
    
    
    if($order_id || $resync){
        $order = wc_get_order($order_id);
        $order = $order->get_data();

        /** GET CUSTOMER ERP ID START */
        $userid = $order['customer_id'];
        if($userid){
            $erp_customer_id = get_post_meta($userid,'erp_customer_id');
        }
        else{
            //get the user email from the order
            $order_email = $order['billing']['email'];

            // check if there are any users with the billing email as user or email
            $email = email_exists( $order_email );
            $user = get_user_by('email',$order_email);
            
            // if the UID is null, then it's a guest checkout
            if(empty($user) && $email == false){
                // random password with 12 chars
                $random_password = wp_generate_password();

                // create new user with email as username & newly created pw
                $userdata = array(
                    'user_login'  =>  $order_email,
                    'user_pass'   =>  $random_password,
                    'user_email' => $order['billing']['email'],
                    'first_name'  =>  $order['billing']['first_name'],
                    'last_name'  =>  $order['billing']['last_name'],
                );
                $new_user_id = wp_insert_user($userdata);
                add_user_meta( $new_user_id, 'billing_first_name', $order['billing']['first_name']);
                add_user_meta( $new_user_id,'billing_last_name' , $order['billing']['last_name']);
                add_user_meta( $new_user_id,'billing_company' , $order['billing']['company']);
                add_user_meta( $new_user_id,'billing_address_1' , $order['billing']['address_1']);
                add_user_meta( $new_user_id,'billing_address_2' , $order['billing']['address_2']);
                add_user_meta( $new_user_id,'billing_city' , $order['billing']['city']);
                add_user_meta( $new_user_id,'billing_state' ,$order['billing']['state']);
                add_user_meta( $new_user_id,'billing_postcode' , $order['billing']['postcode']);
                add_user_meta( $new_user_id,'billing_country' , $order['billing']['country']);
                add_user_meta( $new_user_id,'billing_email' , $order['billing']['email']);
                add_user_meta( $new_user_id,'billing_phone' , $order['billing']['phone']);
                add_user_meta( $new_user_id,'shipping_first_name' , $order['shipping']['first_name']);
                add_user_meta( $new_user_id,'shipping_last_name' , $order['shipping']['last_name']);
                add_user_meta( $new_user_id,'shipping_company' , $order['shipping']['company']);
                add_user_meta( $new_user_id,'shipping_address_1' , $order['shipping']['address_1']);
                add_user_meta( $new_user_id,'shipping_address_2' , $order['shipping']['address_2']);
                add_user_meta( $new_user_id,'shipping_city' , $order['shipping']['city']);
                add_user_meta( $new_user_id,'shipping_state' ,$order['shipping']['state']);
                add_user_meta( $new_user_id,'shipping_postcode' , $order['shipping']['postcode']);
                add_user_meta( $new_user_id,'shipping_country' , $order['shipping']['country']);
                
                $erp_customer_id = create_user_wp($new_user_id);
                update_post_meta($new_user_id,'erp_customer_id',$erp_customer_id);
            }
            else{
                $erp_customer_id = create_user_wp($user->data->ID);
                update_post_meta($user->data->ID,'erp_customer_id',$erp_customer_id);
            }
        }
        /** GET CUSTOMER ERP ID END */

        // echo $erp_customer_id;die;
        $erporder = array();
        $erporder['webshopOrderId'] = mt_rand(1,999999999999);
        $erporder['orderType'] = 2;
        $erporder['date'] = Date("Y-m-d");
        $erporder['currency'] = $order['currency'];
        $erporder['exchangeRate'] = "1.0000000";
        $erporder['customerId'] = $erp_customer_id;  // WE put the erp customer ID here.
        $erporder['brandId'] = "1";
        $erporder['discountPercentage'] = "0.00";
        $erporder['chargePercentage'] = "0.00";
        $erporder['shippingCosts'] = $order['shipping_total'];

        $erporder['reference'] = "R-".$order_id;
        $erporder['comment'] = $order_id;
        $erporder['internalComment'] = "IC-".$order_id;

        $erporder['amount'] = $order['total'];

        //getting countryid for country
        $getting_country_url = $GLOBALS['apiStart']."/api/v2/countries/?token=".$GLOBALS['token'];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $getting_country_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: 86b8e7d1-1a55-4502-931f-1ff462f9476c",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $countryid = 2;
            $countriesdata = json_decode($response);
            foreach($countriesdata->countries as $key=>$country){
                if($country->iso2 == $order['shipping']['country']){
                    $countryid = $country->id;
                }
            }
        }


        $cmpny_name = '';
        if(isset($order['shipping']['company'])){
            if(!empty($order['shipping']['company'])){
                $cmpny_name = $order['shipping']['company']." -";
            }
        }

        $erporder['shippingAddress']['name'] = $cmpny_name." ".$order['shipping']['first_name']." ".$order['shipping']['last_name'];
        $erporder['shippingAddress']['street'] = $order['shipping']['address_1'];
        $erporder['shippingAddress']['housenumber'] = $order['shipping']['address_2'];
        $erporder['shippingAddress']['postalCode'] = $order['shipping']['postcode'];
        $erporder['shippingAddress']['countryId'] = $countryid; //2;
        $erporder['shippingAddress']['city'] = $order['shipping']['city'];
        $erporder['shippingAddress']['email'] = $order['billing']['email'];
        $erporder['shippingAddress']['phone'] = $order['billing']['phone'];

        $ordertotal = 0;
        $flag = 0;
        foreach($order['line_items'] as $key=>$item){
            
            $myitem = array();
            $item = $item->get_data();
            
            $erp_product_id = 0;

            
            $createby = "bycolors";
            if($createby == "bycolors"){
                if(isset($item['product_id'])){
                    
                    $product = wc_get_product($item['product_id']);

                    if($product->get_type() == 'easy_product_bundle'){
                        continue;
                    }

                    $erp_product_id = $product->get_attribute('erp_product_id');

                    $itemcolor = $product->get_attribute('color');
                    $itemcolor = trim_color($itemcolor);

                    $erp_barcode = 0;
                    if($item['variation_id']){
                        $erp_barcode = get_post_meta($item['variation_id'],'erp_barcode');
                        $erp_barcode = $erp_barcode[0];
                    }
                    
                    if($erp_barcode == 0 || empty($erp_barcode)){
                        $itemsize = ''; //freshly set
                        foreach($item['meta_data'] as $key=>$meta_obj){
                            $meta_obj = $meta_obj->get_data();
    
                            if(empty($itemsize)){
                                if($meta_obj['key'] == 'pa_size'){
                                    $itemsize = $meta_obj['value'];
                                }
                            }
    
                            if(empty($itemsize)){
                                if($meta_obj['key'] == 'size'){
                                    $itemsize = $meta_obj['value'];
                                }
                            }
                        }

                        // if(empty($itemsize)){
                        //     $itemsize = 'l';
                        // }

                        $variations = $product->get_children();
                        foreach($variations as $key=>$var_id){
                            $var_meta = get_post_meta($var_id);
                            if($var_meta['attribute_pa_size']){
                                $var_size = $var_meta['attribute_pa_size'];
                                $var_size = $var_size[0];

                                if(trim_color($itemsize) == trim_color($var_size)){
                                    $erp_barcode = $var_meta['erp_barcode'][0];
                                }
                            }
                        }
                    }

                    // if(empty($itemsize)){
                    //     // continue;
                    //     $itemsize = 'l';
                    // }

                    // if($product->get_id() == 32096){
                    //     $itemsize = 'xl';
                    // }

                    // if($product->get_id() == 11751){
                    //     continue;
                    // }
                    // if($product->get_id() == 32486){
                    //     continue;
                    // }

                    // if($item['variation_id'] == 0 || empty($item['variation_id'])){
                    //     $item['variation_id'] = 42057;
                    // }
                    // $var_product = wc_get_product($item['variation_id']);
                    // $itemsize = $var_product->get_attribute('size');


                    if($erp_barcode == 0 || empty($erp_barcode)){
                        
                        $itemsize = trim_color($itemsize);
                        
                        $erpproduct = itsperfect_get_items($erp_product_id,'','',$GLOBALS['apiStart'],$GLOBALS['token']);
                        
                        $erpproduct = $erpproduct[0];
    
                        $erpAttrBarcodes = array();

                        
    
                        foreach ($erpproduct->barcodes as $key => $value) {
                            if(($itemcolor == trim_color($value->color->color)) && ($itemsize == trim_color($value->size->size))){
                                array_push($erpAttrBarcodes,array("barcode"=>$value->barcode,"color"=>$value->color->color,"size"=>$value->size->size));
                            }
                        }

                        $erp_barcode = $erpAttrBarcodes[0]['barcode'];
                    }
                    
                }
            }

            // echo $erp_barcode;die;
            $myitem['barcode'] = $erp_barcode;
            $myitem['itemId'] = $erp_product_id; //$erp_product_id;
            $myitem['item'] = $item['name'];
            $myitem['quantity'] = $item['quantity'];

            if($ordertotal > 200 && $erp_product_id == 167 && $flag == 0){
                $myitem['price'] = 0;
                $myitem['salesListPrice'] = 0;
                $myitem['amount'] = 0;
                $myitem['amountForeignCurrency'] = 0;
                $flag++;
            }
            else{
                $ordertotal = $ordertotal + $erpproduct->salesListPrice;

                $myitem['price'] = $item['total']+$item['total_tax'];
                $myitem['price'] = number_format($myitem['price'], 2); 

                $myitem['salesListPrice'] = $item['total']+$item['total_tax'];
                $myitem['salesListPrice'] = number_format($myitem['salesListPrice'], 2); 

                $myitem['amount'] = $item['total']+$item['total_tax'];
                $myitem['amount'] = number_format($myitem['amount'], 2); 

                $myitem['amountForeignCurrency'] = $item['total']+$item['total_tax'];
                $myitem['amountForeignCurrency'] = number_format($myitem['amountForeignCurrency'], 2);

                
                if(empty($myitem['price']) || $myitem['price'] == 0){
                    $myitem['price'] = $product->get_price();
                }
                if(empty($myitem['salesListPrice']) || $myitem['salesListPrice'] == 0){
                    $myitem['salesListPrice'] = $product->get_price();
                }
                if(empty($myitem['amount']) || $myitem['amount'] == 0){
                    $myitem['amount'] = $product->get_price();
                }
                if(empty($myitem['amountForeignCurrency']) || $myitem['amountForeignCurrency'] == 0){
                    $myitem['amountForeignCurrency'] = $product->get_price();
                }

            }
            $myitem['vat'] = "0.00";
            $myitem['discountPercentage'] = "0.00";


            $erporder['items'][] = $myitem;
        }
        
        // echo '<Pre>';print_r($erporder);die;
        /** Barcode check start */
        foreach($erporder['items'] as $key=>$erp_order_item){
            if($erp_order_item['barcode'] == 0 || empty($erp_order_item['barcode'])){
                //note
                $note = __("Order couldn't sync due to missing barcode in product : ".$erp_order_item['itemId']."<br><br> Item name : ".$erp_order_item['item']);
                
                // If you don't have the WC_Order object (from a dynamic $order_id)
                $order = wc_get_order(  $order_id );
                
                // Add the note
                $order->add_order_note( $note );

                
                //notification email when order sync is failed
                $ord_url = get_site_url()."/wp-admin/post.php?post=".$order_id."&action=edit";
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $admin_email = "kushalpatel2014@gmail.com";
                $message = sprintf( __( '<h3>Order Sync failed</h3>.' ) ) . "\r\n\r\n";
                $message .= sprintf( __( 'Order id #'.$order_id.' has not synced with ERP due to missing barcode.<br><br>' ) ) . "\r\n\r\n";
                $message .= sprintf( __( 'You can check Order details <a href='.$ord_url.'> click here </a>' ) ) . "\r\n\r\n";
                wp_mail( $admin_email, sprintf( __( 'Order sync failed' )), $message,  $headers);

                $message = "Order couldn't sync due to missing barcode in product : ".$erp_order_item['itemId']."<br> Item name : ".$erp_order_item['item'];

                echo $message;
                wp_die();
            }
        }
        /** Barcode check end */
        
        // echo "<pre>";print_r($erporder);die;
        $erporderstring = json_encode($erporder);

        //adding request as a note
        // If you don't have the WC_Order object (from a dynamic $order_id)
        $order = wc_get_order(  $order_id );
        
        // Add the note
        $order->add_order_note( $erporderstring );
        
        $send_order_url = $GLOBALS['apiStart']."/api/v2/orders/?token=".$GLOBALS['token'];
        // $send_order_url = ''; //temp
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $send_order_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "$erporderstring",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Postman-Token: 7ac76043-db63-426a-bbc1-dbadb016c7de",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $urlrequested = $send_order_url;
        $urlrequested = $erporderstring;
        $resultresponse = json_encode($response);
        $date = date("Y-m-d H:i:s");
        if ($err) {
            $resultresponse = json_encode($response) . "____" . $err;

            $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`url_requested`,`error_code`,`created_at`) values ('erp_order_sync_admin','$urlrequested','$resultresponse','$date') ";
            $wpdb->query($query);

            $ord_url = get_site_url()."/wp-admin/post.php?post=".$order_id."&action=edit";

            //notification email when order sync is failed
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $admin_email = "info@blackandgoldofficial.com,kushalpatel2014@gmail.com";
            $message = sprintf( __( '<h3>Order Sync failed</h3>.' ) ) . "\r\n\r\n";
            $message .= sprintf( __( 'Order id #'.$order_id.' has not synced with ERP.<br><br>' ) ) . "\r\n\r\n";
            $message .= sprintf( __( 'You can check Order details <a href='.$ord_url.'> click here </a>' ) ) . "\r\n\r\n";
            wp_mail( $admin_email, sprintf( __( 'Order sync failed' )), $message,  $headers);

            $message = "Order sync is failed due to curl error! Its wordpress order id is: ".$order_id;
            $note = __("Order sync is failed due to curl error! ".$err);

        } else {
            $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`url_requested`,`error_code`,`created_at`) values ('erp_order_sync_admin','$urlrequested','$resultresponse','$date') ";
            $wpdb->query($query);

            $decode = json_decode($response);

            if(isset($decode->insertId)){
                update_post_meta($order_id,'erp_order_status','synced');
                update_post_meta($order_id,'erp_order_id',$decode->insertId);
                $message = 'Order is synced ! <br> its_erp_order_id = '.$decode->insertId;
                $decode = json_decode($response);
                // The text for the note
                $note = __('its_erp_order_id = '.$decode->insertId);
            }
            else{
                $ord_url = get_site_url()."/wp-admin/post.php?post=".$order_id."&action=edit";

                //notification email when order sync is failed
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $admin_email = "info@blackandgoldofficial.com,kushalpatel2014@gmail.com";
                $message = sprintf( __( '<h3>Order Sync failed</h3>.' ) ) . "\r\n\r\n";
                $message .= sprintf( __( 'Order id #'.$order_id.' has not synced with ERP.<br><br>' ) ) . "\r\n\r\n";
                $message .= sprintf( __( 'You can check Order details <a href='.$ord_url.'> click here </a>' ) ) . "\r\n\r\n";
                wp_mail( $admin_email, sprintf( __( 'Order sync failed' )), $message,  $headers);

                $message = "Order sync is failed! Its wordpress order id is: ".$order_id;
                $note = __("Itsperfect Response : ".$response);

            }

            // If you don't have the WC_Order object (from a dynamic $order_id)
            $order = wc_get_order(  $order_id );
            
            // Add the note
            $order->add_order_note( $note );
        }
    }
    else
    {
        $message = "We didn't get the order ID";   
    }

    echo $message;
    // wp_die();
}

});
/**
 * This function creates user in itsperfect ERP system.
 */
function create_user_wp($userid){
    global $wpdb;
    
    $user = get_userdata($userid);
    $meta = get_user_meta($userid);

    $erp_customer_id = '';
    if(isset($meta['erp_customer_id'][0]) && $meta['erp_customer_id'][0] != ''){
        $erp_customer_id = $meta['erp_customer_id'][0];
    }

    
    if($erp_customer_id == '' || empty($erp_customer_id)) {  
        $erpuser = array();
        $erpuser['active'] = 1;
        $erpuser['customerType'] = 1;
        $erpuser['postingGroup'] = 6;
        $erpuser['postingGroupVat'] = 2;
        $erpuser['customerNo'] = 0;
        $erpuser['customerStatus'] = null;
        $erpuser['customerGroup'] = null;
        $erpuser['priority'] = 0;
        $erpuser['language'] = "NL";
        $erpuser['orderConfirmationPreOrder'] = 0;
        $erpuser['orderConfirmationDirectOrder'] = 0;
        $erpuser['invoiceInterval'] = 0;
        $erpuser['name'] = $meta['first_name'][0]." ".$meta['last_name'][0];
        $erpuser['companyName'] = $meta['billing_company'][0];
        $erpuser['legalCompanyName'] = $meta['billing_company'][0];
        $erpuser['website'] = $user->data->user_url;
        $erpuser['email'] = $user->data->user_email;
        $erpuser['creditLimit'] = "0.00";
        
        $add = array();
        $add['contactPerson'] = $meta['first_name'][0]." ".$meta['last_name'][0];
        $add['housenumber'] = $meta['billing_address_1'][0];
        $add['street'] = $meta['billing_address_1'][0]. " " .$meta['billing_address_2'][0];
        $add['housenumberExtension'] = $meta['billing_address_1'][0];;
        $add['postalCode'] = $meta['billing_postcode'][0];
        $add['city'] = $meta['billing_city'][0];
    
        //getting countryid for country
        $curl = curl_init();
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => $GLOBALS['apiStart']."/api/v2/countries/?token=".$GLOBALS['token'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: 86b8e7d1-1a55-4502-931f-1ff462f9476c",
                "cache-control: no-cache"
            ),
        ));
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
    
        curl_close($curl);
    
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $countryid = 2;
            $countriesdata = json_decode($response);
            foreach($countriesdata->countries as $key=>$country){
                if($country->iso2 == $meta['billing_country'][0]){
                    $countryid = $country->id;
                }
            }
        }
    
    
        $add['countryId'] = $countryid;
        $add['phone'] = $meta['billing_phone'][0];
        $add['mobile'] = $meta['billing_phone'][0];
        $add['email'] = $user->data->user_email;
    
        $erpuser['addresses'][1] = $add;
    
        $erpuserstring = json_encode($erpuser , JSON_UNESCAPED_SLASHES );
    
        //create this user in erp system.
        $curl = curl_init();
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => $GLOBALS['apiStart']."/api/v2/customers/&token=".$GLOBALS['token'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "$erpuserstring",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Postman-Token: 14b8476f-258d-4ec2-b847-96f644fd1e70",
                "cache-control: no-cache"
            ),
        ));
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
    
        curl_close($curl);
    
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $data = json_decode($response);
            $id = $data->insertId;
    
            $curl = curl_init();
    
            curl_setopt_array($curl, array(
                CURLOPT_URL => $GLOBALS['apiStart']."/api/v2/customers/$id/&token=".$GLOBALS['token'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 900000000000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{  \n      \"brand\": {\n                \"id\": 1\n            }\n}",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Postman-Token: 00a54439-a13e-420a-8189-bd5940ed33ca",
                    "cache-control: no-cache"
                ),
            ));
    
            $response = curl_exec($curl);
            $err = curl_error($curl);
    
            curl_close($curl);
    
    
            if ($err) {
                
                echo "cURL Error #:" . $err;
                $resulterror = json_encode($response);
                $query = "INSERT INTO {$wpdb->base_prefix}erp_sync_logs (`module`,`url_requested`,`error_code`) values ('customer_mapping_erp','$erpuserstring','$resulterror') ";
                $wpdb->query($query);

            } else {
                $resulterror = json_encode($response);

                $query = "INSERT INTO {$wpdb->base_prefix}erp_sync_logs (`module`,`url_requested`,`error_code`) values ('customer_mapping_erp','$erpuserstring','$resulterror') ";
                $wpdb->query($query);

                $erp_customer_id = $id;
    
                update_user_meta($userid,'erp_customer_id',$id);
    
                return $erp_customer_id;
            }
        }
    }
    
    return $erp_customer_id;
}



/**
 * Adding a new column(itsperfect order id and status) in wordpress order table
 */
function wc_new_order_column( $columns ) {
    $columns['my_column'] = 'My column';
    return $columns;
   add_filter( 'manage_edit-shop_order_columns', 'wc_new_order_column' );
}

/**
 * Adds 'Profit' column header to 'Orders' page immediately after 'Total' column.
 *
 * @param string[] $columns
 * @return string[] $new_columns
 */
function sv_wc_cogs_add_order_profit_column_header( $columns ) {

    $new_columns = array();
   
    foreach ( $columns as $column_name => $column_info ) {
   
        $new_columns[ $column_name ] = $column_info;
    
        if ( 'order_status' === $column_name ) {
            $new_columns['erp_order_id'] = __( 'Itsperfect ID', 'my-textdomain' );
            $new_columns['erp_order_status'] = __( 'Sync Status', 'my-textdomain' );
        }
    }
   
    return $new_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'sv_wc_cogs_add_order_profit_column_header', 20 );



if ( ! function_exists( 'sv_helper_get_order_meta' ) ){
    /**
    * Helper function to get meta for an order.
    *
    * @param WC_Order $order the order object
    * @param string $key the meta key
    * @param bool $single whether to get the meta as a single item. Defaults to `true`
    * @param string $context if 'view' then the value will be filtered
    * @return mixed the order property
    */
    function sv_helper_get_order_meta( $order, $key = '', $single = true, $context = 'edit' ) {
   
    // WooCommerce > 3.0
    if ( defined( 'WC_VERSION' ) && WC_VERSION && version_compare( WC_VERSION, '3.0', '>=' ) ) {
   
    $value = $order->get_meta( $key, $single, $context );
   
    } else {
   
    // have the $order->get_id() check here just in case the WC_VERSION isn't defined correctly
    $order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
        $value = get_post_meta( $order_id, $key, $single );
    }
   
    return $value;
    }   
}

/**
 * Adds 'Profit' column content to 'Orders' page immediately after 'Total' column.
 *
 * @param string[] $column name of column being displayed
 */
function sv_wc_cogs_add_order_profit_column_content( $column ) {
    global $post;
   
    if ( 'erp_order_id' === $column ) {
        $erp_order_id = get_post_meta($post->ID,'erp_order_id');
        if(isset($erp_order_id[0])){
            $erp_order_id = $erp_order_id[0];
        }

        if(empty($erp_order_id) || $erp_order_id == ''){
            echo '-';
        }
        else{
            echo $erp_order_id;
        }
    }

    if ( 'erp_order_status' === $column ) {
        $erp_order_status = get_post_meta($post->ID,'erp_order_status');
        if(isset($erp_order_status[0])){
            $erp_order_status = $erp_order_status[0];
        }

        if(empty($erp_order_status) || $erp_order_status == ''){
            echo '<span class="btn btn-sm btn-danger">Not synced</span>';
        }
        else{
            if($erp_order_status == 'synced') {
                echo '<span class="btn btn-sm btn-success">Synced</span>';
            }
            else{
                echo '<span class="btn btn-sm btn-danger">';
                print_r($erp_order_status);
                echo '</span>';
            }
        }
    }
}
add_action( 'manage_shop_order_posts_custom_column', 'sv_wc_cogs_add_order_profit_column_content' );


add_filter( 'woocommerce_admin_order_actions', 'add_custom_order_status_actions_button', 100, 2 );
function add_custom_order_status_actions_button( $actions, $order ) {

    if ( $order->has_status( array( 'processing' ) ) ) {

        // The key slug defined for your action button
        $action_slug = 'invoice';
         $status = $_GET['status'];
         $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
        // Set the action button
        $actions[$action_slug] = array(
            'url'       => wp_nonce_url(admin_url('admin-ajax.php?action=wip_pdf_generator&status=invoice'.$status.'&order_id=' . $order_id), 'wip_pdf_generator'),
            'name'      => __( 'Invoice', 'woocommerce' ),
            'action'    => $action_slug,
        );
    }
    return $actions;
}


/**
 * Adding resync button to the order edit page.
 */
add_action('add_meta_boxes_woocommerce_page_wc-orders', 'add_custom_other_field_content', 10);


add_meta_box( 'custom_other_field', __('Itsperfect Sync','woocommerce'), 'add_custom_other_field_content', 'shop_order', 'side', 'high' );
if ( ! function_exists( 'add_custom_other_field_content' ) )
{
    function add_custom_other_field_content( $post )
    {
        $meta = $post->get_meta('_mollie_payment_instructions');
        
        $screen = wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
            ? wc_get_page_screen_id('shop-order')
            : 'shop_order';

        $order = wc_get_order($post->ID); // Get the WC_Order object

        $erp_order_status = get_post_meta($post->ID,'erp_order_status');
        if(isset($erp_order_status[0])){
            $erp_order_status = $erp_order_status[0];
        }
        $erp_order_id = get_post_meta($post->ID,'erp_order_id');
        if(isset($erp_order_id[0])){
            $erp_order_id = $erp_order_id[0];
        }
        if(empty($erp_order_id)){
            $erp_order_id = '-';
        }
        if(empty($erp_order_status)){
            $erp_order_status = 'unsynced';
        }
        
        $gif =  get_site_url()."/wp-content/plugins/itsperfect/ajax-loader.gif";
        
        add_meta_box('custom_order_fields', __('Itsperfect Resync order', 'itsperfect-resync-order'), static function () use ($meta,$post,$gif,$erp_order_id,$erp_order_status) {
            $allowedTags = ['strong' => []];
            if($erp_order_id != '' && $erp_order_status == 'synced'){
                printf('<button id="'.$post->ID.'" class="button button-primary resync_order">Re-sync</button>
                <img src="'.$gif.'" class="gif" style="display:none">') ;
            }else { 
                return '<button id="'.$post->ID.'" class="button button-primary sync_order">Sync</button><img src="'.$gif.'" class="gif" style="display:none"> <br><br> This order is not yet synced !';
            }
        }, $screen, 'side', 'high');
    }
}
/**
 * Stock refresh job 
 */
$guard->validCallback(function() {

add_action('wp_ajax_update_stock','kp_update_stock');
function kp_update_stock(){
    $t = 0;

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $GLOBALS['apiStart']."/api/v2/stock/?token=".$GLOBALS['token'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,//mylocal
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
            "Postman-Token: d4c06e6d-16a3-4e51-b290-3c33b3464c2f",
            "cache-control: no-cache"
        ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $data = json_decode($response);
        $stock = $data->stock;
        $final_item_stock = array();
        foreach($stock as $key=>$item_stock){
            if(isset($item_stock->warehouses[0])){
                $final_item_stock[$item_stock->itemId] = $item_stock->warehouses[0]->colors;
            }
        }
    }
    
    if(!empty($final_item_stock)){
        $products = wc_get_products( 
                    array( 
                        'status' => 'publish,draft',
                        'limit' => -1 
                    )
                );
    
        foreach($products as $key=>$product){
            $erp_id = $product->get_attribute('erp_product_id');
            $color = $product->get_attribute('color');
            $product_color = trim_color($color);
    
            $i = 0;
            if(isset($final_item_stock[$erp_id])){
                foreach($final_item_stock[$erp_id] as $key=>$colorobj){
                    $stock_color = trim_color($colorobj->color);
                    if($stock_color == $product_color){
                        $variations = $product->get_children();
                        foreach($variations as $key=>$variation_id){
                            $var_product = wc_get_product($variation_id);
                            $var_size = $var_product->get_attribute('size');
                            if(!empty($var_size)){
                                $var_size = trim_color($var_size);
                                foreach($colorobj->sizes as $key=>$sizeobj){
                                    $stock_size = trim_color($sizeobj->size);
                                    if($stock_size == $var_size){
                                        if($sizeobj->availableStock > 0){
                                            $i++;
                                        }
                                        update_post_meta($variation_id,'_manage_stock','yes');
                                        update_post_meta($variation_id, '_stock', $sizeobj->availableStock);
                                    }
                                }
                            }
                        }
                    }
                }
            }
    
            if($i > 0){
                update_post_meta($product->get_id() , '_stock_status','instock');
            }
            else{
                update_post_meta($product->get_id() , '_stock_status','outofstock');
            }
            
            $t++;
        }
    }
    
    echo $t." Total product's stock updated ! ";

    $message = $t." Total product's stock updated at ".get_site_url();

    $slack_request = array();
    $slack_request["channel"] = "tetresponsible_stock_updates";
    $slack_request["blocks"] = array();
    $temp["type"]  = "section";
    $temp["text"]["type"] = "mrkdwn";
    $temp["text"]["text"] = "`$message`";
    array_push($slack_request["blocks"],$temp);

    $slack_request = json_encode($slack_request);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://slack.com/api/chat.postMessage",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>"$slack_request",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Bearer xoxb-1551726777605-1553661642502-pPFKfCjcOkOJtZqquEnX6tzC"
        ),
    ));

    $slack_response = curl_exec($curl);

    curl_close($curl);

    wp_die();
}

});

/**
 * Register an product-custom-attribute taxonomy.
 */
function kp_create_attribute_taxonomies() {

    $attributes = wc_get_attribute_taxonomies();

    $slugs = wp_list_pluck( $attributes, 'attribute_name' );

    if ( ! in_array( 'pa_color', $slugs ) ) {
        $args = array(
            'slug'    => 'pa_color',
            'name'   => __( 'color', 'your-textdomain' ),
            'type'    => 'select',
            'orderby' => 'menu_order',
            'has_archives'  => false,
        );

        $result = wc_create_attribute( $args );
    }
    if ( ! in_array( 'pa_size', $slugs ) ) {
        $args = array(
            'slug'    => 'pa_size',
            'name'   => __( 'size', 'your-textdomain' ),
            'type'    => 'select',
            'orderby' => 'menu_order',
            'has_archives'  => false,
        );

        $result = wc_create_attribute( $args );
    }
    if ( ! in_array( 'pa_erp_product_id', $slugs ) ) {
        $args = array(
            'slug'    => 'pa_erp_product_id',
            'name'   => __( 'erp_product_id', 'your-textdomain' ),
            'type'    => 'select',
            'orderby' => 'menu_order',
            'has_archives'  => false,
        );

        $result = wc_create_attribute( $args );
    }
}




/**
* Set Default variations job
*/
$guard->validCallback(function() {

add_action('wp_ajax_set_default_variations','set_default_variations');
function set_default_variations(){
    $t = 0;

    $products = wc_get_products( 
        array( 
            'status' => 'publish,draft',
            'limit' => -1 
        )
    );

    foreach($products as $key=>$product){
        if($product->get_type() != 'pw-gift-card'){
            $variations = $product->get_children();
            if(!empty($variations)){
                foreach($variations as $key=>$variation_id){
                    $var_product = wc_get_product($variation_id);
                    $meta = get_post_meta($variation_id);
                    $stock_quantity = $meta['_stock'][0];
                    if($stock_quantity > 0){

                        $size_value = $meta['attribute_pa_size'][0];

                        if($size_value){
                            $new_defaults = array();
                        
                            $new_defaults['pa_size'] = sanitize_key($size_value);
                            
                            update_post_meta($product->get_id(), '_default_attributes', $new_defaults);

                            $t++;
                            break;
                        }

                    }
                }
            }
        }
    }

    echo "Total :". $t." products have default variations now.";
    wp_die();
}


});
/**
 * Function that shows other colors of the products on the PDP
 */
add_action('woocommerce_before_add_to_cart_form','show_colors_on_pdp',30);
function show_colors_on_pdp(){
    global $product;
    $originalid = $product->get_id();

    $erpid = $product->get_attribute('erp_product_id');

    // The query
    $temp_products = new WP_Query( array(
        'post_type'      => array('product'),
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array( array(
            'key' => 'meta_erp_product_id',
            'value' => array($erpid),
            'compare' => 'IN',
        ) )
    ) );

    $product_ids = array();
    
    // The Loop
    if ( $temp_products->have_posts() ){
        while ( $temp_products->have_posts() ){
            $temp_product = $temp_products->the_post();
            $meta_erp = get_post_meta($temp_products->post->ID,'meta_erp_product_id');
            if(isset($meta_erp[0])){
                $product_ids[] = $temp_products->post->ID;
            }
        }
        wp_reset_postdata();
    }
   
    //just for style
    echo '<style>
            a.custom_atag{
                margin:10px;
            }
            .custom_colors_pdp{
                margin-left: -10px !important;
                margin: 20px;
            }
        </style>';
    
    
    if(!empty($product_ids)){
        echo '<div class="custom_colors_pdp">';
        if(count($product_ids) > 1){
            echo '<span class="wooma-selected-variations-terms-title" style="margin-left: 10px;">Colors</span> <br/>';    
        }
        
        $temp_productids = $product_ids;
        foreach($product_ids as $key=>$productid){
            $color_var_product = wc_get_product($productid);
            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $productid ), 'single-post-thumbnail' );
            if($productid == $originalid){
                $url = get_permalink( $productid );
                echo '<a class="custom_atag" target="_blank" href="'.$url.'"><img src="'.$image[0].'" width="100" height="100" style="border:1px solid black;" /> </a>';
            }
        }
        
        foreach($temp_productids as $key=>$productid){
            $color_var_product = wc_get_product($productid);
            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $productid ), 'single-post-thumbnail' );
            if($productid != $originalid){
                $url = get_permalink( $productid );
                echo '<a class="custom_atag" target="_blank" href="'.$url.'"><img src="'.$image[0].'" width="100" height="100" /></a>';
            }
        }
        echo '</div>';
    }
}



add_shortcode('password_protection','password_protection');
function password_protection(){
    $html = '<style>
    ::placeholder {
        color: #fff;
        font-weight: 500;
    }
    body, input, select, textarea, button{
        font-family: "Source Sans Pro", "sans-serif";
        font-weight: 500;
    }

    </style>';

    $html .= '<input style="margin-top : 5%" type="text" name="password_protection" id="password_protection" placeholder="Enter Password" />';

    $html .= '

    <a style="padding-top: 0px;
    padding-left: 20px;
    padding-right: 20px;
    padding-bottom: 0px;
    border : 1px solid black;
    font-weight: 500;
    color : #000000;
    " class="elementor-button elementor-button-link elementor-size-lg" href="javascript:void(0);" id="enter_password_protection">
                        <span class="elementor-button-content-wrapper">
                        <span class="elementor-button-text">SUBMIT</span>
        </span>
                    </a>
                    <br>
                    <span id="incorrect_pwd" style="font-weight: 600;display:none;color:#ffffff">Incorrect password !!</span>';

    return $html;
}
?>