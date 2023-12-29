<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(__FILE__) . '/');

include_once(ABSPATH . 'wp-config.php');
include_once(ABSPATH . 'wp-includes/class-wpdb.php');
global $wpdb;

$type = "";
$id = "";
if(isset($_POST['type'])){
    $type = $_POST['type'];
}
if(isset($_POST['id'])){
    $id = $_POST['id'];
}

$item = $order = $pick =  $return = 0;
$query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'webhooks'";
$wc_auth = $wpdb->get_results($query);
if(empty($wc_auth)){
    $webhook = '';
}
else{
    $webhook = explode(",",$wc_auth[0]->setting_value);
    if(in_array('item',$webhook))
    {
        $item = 1;
    }
    if(in_array('order',$webhook))
    {
        $order = 1;
    }
    if(in_array('pick',$webhook))
    {
        $pick = 1;
    }
 
    if(in_array('return',$webhook))
    {
        $return = 1;
    }
 
 
  }

$post_encode = json_encode($_POST);

$slack_request = array();
$slack_request["channel"] = "tetresponsible_webhooks";
$slack_request["blocks"] = array();
$temp["type"]  = "section";
$temp["text"]["type"] = "mrkdwn";
$temp["text"]["text"] = "`webhook listner triggered at `".get_site_url()." and type = ".$type." and id = ".$id." POST DATA : ".$post_encode;
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

echo "Webhook listner triggered!";

// $id = 1244444;
// $type = 'item';

if($type == "item" && $item == 1){
    if(function_exists('update_product')){
        $products = new WP_Query( array(
            'post_type'      => array('product'),
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array( array(
                'key' => 'meta_erp_product_id',
                'value' => array($id),
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

        if(!empty($product_ids)){
            foreach($product_ids as $wp_product_id=>$erp_product_id){
                update_product($wp_product_id);
            }
        }

    }
}

if($type == "order" && $order == 1){
    if(function_exists('sync_order_status')){
        echo "order id triggered".$id;
    }
}


if($type == "pick" && $pick == 1){


  
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $GLOBALS['apiStart']."/api/v2/picks/$id/?token=".$GLOBALS['token'],
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
        echo "cURL Error #:" . $err;
        die;
    } else {
        $data = json_decode($response);
      
        if(!empty($data)){
            $tracking_number = $carrier = $order_id = $trackingUrl = $returnNumber = '';
            $pick = $data->picks[0];
            if($pick->status == 2){ //status 2 means Delivery note (picked)
                if(isset($pick->shippingNumber)){
                    $tracking_number = $pick->shippingNumber;
                }
                if(isset($pick->trackingUrl)){
                    $trackingUrl = $pick->trackingUrl;
                }
                if(isset($pick->returnNumber)){
                    $returnNumber = $pick->returnNumber;
                }
                if(isset($pick->deliveryCondition->deliveryCondition)){
                    $carrier = $pick->deliveryCondition->deliveryCondition;
                }

                //only if we get blank from $pick->deliveryCondition->deliveryCondition;
                if(isset($pick->expeditor->expeditor) && empty($carrier)){
                    $carrier = $pick->expeditor->expeditor;
                }
                
                if(isset($pick->orderIds)){
                    $order_id = $pick->orderIds;
                }
               // $order_id = 29102;
             

                if($tracking_number != '' && $carrier != '' && $order_id != ''){
                    // echo $tracking_number."<br>".$carrier."<br>".$order_id;die;
                    $sql = "SELECT * FROM {$wpdb->base_prefix}postmeta where meta_key = 'erp_order_id' and meta_value = ".$order_id;
                   
                    $result = $wpdb->get_results($sql);

                    $wp_order_id = 0;
                    if(!empty($result)){
                        $wp_order_id = $result[0]->post_id;
                    }

                    if($wp_order_id){
                        $order_meta = get_post_meta($wp_order_id);
                        $order = wc_get_order($wp_order_id);
                        $order = $order->get_data();
                      
                        
                        $carrier = strtolower($carrier);

                        $final_tracking = $tracking_info = array();

                        $tracking_info['tracking_id'] = md5("{$carrier}-{$tracking_number}");
                        $tracking_info['tracking_number'] = $tracking_number;
                        $tracking_info['slug'] = $carrier;

                        $tracking_info['line_items'] = array();

                        foreach($order['line_items'] as $key=>$item){
                            $item = $item->get_data();
                            $temp = array();
                            $temp['id'] = $item['id'];
                            $temp['quantity'] = $item['quantity'];
                            $tracking_info['line_items'][] = $temp;
                        }

                        $tracking_info['additional_fields']['account_number'] = '';
                        $tracking_info['additional_fields']['key'] = '';
                        $tracking_info['additional_fields']['postal_code'] = '';
                        $tracking_info['additional_fields']['ship_date'] = '';
                        $tracking_info['additional_fields']['destination_country'] = '';
                        $tracking_info['additional_fields']['state'] = '';

                        $tracking_info['metrics'] = array(
                            'created_at' => current_time( 'Y-m-d\TH:i:s\Z' ),
                            'updated_at' => current_time( 'Y-m-d\TH:i:s\Z' ),
                        );

                        $final_tracking[] = $tracking_info;

                        update_post_meta( $wp_order_id, '_aftership_tracking_items', $final_tracking );
                        update_post_meta( $wp_order_id, '_aftership_tracking_number', $tracking_number);
                        update_post_meta( $wp_order_id, '_aftership_tracking_provider_name', $carrier);

                        update_post_meta( $wp_order_id, 'shippingNumber', $tracking_number);
                        update_post_meta( $wp_order_id, 'trackingUrl', $trackingUrl);
                        update_post_meta( $wp_order_id, 'returnNumber', $returnNumber);
                        
                        $order = wc_get_order($wp_order_id);
                        $order->set_status('completed');
                        $order->save();


                        $temp = array();
                        $temp['tracking_number'] = $tracking_number;
                        $temp['carrier'] = $carrier;
                        $temp['order_id'] = $order_id;
                        $temp['pick_id'] = $id;
                        $new_temp = json_encode($temp);
                        $note = __("Successfully updated order #".$order_id." <br>  ".$new_temp);
                      
                        $order->add_order_note( $note );
                        
                    }
                   
                }
                else{
                    $order = wc_get_order(  $order_id );
                  
                    $temp = array();
                    $temp['tracking_number'] = $tracking_number;
                    $temp['carrier'] = $carrier;
                    $temp['order_id'] = $order_id;
                    $temp['pick_id'] = $id;
                    $new_temp = json_encode($temp);
                    $note = __("Attempted to update order #".$order_id." <br> ". $new_temp);
                    $order->add_order_note( $note );
                }
            }
        }
    }
}



if($type == 'return__TEMP_DISABLED' && $return == 1){

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $GLOBALS['apiStart']."/api/v2/returns/".$id."/?token=".$GLOBALS['token'],
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

    $return_data = json_decode($response);
    // echo '<pre>';print_r($return_data);die;
    if(isset($return_data->returns)){
        $return_obj = $return_order = $return_data->returns[0];

        if($return_obj->webshopProcessed == 0 && $return_obj->autorisation == 0){
            //autorisation status = 0 => Waiting for receipt
            //autorisation status = 1 => Received
            $its_erp_order_id = array();

            foreach($return_obj->items as $key=>$item){
                $its_erp_order_id[] = $item->orderId;
            }
            // echo '<pre>';print_r($its_erp_order_id);die;

            if(!empty($its_erp_order_id)){
                foreach($its_erp_order_id as $key=>$erp_order_id){

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                    CURLOPT_URL => $GLOBALS['apiStart']."/api/v2/orders/$erp_order_id/?token=".$GLOBALS['token'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Cookie: SERVERID=s1'
                    ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $order_data = json_decode($response);
                    $order_data = $order_data->orders;

                    $order_data = $order_data[0];



                    $sql = "SELECT * FROM {$wpdb->base_prefix}postmeta where meta_key = 'erp_order_id' and meta_value = ".$erp_order_id;
                    $result = $wpdb->get_results($sql);
                
                    if(isset($result[0])){
                        $result = $result[0];
                        
                        $wp_order_id = $result->post_id;

                        

                        foreach($return_order->items as $key=>$return_item){

                            if($wp_order_id){
                                  $order  = wc_get_order( $wp_order_id );
                                  
                                  $note = __("Return received, return id #".$id." <br> and itsperfect order id is #". $erp_order_id);
                                  $order->add_order_note( $note );

                                  // echo "<pre>";print_r($order);die;

                                  // Get Items
                                  $order_items   = $order->get_items();
                                  
                                  // Refund Amount
                                  $refund_amount = 0;

                                  //Total Amount
                                  $mytotal = 0;

                                  // Prepare line items which we are refunding
                                  $line_items = array();

                                  if ( $order_items ) {
                                    // echo "<pre>";print_r($order_items);die;
                                    foreach( $order_items as $item_id => $item ){
                                      $item_data = $item->get_data();

                                      // echo "<pre>";print_r($item_data);die;
                                      foreach($item_data['meta_data'] as $key=>$meta_obj){
                                        $meta_obj = $meta_obj->get_data();
                                        if($meta_obj['key'] == 'pa_size'){
                                            $item_size = $meta_obj['value'];
                                        }
                                      }

                                      $product = wc_get_product($item_data['product_id']);
                                      
                                      $item_color = $product->get_attribute('color');

                                      $item_color = strtolower(str_replace("-", "_", $item_color));
                                      $item_color = str_replace(" ","",$item_color);
                                      $item_color = str_replace("'","",$item_color);
                                      $item_color = str_replace(",","",$item_color);

                                      $erp_item_id = $product->get_attribute('erp_product_id');

                                      $item_meta = $order->get_item_meta( $item_id );

                                      // $variation_id = $item_meta['_variation_id'][0];
                                      // $variation_meta = get_post_meta($variation_id);
                                      // $item_size = $variation_meta['attribute_pa_size'][0];

                                      $item_size = strtolower(str_replace("-", "_", $item_size));
                                      $item_size = str_replace(" ","",$item_size);
                                      $item_size = str_replace("'","",$item_size);
                                      $item_size = str_replace(",","",$item_size);
                                      // echo "hello:".$item_size;die;

                                      // $barcode = $variation_meta['hwp_var_gtin'][0];

                                      $return_item_color = $return_item->color;
                                      $return_item_color = strtolower(str_replace("-", "_", $return_item_color));
                                      $return_item_color = str_replace(" ","",$return_item_color);

                                      $return_item_id = $return_item->itemId;

                                      $return_item_size = $return_item->size;
                                      $return_item_size = strtolower(str_replace("-", "_", $return_item_size));
                                      $return_item_size = str_replace(" ","",$return_item_size);

                                      // echo $item_size."<br>".$return_item_size;die;

                                      if($return_item_id == $erp_item_id && $item_color == $return_item_color && $item_size == $return_item_size){

                                          $tax_data = $item_meta['_line_tax'];

                                          $refund_tax = 0;

                                          if( is_array( $tax_data ) ) {
                                            $refund_tax = $tax_data[0];
                                          }

                                          $refund_amount = wc_format_decimal( $refund_amount ) + wc_format_decimal( $item_meta['_line_total'][0] );

                                          $refund_amount = $refund_amount + $refund_tax;

                                          $mytotal = $mytotal + $refund_amount;

                                          $line_items[ $item_id ] = array(
                                            'qty' => $item_meta['_qty'][0],
                                            'refund_total' => wc_format_decimal($item_meta['_line_total'][0]),
                                            'refund_tax' =>  $refund_tax
                                          );

                                        // echo "<pre>";print_r($line_items);die;

                                      }
                                    }
                                  }

                                $refund_reason = "Customer Returned Item";

                                //echo "<pre>";print_r($line_items);die;

                                $refund_amount = number_format((float)$refund_amount, 2, '.', '');

                                $note = __("Return request prepared <br> refund amount :".$refund_amount." <br> refund reason". $refund_reason." <br> wp order id : ".$wp_order_id."<br> Line items : ".json_encode($line_items));

                                $order->add_order_note( $note );

                                // echo 'Amount : '.$refund_amount."<br> reason :".$refund_reason."<br> wp_order_id : ".$wp_order_id;
                                // echo "<br><br>";
                                // echo "<pre>";print_r($line_items);
                                // die;

                                $refund = wc_create_refund( array(
                                    'amount'         => $refund_amount,
                                    'reason'         => $refund_reason,
                                    'order_id'       => $wp_order_id,
                                    'line_items'     => $line_items,
                                    'refund_payment' => true
                                ));

                                // echo "<pre>";print_r($refund);die;

                                

                                if(is_wp_error($refund)){
                                    $headers = array('Content-Type: text/html; charset=UTF-8');
                                    $admin_email = "sagar22.shah@gmail.com,kushalpatel2014@gmail.com";
                                    $message = sprintf( __( '<h3>Return webhook processing failed.</h3>.' ) ) . "\r\n\r\n";
                                    $message .= sprintf( __( 'Return id #'.$id.' is not processed with webhook.<br><br>' ) ) . "\r\n\r\n";
                                    wp_mail( $admin_email, sprintf( __( 'Return webhook processing failed.' )), $message,  $headers);

                                    $note = __("Refund request's response : ".json_encode($refund));
                                    $order->add_order_note( $note );


                                    $note = __("Return request failed (line-items): ".json_encode($line_items));

                                    $order->add_order_note( $note );
                                }
                                else{
                                    $note = __("Refund request's response : ".json_encode($refund));
                                    $order->add_order_note( $note );

                                    $note = __("Calling itsperfect api to mark return as webshopProcessed=1");
                                    $order->add_order_note( $note );

                                    $curl = curl_init();

                                    curl_setopt_array($curl, array(
                                      CURLOPT_URL => $GLOBALS['apiStart']."/api/v2/returns/".$id."/?token=".$GLOBALS['token'],
                                      CURLOPT_RETURNTRANSFER => true,
                                      CURLOPT_ENCODING => '',
                                      CURLOPT_MAXREDIRS => 10,
                                      CURLOPT_TIMEOUT => 0,
                                      CURLOPT_FOLLOWLOCATION => true,
                                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                      CURLOPT_CUSTOMREQUEST => 'PUT',
                                      CURLOPT_POSTFIELDS =>'{
                                        "webshopProcessed": 1
                                    }',
                                      CURLOPT_HTTPHEADER => array(
                                        'Content-Type: application/json',
                                        'Cookie: SERVERID=s2'
                                      ),
                                    ));

                                    $response = curl_exec($curl);

                                    curl_close($curl);

                                    $note = __("return itsperfect id : ".$id." should be marked as webshopProcessed=1");
                                    $order->add_order_note( $note );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}



$slack_request = array();
$slack_request["channel"] = "tetresponsible_webhooks";
$slack_request["blocks"] = array();
$temp["type"]  = "section";
$temp["text"]["type"] = "mrkdwn";
$temp["text"]["text"] = "ID : ".$id." and type :".$type." is processed! ";
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

echo "Its over !";